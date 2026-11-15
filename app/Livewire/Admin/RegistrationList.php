<?php

namespace App\Livewire\Admin;

use App\Models\Program;
use App\Models\Registration;
use App\Models\Scopes\CampusScope;
use App\Support\AdminPeriod;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\AuthorizesAccess;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Data pendaftar. Cari, status, dan prodi (dropdown) langsung menyaring dan tersimpan di URL. */
class RegistrationList extends Component
{
    use AuthorizesAccess, WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $prodi = '';

    public function boot(): void
    {
        $this->authorizeStaff();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'prodi'], true)) {
            $this->resetPage();
        }
    }

    /** Hapus semua filter: cari, status, dan prodi. */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->prodi = '';
        $this->resetPage();
    }

    public function render()
    {
        $periodId = AdminPeriod::id();
        $term = trim($this->search);

        $registrations = Registration::forPeriod($periodId)
            ->with(['user', 'firstChoiceProgram', 'secondChoiceProgram', 'referrer'])
            ->when($term !== '', fn ($query) => $query->where(fn ($q) => $q->where('registration_number', 'like', "%{$term}%")
                ->orWhere('full_name', 'like', "%{$term}%")))
            ->when(array_key_exists($this->status, Registration::STATUS_LABELS), fn ($q) => $q->where('status', $this->status))
            ->when(ctype_digit($this->prodi), fn ($q) => $q->where('first_choice_program_id', (int) $this->prodi))
            ->latest()
            ->paginate(20);

        // Angka di pilihan status: semua pendaftar periode ini per status (tidak ikut filter lain).
        $statusCounts = Registration::forPeriod($periodId)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status')->toArray();

        // NIK yang dipakai lebih dari satu pendaftaran aktif (tanda "NIK ganda", K7).
        $duplicateNiks = Registration::withoutGlobalScope(CampusScope::class)
            ->whereIn('nik', $registrations->pluck('nik')->filter()->unique())
            ->select('nik')->groupBy('nik')->havingRaw('COUNT(*) > 1')
            ->pluck('nik')->flip()->all();

        return view('livewire.admin.registration-list', [
            'registrations'      => $registrations,
            'statusCounts'       => $statusCounts,
            'totalRegistrations' => array_sum($statusCounts),
            'programs'           => Program::where('is_active', true)->pluck('name', 'id')->all(),
            'duplicateNiks'      => $duplicateNiks,
            'hasFilters'         => $term !== '' || $this->prodi !== '' || $this->status !== '',
        ]);
    }
}
