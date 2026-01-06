<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <span class="bg-[#ffff00] text-[#035b71] text-xs font-bold px-3 py-1 rounded-full uppercase">
                ROLE: {{ Auth::user()->role }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border-l-4 border-[#035b71]">
                <div class="p-6 text-gray-900 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-[#035b71]">Selamat Datang, {{ Auth::user()->name }}!</h3>
                        <p class="text-sm text-gray-500">
                            Lokasi Tugas: 
                            <strong class="text-gray-800">
                                {{ Auth::user()->gudang ? Auth::user()->gudang->nama : 'Kantor Pusat / Global' }}
                            </strong>
                        </p>
                    </div>
                    <div class="text-right text-xs text-gray-400">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </div>
                </div>
            </div>

            @if(Auth::user()->role === 'admin')
                @include('dashboard.admin')
            
            @elseif(Auth::user()->role === 'operator_gudang')
                @include('dashboard.operator')

            @elseif(Auth::user()->role === 'manager')
                @include('dashboard.manager')
            
            @elseif(Auth::user()->role === 'security')
                @include('dashboard.security')

            @elseif(Auth::user()->role === 'penerima')
                @include('dashboard.penerima')
            
            @else
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">Role akun Anda tidak dikenali. Hubungi Admin IT.</span>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
