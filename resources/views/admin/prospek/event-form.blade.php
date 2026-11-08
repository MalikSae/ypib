@extends('layouts.admin')
@section('title', ($event->exists ? 'Edit' : 'Tambah') . ' Event atau Kampanye — Admin PMB YPIB')
@section('page-title', $event->exists ? 'Edit Event / Kampanye' : 'Tambah Event / Kampanye')

@section('content')
@php $isEdit = $event->exists; @endphp

<div class="max-w-2xl">
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.prospects.events.index') }}" aria-label="Kembali ke daftar event dan kampanye"
       class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 focus-visible:ring-2 focus-visible:ring-primary-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">{{ $isEdit ? 'Edit Event / Kampanye' : 'Tambah Event / Kampanye' }}</h1>
        <p class="text-sm text-neutral-500">Potongan disimpan ke link saat siswa mengisi form minat.</p>
    </div>
</div>

<form method="POST" x-data="{ kind: '{{ old('kind', $event->kind ?? 'event') }}' }"
      action="{{ $isEdit ? route('admin.prospects.events.update', $event) : route('admin.prospects.events.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card class="p-4 md:p-8">
        {{-- Mobile: hanya card. Kotak abu-abu baru muncul di md ke atas supaya tidak bertumpuk. --}}
        <div class="space-y-5 md:p-6 md:bg-neutral-50 md:rounded-xl md:border md:border-neutral-200">
            <div>
                <x-input-label for="kind" value="Jenis" required="true" />
                <x-select id="kind" name="kind" required :error="$errors->has('kind')" x-model="kind">
                    @foreach($kinds as $kindValue => $kindLabel)
                        <option value="{{ $kindValue }}" @selected(old('kind', $event->kind ?? 'event') === $kindValue)>{{ $kindLabel }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('kind')" />
            </div>

            <div>
                <x-input-label for="name" value="Nama event / kampanye" required="true" />
                <x-text-input type="text" id="name" name="name" :value="old('name', $event->name)" required
                              x-bind:placeholder="kind === 'online' ? 'Contoh: Daftar Kuliah YPIB Majalengka' : 'Contoh: Sosialisasi SMA Negeri 1 Majalengka'"
                              :error="$errors->has('name')" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5" x-show="kind === 'event'" x-cloak>
                <div>
                    <x-input-label for="school_name" value="Sekolah tujuan (opsional)" />
                    <x-text-input type="text" id="school_name" name="school_name" :value="old('school_name', $event->school_name)" :error="$errors->has('school_name')" />
                    <x-input-error :messages="$errors->get('school_name')" />
                </div>
                <div>
                    <x-input-label for="event_date" value="Tanggal event (opsional)" />
                    <x-text-input type="date" id="event_date" name="event_date" :value="old('event_date', $event->event_date?->format('Y-m-d'))" :error="$errors->has('event_date')" />
                    <x-input-error :messages="$errors->get('event_date')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="campus_id" value="Kampus" required="true" />
                    <x-select id="campus_id" name="campus_id" required :error="$errors->has('campus_id')">
                        <option value="">-- Pilih Kampus --</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('campus_id', $event->campus_id) == $campus->id)>{{ $campus->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('campus_id')" />
                </div>
                <div>
                    <x-input-label for="period_id" value="Periode" />
                    <x-select id="period_id" name="period_id" :error="$errors->has('period_id')">
                        <option value="">Ikuti periode berjalan</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" @selected(old('period_id', $event->period_id) == $period->id)>{{ $period->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('period_id')" />
                </div>
            </div>

            <div>
                <x-input-label for="discount_amount" value="Potongan biaya daftar ulang (Rp)" required="true" />
                <x-money-input id="discount_amount" name="discount_amount"
                               :value="old('discount_amount', $event->exists ? $event->discount_amount : 500000)" :error="$errors->has('discount_amount')" />
                <x-input-error :messages="$errors->get('discount_amount')" />
            </div>

            <label class="flex items-center gap-2 text-sm text-neutral-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-neutral-300 text-primary-600"
                       @checked(old('is_active', $event->exists ? $event->is_active : true))>
                Aktif
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 mt-6">
            <x-button type="button" color="neutral" variant="ghost"
                      onclick="location.href = {{ \Illuminate\Support\Js::from(route('admin.prospects.events.index')) }}">Batal</x-button>
            <x-button type="submit" color="primary">Simpan</x-button>
        </div>
    </x-card>
</form>
</div>
@endsection
