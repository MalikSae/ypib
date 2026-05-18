<form wire:submit.prevent="submit" class="max-w-3xl mx-auto">

    {{-- PROGRESS BAR --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            @foreach(['Data Diri','Asal Sekolah','Konfirmasi'] as $i => $label)
                @php $num = $i + 1; @endphp
                <div class="flex flex-col items-center flex-1">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all
                        {{ $step >= $num ? 'text-white border-transparent' : 'text-gray-400 border-gray-200 bg-white' }}"
                        style="{{ $step >= $num ? 'background-color:#0064E0; border-color:#0064E0' : '' }}">
                        @if($step > $num)
                            ✓
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <div class="text-xs mt-1 font-medium text-center {{ $step >= $num ? '' : 'text-gray-400' }}" style="white-space: nowrap; {{ $step >= $num ? 'color:#0064E0;' : '' }}">{{ $label }}</div>
                </div>
                @if(!$loop->last)
                    <div class="flex-1 h-0.5 mb-5 mx-1 {{ $step > $num ? '' : 'bg-gray-200' }}" {!! $step > $num ? 'style="background-color:#0064E0;"' : '' !!}></div>
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
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: #0064E0;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-xs font-bold tracking-widest text-gray-500 uppercase">Prodi Pilihan Anda</span>
        </div>
        <div class="p-5 md:p-6">
            <h3 class="text-xl md:text-2xl font-extrabold text-gray-900 mb-4 leading-tight">{{ $selectedProgram->name }}</h3>
            <style>
                .prodi-badges { display: flex; flex-direction: column; gap: 10px; align-items: flex-start; }
                @media (min-width: 640px) { .prodi-badges { flex-direction: row; flex-wrap: wrap; align-items: center; gap: 12px; } }
            </style>
            <div class="prodi-badges">
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-xs md:text-sm font-semibold text-gray-700 bg-white shadow-sm" style="white-space: nowrap;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                    {{ $selectedProgram->faculty }}
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-xs md:text-sm font-bold shadow-sm" style="border-color: #DBEAFE; background-color: #EFF4FF; color: #0064E0; white-space: nowrap;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color: #0064E0;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                    Akreditasi {{ $selectedProgram->accreditation }}
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-xs md:text-sm font-semibold text-gray-700 bg-white shadow-sm" style="white-space: nowrap;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    Kuota {{ $selectedProgram->quota }}
                </div>
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
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: #0064E0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Data Diri
        </h2>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input wire:model="full_name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none" style="focus:border-color:#0064E0; focus:ring-color:#0064E0;" placeholder="Sesuai KTP/Ijazah">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK (16 digit) <span class="text-red-500">*</span></label>
                <input wire:model="nik" type="text" maxlength="16" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none" style="focus:border-color:#0064E0; focus:ring-color:#0064E0;" placeholder="3201xxxxxxxx">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- TEMPAT LAHIR: Search Dropdown --}}
                <div
                    x-data="{
                        open: false,
                        query: @entangle('birth_place').live,
                        results: [],
                        cities: [],
                        loading: false,
                        init() {
                            fetch('/data/cities.json')
                                .then(r => r.json())
                                .then(data => { this.cities = data; });
                        },
                        search() {
                            if (this.query.length < 1) { this.results = []; this.open = false; return; }
                            const q = this.query.toLowerCase();
                            this.results = this.cities.filter(c => c.toLowerCase().includes(q)).slice(0, 8);
                            this.open = this.results.length > 0;
                        },
                        select(city) {
                            this.query = city;
                            $wire.set('birth_place', city);
                            this.open = false;
                            this.results = [];
                        }
                    }"
                    x-init="init()"
                    @click.outside="open = false"
                    class="relative"
                >
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                    <div style="position:relative;">
                        <input
                            x-model="query"
                            @input="search()"
                            @focus="if(query.length>0) search()"
                            type="text"
                            placeholder="Ketik nama kota/kabupaten..."
                            autocomplete="off"
                            class="w-full border border-gray-300 rounded-lg text-sm focus:outline-none"
                            style="padding:10px 36px 10px 16px;"
                        >
                        <button
                            x-show="query.length > 0"
                            @click.prevent="query=''; $wire.set('birth_place',''); results=[]; open=false;"
                            type="button"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;padding:2px;cursor:pointer;display:flex;align-items:center;color:#9CA3AF;"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
                        style="max-height:220px;overflow-y:auto;"
                    >
                        <template x-for="city in results" :key="city">
                            <div
                                @click="select(city)"
                                class="px-4 py-2.5 text-sm text-gray-700 cursor-pointer hover:bg-blue-50 hover:text-blue-700 border-b border-gray-50 last:border-0"
                                x-text="city"
                            ></div>
                        </template>
                    </div>
                </div>

                {{-- TANGGAL LAHIR: Input ketik DD/MM/YYYY --}}
                <div
                    x-data="{
                        raw: '',
                        errMsg: '',
                        digits(v) { return v.replace(/\D/g,''); },
                        fmt(v) {
                            let d = this.digits(v).substring(0,8);
                            if (d.length > 4) return d.substring(0,2)+'/'+d.substring(2,4)+'/'+d.substring(4);
                            if (d.length > 2) return d.substring(0,2)+'/'+d.substring(2);
                            return d;
                        },
                        isValid(v) {
                            if (!/^\d{2}\/\d{2}\/\d{4}$/.test(v)) return false;
                            const [dd,mm,yyyy] = v.split('/').map(Number);
                            if (mm < 1 || mm > 12) return false;
                            const curY = new Date().getFullYear();
                            if (yyyy < 1900 || yyyy > curY) return false;
                            const maxD = new Date(yyyy, mm, 0).getDate();
                            if (dd < 1 || dd > maxD) return false;
                            return true;
                        },
                        onInput(e) {
                            const pos = e.target.selectionStart;
                            const formatted = this.fmt(e.target.value);
                            this.raw = formatted;
                            e.target.value = formatted;
                            this.errMsg = '';
                            if (formatted.length === 10) {
                                if (this.isValid(formatted)) {
                                    const [dd,mm,yyyy] = formatted.split('/');
                                    $wire.set('birth_date', yyyy+'-'+mm+'-'+dd);
                                } else {
                                    this.errMsg = 'Tanggal tidak valid.';
                                    $wire.set('birth_date', '');
                                }
                            } else {
                                $wire.set('birth_date', '');
                            }
                        }
                    }"
                >
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input
                        :value="raw"
                        @input="onInput($event)"
                        @keydown.backspace="if($event.target.value.endsWith('/')) { $event.preventDefault(); raw = raw.slice(0,-2); $event.target.value = raw; $wire.set('birth_date',''); }"
                        type="text"
                        inputmode="numeric"
                        placeholder="DD/MM/YYYY"
                        maxlength="10"
                        autocomplete="off"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none"
                        :class="errMsg ? 'border-red-400' : ''"
                    >
                    <p x-show="errMsg" x-text="errMsg" class="text-xs text-red-500 mt-1"></p>
                    <p x-show="!errMsg" class="text-xs text-gray-400 mt-1">Contoh: 24/05/1992</p>
                    <input wire:model="birth_date" type="hidden">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="gender" type="radio" value="male" class="text-blue-600 focus:ring-blue-600">
                        <span class="text-sm">Laki-laki</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="gender" type="radio" value="female" class="text-blue-600 focus:ring-blue-600">
                        <span class="text-sm">Perempuan</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea wire:model="address" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none" style="focus:border-color:#0064E0; focus:ring-color:#0064E0;" placeholder="Jl. Contoh No. 1, Desa, Kecamatan, Kabupaten"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP <span class="text-red-500">*</span></label>
                <input wire:model="phone" type="tel" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none" style="focus:border-color:#0064E0; focus:ring-color:#0064E0;" placeholder="08xxxxxxxxxx">
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 2 — ASAL SEKOLAH --}}
    @if($step === 2)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: #0064E0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
            </svg>
            Asal Sekolah
        </h2>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah <span class="text-red-500">*</span></label>
                <input wire:model="school_name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none" style="focus:border-color:#0064E0; focus:ring-color:#0064E0;" placeholder="SMA/SMK/MA Negeri/Swasta ...">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Lulus <span class="text-red-500">*</span></label>
                    <input wire:model="graduation_year" type="number" min="2000" max="2030" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none" style="focus:border-color:#0064E0; focus:ring-color:#0064E0;" placeholder="2024">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Rata-rata Ijazah <span class="text-gray-400 text-xs">(opsional)</span></label>
                    <input wire:model="school_grade" type="number" step="0.01" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none" style="focus:border-color:#0064E0; focus:ring-color:#0064E0;" placeholder="85.50">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 3 — KONFIRMASI --}}
    @if($step === 3)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: #0064E0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

            {{-- Pilihan Prodi --}}
            <div class="bg-gray-50 rounded-xl p-5">
                <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Program Studi</h3>
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                    @php
                        $firstProg  = $programs->firstWhere('id', $first_choice_program_id);
                    @endphp
                    <span class="text-gray-500">Program Studi</span><span class="font-medium">{{ $firstProg?->name ?? '-' }}</span>
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
            <div class="border rounded-xl p-4 text-sm" style="background-color:#EFF4FF; border-color:#DBEAFE; color:#0064E0;">
                <strong>Biaya pendaftaran: Rp {{ number_format($period->registration_fee, 0, ',', '.') }}</strong><br>
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

        @if($step < 3)
            <button wire:click="nextStep" type="button"
                class="flex items-center gap-2 px-8 py-3 text-white font-semibold rounded-xl shadow transition hover:opacity-90"
                style="background-color:#0064E0">
                Selanjutnya
                <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </button>
        @else
            <button type="submit"
                wire:loading.attr="disabled"
                class="flex items-center gap-2 px-8 py-3 text-white font-semibold rounded-xl shadow transition hover:opacity-90 disabled:opacity-60"
                style="background-color:#0064E0">
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
