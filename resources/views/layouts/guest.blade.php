<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .fade-in-up { animation: fadeInUp 0.6s ease-out; }
            
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            .float { animation: float 3s ease-in-out infinite; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
        <div class="min-h-screen flex">
            <!-- Left Side - Image & Branding -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>

                <div class="relative z-10 flex flex-col justify-center items-center w-full p-12 text-white">
                    <!-- Logo -->
                    <div class="mb-8 fade-in-up">
                        <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=150&h=150&fit=crop" 
                             alt="Logo" 
                             class="w-24 h-24 rounded-full shadow-2xl ring-4 ring-white/50">
                    </div>

               

                    <!-- Text Content -->
                    <div class="text-center fade-in-up" style="animation-delay: 0.3s">
                        <h1 class="text-4xl font-bold mb-4">Sistem PMB 2025</h1>
                        <p class="text-xl text-indigo-100 mb-2">Penerimaan Mahasiswa Baru</p>
                        <p class="text-indigo-200">Mulai perjalanan akademik Anda bersama kami!</p>
                    </div>

                    <!-- Features -->
                    <div class="mt-12 grid grid-cols-3 gap-6 fade-in-up" style="animation-delay: 0.4s">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-rocket text-2xl"></i>
                            </div>
                            <p class="text-sm">Pendaftaran Mudah</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-shield-alt text-2xl"></i>
                            </div>
                            <p class="text-sm">Aman & Terpercaya</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-graduation-cap text-2xl"></i>
                            </div>
                            <p class="text-sm">Kualitas Terjamin</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
                <div class="w-full max-w-md">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden text-center mb-8 fade-in-up">
                        <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=100&h=100&fit=crop" 
                             alt="Logo" 
                             class="w-20 h-20 rounded-full shadow-xl mx-auto mb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Sistem PMB 2025</h2>
                    </div>

                    <!-- Form Card -->
                    <div class="bg-white rounded-2xl shadow-2xl p-8 fade-in-up" style="animation-delay: 0.2s">
                        {{ $slot }}
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 text-center text-sm text-gray-600 fade-in-up" style="animation-delay: 0.3s">
                        <p>© 2025 Sistem PMB. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>