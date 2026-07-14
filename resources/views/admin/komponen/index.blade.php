@extends('layouts.admin')
@section('title', 'Komponen UI (Dev Reference) — Admin PMB YPIB')
@section('page-title', 'Komponen UI (Dev Reference)')

@section('content')

<div class="space-y-12 pb-16">

    <!-- HEADER -->
    <div>
        <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Komponen UI</h1>
        <p class="mt-1 text-sm text-neutral-500">Dokumentasi dan <em>showcase</em> komponen Blade untuk konsistensi desain halaman Admin.</p>
    </div>

    <!-- SECTION: Buttons -->
    <section>
        <div class="mb-4 pb-2 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">1. Buttons <code>&lt;x-button&gt;</code></h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 bg-white p-6 rounded-2xl border border-neutral-200">
            @foreach(['primary', 'neutral', 'success', 'danger'] as $color)
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-neutral-400 uppercase tracking-wider mb-2">Color: {{ $color }}</h3>
                    
                    @foreach(['solid', 'outline', 'ghost'] as $variant)
                        <div class="flex flex-col items-start gap-1">
                            <span class="text-xs font-medium text-neutral-500 bg-neutral-100 px-2 py-0.5 rounded">variant="{{ $variant }}"</span>
                            <x-button color="{{ $color }}" variant="{{ $variant }}" size="md">Button {{ ucfirst($color) }}</x-button>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>

    <!-- SECTION: Cards -->
    <section>
        <div class="mb-4 pb-2 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">2. Cards <code>&lt;x-card&gt;</code></h2>
        </div>
        
        <x-card class="p-6 md:p-8">
            <h3 class="font-bold text-neutral-900 mb-2">Ini adalah Card</h3>
            <p class="text-sm text-neutral-600">Digunakan sebagai <em>wrapper</em> utama form atau section konten. Dibuat dengan <code>&lt;x-card class="p-6"&gt;</code> yang sudah memiliki border dan shadow standar.</p>
        </x-card>
    </section>

    <!-- SECTION: Form Elements -->
    <section>
        <div class="mb-4 pb-2 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">3. Form Elements</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 bg-neutral-50 p-6 md:p-8 rounded-2xl border border-neutral-200">
            
            <!-- Normal Input -->
            <div>
                <x-input-label for="example_input" value="Text Input (Normal)" required="true" />
                <x-text-input id="example_input" type="text" placeholder="Ketik sesuatu..." />
            </div>

            <!-- Error Input -->
            <div>
                <x-input-label for="example_error" value="Text Input (Error State)" required="true" />
                <x-text-input id="example_error" type="text" value="Salah ketik" :error="true" />
                <x-input-error :messages="['Kolom ini wajib diisi dengan format yang benar.']" class="mt-2" />
            </div>

            <!-- Select -->
            <div>
                <x-input-label for="example_select" value="Select / Dropdown" />
                <x-select id="example_select">
                    <option value="">-- Pilih Opsi --</option>
                    <option value="1">Opsi Pertama</option>
                    <option value="2">Opsi Kedua</option>
                </x-select>
            </div>

            <!-- Textarea -->
            <div class="lg:col-span-2">
                <x-input-label for="example_textarea" value="Textarea" />
                <x-textarea id="example_textarea" placeholder="Tulis deskripsi di sini..."></x-textarea>
            </div>

        </div>
    </section>

    <!-- SECTION: Badge Status -->
    <section>
        <div class="mb-4 pb-2 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">4. Badge Status <code>&lt;x-badge-status&gt;</code></h2>
        </div>

        <div class="flex flex-wrap gap-6 items-center p-6 bg-white rounded-2xl border border-neutral-200">
            <div class="flex flex-col items-center gap-2">
                <span class="text-xs text-neutral-400 font-mono">:active="true"</span>
                <x-badge-status :active="true" />
            </div>
            
            <div class="flex flex-col items-center gap-2">
                <span class="text-xs text-neutral-400 font-mono">:active="false"</span>
                <x-badge-status :active="false" />
            </div>

            <div class="flex flex-col items-center gap-2 border-l border-neutral-200 pl-6">
                <span class="text-xs text-neutral-400 font-mono">Custom Text</span>
                <x-badge-status :active="true" activeText="Selesai" />
            </div>
        </div>
    </section>

    <!-- SECTION: Table Actions -->
    <section>
        <div class="mb-4 pb-2 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">5. Table Actions (Icons)</h2>
        </div>

        <div class="flex gap-6 p-6 bg-white rounded-2xl border border-neutral-200">
            <div class="flex flex-col items-center gap-2">
                <x-table-action-detail href="#" />
                <span class="text-xs text-neutral-500 font-mono">detail</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <x-table-action-edit href="#" />
                <span class="text-xs text-neutral-500 font-mono">edit</span>
            </div>
            <div class="flex flex-col items-center gap-2 border-l border-neutral-200 pl-6">
                <!-- Gunakan span dengan class yang mirip untuk preview tanpa form asli agar tidak redirect -->
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-400 hover:bg-error-50 hover:text-error-600 transition-colors duration-150 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </span>
                <span class="text-xs text-neutral-500 font-mono">delete</span>
            </div>
        </div>
    </section>

    <!-- SECTION: Table Pattern -->
    <section>
        <div class="mb-4 pb-2 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">6. Table Pattern (List/Index)</h2>
        </div>

        <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-neutral-50 border-b border-neutral-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-16">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @for($i = 1; $i <= 3; $i++)
                        <tr class="hover:bg-neutral-50 transition-colors duration-100">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">{{ $i }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-neutral-900">Data Contoh {{ $i }}</div>
                                <div class="text-xs text-neutral-500">Keterangan tambahan data {{ $i }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge-status :active="$i % 2 !== 0" />
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <x-table-action-edit href="#" />
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-400 hover:bg-error-50 hover:text-error-600 transition-colors duration-150 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- SECTION: Palet Warna -->
    <section>
        <div class="mb-4 pb-2 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">7. Palet Warna (Brand Tokens)</h2>
        </div>

        <div class="space-y-6">
            @php
                $colors = [
                    'primary' => [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950],
                    'neutral' => [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950],
                ];
                $semantic = ['success', 'warning', 'error', 'info'];
            @endphp

            @foreach($colors as $name => $shades)
                <div>
                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wider mb-3">{{ $name }}</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-11 gap-2">
                        @foreach($shades as $shade)
                            <div class="flex flex-col">
                                <div class="h-12 w-full rounded-lg shadow-sm border border-neutral-200 bg-{{ $name }}-{{ $shade }}"></div>
                                <span class="text-[10px] text-neutral-500 mt-1 font-mono text-center">{{ $shade }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-8">
                <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wider mb-3">Semantic (DEFAULT)</h3>
                <div class="flex flex-wrap gap-4">
                    @foreach($semantic as $name)
                        <div class="flex flex-col w-24">
                            <div class="h-12 w-full rounded-lg shadow-sm border border-neutral-200 bg-{{ $name }}"></div>
                            <span class="text-[10px] text-neutral-500 mt-1 font-mono text-center">bg-{{ $name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

</div>

@endsection
