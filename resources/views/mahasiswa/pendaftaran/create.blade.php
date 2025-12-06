@extends('layouts.mahasiswa')

@section('title', 'Form Pendaftaran')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl p-8 mb-8 text-white fade-in-up">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                <i class="fas fa-file-alt text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold">Form Pendaftaran Mahasiswa Baru</h1>
                <p class="text-indigo-100 mt-1">Lengkapi data diri Anda dengan benar</p>
            </div>
        </div>
    </div>

    <!-- Video Tutorial -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 fade-in-up" style="animation-delay: 0.1s">
        <div class="flex items-center mb-4">
            <i class="fas fa-play-circle text-red-500 text-2xl mr-3"></i>
            <h2 class="text-xl font-bold text-gray-800">Tutorial Pengisian Form</h2>
        </div>
        <div class="aspect-video rounded-xl overflow-hidden shadow-lg">
            <iframe width="100%" height="100%" 
                    src="https://www.youtube.com/embed/dQw4w9WgXcQ" 
                    title="Tutorial Pendaftaran" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
            </iframe>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('mahasiswa.pendaftaran.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Pilih Jurusan -->
        <div class="bg-white rounded-2xl shadow-xl p-8 fade-in-up" style="animation-delay: 0.2s">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-graduation-cap text-indigo-600 mr-3"></i>
                Pilih Jurusan
            </h3>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($jurusans as $jurusan)
                <label class="relative">
                    <input type="radio" name="jurusan_id" value="{{ $jurusan->id }}" 
                           class="peer sr-only" required>
                    <div class="p-6 border-2 border-gray-200 rounded-xl cursor-pointer transition-all duration-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:shadow-lg hover:border-indigo-300">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-full">
                                {{ $jurusan->kode_jurusan }}
                            </span>
                            <i class="fas fa-check-circle text-indigo-600 text-xl opacity-0 peer-checked:opacity-100"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 mb-2">{{ $jurusan->nama_jurusan }}</h4>
                        <p class="text-sm text-gray-600">{{ Str::limit($jurusan->deskripsi, 80) }}</p>
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-users mr-1"></i>Kuota: {{ $jurusan->kuota }} orang
                            </p>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('jurusan_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Data Diri -->
        <div class="bg-white rounded-2xl shadow-xl p-8 fade-in-up" style="animation-delay: 0.3s">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-user text-indigo-600 mr-3"></i>
                Data Diri
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Nama Lengkap -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                           placeholder="Masukkan nama lengkap">
                    @error('nama_lengkap')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }} required
                                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-gray-700">Laki-laki</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }} required
                                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-gray-700">Perempuan</span>
                        </label>
                    </div>
                    @error('jenis_kelamin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tempat Lahir <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                           placeholder="Kota tempat lahir">
                    @error('tempat_lahir')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Lahir <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('tanggal_lahir')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No HP -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        No. Handphone <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="no_hp" value="{{ old('no_hp') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                           placeholder="08xxxxxxxxxx">
                    @error('no_hp')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Asal Sekolah -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Asal Sekolah <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="asal_sekolah" value="{{ old('asal_sekolah') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                           placeholder="SMA/SMK/MA">
                    @error('asal_sekolah')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat Lengkap <span class="text-red-500">*</span>
                    </label>
                    <textarea name="alamat" rows="3" required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                              placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota">{{ old('alamat') }}</textarea>
                    @error('alamat')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Upload Foto -->
        <div class="bg-white rounded-2xl shadow-xl p-8 fade-in-up" style="animation-delay: 0.4s">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-camera text-indigo-600 mr-3"></i>
                Upload Foto
            </h3>

            <div class="grid md:grid-cols-2 gap-6 items-center">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Foto Diri (3x4) <span class="text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                        <input type="file" name="foto" accept="image/*" required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                               onchange="previewImage(event)">
                        <p class="text-xs text-gray-500 mt-2">PNG, JPG (Max. 2MB)</p>
                    </div>
                    @error('foto')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-center">
                    <div class="text-center">
                        <img id="preview" src="https://via.placeholder.com/200x250?text=Preview+Foto" 
                             alt="Preview" 
                             class="w-48 h-60 object-cover rounded-lg shadow-lg">
                        <p class="text-xs text-gray-500 mt-2">Preview Foto Anda</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4 fade-in-up" style="animation-delay: 0.5s">
            <a href="{{ route('mahasiswa.dashboard') }}" 
               class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" 
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl">
                <i class="fas fa-paper-plane mr-2"></i>
                Kirim Pendaftaran
            </button>
        </div>
    </form>

</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection