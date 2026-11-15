<?php

namespace App\Livewire\Admin;

use App\Models\Registration;
use App\Support\AdminPeriod;
use App\Livewire\Concerns\AuthorizesAccess;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Arsip pendaftar terhapus. Cari langsung menyaring dan tersimpan di URL. */
class RegistrationTrashList extends Component
{
    use AuthorizesAccess, WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public function boot(): void
    {
        $this->authorizeStaff();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = trim($this->search);

        $registrations = Registration::forPeriod(AdminPeriod::id())->onlyTrashed()
            ->with(['user', 'firstChoiceProgram', 'secondChoiceProgram', 'referrer'])
            ->when($term !== '', fn ($query) => $query->where(fn ($q) => $q->where('registration_number', 'like', "%{$term}%")
                ->orWhere('full_name', 'like', "%{$term}%")))
            ->latest('deleted_at')
            ->paginate(20);

        return view('livewire.admin.registration-trash-list', ['registrations' => $registrations]);
    }
}
