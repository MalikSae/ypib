@extends('layouts.landing')
@section('title', 'Form Minat — ' . $event->name . ' — PMB YPIB Majalengka')

@section('content')
<section style="padding:48px 0;min-height:calc(100vh - 64px);" class="bg-neutral-100">
<div class="pub-container">
<div style="max-width:560px;margin:0 auto;">

    <div style="margin-bottom:24px;">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;" class="text-primary-600">Form Minat</div>
        <h1 style="font-size:22px;font-weight:700;margin:4px 0 6px 0;" class="text-neutral-900">{{ $event->name }}</h1>
        <p style="font-size:14px;margin:0;" class="text-neutral-500">Isi data singkat. Setelah kirim, kamu akan terhubung langsung ke WhatsApp admin.</p>
        @if($event->period?->close_date)
            <p style="font-size:13px;margin:6px 0 0 0;" class="text-neutral-400">Pendaftaran dibuka sampai {{ $event->period->close_date->translatedFormat('d F Y') }}.</p>
        @endif
    </div>

    @if($event->discount_amount > 0)
        <div class="pub-card" style="margin-bottom:20px;background:#f8faff;">
            <div style="font-size:16px;font-weight:700;" class="text-neutral-900">Hemat Rp {{ number_format($event->discount_amount, 0, ',', '.') }} saat daftar ulang</div>
            <p style="font-size:13px;margin:4px 0 0 0;" class="text-neutral-500">Berlaku jika kamu diterima. Dipotong otomatis dari tagihan daftar ulang.</p>
        </div>
    @endif

    @if(session('error'))
        <div class="pub-flash-error" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('prospect.store', [$event->slug] + ($ref ? ['ref' => $ref] : [])) }}" class="pub-card" style="padding:24px;">
        @csrf

        <div style="display:flex;flex-direction:column;gap:16px;"
             x-data="{ edu: '{{ old('education_level', \App\Models\Registration::DEFAULT_EDUCATION_LEVEL) }}', placeholders: @js(collect(\App\Models\Registration::EDUCATION_LEVELS)->map(fn ($c) => $c['placeholder'])->all()) }">

            <div>
                <label for="full_name" class="block text-sm font-semibold text-neutral-700 mb-1">Nama lengkap <span class="text-error-600">*</span></label>
                <input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" required maxlength="255"
                       class="w-full border border-neutral-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary-600">
                @error('full_name')<p class="text-xs text-error-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="education_level" class="block text-sm font-semibold text-neutral-700 mb-1">Pendidikan terakhir <span class="text-error-600">*</span></label>
                <select id="education_level" name="education_level" x-model="edu" required
                        class="w-full border border-neutral-200 rounded-lg px-4 py-2.5 text-sm bg-white focus:outline-none focus:border-primary-600">
                    @foreach(\App\Models\Registration::EDUCATION_LEVELS as $levelKey => $levelConfig)
                        <option value="{{ $levelKey }}" @selected(old('education_level', \App\Models\Registration::DEFAULT_EDUCATION_LEVEL) === $levelKey)>{{ $levelConfig['label'] }}</option>
                    @endforeach
                </select>
                @error('education_level')<p class="text-xs text-error-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="school_name" class="block text-sm font-semibold text-neutral-700 mb-1">Nama sekolah atau kampus <span class="text-error-600">*</span></label>
                <input id="school_name" name="school_name" type="text" value="{{ old('school_name') }}" required maxlength="255"
                       x-bind:placeholder="placeholders[edu]"
                       class="w-full border border-neutral-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary-600">
                @error('school_name')<p class="text-xs text-error-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-neutral-700 mb-1">Nomor WhatsApp <span class="text-error-600">*</span></label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="20"
                       placeholder="08xxxxxxxxxx"
                       class="w-full border border-neutral-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary-600">
                @error('phone')<p class="text-xs text-error-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="program_id" class="block text-sm font-semibold text-neutral-700 mb-1">Program studi yang diminati</label>
                <select id="program_id" name="program_id"
                        class="w-full border border-neutral-200 rounded-lg px-4 py-2.5 text-sm bg-white focus:outline-none focus:border-primary-600">
                    <option value="">Belum menentukan</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>{{ $program->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-neutral-400 mt-1">Belum yakin? Pilih "Belum menentukan", admin bantu pilih.</p>
                @error('program_id')<p class="text-xs text-error-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;margin-top:20px;justify-content:center;">
            Kirim &amp; Chat Admin
        </button>
    </form>

    <p style="font-size:12px;margin:16px 0 0 0;text-align:center;" class="text-neutral-400">
        Data hanya digunakan untuk keperluan informasi pendaftaran YPIB.
    </p>
</div>
</div>
</section>
@endsection
