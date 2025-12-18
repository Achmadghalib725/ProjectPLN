<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                        Kelola Master Barang
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Daftar semua jenis barang yang terdaftar dalam sistem.</p>
                </div>
                <a href="{{ route('admin.items.create') }}" 
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-lg shadow-md hover:shadow-lg hover:from-blue-700 hover:to-cyan-600 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Barang Baru
                </a>
            </div>

            {{-- Statistik Cards (Total Item & Kategori) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-3 bg-blue-100 rounded-xl text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Jenis Barang</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $items->total() }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-3 bg-purple-100 rounded-xl text-purple-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Kategori</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $items->pluck('kategori')->unique()->count() }}</p>
                    </div>
                </div>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 flex justify-between items-center" role="alert">
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                        <span class="font-medium">Berhasil!</span> {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            {{-- Main Content Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/50 overflow-hidden">
                
                {{-- Toolbar (Filter & Search) --}}
                <div class="p-6 border-b border-gray-100 bg-white">
                    <form method="GET" action="{{ route('admin.items.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        {{-- Search --}}
                        <div class="relative w-full md:w-96 group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-cyan-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-200 rounded-lg bg-gray-50 focus:ring-cyan-500 focus:border-cyan-500 transition-all focus:bg-white" 
                                placeholder="Cari nama atau kode barang...">
                        </div>

                        {{-- Filter Kategori --}}
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <select name="kategori" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full md:w-48 p-2.5 cursor-pointer hover:bg-gray-100 transition-colors">
                                <option value="">Semua Kategori</option>
                                {{-- Mengambil kategori unik dari database secara langsung jika memungkinkan, 
                                     atau pastikan variabel kategori tersedia dari controller --}}
                                @foreach($items->pluck('kategori')->unique()->filter() as $cat)
                                    <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>

                            @if(request('search') || request('kategori'))
                                <a href="{{ route('admin.items.index') }}" class="text-sm text-red-500 hover:text-red-700 font-medium whitespace-nowrap px-3 py-2 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    Reset Filter
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50/80 border-b border-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Informasi Barang</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Kategori</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Satuan</th>
                                <th scope="col" class="px-6 py-4 text-center font-semibold tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($items as $item)
                            <tr class="bg-white hover:bg-cyan-50/30 transition-colors duration-200 group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-sm group-hover:scale-110 transition-transform duration-200">
                                                {{ strtoupper(substr($item->satuan, 0, 3)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 group-hover:text-cyan-700 transition-colors">{{ $item->nama }}</div>
                                            <div class="text-xs text-gray-500">Kode: {{ $item->kode }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-purple-100 text-purple-700 border-purple-200">
                                        {{ $item->kategori ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                    {{ $item->satuan }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-3 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.items.edit', $item->id) }}" class="text-indigo-500 hover:text-indigo-700 p-2 hover:bg-indigo-50 rounded-full transition-all duration-200" title="Edit Barang">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang {{ $item->nama }}?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-full transition-all duration-200" title="Hapus Barang">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        <p class="text-base font-medium">Belum ada data barang ditemukan.</p>
                                        <p class="text-sm">Coba ubah filter pencarian atau tambah barang baru.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>