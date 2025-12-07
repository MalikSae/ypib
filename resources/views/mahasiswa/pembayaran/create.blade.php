@extends('layouts.mahasiswa')

@section('title', 'Upload Pembayaran')

@section('content')
<div class="fade-in-up">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('mahasiswa.pembayaran.index') }}" 
                       class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                    </a>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Upload Pembayaran
                    </h1>
                </div>
                <p class="text-gray-600 ml-13">Upload bukti pembayaran Anda</p>
            </div>
            <div class="hidden md:block">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center float">
                    <i class="fas fa-cloud-upload-alt text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Form Upload -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-file-upload mr-3"></i>
                        Form Pembayaran
                    </h2>
                </div>

                <form action="{{ route('mahasiswa.pembayaran.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      x-data="{ 
                          fileName: '', 
                          filePreview: null,
                          fileType: ''
                      }"
                      class="p-8">
                    @csrf

                    <!-- Data Mahasiswa -->
                    <div class="mb-8 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-200">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user-circle text-indigo-600 mr-2"></i>
                            Data Mahasiswa
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Nama Lengkap:</span>
                                <p class="font-semibold text-gray-800">{{ $mahasiswa->nama_lengkap }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Email:</span>
                                <p class="font-semibold text-gray-800">{{ $mahasiswa->user->email }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">No. Telepon:</span>
                                <p class="font-semibold text-gray-800">{{ $mahasiswa->no_hp }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Program Studi:</span>
                                <p class="font-semibold text-gray-800">{{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Jumlah Pembayaran (Readonly) -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-money-bill-wave text-indigo-600 mr-2"></i>
                            Jumlah Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                            <input type="text" 
                                   value="{{ number_format($biaya, 0, ',', '.') }}"
                                   class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 bg-gray-50 text-gray-500 rounded-xl focus:border-gray-200 cursor-not-allowed font-bold"
                                   readonly>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            <i class="fas fa-lock mr-1 text-gray-400"></i>
                            Nominal pembayaran sudah ditentukan oleh sistem.
                        </p>
                    </div>

                    <!-- Tanggal Bayar -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="far fa-calendar text-indigo-600 mr-2"></i>
                            Tanggal Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="tanggal_bayar" 
                               max="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all"
                               required>
                        @error('tanggal_bayar')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pilih tanggal saat Anda melakukan pembayaran
                        </p>
                    </div>

                    <!-- Upload Bukti Pembayaran -->
                    <div class="mb-8">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-file-image text-indigo-600 mr-2"></i>
                            Bukti Pembayaran <span class="text-red-500">*</span>
                        </label>
                        
                        <!-- Upload Area -->
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-500 transition-all"
                             :class="fileName ? 'border-indigo-500 bg-indigo-50' : ''">
                            <input type="file" 
                                   name="bukti_pembayaran" 
                                   id="bukti_pembayaran"
                                   accept="image/*,.pdf"
                                   @change="
                                       const file = $event.target.files[0];
                                       if(file) {
                                           fileName = file.name;
                                           fileType = file.type;
                                           if(file.type.startsWith('image/')) {
                                               const reader = new FileReader();
                                               reader.onload = (e) => filePreview = e.target.result;
                                               reader.readAsDataURL(file);
                                           } else {
                                               filePreview = null;
                                           }
                                       }
                                   "
                                   class="hidden"
                                   required>
                            
                            <label for="bukti_pembayaran" class="cursor-pointer">
                                <!-- Preview Image -->
                                <div x-show="filePreview && fileType.startsWith('image/')" class="mb-4">
                                    <img :src="filePreview" class="max-h-64 mx-auto rounded-lg shadow-lg">
                                </div>

                                <!-- Icon -->
                                <div x-show="!fileName" class="mb-4">
                                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto">
                                        <i class="fas fa-cloud-upload-alt text-indigo-600 text-3xl"></i>
                                    </div>
                                </div>

                                <!-- Success Icon -->
                                <div x-show="fileName && !fileType.startsWith('image/')" class="mb-4">
                                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                                        <i class="fas fa-file-pdf text-green-600 text-3xl"></i>
                                    </div>
                                </div>

                                <!-- Text -->
                                <div>
                                    <p class="text-gray-700 font-semibold mb-2" x-show="!fileName">
                                        Klik untuk upload bukti pembayaran
                                    </p>
                                    <p class="text-green-600 font-semibold mb-2" x-show="fileName">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span x-text="fileName"></span>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Format: JPG, PNG, PDF (Max 2MB)
                                    </p>
                                </div>
                            </label>
                        </div>

                        @error('bukti_pembayaran')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="submit" 
                                class="flex-1 px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Upload Pembayaran
                        </button>
                        <a href="{{ route('mahasiswa.pembayaran.index') }}" 
                           class="flex-1 px-6 py-4 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-all duration-200 font-semibold text-center">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Informasi Rekening -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-university text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Informasi Rekening</h3>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="bg-white/10 rounded-lg p-3">
                        <p class="text-blue-100 mb-1">Bank</p>
                        <p class="font-bold text-lg">BCA</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-3">
                        <p class="text-blue-100 mb-1">No. Rekening</p>
                        <p class="font-bold text-lg">1234567890</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-3">
                        <p class="text-blue-100 mb-1">Atas Nama</p>
                        <p class="font-bold">Universitas LSP</p>
                    </div>
                </div>
            </div>

            <!-- Petunjuk -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-lightbulb text-indigo-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Petunjuk Upload</h3>
                </div>
                <ol class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 text-xs font-bold">1</span>
                        <span>Lakukan pembayaran ke rekening yang tertera</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 text-xs font-bold">2</span>
                        <span>Simpan bukti transfer/struk pembayaran</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 text-xs font-bold">3</span>
                        <span>Upload foto/scan bukti pembayaran yang jelas</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 text-xs font-bold">4</span>
                        <span>Tunggu verifikasi dari admin (1x24 jam)</span>
                    </li>
                </ol>
            </div>

            <!-- Biaya Pendaftaran -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-tags text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Biaya Pendaftaran</h3>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-white/20">
                        <span>Formulir Pendaftaran</span>
                        <span class="font-bold">Rp 200.000</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-white/20">
                        <span>Biaya Administrasi</span>
                        <span class="font-bold">Rp 50.000</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="font-bold text-lg">Total</span>
                        <span class="font-bold text-2xl">Rp {{ number_format($biaya, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Bantuan -->
            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4">
                <div class="flex items-start">
                    <i class="fas fa-question-circle text-yellow-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Butuh Bantuan?</h4>
                        <p class="text-sm text-gray-600 mb-3">Hubungi kami jika ada kendala</p>
                        <div class="space-y-2 text-sm">
                            <a href="tel:+621234567890" class="flex items-center text-gray-700 hover:text-indigo-600">
                                <i class="fas fa-phone mr-2"></i>+62 123 4567 890
                            </a>
                            <a href="mailto:pmb@university.ac.id" class="flex items-center text-gray-700 hover:text-indigo-600">
                                <i class="fas fa-envelope mr-2"></i>pmb@university.ac.id
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection