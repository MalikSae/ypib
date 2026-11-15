<?php

namespace App\Livewire\Admin;

use App\Models\PmbPeriod;
use App\Models\ProspectEvent;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Daftar event dan kampanye beserta corongnya. Filter langsung menyaring dan tersimpan di URL. */
class EventList extends Component
{
    use WithPagination;

    private const STAFF_ROLES = ['admin', 'operator'];

    /** Pilihan urutan: kunci URL => [label, kolom]. */
    private const SORTS = [
        'terbaru' => ['Terbaru', 'prospect_events.id'],
        'prospek' => ['Prospek terbanyak', 'prospects_count'],
        'daftar'  => ['Daftar terbanyak', 'registered_count'],
    ];

    #[Url(except: '')]
    public string $jenis = '';

    /** Event tanpa periode hanya tampil di "Semua periode". */
    #[Url(except: '')]
    public string $periode = '';

    #[Url(except: 'terbaru')]
    public string $urut = 'terbaru';

    /** Request pembaruan Livewire tidak melewati middleware rute admin, jadi akses dicek di sini. */
    public function boot(): void
    {
        abort_unless(in_array(auth()->user()?->role, self::STAFF_ROLES, true), 403);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['jenis', 'periode', 'urut'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->jenis = '';
        $this->periode = '';
        $this->urut = 'terbaru';
        $this->resetPage();
    }

    public function render()
    {
        $query = ProspectEvent::with(['campus', 'period'])->withFunnel();

        if (array_key_exists($this->jenis, ProspectEvent::KIND_LABELS)) {
            $query->where('kind', $this->jenis);
        }
        if ($periodId = (int) $this->periode) {
            $query->where('period_id', $periodId);
        }

        $sort = array_key_exists($this->urut, self::SORTS) ? $this->urut : 'terbaru';
        $query->orderByDesc(self::SORTS[$sort][1])->orderByDesc('prospect_events.id');

        return view('livewire.admin.event-list', [
            'events'     => $query->paginate(20),
            'kinds'      => ProspectEvent::KIND_LABELS,
            'periods'    => PmbPeriod::orderByDesc('open_date')->pluck('name', 'id')->all(),
            'sorts'      => array_map(fn ($s) => $s[0], self::SORTS),
            'hasFilters' => $this->jenis !== '' || $this->periode !== '' || $sort !== 'terbaru',
        ]);
    }
}
