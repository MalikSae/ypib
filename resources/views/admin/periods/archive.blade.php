@extends('layouts.admin')
@section('title', 'Arsipkan Periode — Admin PMB YPIB')
@section('page-title', 'Arsipkan Periode')

@section('content')

<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Arsipkan "{{ $period->name }}"</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Periode arsip hanya baca bagi pendaftar: tidak ada unggahan atau perubahan baru. Unduhan tetap bisa dipakai.</p>
    </div>

    <x-card class="p-6 md:p-8">
        <dl class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                <dt class="text-xs font-semibold text-neutral-400 uppercase">Total pendaftar</dt>
                <dd class="mt-1 text-2xl font-bold text-neutral-900">{{ $summary['total'] }}</dd>
            </div>
            <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                <dt class="text-xs font-semibold text-neutral-400 uppercase">Masih berjalan</dt>
                <dd class="mt-1 text-2xl font-bold text-neutral-900">{{ $summary['running'] }}</dd>
            </div>
            <div class="p-4 rounded-xl border {{ $summary['awaiting_re_registration'] > 0 ? 'bg-warning-50 border-warning-200' : 'bg-neutral-50 border-neutral-200' }}">
                <dt class="text-xs font-semibold text-neutral-400 uppercase">Menunggu daftar ulang</dt>
                <dd class="mt-1 text-2xl font-bold text-neutral-900">{{ $summary['awaiting_re_registration'] }}</dd>
            </div>
        </dl>

        <form action="{{ route('admin.periods.archive', $period->id) }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            @if($summary['awaiting_re_registration'] > 0)
                <label class="flex items-start gap-3 p-4 rounded-xl bg-warning-50 border border-warning-200 mb-6 cursor-pointer">
                    <input type="checkbox" name="acknowledge" value="1" class="mt-1 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-neutral-700">
                        Saya mengerti {{ $summary['awaiting_re_registration'] }} pendaftar masih menunggu konfirmasi daftar ulang
                        dan tidak bisa mengunggah bukti baru setelah periode diarsipkan. Staf tetap dapat memprosesnya.
                    </span>
                </label>
            @elseif($summary['running'] > 0)
                <p class="mb-6 text-sm text-neutral-600">
                    Masih ada {{ $summary['running'] }} pendaftaran yang belum final. Setelah diarsipkan, pendaftar tidak dapat
                    mengunggah atau mengubah data. Staf tetap dapat memprosesnya, dan periode dapat dibuka kembali.
                </p>
            @endif

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200">
                <a href="{{ route('admin.periods.index') }}" class="decoration-none">
                    <x-button color="neutral" variant="ghost">Batal</x-button>
                </a>
                <x-button type="submit" color="danger" ::disabled="submitting">Arsipkan Periode</x-button>
            </div>
        </form>
    </x-card>
</div>

@endsection
