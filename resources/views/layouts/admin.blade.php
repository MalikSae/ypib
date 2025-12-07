<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Sistem PMB</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk interaktivitas -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome untuk icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .slide-in { animation: slideIn 0.5s ease-out; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.6s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    
    <!-- Sidebar -->
    <div x-data="{ open: false }" class="flex">
        <!-- Mobile Menu Button -->
        <button @click="open = !open" class="lg:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white p-3 rounded-lg shadow-lg">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Sidebar -->
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed lg:static lg:translate-x-0 inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-2xl transform transition-transform duration-300 ease-in-out">
            
            <!-- Logo dengan Gambar -->
            <div class="p-6 border-b border-blue-700">
                <div class="flex items-center space-x-3">
                    <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=100&h=100&fit=crop" 
                         alt="Logo" 
                         class="w-12 h-12 rounded-full ring-2 ring-white shadow-lg">
                    <div>
                        <h2 class="text-xl font-bold">Admin Panel</h2>
                        <p class="text-xs text-blue-300">Sistem PMB 2025</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="mt-6 px-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700 shadow-lg' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ route('admin.akun.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 {{ request()->routeIs('admin.akun.*') ? 'bg-blue-700 shadow-lg' : '' }}">
                    <i class="fas fa-user-check w-5"></i>
                    <span>Verifikasi Akun</span>
                </a>

                <a href="{{ route('admin.mahasiswa.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 {{ request()->routeIs('admin.mahasiswa.*') ? 'bg-blue-700 shadow-lg' : '' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>Data Mahasiswa</span>
                </a>
                
                <a href="{{ route('admin.pembayaran.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 {{ request()->routeIs('admin.pembayaran.*') ? 'bg-blue-700 shadow-lg' : '' }}">
                    <i class="fas fa-money-bill-wave w-5"></i>
                    <span>Pembayaran</span>
                </a>
                
                <a href="{{ route('admin.pengumuman.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 {{ request()->routeIs('admin.pengumuman.*') ? 'bg-blue-700 shadow-lg' : '' }}">
                    <i class="fas fa-bullhorn w-5"></i>
                    <span>Pengumuman</span>
                </a>
                
                <a href="{{ route('admin.jurusan.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 {{ request()->routeIs('admin.jurusan.*') ? 'bg-blue-700 shadow-lg' : '' }}">
                    <i class="fas fa-graduation-cap w-5"></i>
                    <span>Jurusan</span>
                </a>
            </nav>

            <!-- User Profile Card -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-blue-700">
                <div class="flex items-center space-x-3 mb-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff" 
                         alt="Profile" 
                         class="w-10 h-10 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-blue-300">Administrator</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-0 min-h-screen">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 ml-12 lg:ml-0">
                            <h1 class="text-2xl font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
                            <p class="text-sm text-gray-500 mt-1">@yield('subtitle', 'Selamat datang di panel admin')</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-700">{{ now()->isoFormat('dddd') }}</p>
                                <p class="text-xs text-gray-500">{{ now()->isoFormat('D MMMM YYYY') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="p-6">
                <!-- Alert Messages -->
                @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm fade-in">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm fade-in">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                @if(session('info'))
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg shadow-sm fade-in">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                        <p class="text-blue-700">{{ session('info') }}</p>
                    </div>
                </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>