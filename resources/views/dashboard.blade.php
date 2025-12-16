<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold">Halo, {{ Auth::user()->name }}!</h3>
                    <p class="mb-4">
                        Status: <span class="badge bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ ucfirst(Auth::user()->role) }}</span>
                        | Gudang: <strong>{{ Auth::user()->gudang ? Auth::user()->gudang->nama : 'Pusat/Global' }}</strong>
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="border p-4 rounded hover:bg-gray-50 cursor-pointer">
                            <h4 class="font-bold">📦 Data Barang</h4>
                            <p class="text-sm text-gray-500">Lihat stok dan item</p>
                        </div>
                        <div class="border p-4 rounded hover:bg-gray-50 cursor-pointer">
                            <h4 class="font-bold">🚚 Peminjaman</h4>
                            <p class="text-sm text-gray-500">Kelola peminjaman barang</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>