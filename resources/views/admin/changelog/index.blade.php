@extends('layouts.admin')
@section('title', 'Changelog — Admin PMB YPIB')
@section('page-title', 'Changelog')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Changelog</h1>
        <p class="text-sm text-neutral-400">Update dan perbaikan di setiap deploy.</p>
    </div>

    <div class="space-y-6">
        @forelse($releases as $release)
            <x-card class="p-4 md:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
                    <div>
                        <div class="text-sm font-semibold text-primary-600">
                            {{ \Illuminate\Support\Carbon::parse($release['date'])->translatedFormat('d F Y') }}
                        </div>
                        <h2 class="text-lg font-bold text-neutral-900">{{ $release['title'] }}</h2>
                    </div>
                    @if(!empty($release['commit']))
                        <span class="shrink-0 self-start rounded-full border border-neutral-200 bg-neutral-100 px-2.5 py-1 font-mono text-xs text-neutral-600">{{ $release['commit'] }}</span>
                    @endif
                </div>

                <div class="space-y-5">
                    @foreach($release['sections'] as $heading => $items)
                        <div>
                            <h3 class="text-sm font-semibold text-neutral-900 mb-2">{{ $heading }}</h3>
                            <ul class="list-disc pl-5 space-y-1.5 text-sm text-neutral-600 marker:text-neutral-300">
                                @foreach($items as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @empty
            <x-card class="p-6 text-sm text-neutral-400">Belum ada catatan update.</x-card>
        @endforelse
    </div>
</div>
@endsection
