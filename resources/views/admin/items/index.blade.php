<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Master Barang') }}
            </h2>
            <a href="#" class="px-4 py-2 bg-pln-light text-white rounded-lg text-sm hover:bg-cyan-600 transition">
                + Tambah Barang
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-pln-light">
                <div class="p-6 text-gray-900">
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-white uppercase bg-pln-light">
                                <tr>
                                    <th class="px-6 py-3">Kode Barang</th>
                                    <th class="px-6 py-3">Nama Barang</th>
                                    <th class="px-6 py-3">Satuan</th>
                                    <th class="px-6 py-3">Kategori</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-mono text-gray-700 font-bold">
                                        {{ $item->kode ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $item->nama }}
                                    </td>
                                    <td class="px-6 py-4">{{ $item->satuan }}</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            {{ $item->kategori ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="text-pln-primary hover:text-pln-light transition mx-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button class="text-red-500 hover:text-red-700 transition mx-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">
                                        Data barang masih kosong.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $items->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>