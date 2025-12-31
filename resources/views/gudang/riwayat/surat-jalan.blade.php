{{-- Statistics Cards --}}
<div class="overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 mb-4 sm:mb-6">
    <div class="flex sm:grid sm:grid-cols-4 gap-3 sm:gap-4 min-w-max sm:min-w-0">
        <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-lg p-2.5 sm:p-3">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5">
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Total Selesai</p>
                        <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-lg p-2.5 sm:p-3">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5">
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Transfer</p>
                        <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['transfer'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-lg p-2.5 sm:p-3">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5">
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Peminjaman</p>
                        <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['peminjaman'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-2.5 sm:p-3">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5">
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Pengembalian</p>
                        <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['pengembalian'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    $activeFilters = collect(['search', 'tipe_sj', 'tanggal_mulai', 'tanggal_selesai', 'gudang_asal', 'gudang_tujuan'])->filter(fn($f) => request()->filled($f))->count();
                @endphp
                @if($activeFilters > 0)
                    <span class="bg-[#035b71] text-white text-xs px-2 py-0.5 rounded-full">{{ $activeFilters }}</span>
                @endif
                <svg class="w-4 h-4 transition-transform duration-200" :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            @if($activeFilters > 0)
                <a href="{{ route('gudang.riwayat', ['tab' => 'surat-jalan']) }}"
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
                <a href="{{ route('gudang.riwayat', array_merge(request()->query(), ['tab' => 'surat-jalan', 'sort' => 'terbaru'])) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ (request('sort', 'terbaru') === 'terbaru') ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terbaru
                </a>
                <a href="{{ route('gudang.riwayat', array_merge(request()->query(), ['tab' => 'surat-jalan', 'sort' => 'terlama'])) }}"
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
            <form method="GET" action="{{ route('gudang.riwayat') }}">
                <input type="hidden" name="tab" value="surat-jalan">
                <input type="hidden" name="sort" value="{{ request('sort', 'terbaru') }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Search Input --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Cari Nomor Surat</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   class="block w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition"
                                   placeholder="Contoh: 705/SJ251223/2025">
                        </div>
                    </div>

                    {{-- Tipe Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tipe Surat Jalan</label>
                        <select name="tipe_sj"
                                class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                            <option value="">Semua Tipe</option>
                            <option value="TRANSFER" {{ ($tipe_sj ?? '') === 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                            <option value="PEMINJAMAN" {{ ($tipe_sj ?? '') === 'PEMINJAMAN' ? 'selected' : '' }}>Peminjaman</option>
                            <option value="PENGEMBALIAN" {{ ($tipe_sj ?? '') === 'PENGEMBALIAN' ? 'selected' : '' }}>Pengembalian</option>
                        </select>
                    </div>

                    {{-- Gudang Asal Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Gudang Asal</label>
                        <input type="text"
                               name="gudang_asal"
                               value="{{ request('gudang_asal') }}"
                               class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition"
                               placeholder="Cari gudang asal...">
                    </div>

                    {{-- Gudang Tujuan Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Gudang Tujuan</label>
                        <input type="text"
                               name="gudang_tujuan"
                               value="{{ request('gudang_tujuan') }}"
                               class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition"
                               placeholder="Cari gudang tujuan...">
                    </div>

                    {{-- Date Range --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Dari Tanggal</label>
                        <input type="date"
                               name="tanggal_mulai"
                               value="{{ $tanggal_mulai ?? '' }}"
                               class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Sampai Tanggal</label>
                        <input type="date"
                               name="tanggal_selesai"
                               value="{{ $tanggal_selesai ?? '' }}"
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

{{-- Table Section --}}
<div id="surat-jalan-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" data-ajax-container>
    {{-- Mobile Card View --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @forelse($suratJalans ?? [] as $sj)
            @php
                $tipeLabel = $sj->tipe ?? '-';
                $tipeClass = match ($tipeLabel) {
                    'PEMINJAMAN' => 'bg-blue-100 text-blue-800',
                    'PENGEMBALIAN' => 'bg-yellow-100 text-yellow-800',
                    'TRANSFER' => 'bg-purple-100 text-purple-800',
                    default => 'bg-gray-100 text-gray-700',
                };
            @endphp
            <a href="{{ route('gudang.surat-jalan.show', $sj->id) }}" class="block p-4 hover:bg-gray-50 active:bg-gray-100 transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 text-sm truncate">{{ $sj->nomor ?? '-' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $sj->tanggal?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-100 text-green-800">
                            SELESAI
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $tipeClass }}">
                            {{ $tipeLabel }}
                        </span>
                    </div>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <p class="text-gray-400">Dari</p>
                        <p class="text-gray-700 truncate">{{ $sj->gudangAsal->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Ke</p>
                        <p class="text-gray-700 truncate">{{ $sj->gudang_tujuan_is_custom ? ($sj->gudang_tujuan_custom_nama ?? 'Gudang Lainnya') : ($sj->gudangTujuan->nama ?? '-') }}</p>
                    </div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs">
                    <span class="text-gray-500">{{ $sj->items_count ?? 0 }} item / {{ $sj->items_sum_jumlah ?? 0 }} unit</span>
                    <span class="text-[#035b71] font-medium flex items-center gap-1">
                        Detail
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </a>
        @empty
            <div class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="mt-2 font-medium text-sm">Belum ada riwayat surat jalan selesai</p>
                @if(request()->hasAny(['search', 'tipe_sj', 'tanggal_mulai', 'tanggal_selesai', 'gudang_asal', 'gudang_tujuan']))
                    <p class="text-xs mt-1">Coba ubah filter pencarian Anda</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang Asal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang Tujuan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ringkasan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembuat</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($suratJalans ?? [] as $index => $sj)
                    @php
                        $tipeLabel = $sj->tipe ?? '-';
                        $tipeClass = match ($tipeLabel) {
                            'PEMINJAMAN' => 'bg-blue-100 text-blue-800',
                            'PENGEMBALIAN' => 'bg-yellow-100 text-yellow-800',
                            'TRANSFER' => 'bg-purple-100 text-purple-800',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $suratJalans->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ $sj->nomor ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sj->tanggal?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sj->gudangAsal->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sj->gudang_tujuan_is_custom ? ($sj->gudang_tujuan_custom_nama ?? 'Gudang Lainnya') : ($sj->gudangTujuan->nama ?? '-') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sj->picTujuan->nama ?? $sj->pic_tujuan_custom_nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $tipeClass }}">
                                {{ $tipeLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sj->items_count ?? 0 }} item / {{ $sj->items_sum_jumlah ?? 0 }} unit
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sj->pembuat->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('gudang.surat-jalan.show', $sj->id) }}"
                                   class="text-[#035b71] hover:text-[#00aff0]"
                                   title="Lihat Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('gudang.surat-jalan.pdf', $sj->id) }}"
                                   class="text-green-600 hover:text-green-800"
                                   title="Download PDF">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-10 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="mt-2 font-medium">Belum ada riwayat surat jalan selesai</p>
                            @if(request()->hasAny(['search', 'tipe_sj', 'tanggal_mulai', 'tanggal_selesai', 'gudang_asal', 'gudang_tujuan']))
                                <p class="text-sm mt-1">Coba ubah filter pencarian Anda</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(isset($suratJalans) && $suratJalans->hasPages())
        <div class="px-4 sm:px-6 py-4 border-t border-gray-100" data-ajax-pagination>
            {{ $suratJalans->links() }}
        </div>
    @endif
</div>
