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
                    <img src="{{ asset('Logo_PLN.png') }}"
                         alt="PLN Logo"
                         class="h-20 mb-2">
                        <div class="text-center mt-2">
                        <h1 class="text-1xl font-extrabold text-[#00aff0] tracking-wide uppercase">E-Storage</h1>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-lg overflow-hidden sm:rounded-xl border-t-4 border-[#035b71]">
                {{ $slot }}
            </div>

            <div class="mt-8 text-center text-xs text-gray-400">
            
            </div>
        </div>
    </body>
</html>