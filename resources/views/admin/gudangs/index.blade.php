<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Lokasi Gudang') }}
            </h2>
            <a href="#" class="px-4 py-2 bg-yellow-400 text-pln-primary font-bold rounded-lg text-sm hover:bg-yellow-500 transition shadow-sm">
                + Tambah Gudang
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-400">
                <div class="p-6 text-gray-900">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($gudangs as $gudang)
                        <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300 group">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-bold text-lg text-pln-primary group-hover:text-pln-light transition-colors">
                                        {{ $gudang->nama }}
                                    </h3>
                                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded mt-1 inline-block">
                                        {{ $gudang->kode ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="bg-yellow-100 p-2 rounded-full text-yellow-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                            </div>
                            
                            <div class="mt-4 space-y-2">
                                <p class="text-sm text-gray-600 flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    {{ $gudang->alamat ?? 'Alamat belum diisi' }}
                                </p>
                                <p class="text-sm text-gray-600 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    Telp: <span class="font-semibold ml-1">{{ $gudang->telepon ?? '-' }}</span>
                                </p>
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end gap-2">
                                <button class="text-sm text-gray-500 hover:text-pln-primary">Detail</button>
                                <button class="text-sm text-pln-light hover:underline font-medium">Kelola Stok</button>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <p class="text-gray-500">Belum ada data gudang.</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $gudangs->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>