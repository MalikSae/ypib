<?php

namespace App\Livewire\Admin;

use App\Models\Campus;
use App\Models\User;
use App\Livewire\Concerns\AuthorizesAccess;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Daftar akun staf. Cari, peran, dan kampus langsung menyaring dan tersimpan di URL. */
class StaffList extends Component
{
    use AuthorizesAccess, WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $role = '';

    /** '' = semua, 'none' = akses semua kampus, angka = id kampus. */
    #[Url(except: '')]
    public string $campus = '';

    public function boot(): void
    {
        $this->authorizeSuperAdmin();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'role', 'campus'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->role = '';
        $this->campus = '';
        $this->resetPage();
    }

    public function render()
    {
        $roles = User::STAFF_ROLES;
        $search = trim($this->search);

        $staff = User::with('campus')
            ->whereIn('role', array_keys($roles))
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->when(array_key_exists($this->role, $roles), fn ($q) => $q->where('role', $this->role))
            ->when($this->campus === 'none', fn ($q) => $q->whereNull('campus_id'))
            ->when(ctype_digit($this->campus), fn ($q) => $q->where('campus_id', (int) $this->campus))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.staff-list', [
            'staff'      => $staff,
            'roles'      => $roles,
            'campuses'   => Campus::orderByDesc('is_default')->orderBy('name')->pluck('name', 'id')->all(),
            'hasFilters' => $search !== '' || $this->role !== '' || $this->campus !== '',
        ]);
    }
}
