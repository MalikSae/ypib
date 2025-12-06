<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Mahasiswa') - Sistem PMB</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float { animation: float 3s ease-in-out infinite; }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up { animation: fadeInUp 0.6s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen">
    
    <!-- Navigation Bar -->
    <nav x-data="{ open: false }" class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=50&h=50&fit=crop" 
                         alt="Logo" 
                         class="w-10 h-10 rounded-full shadow-md">
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Portal Mahasiswa
                        </h1>
                        <p class="text-xs text-gray-500">Sistem PMB 2025</p>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('mahasiswa.dashboard') }}" 
                       class="px-4 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="{{ route('mahasiswa.pendaftaran.create') }}" 
                       class="px-4 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('mahasiswa.pendaftaran.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fas fa-file-alt mr-2"></i>Pendaftaran
                    </a>
                    <a href="{{ route('mahasiswa.pembayaran.index') }}" 
                       class="px-4 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('mahasiswa.pembayaran.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fas fa-wallet mr-2"></i>Pembayaran
                    </a>
                    <a href="{{ route('mahasiswa.pengumuman.index') }}" 
                       class="px-4 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('mahasiswa.pengumuman.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fas fa-bell mr-2"></i>Pengumuman
                    </a>
                </div>

                <!-- User Menu -->
                <div x-data="{ userMenu: false }" class="relative hidden md:block">
                    <button @click="userMenu = !userMenu" 
                            class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff" 
                             alt="Profile" 
                             class="w-8 h-8 rounded-full">
                        <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>
                    
                    <div x-show="userMenu" 
                         @click.away="userMenu = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user-edit mr-2"></i>Edit Profile
                        </a>
                        <hr class="my-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="open = !open" class="md:hidden text-gray-600">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div x-show="open" 
                 x-transition
                 class="md:hidden py-4 border-t">
                <a href="{{ route('mahasiswa.dashboard') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="{{ route('mahasiswa.pendaftaran.create') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                    <i class="fas fa-file-alt mr-2"></i>Pendaftaran
                </a>
                <a href="{{ route('mahasiswa.pembayaran.index') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                    <i class="fas fa-wallet mr-2"></i>Pembayaran
                </a>
                <a href="{{ route('mahasiswa.pengumuman.index') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                    <i class="fas fa-bell mr-2"></i>Pengumuman
                </a>
                <hr class="my-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Alert Messages -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm fade-in-up">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm fade-in-up">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                <p class="text-red-700 font-medium">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @if(session('info'))
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg shadow-sm fade-in-up">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-500 text-xl mr-3"></i>
                <p class="text-blue-700 font-medium">{{ session('info') }}</p>
            </div>
        </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-indigo-900 to-purple-900 text-white mt-16">
        <div class="container mx-auto px-4 py-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-3">Tentang Kami</h3>
                    <p class="text-indigo-200 text-sm">Sistem Penerimaan Mahasiswa Baru terbaik untuk masa depan pendidikan yang lebih cerah.</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-3">Kontak</h3>
                    <p class="text-indigo-200 text-sm"><i class="fas fa-phone mr-2"></i>+62 123 4567 890</p>
                    <p class="text-indigo-200 text-sm"><i class="fas fa-envelope mr-2"></i>pmb@university.ac.id</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-3">Ikuti Kami</h3>
                    <div class="flex space-x-3">
                        <a href="#" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-indigo-700 mt-6 pt-6 text-center text-indigo-300 text-sm">
                © 2025 Sistem PMB. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>