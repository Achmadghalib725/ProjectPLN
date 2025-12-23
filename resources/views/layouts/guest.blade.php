<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PLN Inventory') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100">
        {{-- Mobile Layout --}}
        <div class="min-h-screen lg:hidden bg-[#f0f9ff]">
            {{-- Mobile Header with Logo --}}
            <div class="pt-8 pb-4 text-center">
                <a href="/" class="inline-block">
                    <img src="{{ asset('Logo_PLN_NusantaraPower.png') }}"
                         alt="PLN Logo"
                         class="h-20 mx-auto mb-3">
                    <h1 class="text-xl font-extrabold text-[#00aff0] tracking-wide uppercase">E-Storage</h1>
                </a>
            </div>

            {{-- Mobile Form --}}
            <div class="px-4 py-4">
                <div class="max-w-md mx-auto">
                    {{ $slot }}
                </div>
            </div>

            {{-- Mobile Footer --}}
            <div class="text-center py-4 text-xs text-gray-400">
                PLN Nusantara Power
            </div>
        </div>

        {{-- Desktop Layout --}}
        <div class="hidden lg:flex min-h-screen">
            {{-- Left Panel - Branding --}}
            <div class="w-1/2 bg-gradient-to-br from-[#035b71] via-[#047d99] to-[#00aff0] flex items-center justify-center p-12">
                <div class="text-center text-white max-w-md">
                    {{-- Logo --}}
                    <div class="mb-8">
                        <img src="{{ asset('Logo_PLN_NusantaraPower.png') }}"
                             alt="PLN Logo"
                             class="h-32 mx-auto bg-white rounded-2xl p-4 shadow-2xl">
                    </div>

                    {{-- Title --}}
                    <h1 class="text-4xl font-extrabold mb-3 tracking-wide">E-STORAGE</h1>
                    <p class="text-lg text-white/80 mb-10">Sistem Manajemen Inventori Gudang</p>

                    {{-- Features --}}
                    <div class="grid grid-cols-2 gap-4 text-left">
                        <div class="flex items-center gap-3 bg-white/10 rounded-lg p-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium">Kelola Stok</span>
                        </div>
                        <div class="flex items-center gap-3 bg-white/10 rounded-lg p-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium">Surat Jalan</span>
                        </div>
                        <div class="flex items-center gap-3 bg-white/10 rounded-lg p-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium">Peminjaman</span>
                        </div>
                        <div class="flex items-center gap-3 bg-white/10 rounded-lg p-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium">Laporan</span>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <p class="mt-12 text-white/50 text-sm">PLN Nusantara Power</p>
                </div>
            </div>

            {{-- Right Panel - Form --}}
            <div class="w-1/2 bg-[#f0f9ff] flex flex-col items-center justify-center p-12">
                {{-- Logo & Title for Right Panel --}}
                <div class="mb-6 text-center">
                    <a href="/" class="inline-block">
                        <img src="{{ asset('Logo_PLN_NusantaraPower.png') }}"
                             alt="PLN Logo"
                             class="h-24 mx-auto mb-3">
                        <h1 class="text-xl font-extrabold text-[#00aff0] tracking-wide uppercase">E-Storage</h1>
                    </a>
                </div>

                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
