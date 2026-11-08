@extends('layouts.admin')
@section('title', $prospect->full_name . ' — Kelola Prospek — Admin PMB YPIB')
@section('page-title', 'Detail Prospek')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.prospects.index') }}" class="text-sm font-semibold text-primary-600 hover:underline">← Kembali ke daftar</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Kolom kiri: data prospek dan aksi --}}
    <div class="lg:col-span-2 space-y-6">
        <x-card class="p-4 md:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-neutral-900">{{ $prospect->full_name }}</h1>
                    <p class="text-sm text-neutral-500">{{ $prospect->school_name ?: '—' }}</p>
                    <p class="font-mono text-sm text-neutral-700 mt-1">{{ $prospect->phone_normalized }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button type="button" color="neutral" variant="outline" onclick="window.open({{ \Illuminate\Support\Js::from($chatUrl) }}, '_blank', 'noopener')">Chat WA</x-button>
                    @unless($prospect->isRegistered())
                        <form method="POST" action="{{ route('admin.prospects.resend-link', $prospect) }}">
                            @csrf
                            <x-button type="submit" color="neutral" variant="outline">Kirim link daftar</x-button>
                        </form>
                    @endunless
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-4 mt-6 text-sm">
                <div>
                    <dt class="text-xs text-neutral-400 uppercase">Pendidikan</dt>
                    <dd class="text-neutral-900">{{ \App\Models\Registration::EDUCATION_LEVELS[$prospect->education_level]['label'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-neutral-400 uppercase">Prodi</dt>
                    <dd class="text-neutral-900">{{ $prospect->program?->name ?? 'Belum menentukan' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-neutral-400 uppercase">Event</dt>
                    <dd class="text-neutral-900">{{ $prospect->sources->map(fn ($s) => $s->event?->name)->filter()->implode(', ') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-neutral-400 uppercase">Affiliator</dt>
                    <dd class="text-neutral-900">{{ $prospect->referrer?->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-neutral-400 uppercase">Klik WA</dt>
                    <dd class="text-neutral-900">{{ $prospect->wa_clicked_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-neutral-400 uppercase">Terakhir dihubungi</dt>
                    <dd class="text-neutral-900">{{ $prospect->last_contacted_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
            </dl>

            @if($prospect->isRegistered())
                <p class="mt-6 text-sm font-semibold text-success-600">Sudah mendaftar{{ $prospect->registration ? ' (' . ($prospect->registration->registration_number ?: 'Pendaftaran #' . $prospect->registration->id) . ')' : '' }}.</p>
            @endif
        </x-card>

        <x-card class="p-6">
            <h2 class="text-sm font-semibold text-neutral-900 mb-3">Catatan &amp; riwayat</h2>
            <form method="POST" action="{{ route('admin.prospects.contact', $prospect) }}" class="space-y-3 mb-5">
                @csrf
                <x-textarea name="note" rows="2" placeholder="Hasil obrolan, misal: minta info biaya, akan konfirmasi minggu depan" :error="$errors->has('note')" />
                <x-input-error :messages="$errors->get('note')" />
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    @unless($prospect->isRegistered())
                        <label class="inline-flex items-center gap-2 text-sm text-neutral-700">
                            <input type="checkbox" name="dihubungi" value="1" class="rounded border-neutral-300 text-primary-600" @checked(old('dihubungi'))>
                            Sudah dihubungi
                        </label>
                    @endunless
                    <x-button type="submit" color="primary" class="sm:ml-auto">Simpan</x-button>
                </div>
            </form>

            @forelse($prospect->activities as $activity)
                <div class="border-t border-neutral-100 py-3 text-sm">
                    <div class="flex items-center gap-2 text-xs text-neutral-400">
                        <span class="font-semibold text-neutral-700">{{ \App\Models\ProspectActivity::TYPE_LABELS[$activity->type] ?? $activity->type }}</span>
                        <span>·</span>
                        <span>{{ $activity->created_at->format('d/m/Y H:i') }}</span>
                        <span>·</span>
                        <span>{{ $activity->user?->name ?? 'Sistem' }}</span>
                    </div>
                    @if($activity->note)
                        <p class="mt-1 text-neutral-900">{{ $activity->note }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-neutral-400">Belum ada riwayat.</p>
            @endforelse
        </x-card>
    </div>

    {{-- Kolom kanan: status --}}
    <div>
        <x-card class="p-6">
            <h2 class="text-sm font-semibold text-neutral-900 mb-3">Status</h2>
            @if($prospect->isRegistered())
                <p class="text-sm text-neutral-500">Status dikunci karena sudah mendaftar.</p>
            @else
                <form method="POST" action="{{ route('admin.prospects.update-status', $prospect) }}" class="space-y-3">
                    @csrf
                    <x-select name="status" id="status">
                        @foreach($adminStatuses as $s)
                            <option value="{{ $s }}" @selected($prospect->status === $s)>{{ $statuses[$s]['label'] }}</option>
                        @endforeach
                    </x-select>
                    <x-button type="submit" color="primary" class="w-full justify-center">Simpan status</x-button>
                </form>
            @endif
        </x-card>
    </div>
</div>
@endsection
