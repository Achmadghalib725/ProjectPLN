<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistem Inventaris Gudang PLN</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50">
        
        <nav class="bg-[#035b71] shadow-lg border-b-4 border-[#ffff00]"> 
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#ffff00] rounded flex items-center justify-center shadow-md border border-white/20">
                            <svg class="w-7 h-7 text-[#ff0000] drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-white text-lg leading-none tracking-wide">PLN</span>
                            <span class="text-[10px] text-[#00aff0] font-bold tracking-widest uppercase">Inventory System</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-white hover:text-[#ffff00] font-semibold transition">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="bg-[#00aff0] text-white px-5 py-2 rounded-lg font-bold hover:bg-[#008ec2] transition shadow-md border border-white/10">
                                    Log in
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <div class="relative bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto">
                <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                    
                    <svg class="hidden lg:block absolute right-0 inset-y-0 h-full w-48 text-white transform translate-x-1/2" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                        <polygon points="50,0 100,0 50,100 0,100" />
                    </svg>

                    <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                        <div class="sm:text-center lg:text-left">
                            <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                                <span class="block xl:inline">Sistem Manajemen</span>
                                <span class="block text-[#035b71] xl:inline">Gudang & Logistik</span>
                            </h1>
                            <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                                Platform terintegrasi untuk pengelolaan stok material, peminjaman peralatan, dan pelaporan aset Unit Induk PLN secara <i>real-time</i>, akurat, dan transparan.
                            </p>
                            <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start gap-3">
                                <div class="rounded-md shadow">
                                    <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#035b71] hover:bg-[#024455] md:py-4 md:text-lg md:px-10 transition">
                                        Masuk ke Sistem
                                    </a>
                                </div>
                                <div class="rounded-md shadow">
                                    <a href="#" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-[#035b71] bg-blue-50 hover:bg-blue-100 md:py-4 md:text-lg md:px-10 transition">
                                        Panduan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
            
            <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-[#f0f9ff] flex items-center justify-center border-l-8 border-[#ffff00]">
                <div class="text-center p-10">
                    <div class="w-40 h-40 bg-white rounded-full mx-auto flex items-center justify-center shadow-lg mb-6 border-4 border-[#00aff0]">
                         <svg class="h-20 w-20 text-[#035b71]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <p class="mt-4 text-[#035b71] font-bold text-xl">Unit Induk Distribusi</p>
                    <div class="mt-2 text-sm text-gray-500 font-semibold italic">"Listrik untuk Kehidupan yang Lebih Baik"</div>
                </div>
            </div>
        </div>

        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-[#00aff0] font-bold tracking-wide uppercase">Fitur Utama</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Handal & Terpercaya
                    </p>
                </div>

                <div class="mt-10">
                    <dl class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                        <div class="relative">
                            <dt>
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-[#00aff0] text-white shadow-lg">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <p class="ml-16 text-lg leading-6 font-medium text-[#035b71]">Monitoring Stok</p>
                            </dt>
                            <dd class="mt-2 ml-16 text-base text-gray-500">
                                Pantau ketersediaan material cadangan dan peralatan kerja di seluruh unit gudang secara terpusat.
                            </dd>
                        </div>

                        <div class="relative">
                            <dt>
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-[#00aff0] text-white shadow-lg">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <p class="ml-16 text-lg leading-6 font-medium text-[#035b71]">Surat Jalan Digital</p>
                            </dt>
                            <dd class="mt-2 ml-16 text-base text-gray-500">
                                Pembuatan dan pelacakan surat jalan pengiriman antar unit yang lebih cepat dan paperless.
                            </dd>
                        </div>

                        <div class="relative">
                            <dt>
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-[#00aff0] text-white shadow-lg">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <p class="ml-16 text-lg leading-6 font-medium text-[#035b71]">Keamanan Akses</p>
                            </dt>
                            <dd class="mt-2 ml-16 text-base text-gray-500">
                                Hak akses bertingkat untuk Admin, Operator Gudang, dan Security untuk menjaga integritas data.
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <footer class="bg-[#035b71] border-t-8 border-[#ffff00]">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-base text-gray-200">
                    &copy; {{ date('Y') }} PT PLN (Persero). All rights reserved.
                </p>
                <div class="mt-2 flex justify-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-[#ff0000]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#ffff00]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#00aff0]"></span>
                </div>
            </div>
        </footer>
    </body>
</html>
