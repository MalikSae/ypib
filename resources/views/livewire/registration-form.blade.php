<form wire:submit.prevent="submit" class="max-w-3xl mx-auto">

    {{-- PROGRESS BAR --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            @foreach(['Data Diri','Pilih Jalur','Asal Sekolah','Konfirmasi'] as $i => $label)
                @php $num = $i + 1; @endphp
                <div class="flex flex-col items-center flex-1">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all
                        {{ $step >= $num ? 'text-white border-transparent' : 'text-gray-400 border-gray-200 bg-white' }}"
                        style="{{ $step >= $num ? 'background-color:#082e8f; border-color:#082e8f' : '' }}">
                        @if($step > $num)
                            ✓
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <div class="text-xs mt-1 font-medium text-center {{ $step >= $num ? '' : 'text-gray-400' }}" style="white-space: nowrap; {{ $step >= $num ? 'color:#082e8f;' : '' }}">{{ $label }}</div>
                </div>
                @if(!$loop->last)
                    <div class="flex-1 h-0.5 mb-5 mx-1 {{ $step > $num ? '' : 'bg-gray-200' }}" {!! $step > $num ? 'style="background-color:#082e8f;"' : '' !!}></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- SELECTED PRODI INFO CARD --}}
    @php
        $selectedProgram = $programs->firstWhere('id', $first_choice_program_id);
    @endphp
    @if($selectedProgram)
    <div class="mb-8 rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-white text-left text-gray-800">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-3" style="background-color: #FAFAFA;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-xs font-bold tracking-widest text-gray-500 uppercase">Prodi Pilihan Anda</span>
        </div>
        <div class="p-5 md:p-6 flex flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-xl md:text-2xl font-extrabold text-gray-900 mb-4 leading-tight">{{ $selectedProgram->name }}</h3>
                <style>
                    .prodi-badges { display: flex; flex-direction: row; flex-wrap: wrap; gap: 10px; align-items: center; }
                    @media (min-width: 640px) { .prodi-badges { gap: 12px; } }
                </style>
                <div class="prodi-badges">
                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-xs md:text-sm font-semibold text-gray-700 bg-white shadow-sm" style="white-space: nowrap;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                        {{ $selectedProgram->faculty->name }}
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-xs md:text-sm font-semibold text-gray-700 bg-white shadow-sm" style="white-space: nowrap;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Kuota {{ $selectedProgram->quota }}
                    </div>
                </div>
            </div>
            <div class="shrink-0 flex items-center justify-center">
                @php
                    $akreditasiImg = 'images/akreditasi-b.jpg';
                    $acr = strtoupper(trim($selectedProgram->accreditation));
                    if ($acr === 'A') $akreditasiImg = 'images/akreditasi-a.webp';
                    if ($acr === 'UNGGUL') $akreditasiImg = 'images/akreditasi-unggul.webp';
                @endphp
                <img src="{{ asset($akreditasiImg) }}" alt="Akreditasi {{ $selectedProgram->accreditation }}" class="h-16 md:h-20 w-auto object-contain">
            </div>
        </div>
    </div>
    @endif

    {{-- FLASH SESSION --}}
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm flex gap-2">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- ERRORS --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- STEP 1 — DATA DIRI --}}
    @if($step === 1)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Data Diri
        </h2>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input wire:model="full_name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none border-primary-600 text-primary-600" style="focus: focus:ring-" placeholder="Sesuai KTP/Ijazah">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK (16 digit) <span class="text-red-500">*</span></label>
                <input wire:model="nik" type="text" maxlength="16" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none" placeholder="3201xxxxxxxx">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $citiesData = [];
                    $citiesPath = base_path('public/data/cities.json');
                    if(file_exists($citiesPath)) {
                        $citiesData = json_decode(file_get_contents($citiesPath), true);
                    }
                    if (!is_array($citiesData) || empty($citiesData)) {
                        $citiesData = ["Cirebon", "Majalengka", "Indramayu", "Kuningan", "Bandung", "Jakarta", "Surabaya", "Semarang", "Yogyakarta", "Bekasi", "Tangerang", "Bogor", "Depok"];
                    }
                @endphp
                {{-- TEMPAT LAHIR: TomSelect (Industry Standard Searchable Dropdown) --}}
                <div wire:ignore>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                    <select id="birth_place_select" placeholder="Ketik nama kota..." autocomplete="off">
                        <option value="">Ketik nama kota...</option>
                        @foreach($citiesData as $city)
                            <option value="{{ $city }}" {{ ($birth_place ?? '') === $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                @error('birth_place') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror

                {{-- Include TomSelect CSS & JS --}}
                <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
                <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
                @script
                <script>
                    new TomSelect('#birth_place_select', {
                        create: false,
                        maxOptions: 100,
                        onChange: function(value) {
                            $wire.set('birth_place', value);
                        }
                    });
                </script>
                @endscript

                {{-- TANGGAL LAHIR: Native date input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input
                        wire:model="birth_date"
                        type="date"
                        max="{{ date('Y-m-d') }}"
                        min="1900-01-01"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                    <p class="text-xs text-gray-400 mt-1">Pilih tanggal lahir Anda</p>
                </div>

            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="gender" name="gender" type="radio" value="male" class="text-blue-600 focus:ring-blue-600">
                        <span class="text-sm">Laki-laki</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="gender" name="gender" type="radio" value="female" class="text-blue-600 focus:ring-blue-600">
                        <span class="text-sm">Perempuan</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea wire:model="address" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none border-primary-600 text-primary-600" style="focus: focus:ring-" placeholder="Jl. Contoh No. 1, Desa, Kecamatan, Kabupaten"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP <span class="text-red-500">*</span></label>
                <input wire:model="phone" type="tel" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none border-primary-600 text-primary-600" style="focus: focus:ring-" placeholder="08xxxxxxxxxx">
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 2 — PILIH JALUR --}}
    @if($step === 2)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
            Pilih Jalur Pendaftaran
        </h2>
        <style>
            /* Bulletproof CSS for nested checked states (Bypasses Tailwind JIT limitations) */
            .radio-peer:checked ~ .radio-card {
                border-color: #2563EB !important; /* primary-600 */
                background-color: #EFF6FF !important; /* primary-50 */
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            }
            .radio-peer:checked ~ .radio-card .radio-icon-wrapper {
                background-color: #DBEAFE !important; /* primary-100 */
                color: #2563EB !important; /* primary-600 */
            }
            .radio-peer:checked ~ .radio-card .radio-check-circle {
                background-color: #2563EB !important;
                border-color: #2563EB !important;
            }
            .radio-peer:checked ~ .radio-card .radio-check-icon {
                opacity: 1 !important;
            }
            .radio-peer:checked ~ .radio-card .radio-check-text {
                color: #2563EB !important;
            }
        </style>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- Jalur Umum -->
            <label class="cursor-pointer">
                <input type="radio" wire:model="admission_path" name="admission_path" value="umum" class="radio-peer sr-only">
                <div class="radio-card rounded-2xl p-6 border-2 transition-all duration-300 border-gray-200 hover:border-gray-300 flex flex-col h-full bg-white">
                    <div class="radio-icon-wrapper w-12 h-12 rounded-xl flex items-center justify-center mb-4 bg-gray-100 text-gray-500 transition-colors">
                        <i class="ti ti-clipboard-list text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-gray-900">Jalur Reguler</h3>
                    <p class="text-xs font-medium leading-relaxed text-gray-500 mb-4 flex-grow">Jalur masuk reguler dengan sistem seleksi melalui tes tertulis (CBT). Tersedia untuk seluruh lulusan SMA/SMK/MA.</p>
                    <div class="radio-check-text mt-auto flex items-center gap-2 text-sm font-semibold text-gray-400 transition-colors">
                        <div class="radio-check-circle w-5 h-5 rounded-full border-2 flex items-center justify-center border-gray-300 transition-colors">
                            <i class="radio-check-icon ti ti-check text-white text-xs opacity-0 transition-opacity"></i>
                        </div>
                        <span>Pilih Jalur</span>
                    </div>
                </div>
            </label>

            <!-- Jalur Prestasi -->
            <label class="cursor-pointer">
                <input type="radio" wire:model="admission_path" name="admission_path" value="prestasi" class="radio-peer sr-only">
                <div class="radio-card rounded-2xl p-6 border-2 transition-all duration-300 border-gray-200 hover:border-gray-300 flex flex-col h-full bg-white">
                    <div class="radio-icon-wrapper w-12 h-12 rounded-xl flex items-center justify-center mb-4 bg-gray-100 text-gray-500 transition-colors">
                        <i class="ti ti-award text-2xl"></i>
                    </div>
                    <div class="inline-block px-2 py-1 rounded text-[10px] font-bold mb-2 w-max" style="background-color: rgba(241, 177, 14, 0.2); color: #C98C00;">Tanpa Tes Tulis</div>
                    <h3 class="text-lg font-bold mb-2 text-gray-900">Jalur Prestasi</h3>
                    <p class="text-xs font-medium leading-relaxed text-gray-500 mb-4 flex-grow">Bebas tes tertulis khusus untuk siswa peraih prestasi akademik (rapor) maupun non-akademik.</p>
                    <div class="radio-check-text mt-auto flex items-center gap-2 text-sm font-semibold text-gray-400 transition-colors">
                        <div class="radio-check-circle w-5 h-5 rounded-full border-2 flex items-center justify-center border-gray-300 transition-colors">
                            <i class="radio-check-icon ti ti-check text-white text-xs opacity-0 transition-opacity"></i>
                        </div>
                        <span>Pilih Jalur</span>
                    </div>
                </div>
            </label>

            <!-- Jalur Tahfidz -->
            <label class="cursor-pointer">
                <input type="radio" wire:model="admission_path" name="admission_path" value="tahfidz" class="radio-peer sr-only">
                <div class="radio-card rounded-2xl p-6 border-2 transition-all duration-300 border-gray-200 hover:border-gray-300 flex flex-col h-full bg-white">
                    <div class="radio-icon-wrapper w-12 h-12 rounded-xl flex items-center justify-center mb-4 bg-gray-100 text-gray-500 transition-colors">
                        <i class="ti ti-book text-2xl"></i>
                    </div>
                    <div class="inline-flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold mb-2 w-max" style="background-color: #E0E7FF; color: #2563EB;">
                        <i class="ti ti-star-filled text-[8px]"></i> Beasiswa
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-gray-900">Jalur Tahfidz</h3>
                    <p class="text-xs font-medium leading-relaxed text-gray-500 mb-4 flex-grow">Apresiasi bagi para penghafal Al-Qur'an (minimal 3 juz). Tersedia program beasiswa pendidikan khusus.</p>
                    <div class="radio-check-text mt-auto flex items-center gap-2 text-sm font-semibold text-gray-400 transition-colors">
                        <div class="radio-check-circle w-5 h-5 rounded-full border-2 flex items-center justify-center border-gray-300 transition-colors">
                            <i class="radio-check-icon ti ti-check text-white text-xs opacity-0 transition-opacity"></i>
                        </div>
                        <span>Pilih Jalur</span>
                    </div>
                </div>
            </label>

        </div>
    </div>
    @endif

    {{-- STEP 3 — ASAL SEKOLAH --}}
    @if($step === 3)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
            </svg>
            Asal Sekolah
        </h2>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah <span class="text-red-500">*</span></label>
                <input wire:model="school_name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none border-primary-600 text-primary-600" style="focus: focus:ring-" placeholder="SMA/SMK/MA Negeri/Swasta ...">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Lulus <span class="text-red-500">*</span></label>
                    <input wire:model="graduation_year" type="number" min="2000" max="2030" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none border-primary-600 text-primary-600" style="focus: focus:ring-" placeholder="2024">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Rata-rata Ijazah <span class="text-gray-400 text-xs">(opsional)</span></label>
                    <input wire:model="school_grade" type="number" step="0.01" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none border-primary-600 text-primary-600" style="focus: focus:ring-" placeholder="85.50">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 4 — KONFIRMASI --}}
    @if($step === 4)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Konfirmasi Data Pendaftaran
        </h2>

        <div class="space-y-6">
            {{-- Data Diri --}}
            <div class="bg-gray-50 rounded-xl p-5">
                <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Data Diri</h3>
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                    <span class="text-gray-500">Nama Lengkap</span><span class="font-medium">{{ $full_name }}</span>
                    <span class="text-gray-500">NIK</span><span class="font-medium">{{ $nik }}</span>
                    <span class="text-gray-500">Tempat, Tgl Lahir</span><span class="font-medium">{{ $birth_place }}, {{ \Carbon\Carbon::parse($birth_date)->isoFormat('D MMMM Y') }}</span>
                    <span class="text-gray-500">Jenis Kelamin</span><span class="font-medium">{{ $gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                    <span class="text-gray-500">Nomor HP</span><span class="font-medium">{{ $phone }}</span>
                    <span class="text-gray-500">Alamat</span><span class="font-medium">{{ Str::limit($address, 60) }}</span>
                </div>
            </div>

            {{-- Pilihan Prodi & Jalur --}}
            <div class="bg-gray-50 rounded-xl p-5">
                <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Program Studi & Jalur Masuk</h3>
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                    @php
                        $firstProg  = $programs->firstWhere('id', $first_choice_program_id);
                        $pathLabels = ['umum' => 'Jalur Reguler', 'prestasi' => 'Jalur Prestasi', 'tahfidz' => 'Jalur Tahfidz'];
                    @endphp
                    <span class="text-gray-500">Program Studi</span><span class="font-medium">{{ $firstProg?->name ?? '-' }}</span>
                    <span class="text-gray-500">Jalur Pendaftaran</span><span class="font-medium">{{ $pathLabels[$admission_path] ?? '-' }}</span>
                </div>
            </div>

            {{-- Asal Sekolah --}}
            <div class="bg-gray-50 rounded-xl p-5">
                <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Asal Sekolah</h3>
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                    <span class="text-gray-500">Nama Sekolah</span><span class="font-medium">{{ $school_name }}</span>
                    <span class="text-gray-500">Tahun Lulus</span><span class="font-medium">{{ $graduation_year }}</span>
                    <span class="text-gray-500">Nilai Rata-rata</span><span class="font-medium">{{ $school_grade ?: '-' }}</span>
                </div>
            </div>

            @if($period)
            <div class="border rounded-xl p-4 text-sm text-primary-600" style="background-color:#e6edfc; border-color:#DBEAFE;">
                <strong>Biaya pendaftaran: Rp {{ number_format($firstProg?->registration_fee ?? 0, 0, ',', '.') }}</strong><br>
                Setelah submit, Anda akan mendapat nomor pendaftaran dan instruksi pembayaran.
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- NAVIGATION BUTTONS --}}
    <div class="flex justify-between mt-6 gap-3">
        @if($step > 1)
            <button wire:click="prevStep" type="button"
                class="flex items-center gap-2 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition"
                style="padding:12px 16px;"
                title="Sebelumnya"
            >
                <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span class="hidden sm:inline">Sebelumnya</span>
            </button>
        @else
            <div></div>
        @endif

        @if($step < 4)
            <button wire:click="nextStep" type="button"
                class="flex items-center gap-2 px-8 py-3 text-white font-semibold rounded-xl shadow transition hover:opacity-90 bg-primary-600">
                Selanjutnya
                <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </button>
        @else
            <button type="submit"
                wire:loading.attr="disabled"
                class="flex items-center gap-2 px-8 py-3 text-white font-semibold rounded-xl shadow transition hover:opacity-90 disabled:opacity-60 bg-primary-600">
                <span wire:loading.remove class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    Kirim Pendaftaran
                </span>
                <span wire:loading>Memproses...</span>
            </button>
        @endif
    </div>
</form>
