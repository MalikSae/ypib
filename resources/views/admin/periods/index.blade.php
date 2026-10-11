@extends('layouts.admin')
@section('title', 'Periode PMB — Admin PMB YPIB')
@section('page-title', 'Periode PMB')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Periode PMB</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola tahun ajaran: tanggal pendaftaran, masa bayar, tenggat daftar ulang, dan rekening.</p>
    </div>
    <a href="{{ route('admin.periods.create') }}" class="decoration-none shrink-0">
        <button type="button" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-all duration-200 shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Periode
        </button>
    </a>
</div>

{{-- Kartu per periode: tetap terbaca di layar sempit tanpa gulir horizontal --}}
<div class="space-y-4 mb-8">
    @forelse($periods as $period)
        @php
            $badge = match (true) {
                $period->isPublished() => 'bg-success-50 text-success-700 border-success-200',
                $period->isArchived()  => 'bg-warning-50 text-warning-700 border-warning-200',
                default                => 'bg-neutral-100 text-neutral-600 border-neutral-200',
            };
        @endphp
        <div class="bg-white rounded-2xl border border-neutral-200 p-5">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-bold text-neutral-900">{{ $period->name }}</h2>
                        <span class="inline-flex items-center px-2.5 py-1 border rounded-full text-xs font-semibold whitespace-nowrap {{ $badge }}">
                            {{ $period->phaseLabel() }}
                        </span>
                    </div>
                    <div class="text-xs text-neutral-400 mt-0.5">Tahun {{ $period->year }}</div>

                    <dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-neutral-400 uppercase tracking-wide">Masa pendaftaran</dt>
                            <dd class="text-neutral-800">{{ $period->open_date->isoFormat('D MMM Y') }} &ndash; {{ $period->close_date->isoFormat('D MMM Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-neutral-400 uppercase tracking-wide">Batas bukti bayar</dt>
                            <dd class="text-neutral-800">
                                {{ $period->close_date->copy()->addDays((int) $period->payment_grace_days)->isoFormat('D MMM Y') }}
                                <span class="text-xs text-neutral-400">(+{{ $period->payment_grace_days }} hari)</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-neutral-400 uppercase tracking-wide">Tenggat daftar ulang</dt>
                            <dd>
                                @if($period->re_registration_deadline)
                                    <span class="text-neutral-800">{{ $period->re_registration_deadline->isoFormat('D MMM Y') }}</span>
                                @else
                                    <span class="text-warning-700 font-semibold">Belum diisi</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-neutral-400 uppercase tracking-wide">Pendaftar</dt>
                            <dd class="text-neutral-800">
                                <span class="font-semibold">{{ $period->registrations_count }}</span>
                                <span class="text-xs text-neutral-400">({{ $period->running_registrations_count }} berjalan)</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="flex flex-wrap lg:justify-end items-center gap-2 lg:max-w-sm shrink-0">
                    @if(!$period->isArchived())
                        <x-table-action-edit :href="route('admin.periods.edit', $period->id)" />
                    @endif

                    <a href="{{ route('admin.periods.pricing', $period->id) }}" class="decoration-none">
                        <x-button color="primary" variant="outline" size="sm">Biaya &amp; Kuota</x-button>
                    </a>

                    @if($period->status === 'draft')
                        <form action="{{ route('admin.periods.publish', $period->id) }}" method="POST" class="m-0">
                            @csrf
                            <x-button type="submit" color="success" size="sm">Tayangkan</x-button>
                        </form>
                    @endif

                    @if($period->isPublished() && $period->registrations_count === 0)
                        <form action="{{ route('admin.periods.unpublish', $period->id) }}" method="POST" class="m-0">
                            @csrf
                            <x-button type="submit" color="neutral" variant="outline" size="sm">Batalkan Tayang</x-button>
                        </form>
                    @endif

                    @if(!$period->isArchived())
                        <a href="{{ route('admin.periods.archive.confirm', $period->id) }}" class="decoration-none">
                            <x-button color="danger" variant="outline" size="sm">Arsipkan</x-button>
                        </a>
                    @else
                        <form action="{{ route('admin.periods.reopen', $period->id) }}" method="POST" class="m-0">
                            @csrf
                            <x-button type="submit" color="primary" variant="outline" size="sm">Buka Kembali</x-button>
                        </form>
                    @endif

                    <form action="{{ route('admin.periods.duplicate', $period->id) }}" method="POST" class="m-0">
                        @csrf
                        <x-button type="submit" color="neutral" variant="ghost" size="sm">Salin</x-button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-neutral-200 px-6 py-12 text-center text-sm text-neutral-400">
            Belum ada periode. Tambahkan periode pertama.
        </div>
    @endforelse
</div>

@endsection
