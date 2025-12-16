<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PLN Inventory') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#f0f9ff]">
            
            <div class="flex flex-col items-center mb-6">
                <a href="/" class="flex flex-col items-center gap-2">
                    <div class="w-20 h-20 bg-[#ffff00] rounded-lg flex items-center justify-center shadow-lg border-2 border-white">
                        <svg class="w-12 h-12 text-[#ff0000] drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="text-center mt-2">
                        <h1 class="text-2xl font-extrabold text-[#035b71] tracking-wide">PLN</h1>
                        <span class="text-xs font-bold text-[#00aff0] uppercase tracking-widest">Inventory System</span>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-lg overflow-hidden sm:rounded-xl border-t-4 border-[#035b71]">
                {{ $slot }}
            </div>

            <div class="mt-8 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} PT PLN (Persero). All rights reserved.
            </div>
        </div>
    </body>
</html>