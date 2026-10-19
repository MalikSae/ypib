<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Akun staf: admin, operator, dan panitia. Hanya admin semua kampus (middleware super.admin).
 * Akun tidak dihapus, hanya dinonaktifkan, karena payment_logs.acted_by dan
 * referrer_logs.acted_by wajib menunjuk ke akun yang pernah beraksi.
 */
class StaffController extends Controller
{
    public const ROLES = [
        'admin'    => 'Admin',
        'operator' => 'Operator',
        'panitia'  => 'Panitia',
    ];

    public function index(Request $request)
    {
        $search = \App\Support\QueryInput::string($request, 'search');
        $role = \App\Support\QueryInput::string($request, 'role');
        $campus = \App\Support\QueryInput::string($request, 'campus');

        $staff = User::with('campus')
            ->whereIn('role', array_keys(self::ROLES))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists($role, self::ROLES), fn ($q) => $q->where('role', $role))
            ->when($campus === 'none', fn ($q) => $q->whereNull('campus_id'))
            ->when(ctype_digit($campus), fn ($q) => $q->where('campus_id', (int) $campus))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.staff.index', [
            'staff'    => $staff,
            'roles'    => self::ROLES,
            'campuses' => $this->campuses(),
            'search'   => $search,
            'role'     => $role,
            'campus'   => $campus,
        ]);
    }

    public function create()
    {
        return view('admin.staff.form', [
            'staff'    => new User(['role' => 'panitia', 'is_active' => true]),
            'roles'    => self::ROLES,
            'campuses' => $this->campuses(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role'      => ['required', Rule::in(array_keys(self::ROLES))],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'password'  => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = new User();
        $user->forceFill([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'role'              => $data['role'],
            'campus_id'         => $data['campus_id'] ?? null,
            'is_active'         => true,
            'password'          => $data['password'],
            'email_verified_at' => now(), // akun buatan admin langsung terverifikasi
        ])->save();

        $this->audit('created', $user, ['role' => $user->role, 'campus_id' => $user->campus_id]);

        return redirect()->route('admin.staff.index')->with('success', 'Akun staf berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->ensureStaff($user);

        return view('admin.staff.form', [
            'staff'    => $user,
            'roles'    => self::ROLES,
            'campuses' => $this->campuses(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureStaff($user);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => ['required', Rule::in(array_keys(self::ROLES))],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
        ]);

        $newCampus = $data['campus_id'] ?? null;

        if ($error = $this->violation($user, $data['role'], $newCampus, $user->is_active)) {
            return back()->withInput()->withErrors(['role' => $error]);
        }

        $before = $user->only(['name', 'email', 'role', 'campus_id']);

        $user->forceFill([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'campus_id' => $newCampus,
        ])->save();

        $this->audit('updated', $user, ['before' => $before, 'after' => $user->only(['name', 'email', 'role', 'campus_id'])]);

        return redirect()->route('admin.staff.index')->with('success', 'Akun staf berhasil diperbarui.');
    }

    public function toggle(User $user)
    {
        $this->ensureStaff($user);

        $activate = !$user->is_active;

        if (!$activate && ($error = $this->violation($user, $user->role, $user->campus_id, false))) {
            return back()->with('error', $error);
        }

        $user->forceFill(['is_active' => $activate])->save();

        if (!$activate) {
            \App\Support\StaffSessions::forget($user);
        }

        $this->audit($activate ? 'activated' : 'deactivated', $user);

        return back()->with('success', 'Akun ' . $user->name . ($activate ? ' diaktifkan.' : ' dinonaktifkan.'));
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->ensureStaff($user);

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill(['password' => Hash::make($request->input('password'))])->save();
        \App\Support\StaffSessions::forget($user);

        $this->audit('password_reset', $user);

        return redirect()->route('admin.staff.index')->with('success', 'Password ' . $user->name . ' berhasil direset.');
    }

    private function campuses()
    {
        return Campus::orderByDesc('is_default')->orderBy('name')->get();
    }

    /** Endpoint ini hanya boleh menyentuh akun staf (cegah eskalasi ke/dari pendaftar dan afiliator). */
    private function ensureStaff(User $user): void
    {
        abort_unless(array_key_exists($user->role, self::ROLES), 404);
    }

    /**
     * Pengaman akun. Mengembalikan pesan bila perubahan dilarang:
     * - akun sendiri tidak boleh diubah peran, kampus, atau statusnya
     * - admin global aktif terakhir tidak boleh diturunkan, dibatasi kampus, atau dinonaktifkan
     */
    private function violation(User $target, string $newRole, ?int $newCampus, bool $newActive): ?string
    {
        $changesAccess = $newRole !== $target->role
            || $newCampus !== ($target->campus_id ? (int) $target->campus_id : null)
            || $newActive !== (bool) $target->is_active;

        if (!$changesAccess) {
            return null;
        }

        if ($target->id === auth()->id()) {
            return 'Anda tidak dapat mengubah peran, kampus, atau status akun Anda sendiri.';
        }

        $isGlobalAdmin = $target->role === 'admin' && !$target->campus_id && $target->is_active;
        $staysGlobalAdmin = $newRole === 'admin' && $newCampus === null && $newActive;

        if ($isGlobalAdmin && !$staysGlobalAdmin) {
            $others = User::where('role', 'admin')->whereNull('campus_id')->where('is_active', true)
                ->where('id', '!=', $target->id)->count();

            if ($others === 0) {
                return 'Akun ini adalah admin semua kampus terakhir yang aktif, jadi tidak boleh diturunkan, dibatasi kampus, atau dinonaktifkan.';
            }
        }

        return null;
    }

    private function audit(string $action, User $target, array $extra = []): void
    {
        Log::info('Staff account ' . $action, array_merge([
            'target_id'    => $target->id,
            'target_email' => $target->email,
            'by_user'      => auth()->id(),
        ], $extra));
    }
}
