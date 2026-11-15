<?php

namespace App\Livewire\Admin;

use App\Models\Prospect;
use App\Models\ProspectEvent;
use App\Models\Referrer;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar prospek untuk follow-up harian. Semua filter langsung menyaring (tanpa tombol Terapkan)
 * dan tersimpan di URL, sehingga tautan dari halaman lain (?event=, ?referrer=) tetap bekerja.
 */
class ProspectList extends Component
{
    use WithPagination;

    private const STAFF_ROLES = ['admin', 'operator'];

    /** Status yang tidak perlu dihubungi lagi: ditaruh di bawah daftar. */
    private const CLOSED_STATUSES = [Prospect::STATUS_TIDAK_TERTARIK, Prospect::STATUS_SUDAH_DAFTAR];

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $event = '';

    /** Hanya diisi lewat tautan (Data Afiliasi, detail prospek); tidak ada kolom isiannya di sini. */
    #[Url(except: '')]
    public string $referrer = '';

    /** Request pembaruan Livewire tidak melewati middleware rute admin, jadi akses dicek di sini. */
    public function boot(): void
    {
        abort_unless(in_array(auth()->user()?->role, self::STAFF_ROLES, true), 403);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'status', 'event', 'referrer'], true)) {
            $this->resetPage();
        }
    }

    public function setStatus(string $status): void
    {
        $this->status = array_key_exists($status, Prospect::STATUS_LABELS) ? $status : '';
        $this->resetPage();
    }

    public function clearReferrer(): void
    {
        $this->referrer = '';
        $this->resetPage();
    }

    /** Hapus Cari, Event, dan Affiliator. Tab status tidak ikut direset. */
    public function clearFilters(): void
    {
        $this->q = '';
        $this->event = '';
        $this->referrer = '';
        $this->resetPage();
    }

    public function render()
    {
        $selectedAffiliator = (int) $this->referrer ? Referrer::with('user')->find((int) $this->referrer) : null;

        // Angka tab mengikuti Cari, Event, dan Affiliator, tetapi tidak mengikuti tab status itu sendiri.
        $counts = $this->filtered($selectedAffiliator)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $query = $this->filtered($selectedAffiliator)->with(['campus', 'program']);

        if (array_key_exists($this->status, Prospect::STATUS_LABELS)) {
            $query->where('status', $this->status);
        }

        // Paling butuh dihubungi di atas: belum pernah dihubungi (terlama masuk dulu), lalu kontak
        // terakhir paling lama. Yang sudah selesai (tidak tertarik, sudah daftar) di bawah.
        $closed = "'" . implode("','", self::CLOSED_STATUSES) . "'";
        $prospects = $query
            ->orderByRaw("case when status in ({$closed}) then 1 else 0 end")
            ->orderByRaw('case when last_contacted_at is null then 0 else 1 end')
            ->orderBy('last_contacted_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate(25);

        return view('livewire.admin.prospect-list', [
            'prospects'          => $prospects,
            'counts'             => $counts,
            'total'              => $counts->sum(),
            'statuses'           => Prospect::STATUS_LABELS,
            'events'             => ProspectEvent::orderByDesc('id')->get(['id', 'name']),
            'selectedAffiliator' => $selectedAffiliator,
            'hasFilters'         => trim($this->q) !== '' || (int) $this->event > 0 || $selectedAffiliator !== null,
        ]);
    }

    /** Prospek sesuai Cari, Event, dan Affiliator (tanpa filter status). */
    private function filtered(?Referrer $affiliator): Builder
    {
        $query = Prospect::query();

        $q = trim($this->q);
        if ($q !== '') {
            $query->where(function (Builder $w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('school_name', 'like', "%{$q}%")
                    ->orWhere('phone_normalized', 'like', "%{$q}%");
            });
        }

        if ($eventId = (int) $this->event) {
            $query->whereHas('sources', fn (Builder $s) => $s->where('prospect_event_id', $eventId));
        }

        if ($affiliator) {
            $query->where('referrer_id', $affiliator->id);
        }

        return $query;
    }
}
