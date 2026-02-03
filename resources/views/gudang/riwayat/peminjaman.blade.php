{{-- Statistics Cards --}}
@if(isset($stats))
<div class="grid grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-5">
        <div class="flex flex-col sm:flex-row items-center sm:items-center gap-2 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="text-center sm:text-left">
                <p class="text-xs sm:text-sm text-gray-500">Total</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-5">
        <div class="flex flex-col sm:flex-row items-center sm:items-center gap-2 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-center sm:text-left">
                <p class="text-xs sm:text-sm text-gray-500">Aktif</p>
                <p class="text-xl sm:text-2xl font-bold text-yellow-600">{{ $stats['aktif'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-5">
        <div class="flex flex-col sm:flex-row items-center sm:items-center gap-2 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-center sm:text-left">
                <p class="text-xs sm:text-sm text-gray-500">Selesai</p>
                <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $stats['selesai'] }}</p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Filter & Sort Section --}}
@php
    $activeFilters = collect(['search', 'tipe_pinjam', 'status_pinjam', 'tanggal_pinjam', 'tanggal_kembali'])->filter(fn($key) => request()->filled($key))->count();
    $currentSort = request('sort', 'terbaru');
@endphp
<div class="bg-white overflow-hidden shadow-sm rounded-xl mb-4 sm:mb-6" x-data="{ showFilter: false }">
    {{-- Header: Filter Toggle + Quick Sort --}}
    <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        {{-- Filter Toggle Button --}}
        <div class="flex items-center gap-3">
            <button @click="showFilter = !showFilter"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition font-medium text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter & Search</span>
                @if($activeFilters > 0)
                    <span class="bg-[#035b71] text-white text-xs px-2 py-0.5 rounded-full">{{ $activeFilters }}</span>
                @endif
                <svg class="w-4 h-4 transition-transform duration-200" :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            @if($activeFilters > 0)
                <a href="{{ route('gudang.riwayat', ['tab' => 'peminjaman']) }}"
                   data-ajax-tab data-ajax-target="#riwayat-content"
                   class="text-sm text-gray-500 hover:text-red-600 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Hapus Filter
                </a>
            @endif
        </div>

        {{-- Quick Sort Toggle --}}
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Urutkan:</span>
            <div class="inline-flex rounded-lg border border-gray-200 p-1 bg-gray-50">
                <a href="{{ route('gudang.riwayat', array_merge(request()->query(), ['tab' => 'peminjaman', 'sort' => 'terbaru'])) }}"
                   data-ajax-tab data-ajax-target="#riwayat-content"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $currentSort === 'terbaru' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terbaru
                </a>
                <a href="{{ route('gudang.riwayat', array_merge(request()->query(), ['tab' => 'peminjaman', 'sort' => 'terlama'])) }}"
                   data-ajax-tab data-ajax-target="#riwayat-content"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $currentSort === 'terlama' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terlama
                </a>
            </div>
        </div>
    </div>

    {{-- Expandable Filter Panel --}}
    <div x-show="showFilter" x-collapse x-cloak class="border-t border-gray-100">
        <form method="GET" action="{{ route('gudang.riwayat') }}" class="p-4" data-ajax-form data-ajax-target="#riwayat-content">
            <input type="hidden" name="tab" value="peminjaman">
            <input type="hidden" name="sort" value="{{ $currentSort }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Pencarian</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Kode, gudang, item..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                    </div>
                </div>

                {{-- Tipe Pinjam --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Tipe Peminjaman</label>
                    <select name="tipe_pinjam"
                            class="w-full py-2 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                        <option value="">Semua Tipe</option>
                        <option value="dipinjamkan" {{ request('tipe_pinjam') === 'dipinjamkan' ? 'selected' : '' }}>Dipinjamkan</option>
                        <option value="meminjam" {{ request('tipe_pinjam') === 'meminjam' ? 'selected' : '' }}>Meminjam</option>
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Status</label>
                    <select name="status_pinjam"
                            class="w-full py-2 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                        <option value="">Semua Status</option>
                        <option value="DIAJUKAN" {{ request('status_pinjam') === 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                        <option value="DIKIRIM" {{ request('status_pinjam') === 'DIKIRIM' ? 'selected' : '' }}>Dikirim</option>
                        <option value="DITERIMA" {{ request('status_pinjam') === 'DITERIMA' ? 'selected' : '' }}>Diterima</option>
                        <option value="DIKEMBALIKAN" {{ request('status_pinjam') === 'DIKEMBALIKAN' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="SELESAI" {{ request('status_pinjam') === 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                {{-- Tanggal Pinjam --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Pinjam (dari)</label>
                    <input type="date"
                           name="tanggal_pinjam"
                           value="{{ request('tanggal_pinjam') }}"
                           class="w-full py-2 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                </div>

                {{-- Tanggal Kembali --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Kembali (sampai)</label>
                    <input type="date"
                           name="tanggal_kembali"
                           value="{{ request('tanggal_kembali') }}"
                           class="w-full py-2 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
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

{{-- Table --}}
<div id="peminjaman-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" data-ajax-container>
    {{-- Mobile Card View --}}
    <div class="sm:hidden">
        @forelse($peminjamans ?? [] as $pinjam)
            @php
                $statusColor = match($pinjam->status) {
                    'DIAJUKAN' => 'bg-gray-100 text-gray-800',
                    'DIKIRIM' => 'bg-blue-100 text-blue-800',
                    'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                    'DIKEMBALIKAN' => 'bg-green-100 text-green-800',
                    'DIKEMBALIKAN_SEBAGIAN' => 'bg-amber-100 text-amber-800',
                    'SELESAI' => 'bg-green-100 text-green-800',
                    'DITOLAK' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                $statusLabel = match($pinjam->status) {
                    'DIKEMBALIKAN_SEBAGIAN' => 'SEBAGIAN',
                    default => $pinjam->status
                };
                $itemCount = $pinjam->items->count();
                $totalQty = $pinjam->items->sum('jumlah_dipinjam');
                $pengembalianCount = $pinjam->pengembalian_entries->count();
            @endphp
            <div class="border-b border-gray-200" x-data="{ expanded: false, showItems: false }">
                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm">{{ $pinjam->kode }}</h3>
                            <p class="text-xs text-gray-500">{{ $pinjam->waktu_mulai?->format('d M Y') }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                        <div class="bg-gray-50 rounded p-2">
                            <p class="text-gray-500">Peminjam</p>
                            <p class="font-medium text-gray-900 truncate">{{ $pinjam->gudangPeminjam?->nama ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded p-2">
                            <p class="text-gray-500">Pemilik</p>
                            <p class="font-medium text-gray-900 truncate">{{ $pinjam->gudangPemilik?->nama ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded p-2 mb-3 text-xs">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Item Dipinjam</p>
                                <p class="font-medium text-gray-900">{{ $itemCount }} item ({{ $totalQty }} unit)</p>
                            </div>
                            <button type="button"
                                    @click="showItems = !showItems"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium text-[#035b71] bg-[#035b71]/10 rounded-md hover:bg-[#035b71]/20 transition">
                                <span>Lihat</span>
                                <svg class="w-3 h-3 transition-transform" :class="showItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                        <div x-show="showItems" x-collapse class="mt-2 space-y-1">
                            @foreach($pinjam->items as $item)
                                <div class="flex items-center justify-between bg-white rounded-md border border-gray-100 px-2 py-1">
                                    <div class="min-w-0 mr-2">
                                        <p class="text-[11px] font-semibold text-gray-800 truncate">{{ $item->item->kode ?? '-' }}</p>
                                        <p class="text-[11px] text-gray-500 truncate">{{ $item->item->nama ?? '-' }}</p>
                                    </div>
                                    <span class="text-[11px] font-bold text-[#035b71] bg-[#035b71]/10 px-2 py-0.5 rounded">
                                        {{ $item->jumlah_dipinjam }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @php
                        $mobileDurationParts = [];
                        if (($pinjam->total_hari ?? 0) > 0) $mobileDurationParts[] = $pinjam->total_hari . ' hari';
                        if (($pinjam->total_jam ?? 0) > 0) $mobileDurationParts[] = $pinjam->total_jam . ' jam';
                        if (empty($mobileDurationParts) && ($pinjam->total_menit ?? 0) > 0) $mobileDurationParts[] = $pinjam->total_menit . ' menit';
                        $mobileDurationText = !empty($mobileDurationParts) ? implode(', ', $mobileDurationParts) : '< 1 menit';
                    @endphp
                    @if($pinjam->waktu_mulai)
                    <div class="mb-3 text-xs">
                        <span class="text-gray-500">Durasi:</span>
                        <span class="font-semibold text-[#035b71]">{{ $mobileDurationText }}</span>
                    </div>
                    @endif

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $pinjam->is_owner ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ $pinjam->is_owner ? 'Dipinjamkan' : 'Meminjam' }}
                        </span>
                        {{-- Surat Jalan Dropdown --}}
                        @if($pinjam->surat_jalan_kirim_id || $pengembalianCount > 0)
                            <div x-data="{ showSJ: false }" @click.away="showSJ = false" class="relative">
                                <button @click="showSJ = !showSJ" type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-[#035b71] bg-[#035b71]/10 rounded-lg hover:bg-[#035b71]/20 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Surat Jalan</span>
                                    <svg class="w-3 h-3 transition-transform" :class="showSJ ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="showSJ" x-transition class="absolute z-50 right-0 mt-1 w-44 bg-white rounded-lg shadow-xl border border-gray-200 py-1">
                                    @if($pinjam->surat_jalan_kirim_id)
                                        <a href="{{ route('gudang.surat-jalan.show', $pinjam->surat_jalan_kirim_id) }}"
                                           class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            SJ Pengiriman
                                        </a>
                                    @endif
                                    @foreach($pinjam->pengembalian_entries as $idx => $sjKembali)
                                        <a href="{{ route('gudang.surat-jalan.show', $sjKembali->id) }}"
                                           class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            SJ Pengembalian{{ $pengembalianCount > 1 ? ' #' . ($idx + 1) : '' }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Expandable Pengembalian Button --}}
                    @if($pengembalianCount > 0)
                    <button @click="expanded = !expanded" class="mt-3 w-full flex items-center justify-between p-2 bg-gray-100 rounded-lg text-xs text-gray-700 hover:bg-gray-200 transition">
                        <span class="font-medium">Riwayat Pengembalian ({{ $pengembalianCount }}x)</span>
                        <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    @endif
                </div>

                {{-- Expandable Pengembalian Details - Same style as main entry --}}
                @if($pengembalianCount > 0)
                <div x-show="expanded" x-collapse class="border-t border-gray-200 bg-gray-50">
                    @foreach($pinjam->pengembalian_entries as $idx => $sjKembali)
                        @php
                            $durasiParts = [];
                            if (($sjKembali->durasi_hari ?? 0) > 0) $durasiParts[] = $sjKembali->durasi_hari . ' hari';
                            if (($sjKembali->durasi_jam ?? 0) > 0) $durasiParts[] = $sjKembali->durasi_jam . ' jam';
                            $durasiText = !empty($durasiParts) ? implode(', ', $durasiParts) : '< 1 jam';
                            $sjItemCount = $sjKembali->items->count();
                            $sjTotalQty = $sjKembali->items->sum('jumlah');
                        @endphp
                        <div class="p-4 border-b border-gray-200 last:border-b-0">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 text-sm">{{ $sjKembali->nomor }}</h3>
                                        <p class="text-xs text-gray-500">Pengembalian #{{ $idx + 1 }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    DIKEMBALIKAN
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                                <div class="bg-white rounded p-2 border border-gray-200">
                                    <p class="text-gray-500">Tanggal Kembali</p>
                                    <p class="font-medium text-gray-900">{{ $sjKembali->tanggal?->format('d M Y') }}</p>
                                </div>
                                <div class="bg-white rounded p-2 border border-gray-200">
                                    <p class="text-gray-500">Durasi Pinjam</p>
                                    <p class="font-medium text-gray-900">{{ $durasiText }}</p>
                                </div>
                            </div>
                            <div class="bg-white rounded p-2 mb-3 text-xs border border-gray-200" x-data="{ showReturnItems: false }">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500">Item Dikembalikan</p>
                                        <p class="font-medium text-gray-900">{{ $sjItemCount }} item ({{ $sjTotalQty }} unit)</p>
                                    </div>
                                    <button type="button"
                                            @click="showReturnItems = !showReturnItems"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium text-[#035b71] bg-[#035b71]/10 rounded-md hover:bg-[#035b71]/20 transition">
                                        <span>Lihat</span>
                                        <svg class="w-3 h-3 transition-transform" :class="showReturnItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                                <div x-show="showReturnItems" x-collapse class="mt-2 space-y-1">
                                    @foreach($sjKembali->items as $returnItem)
                                        <div class="flex items-center justify-between bg-gray-50 rounded-md border border-gray-100 px-2 py-1">
                                            <div class="min-w-0 mr-2">
                                                <p class="text-[11px] font-semibold text-gray-800 truncate">{{ $returnItem->item->kode ?? '-' }}</p>
                                                <p class="text-[11px] text-gray-500 truncate">{{ $returnItem->item->nama ?? '-' }}</p>
                                            </div>
                                            <span class="text-[11px] font-bold text-[#035b71] bg-[#035b71]/10 px-2 py-0.5 rounded">
                                                {{ $returnItem->jumlah }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center justify-end text-xs">
                                <a href="{{ route('gudang.surat-jalan.show', $sjKembali->id) }}" class="text-[#035b71] hover:text-[#035b71]/80 font-medium">Lihat SJ Pengembalian</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <p class="font-medium">Belum ada riwayat peminjaman</p>
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemilik</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Kembali</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Surat Jalan</th>
                </tr>
            </thead>
            @forelse($peminjamans ?? [] as $index => $pinjam)
                @php
                    $statusColor = match($pinjam->status) {
                        'DIAJUKAN' => 'bg-gray-100 text-gray-800',
                        'DIKIRIM' => 'bg-blue-100 text-blue-800',
                        'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                        'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                        'DIKEMBALIKAN_SEBAGIAN' => 'bg-amber-100 text-amber-800',
                        'SELESAI' => 'bg-green-100 text-green-800',
                        'DITOLAK' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                    $statusLabel = match($pinjam->status) {
                        'DIKEMBALIKAN_SEBAGIAN' => 'SEBAGIAN',
                        default => $pinjam->status
                    };
                    $itemCount = $pinjam->items->count();
                    $totalQty = $pinjam->items->sum('jumlah_dipinjam');
                    $pengembalianCount = $pinjam->pengembalian_entries->count();
                @endphp
                {{-- Wrapper tbody with x-data for Alpine.js scope --}}
                <tbody x-data="{ expanded: false }" class="bg-white">
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $peminjamans->firstItem() + $index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $pinjam->kode }}</div>
                            <div class="text-xs {{ $pinjam->is_owner ? 'text-blue-600' : 'text-green-600' }}">
                                {{ $pinjam->is_owner ? 'Dipinjamkan' : 'Meminjam' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $pinjam->gudangPeminjam?->nama ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $pinjam->gudangPemilik?->nama ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div x-data="{
                                showItems: false,
                                pos: { top: 0, left: 0 },
                                calcPos() {
                                    const btn = this.$refs.itemBtn;
                                    const rect = btn.getBoundingClientRect();
                                    const dropdownHeight = 240;
                                    const spaceBelow = window.innerHeight - rect.bottom;
                                    if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                                        this.pos = { top: rect.top - dropdownHeight - 4, left: rect.left };
                                    } else {
                                        this.pos = { top: rect.bottom + 4, left: rect.left };
                                    }
                                }
                            }" @click.away="showItems = false" class="relative">
                                <button x-ref="itemBtn" @click="calcPos(); showItems = !showItems" type="button"
                                        class="inline-flex items-center gap-2 text-sm text-[#035b71] hover:text-[#035b71]/80 font-medium bg-[#035b71]/5 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <span>{{ $itemCount }} item ({{ $totalQty }} unit)</span>
                                    <svg class="w-4 h-4 shrink-0 transition-transform" :class="showItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <template x-teleport="body">
                                    <div x-show="showItems" x-transition @click.away="showItems = false"
                                         class="fixed z-[9999] w-72 bg-white rounded-lg shadow-2xl border border-gray-200"
                                         :style="{ top: pos.top + 'px', left: pos.left + 'px' }">
                                        <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 rounded-t-lg">
                                            <span class="text-xs font-semibold text-gray-500 uppercase">Daftar Item ({{ $itemCount }})</span>
                                        </div>
                                        <div class="p-2 space-y-1 max-h-48 overflow-y-auto">
                                            @foreach($pinjam->items as $item)
                                                <div class="text-sm text-gray-900 flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                                    <div class="flex-1 min-w-0 mr-2">
                                                        <span class="font-medium">{{ $item->item->kode ?? '-' }}</span>
                                                        <span class="text-gray-500 block text-xs truncate">{{ $item->item->nama ?? '-' }}</span>
                                                    </div>
                                                    <span class="text-[#035b71] font-bold bg-[#035b71]/10 px-2 py-0.5 rounded shrink-0">{{ $item->jumlah_dipinjam }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($pinjam->waktu_mulai)
                                <div class="text-sm font-medium text-gray-900">{{ $pinjam->waktu_mulai->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $pinjam->waktu_mulai->format('H:i') }} WIB</div>
                            @else
                                <span class="text-sm text-gray-400 italic">Belum diterima</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($pinjam->waktu_kembali)
                                <div class="text-sm font-medium text-gray-900">{{ $pinjam->waktu_kembali->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $pinjam->waktu_kembali->format('H:i') }} WIB</div>
                            @else
                                <span class="text-sm text-gray-400 italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($pinjam->waktu_mulai)
                                @php
                                    $durationParts = [];
                                    if (($pinjam->total_hari ?? 0) > 0) $durationParts[] = $pinjam->total_hari . ' hari';
                                    if (($pinjam->total_jam ?? 0) > 0) $durationParts[] = $pinjam->total_jam . ' jam';
                                    if (empty($durationParts) && ($pinjam->total_menit ?? 0) > 0) $durationParts[] = $pinjam->total_menit . ' menit';
                                    $durationText = !empty($durationParts) ? implode(', ', $durationParts) : '< 1 menit';
                                @endphp
                                <div class="text-sm font-medium text-gray-900">{{ $durationText }}</div>
                                <div class="text-xs text-gray-500">Sejak {{ $pinjam->waktu_mulai->format('d M Y') }}</div>
                                @if($pinjam->status !== 'SELESAI')
                                    <div class="text-xs text-yellow-600 font-medium mt-0.5">Masih berjalan</div>
                                @endif
                            @else
                                <span class="text-sm text-gray-400 italic">Belum diterima</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                            @if($pengembalianCount > 0)
                                <button @click="expanded = !expanded" class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-full hover:bg-gray-200 transition">
                                    <span>{{ $pengembalianCount }}x kembali</span>
                                    <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($pinjam->surat_jalan_kirim_id || $pengembalianCount > 0)
                                <div x-data="{
                                    showSJ: false,
                                    pos: { top: 0, left: 0 },
                                    calcPos() {
                                        const btn = this.$refs.sjBtn;
                                        const rect = btn.getBoundingClientRect();
                                        const dropdownHeight = 120;
                                        const spaceBelow = window.innerHeight - rect.bottom;
                                        if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                                            this.pos = { top: rect.top - dropdownHeight - 4, left: rect.right - 192 };
                                        } else {
                                            this.pos = { top: rect.bottom + 4, left: rect.right - 192 };
                                        }
                                    }
                                }" @click.away="showSJ = false" class="relative">
                                    <button x-ref="sjBtn" @click="calcPos(); showSJ = !showSJ" type="button"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#035b71] bg-[#035b71]/10 rounded-lg hover:bg-[#035b71]/20 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>Surat Jalan</span>
                                        <svg class="w-3 h-3 transition-transform" :class="showSJ ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="showSJ" x-transition @click.away="showSJ = false"
                                             class="fixed z-[9999] w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1"
                                             :style="{ top: pos.top + 'px', left: pos.left + 'px' }">
                                            @if($pinjam->surat_jalan_kirim_id)
                                                <a href="{{ route('gudang.surat-jalan.show', $pinjam->surat_jalan_kirim_id) }}"
                                                   class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    SJ Pengiriman
                                                </a>
                                            @endif
                                            @foreach($pinjam->pengembalian_entries as $idx => $sjKembali)
                                                <a href="{{ route('gudang.surat-jalan.show', $sjKembali->id) }}"
                                                   class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    SJ Pengembalian{{ $pengembalianCount > 1 ? ' #' . ($idx + 1) : '' }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </template>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    {{-- Expandable Pengembalian Sub-Rows - Same style as main entry --}}
                    @if($pengembalianCount > 0)
                        @foreach($pinjam->pengembalian_entries as $idx => $sjKembali)
                            @php
                                $durasiParts = [];
                                if (($sjKembali->durasi_hari ?? 0) > 0) $durasiParts[] = $sjKembali->durasi_hari . ' hari';
                                if (($sjKembali->durasi_jam ?? 0) > 0) $durasiParts[] = $sjKembali->durasi_jam . ' jam';
                                $durasiText = !empty($durasiParts) ? implode(', ', $durasiParts) : '< 1 jam';
                                $sjItemCount = $sjKembali->items->count();
                                $sjTotalQty = $sjKembali->items->sum('jumlah');
                            @endphp
                            <tr x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="bg-gray-50 hover:bg-gray-100 border-b border-gray-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $sjKembali->nomor }}</div>
                                            <div class="text-xs text-gray-500">Pengembalian #{{ $idx + 1 }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $pinjam->gudangPeminjam?->nama ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $pinjam->gudangPemilik?->nama ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div x-data="{
                                        showReturnItems: false,
                                        pos: { top: 0, left: 0 },
                                        calcPos() {
                                            const btn = this.$refs.returnItemBtn;
                                            const rect = btn.getBoundingClientRect();
                                            const dropdownHeight = 240;
                                            const spaceBelow = window.innerHeight - rect.bottom;
                                            if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                                                this.pos = { top: rect.top - dropdownHeight - 4, left: rect.left };
                                            } else {
                                                this.pos = { top: rect.bottom + 4, left: rect.left };
                                            }
                                        }
                                    }" @click.away="showReturnItems = false" class="relative">
                                        <button x-ref="returnItemBtn" @click="calcPos(); showReturnItems = !showReturnItems" type="button"
                                                class="inline-flex items-center gap-2 text-sm text-[#035b71] hover:text-[#035b71]/80 font-medium bg-[#035b71]/5 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            <span>{{ $sjItemCount }} item ({{ $sjTotalQty }} unit)</span>
                                            <svg class="w-4 h-4 shrink-0 transition-transform" :class="showReturnItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="showReturnItems" x-transition @click.away="showReturnItems = false"
                                                 class="fixed z-[9999] w-72 bg-white rounded-lg shadow-2xl border border-gray-200"
                                                 :style="{ top: pos.top + 'px', left: pos.left + 'px' }">
                                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 rounded-t-lg">
                                                    <span class="text-xs font-semibold text-gray-500 uppercase">Item Dikembalikan ({{ $sjItemCount }})</span>
                                                </div>
                                                <div class="p-2 space-y-1 max-h-48 overflow-y-auto">
                                                    @foreach($sjKembali->items as $returnItem)
                                                        <div class="text-sm text-gray-900 flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                                            <div class="flex-1 min-w-0 mr-2">
                                                                <span class="font-medium">{{ $returnItem->item->kode ?? '-' }}</span>
                                                                <span class="text-gray-500 block text-xs truncate">{{ $returnItem->item->nama ?? '-' }}</span>
                                                            </div>
                                                            <span class="text-[#035b71] font-bold bg-[#035b71]/10 px-2 py-0.5 rounded shrink-0">{{ $returnItem->jumlah }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($pinjam->waktu_mulai)
                                        <div class="text-sm font-medium text-gray-900">{{ $pinjam->waktu_mulai->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $pinjam->waktu_mulai->format('H:i') }} WIB</div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $sjKembali->tanggal?->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">Dikembalikan</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $durasiText }}</div>
                                    <div class="text-xs text-gray-500">Durasi pinjam</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        DIKEMBALIKAN
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('gudang.surat-jalan.show', $sjKembali->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#035b71] bg-[#035b71]/10 rounded-lg hover:bg-[#035b71]/20 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Lihat SJ
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            @empty
                <tbody class="bg-white">
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <p class="text-gray-500">Belum ada riwayat peminjaman</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>

    @if(isset($peminjamans) && $peminjamans->hasPages())
        <div class="px-6 py-4 border-t border-gray-100" data-ajax-pagination>
            {{ $peminjamans->links() }}
        </div>
    @endif
</div>
