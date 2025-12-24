<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center">
                        <div class="text-center sm:text-left flex-1">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Riwayat Pergerakan Stok</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                {{ Auth::user()->gudang->nama ?? 'Gudang Saya' }}
                            </p>
                        </div>
                        <div class="hidden sm:flex items-center gap-2">
                            <svg class="w-8 h-8 text-[#035b71]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg sm:rounded-lg mb-4 sm:mb-6" x-data="{ showFilter: false }">
                {{-- Mobile Filter Toggle --}}
                <div class="sm:hidden p-4 border-b border-gray-200">
                    <button @click="showFilter = !showFilter" type="button" class="w-full flex items-center justify-between text-gray-700">
                        <span class="flex items-center gap-2 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filter & Pencarian
                            @if(request()->hasAny(['search', 'tipe', 'referensi']))
                                <span class="bg-[#035b71] text-white text-xs px-2 py-0.5 rounded-full">Aktif</span>
                            @endif
                        </span>
                        <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': showFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                {{-- Filter Form --}}
                <div class="p-4 sm:p-6" :class="{ 'hidden sm:block': !showFilter }">
                    <form method="GET" action="{{ route('gudang.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4">
                        {{-- Search Input --}}
                        <div class="sm:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text"
                                       name="search"
                                       id="search"
                                       value="{{ request('search') }}"
                                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-[#035b71] focus:border-[#035b71] sm:text-sm"
                                       placeholder="Cari nama barang, keterangan...">
                            </div>
                        </div>

                        {{-- Tipe Filter --}}
                        <div>
                            <label for="tipe" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                            <select name="tipe"
                                    id="tipe"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#035b71] focus:border-[#035b71] sm:text-sm">
                                <option value="">Semua Tipe</option>
                                <option value="IN" {{ request('tipe') === 'IN' ? 'selected' : '' }}>IN (Masuk)</option>
                                <option value="OUT" {{ request('tipe') === 'OUT' ? 'selected' : '' }}>OUT (Keluar)</option>
                            </select>
                        </div>

                        {{-- Referensi Filter --}}
                        <div>
                            <label for="referensi" class="block text-sm font-medium text-gray-700 mb-1">Referensi</label>
                            <select name="referensi"
                                    id="referensi"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#035b71] focus:border-[#035b71] sm:text-sm">
                                <option value="">Semua Referensi</option>
                                @foreach($referensiTypes as $type)
                                    <option value="{{ $type }}" {{ request('referensi') === $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="sm:col-span-4 flex gap-2">
                            <button type="submit"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-[#035b71] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0] focus:bg-[#00aff0] active:bg-[#024a5c] focus:outline-none focus:ring-2 focus:ring-[#035b71] focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('gudang.riwayat') }}"
                               class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Movement History Table --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg sm:rounded-lg">
                {{-- Mobile Card View --}}
                <div class="sm:hidden">
                    @forelse($movements as $movement)
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 text-sm">{{ $movement->item->nama ?? '-' }}</h3>
                                    <p class="text-xs text-gray-500">{{ $movement->item->kode ?? '-' }}</p>
                                </div>
                                @if($movement->tipe === 'IN')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        IN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        OUT
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-3 gap-2 mb-3 text-xs">
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-gray-500">Jumlah</p>
                                    <p class="font-bold {{ $movement->tipe === 'IN' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movement->tipe === 'IN' ? '+' : '-' }}{{ number_format($movement->jumlah) }}
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-gray-500">Sebelum</p>
                                    <p class="font-medium text-gray-700">{{ number_format($movement->stok_sebelum) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-gray-500">Sesudah</p>
                                    <p class="font-medium text-gray-700">{{ number_format($movement->stok_sesudah) }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-gray-100 rounded">{{ $movement->referensi_type }}</span>
                                    <span>{{ $movement->creator->name ?? 'System' }}</span>
                                </div>
                                <span>{{ $movement->created_at->format('d M Y H:i') }}</span>
                            </div>

                            @if($movement->keterangan)
                                <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2">
                                    {{ $movement->keterangan }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2 font-medium">Belum ada riwayat pergerakan stok</p>
                            @if(request()->has('search') || request()->has('tipe') || request()->has('referensi'))
                                <p class="mt-1 text-sm">Coba ubah filter pencarian Anda</p>
                            @endif
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden sm:block p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Sebelum</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Sesudah</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referensi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($movements as $movement)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>{{ $movement->created_at->format('d M Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $movement->created_at->format('H:i') }} WIB</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="font-medium">{{ $movement->item->nama ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $movement->item->kode ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($movement->tipe === 'IN')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                                                    </svg>
                                                    IN
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" transform="rotate(180 10 10)"/>
                                                    </svg>
                                                    OUT
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $movement->tipe === 'IN' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $movement->tipe === 'IN' ? '+' : '-' }}{{ number_format($movement->jumlah) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($movement->stok_sebelum) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ number_format($movement->stok_sesudah) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                                {{ $movement->referensi_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $movement->creator->name ?? 'System' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                            {{ $movement->keterangan ?? '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <p class="mt-2 font-medium">Belum ada riwayat pergerakan stok</p>
                                            @if(request()->has('search') || request()->has('tipe') || request()->has('referensi'))
                                                <p class="mt-1 text-sm">Coba ubah filter pencarian Anda</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($movements->hasPages())
                        <div class="mt-6">
                            {{ $movements->links() }}
                        </div>
                    @endif

                    {{-- Info Footer --}}
                    <div class="mt-4 text-sm text-gray-600">
                        Menampilkan {{ $movements->firstItem() ?? 0 }} - {{ $movements->lastItem() ?? 0 }} dari {{ $movements->total() }} riwayat
                    </div>
                </div>

                {{-- Mobile Pagination --}}
                <div class="sm:hidden px-4 pb-4">
                    @if($movements->hasPages())
                        <div class="mt-4">
                            {{ $movements->links() }}
                        </div>
                    @endif
                    <div class="mt-3 text-xs text-gray-500 text-center">
                        Menampilkan {{ $movements->firstItem() ?? 0 }} - {{ $movements->lastItem() ?? 0 }} dari {{ $movements->total() }} riwayat
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
