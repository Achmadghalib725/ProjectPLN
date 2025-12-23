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
        <div class="hidden lg:flex min-h-screen bg-gradient-to-br from-[#035b71] via-[#047d99] to-[#00aff0]">
            <div class="w-full flex flex-col items-center justify-center p-12">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
