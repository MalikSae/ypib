<?php

namespace App\Livewire\Panitia;

use App\Models\PaymentLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Riwayat interview milik panitia yang login. Cari langsung menyaring dan tersimpan di URL. */
class InterviewHistory extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    /** Request pembaruan Livewire tidak melewati middleware role:panitia, jadi dicek di sini. */
    public function boot(): void
    {
        abort_unless(Auth::user()?->role === 'panitia', 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = trim($this->search);

        $riwayatInterview = PaymentLog::where('acted_by', Auth::id())
            ->where('action', 'interview_completed')
            ->with(['registration' => fn ($q) => $q->withTrashed()->with('firstChoiceProgram')])
            ->when($term !== '', fn ($query) => $query->whereHas('registration', fn ($q) => $q->where(fn ($w) => $w
                ->where('full_name', 'like', "%{$term}%")
                ->orWhere('registration_number', 'like', "%{$term}%"))))
            ->latest()
            ->paginate(10);

        return view('livewire.panitia.interview-history', ['riwayatInterview' => $riwayatInterview]);
    }
}
