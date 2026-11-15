{{--
    Keadaan kosong standar untuk tabel, daftar kartu, dan panel.
    Di tabel:  <tr><td colspan="5" class="px-4"><x-empty-state title="Belum ada kampus." /></td></tr>
    Dengan keterangan: <x-empty-state :title="$hasFilters ? 'Tidak ada yang cocok.' : 'Belum ada data.'" :description="$hasFilters ? 'Ubah kata kunci atau hapus filter.' : null" />
--}}
@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'py-12 text-center']) }}>
    <p class="text-sm font-medium text-neutral-900">{{ $title }}</p>
    @if(filled($description))
        <p class="mt-1 text-xs text-neutral-500">{{ $description }}</p>
    @endif
</div>
