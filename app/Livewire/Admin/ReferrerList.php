<?php

namespace App\Livewire\Admin;

use App\Models\Referrer;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Daftar affiliator. Cari, jumlah per halaman, dan urutan kolom langsung berlaku dan tersimpan di URL. */
class ReferrerList extends Component
{
    use WithPagination;

    /** Data Afiliasi hanya untuk admin tanpa batasan kampus (sama dengan middleware global.admin). */
    private const STAFF_ROLES = ['admin', 'operator'];

    private const SORT_COLUMNS = ['prospek', 'klik', 'konversi', 'rate', 'reward'];

    private const PER_PAGE = [5, 10, 20, 50, 100];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $sort = '';

    #[Url(except: 'desc')]
    public string $dir = 'desc';

    #[Url(as: 'per_page', except: 20)]
    public int $perPage = 20;

    public function boot(): void
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, self::STAFF_ROLES, true) && ! $user->campus_id, 403);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    /** Klik header: urut turun dulu, klik lagi untuk naik. */
    public function sortBy(string $column): void
    {
        if (! in_array($column, self::SORT_COLUMNS, true)) {
            return;
        }
        $this->dir = $this->sort === $column && $this->dir === 'desc' ? 'asc' : 'desc';
        $this->sort = $column;
        $this->resetPage();
    }

    public function render()
    {
        $query = Referrer::with(['user', 'rewards'])
            ->withCount('prospects')
            ->withSum(['rewards as total_reward' => fn ($q) => $q->whereIn('status', ['approved', 'disbursed'])], 'amount');

        $term = trim($this->search);
        if ($term !== '') {
            // Dikelompokkan agar OR tidak lolos dari filter lain.
            $query->where(function ($w) use ($term) {
                $w->where('code', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            });
        }

        $dir = $this->dir === 'asc' ? 'asc' : 'desc';
        match (in_array($this->sort, self::SORT_COLUMNS, true) ? $this->sort : '') {
            'prospek'  => $query->orderBy('prospects_count', $dir),
            'klik'     => $query->orderBy('total_clicks', $dir),
            'konversi' => $query->orderBy('total_conversions', $dir),
            'rate'     => $query->orderByRaw('(CASE WHEN total_clicks = 0 THEN 0 ELSE total_conversions / total_clicks END) ' . $dir),
            'reward'   => $query->orderBy('total_reward', $dir),
            default    => $query->latest(),
        };

        $perPage = in_array($this->perPage, self::PER_PAGE, true) ? $this->perPage : 20;

        return view('livewire.admin.referrer-list', [
            'referrers' => $query->paginate($perPage),
            'perPageOptions' => array_combine(self::PER_PAGE, array_map('strval', self::PER_PAGE)),
        ]);
    }
}
