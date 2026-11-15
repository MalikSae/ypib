<?php

namespace App\Livewire\Admin;

use App\Models\Reward;
use App\Livewire\Concerns\AuthorizesAccess;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Tab "Detail transaksi" di halaman Komisi. Filter status langsung menyaring dan tersimpan di URL. */
class RewardDetailList extends Component
{
    use AuthorizesAccess, WithPagination;

    private const STATUSES = ['pending', 'approved', 'disbursed'];

    #[Url(except: '')]
    public string $status = '';

    public function boot(): void
    {
        $this->authorizeGlobalStaff();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $rewards = Reward::with(['referrer.user', 'registration.user', 'approvedBy'])
            ->when(in_array($this->status, self::STATUSES, true), fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.reward-detail-list', ['rewards' => $rewards]);
    }
}
