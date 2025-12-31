{{-- Filter & Sort Section --}}
<div class="bg-white overflow-hidden shadow-sm rounded-xl mb-4 sm:mb-6" x-data="{ showFilter: false }">
    {{-- Filter Toggle Button --}}
    <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <button @click="showFilter = !showFilter"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition font-medium text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter & Urutkan</span>
                @php
                    $activeFilters = collect(['search', 'tipe', 'referensi', 'tanggal_mulai', 'tanggal_selesai'])->filter(fn($f) => request()->filled($f))->count();
                @endphp
                @if($activeFilters > 0)
                    <span class="bg-[#035b71] text-white text-xs px-2 py-0.5 rounded-full">{{ $activeFilters }}</span>
                @endif
                <svg class="w-4 h-4 transition-transform duration-200" :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            @if($activeFilters > 0)
                <a href="{{ route('gudang.riwayat', ['tab' => 'pergerakan']) }}"
                   class="text-sm text-gray-500 hover:text-red-600 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Hapus Filter
                </a>
            @endif
        </div>

        {{-- Quick Sort --}}
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Urutkan:</span>
            <div class="inline-flex rounded-lg border border-gray-200 p-1 bg-gray-50">
                <a href="{{ route('gudang.riwayat', array_merge(request()->query(), ['tab' => 'pergerakan', 'sort' => 'terbaru'])) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ (request('sort', 'terbaru') === 'terbaru') ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terbaru
                </a>
                <a href="{{ route('gudang.riwayat', array_merge(request()->query(), ['tab' => 'pergerakan', 'sort' => 'terlama'])) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ request('sort') === 'terlama' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terlama
                </a>
            </div>
        </div>
    </div>

    {{-- Expandable Filter Panel --}}
    <div x-show="showFilter"
         x-collapse
         x-cloak>
        <div class="px-4 pb-4 border-t border-gray-100 pt-4">
            <form method="GET" action="{{ route('gudang.riwayat') }}" id="filterForm">
                <input type="hidden" name="tab" value="pergerakan">
                <input type="hidden" name="sort" value="{{ request('sort', 'terbaru') }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Search Input --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Pencarian</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   class="block w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition"
                                   placeholder="Cari nama barang, keterangan...">
                        </div>
                    </div>

                    {{-- Tipe Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tipe Pergerakan</label>
                        <select name="tipe"
                                class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                            <option value="">Semua Tipe</option>
                            <option value="IN" {{ request('tipe') === 'IN' ? 'selected' : '' }}>IN (Masuk)</option>
                            <option value="OUT" {{ request('tipe') === 'OUT' ? 'selected' : '' }}>OUT (Keluar)</option>
                        </select>
                    </div>

                    {{-- Referensi Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Referensi</label>
                        <select name="referensi"
                                class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                            <option value="">Semua Referensi</option>
                            @foreach($referensiTypes ?? [] as $type)
                                <option value="{{ $type }}" {{ request('referensi') === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Mulai</label>
                        <input type="date"
                               name="tanggal_mulai"
                               value="{{ request('tanggal_mulai') }}"
                               class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Selesai</label>
                        <input type="date"
                               name="tanggal_selesai"
                               value="{{ request('tanggal_selesai') }}"
                               class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                    </div>
                </div>

                {{-- Apply Button --}}
                <div class="mt-4 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-[#035b71] hover:bg-[#024a5c] text-white text-sm font-medium rounded-lg transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Movement History Table --}}
<div id="pergerakan-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" data-ajax-container>
    {{-- Mobile Card View --}}
    <div class="sm:hidden">
        @forelse($movements ?? [] as $movement)
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
    <div class="hidden sm:block overflow-x-auto">
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
                    @forelse($movements ?? [] as $movement)
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
    @if(isset($movements) && $movements->hasPages())
        <div class="px-6 py-4 border-t border-gray-100" data-ajax-pagination>
            {{ $movements->links() }}
        </div>
    @endif
</div>
