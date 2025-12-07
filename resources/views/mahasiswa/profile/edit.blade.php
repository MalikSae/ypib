@extends('layouts.mahasiswa')

@section('title', 'Edit Profile')

@section('content')
<div class="fade-in-up">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    Edit Profile
                </h1>
                <p class="text-gray-600">Kelola informasi akun Anda</p>
            </div>
            <div class="hidden md:block">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center float">
                    <i class="fas fa-user-edit text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Profile Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-xl p-6 text-center">
                <!-- Avatar -->
                <div class="mb-6">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=150&background=6366f1&color=fff" 
                         alt="Avatar" 
                         class="w-32 h-32 rounded-full mx-auto shadow-lg border-4 border-indigo-100">
                </div>

                <!-- User Info -->
                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $user->name }}</h3>
                <p class="text-gray-600 mb-1">{{ $user->email }}</p>
                
                <!-- Badge Role -->
                <div class="mt-4">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-700">
                        <i class="fas fa-user-graduate mr-2"></i>
                        Mahasiswa
                    </span>
                </div>

                <!-- Stats -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        <div class="flex items-center justify-center mb-2">
                            <i class="far fa-calendar text-indigo-600 mr-2"></i>
                            <span>Bergabung {{ $user->created_at->format('d M Y') }}</span>
                        </div>
                        @if($user->email_verified_at)
                        <div class="flex items-center justify-center text-green-600">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>Email Terverifikasi</span>
                        </div>
                        @else
                        <div class="flex items-center justify-center text-yellow-600">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>Email Belum Terverifikasi</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl shadow-xl p-6 text-white mt-6">
                <h4 class="font-bold mb-4 flex items-center">
                    <i class="fas fa-link mr-2"></i>
                    Link Cepat
                </h4>
                <div class="space-y-2">
                    <a href="{{ route('mahasiswa.dashboard') }}" 
                       class="block p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="{{ route('mahasiswa.pendaftaran.create') }}" 
                       class="block p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all">
                        <i class="fas fa-file-alt mr-2"></i>Pendaftaran
                    </a>
                    <a href="{{ route('mahasiswa.pembayaran.index') }}" 
                       class="block p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all">
                        <i class="fas fa-wallet mr-2"></i>Pembayaran
                    </a>
                </div>
            </div>
        </div>

        <!-- Forms Section -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Update Profile Information -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-user-circle mr-3"></i>
                        Informasi Profile
                    </h2>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="p-8">
                    @csrf
                    @method('PATCH')

                    <!-- Nama Lengkap -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-user text-indigo-600 mr-2"></i>
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', $user->name) }}"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all"
                               placeholder="Masukkan nama lengkap"
                               required>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-envelope text-indigo-600 mr-2"></i>
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all"
                               placeholder="email@example.com"
                               required>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                        @if($user->isDirty('email') && $user->email_verified_at)
                            <p class="mt-2 text-sm text-yellow-600 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Email baru Anda perlu diverifikasi kembali
                            </p>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                        
                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" 
                               x-show="show" 
                               x-transition 
                               x-init="setTimeout(() => show = false, 3000)"
                               class="text-sm text-green-600 font-semibold flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                Berhasil diperbarui!
                            </p>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Update Password -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-purple-600 to-pink-600">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-lock mr-3"></i>
                        Ubah Password
                    </h2>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="p-8">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-key text-purple-600 mr-2"></i>
                            Password Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="current_password" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all"
                               placeholder="Masukkan password saat ini"
                               required>
                        @error('current_password', 'updatePassword')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-lock text-purple-600 mr-2"></i>
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all"
                               placeholder="Masukkan password baru"
                               required>
                        @error('password', 'updatePassword')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Password minimal 8 karakter
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-lock text-purple-600 mr-2"></i>
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="password_confirmation" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all"
                               placeholder="Ulangi password baru"
                               required>
                        @error('password_confirmation', 'updatePassword')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Update Password
                        </button>
                        
                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" 
                               x-show="show" 
                               x-transition 
                               x-init="setTimeout(() => show = false, 3000)"
                               class="text-sm text-green-600 font-semibold flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                Password berhasil diubah!
                            </p>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Delete Account (Optional) -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border-2 border-red-200">
                <div class="p-6 bg-gradient-to-r from-red-500 to-red-600">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        Zona Berbahaya
                    </h2>
                </div>

                <div class="p-8">
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800 mb-2">Hapus Akun</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Setelah akun Anda dihapus, semua data dan informasi akan dihapus secara permanen. 
                                Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                        <button x-data="" 
                                @click="$dispatch('open-modal', 'confirm-user-deletion')"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all font-semibold whitespace-nowrap">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Hapus Akun
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-data="{ show: false }" 
     @open-modal.window="show = ($event.detail === 'confirm-user-deletion')"
     @close.stop="show = false"
     x-show="show"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
         @click="show = false">
    </div>

    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full"
             @click.away="show = false">
            
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                
                <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Hapus Akun?</h3>
                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan.
                </p>

                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2">
                            Konfirmasi dengan Password
                        </label>
                        <input type="password" 
                               name="password" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200"
                               placeholder="Masukkan password Anda"
                               required>
                        @error('password', 'userDeletion')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="button" 
                                @click="show = false"
                                class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-all font-semibold">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all font-semibold">
                            Ya, Hapus Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection