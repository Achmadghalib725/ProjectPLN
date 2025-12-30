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
                <span>Filter & Urutkan</span>
                @if($activeFilters > 0)
                    <span class="bg-[#035b71] text-white text-xs px-2 py-0.5 rounded-full">{{ $activeFilters }}</span>
                @endif
                <svg class="w-4 h-4 transition-transform duration-200" :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            @if($activeFilters > 0)
                <a href="{{ route('gudang.riwayat', ['tab' => 'peminjaman']) }}"
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
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $currentSort === 'terbaru' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terbaru
                </a>
                <a href="{{ route('gudang.riwayat', array_merge(request()->query(), ['tab' => 'peminjaman', 'sort' => 'terlama'])) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $currentSort === 'terlama' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terlama
                </a>
            </div>
        </div>
    </div>

    {{-- Expandable Filter Panel --}}
    <div x-show="showFilter" x-collapse x-cloak class="border-t border-gray-100">
        <form method="GET" action="{{ route('gudang.riwayat') }}" class="p-4">
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
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Tgl Pinjam (dari)</label>
                    <input type="date"
                           name="tanggal_pinjam"
                           value="{{ request('tanggal_pinjam') }}"
                           class="w-full py-2 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                </div>

                {{-- Tanggal Kembali --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Tgl Kembali (sampai)</label>
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
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Mobile Card View --}}
    <div class="sm:hidden">
        @forelse($peminjamans ?? [] as $pinjam)
            @php
                $statusColor = match($pinjam->status) {
                    'DIAJUKAN' => 'bg-gray-100 text-gray-800',
                    'DIKIRIM' => 'bg-blue-100 text-blue-800',
                    'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                    'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                    'SELESAI' => 'bg-green-100 text-green-800',
                    'DITOLAK' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                $itemCount = $pinjam->items->count();
                $totalQty = $pinjam->items->sum('jumlah_dipinjam');
            @endphp
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 text-sm">{{ $pinjam->kode }}</h3>
                        <p class="text-xs text-gray-500">{{ $pinjam->waktu_diterima?->format('d M Y') ?? $pinjam->created_at->format('d M Y') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                        {{ $pinjam->status }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                    <div class="bg-gray-50 rounded p-2">
                        <p class="text-gray-500">Peminjam</p>
                        <p class="font-medium text-gray-900 truncate">
                            {{ $pinjam->gudang_peminjam_is_custom ? ($pinjam->gudang_peminjam_custom_nama ?? 'Gudang Lainnya') : ($pinjam->gudangPeminjam->nama ?? '-') }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded p-2">
                        <p class="text-gray-500">Pemilik</p>
                        <p class="font-medium text-gray-900 truncate">
                            {{ $pinjam->gudangPemilik->nama ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="bg-gray-50 rounded p-2 mb-3 text-xs">
                    <p class="text-gray-500">Item</p>
                    <p class="font-medium text-gray-900">{{ $itemCount }} item ({{ $totalQty }} unit)</p>
                </div>

                {{-- Duration --}}
                <div class="mb-3 text-xs">
                    <span class="text-gray-500">Durasi Pinjam:</span>
                    @if($pinjam->waktu_diterima)
                        @php
                            $mobileDurationParts = [];
                            if ($pinjam->total_hari > 0) $mobileDurationParts[] = $pinjam->total_hari . ' hari';
                            if ($pinjam->total_jam > 0) $mobileDurationParts[] = $pinjam->total_jam . ' jam';
                            if (empty($mobileDurationParts) && isset($pinjam->total_menit)) $mobileDurationParts[] = $pinjam->total_menit . ' menit';
                            $mobileDurationText = !empty($mobileDurationParts) ? implode(', ', $mobileDurationParts) : '< 1 menit';
                        @endphp
                        <span class="font-semibold text-[#035b71]">{{ $mobileDurationText }}</span>
                        @if(!$pinjam->waktu_selesai)
                            <span class="text-yellow-600 ml-1">(Berjalan)</span>
                        @endif
                    @else
                        <span class="text-gray-400">Belum diterima</span>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $pinjam->is_owner ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($pinjam->is_owner)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            @endif
                        </svg>
                        {{ $pinjam->is_owner ? 'Dipinjamkan' : 'Meminjam' }}
                    </span>
                    <div class="flex items-center gap-2">
                        @if($pinjam->surat_jalan_kirim_id)
                            <a href="{{ route('gudang.surat-jalan.show', $pinjam->surat_jalan_kirim_id) }}"
                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                SJ Kirim
                            </a>
                        @endif
                        @if($pinjam->surat_jalan_kembali_id)
                            <a href="{{ route('gudang.surat-jalan.show', $pinjam->surat_jalan_kembali_id) }}"
                               class="inline-flex items-center gap-1 text-green-600 hover:text-green-800 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                SJ Kembali
                            </a>
                        @endif
                    </div>
                </div>
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
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Peminjam</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemilik</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Kembali</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durasi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Surat Jalan</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($peminjamans ?? [] as $pinjam)
                    @php
                        $statusColor = match($pinjam->status) {
                            'DIAJUKAN' => 'bg-gray-100 text-gray-800',
                            'DIKIRIM' => 'bg-blue-100 text-blue-800',
                            'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                            'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                            'SELESAI' => 'bg-green-100 text-green-800',
                            'DITOLAK' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        $itemCount = $pinjam->items->count();
                        $totalQty = $pinjam->items->sum('jumlah_dipinjam');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $pinjam->kode }}</div>
                            <div class="text-xs {{ $pinjam->is_owner ? 'text-blue-600' : 'text-green-600' }}">
                                {{ $pinjam->is_owner ? 'Dipinjamkan' : 'Meminjam' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $pinjam->gudang_peminjam_is_custom ? ($pinjam->gudang_peminjam_custom_nama ?? 'Gudang Lainnya') : ($pinjam->gudangPeminjam->nama ?? '-') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $pinjam->gudangPemilik->nama ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div x-data="{
                                    showItems: false,
                                    position: { top: 0, left: 0 },
                                    openAbove: false,
                                    updatePosition() {
                                        const rect = this.$refs.trigger.getBoundingClientRect();
                                        const spaceBelow = window.innerHeight - rect.bottom;
                                        this.openAbove = spaceBelow < 200;
                                        if (this.openAbove) {
                                            this.position = { top: rect.top - 4, left: rect.left };
                                        } else {
                                            this.position = { top: rect.bottom + 4, left: rect.left };
                                        }
                                    }
                                 }"
                                 x-init="$watch('showItems', value => { if (value) updatePosition(); })"
                                 @click.away="showItems = false">
                                <button x-ref="trigger"
                                        @click="showItems = !showItems"
                                        type="button"
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
                                    <div x-show="showItems"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         :style="'position: fixed; top: ' + position.top + 'px; left: ' + position.left + 'px; z-index: 9999;' + (openAbove ? ' transform: translateY(-100%);' : '')"
                                         class="w-72 bg-white rounded-lg shadow-2xl border border-gray-200">
                                        <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 rounded-t-lg">
                                            <span class="text-xs font-semibold text-gray-500 uppercase">Daftar Item ({{ $itemCount }})</span>
                                        </div>
                                        <div class="p-2 space-y-1 max-h-36 overflow-y-auto">
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
                            @if($pinjam->waktu_diterima)
                                <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($pinjam->waktu_diterima)->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($pinjam->waktu_diterima)->format('H:i') }} WIB</div>
                            @else
                                <span class="text-sm text-gray-400 italic">Belum diterima</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($pinjam->waktu_selesai)
                                <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($pinjam->waktu_selesai)->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($pinjam->waktu_selesai)->format('H:i') }} WIB</div>
                            @else
                                <span class="text-sm text-gray-400 italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($pinjam->waktu_diterima)
                                @php
                                    $durationParts = [];
                                    if ($pinjam->total_hari > 0) $durationParts[] = $pinjam->total_hari . ' hari';
                                    if ($pinjam->total_jam > 0) $durationParts[] = $pinjam->total_jam . ' jam';
                                    if (empty($durationParts) && isset($pinjam->total_menit)) $durationParts[] = $pinjam->total_menit . ' menit';
                                    $durationText = !empty($durationParts) ? implode(', ', $durationParts) : '< 1 menit';
                                @endphp
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $durationText }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Sejak {{ \Carbon\Carbon::parse($pinjam->waktu_diterima)->format('d M Y') }}
                                </div>
                                @if(!$pinjam->waktu_selesai)
                                    <div class="text-xs text-yellow-600 font-medium mt-0.5">Masih berjalan</div>
                                @endif
                            @else
                                <span class="text-sm text-gray-400 italic">Belum diterima</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $pinjam->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($pinjam->surat_jalan_kirim_id || $pinjam->surat_jalan_kembali_id)
                                <div x-data="{
                                        open: false,
                                        position: { top: 0, left: 0 },
                                        openAbove: false,
                                        updatePosition() {
                                            const rect = this.$refs.trigger.getBoundingClientRect();
                                            const spaceBelow = window.innerHeight - rect.bottom;
                                            this.openAbove = spaceBelow < 120;
                                            if (this.openAbove) {
                                                this.position = { top: rect.top - 4, left: rect.right - 192 };
                                            } else {
                                                this.position = { top: rect.bottom + 4, left: rect.right - 192 };
                                            }
                                        }
                                     }"
                                     x-init="$watch('open', value => { if (value) updatePosition(); })"
                                     @click.away="open = false">
                                    <button x-ref="trigger"
                                            @click="open = !open"
                                            type="button"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#035b71] bg-[#035b71]/10 rounded-lg hover:bg-[#035b71]/20 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Surat Jalan
                                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             :style="'position: fixed; top: ' + position.top + 'px; left: ' + position.left + 'px; z-index: 9999;' + (openAbove ? ' transform: translateY(-100%);' : '')"
                                             class="w-48 bg-white rounded-lg shadow-2xl border border-gray-200 py-1">
                                            @if($pinjam->surat_jalan_kirim_id)
                                                <a href="{{ route('gudang.surat-jalan.show', $pinjam->surat_jalan_kirim_id) }}"
                                                   class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                    </svg>
                                                    SJ Pengiriman
                                                </a>
                                            @endif
                                            @if($pinjam->surat_jalan_kembali_id)
                                                <a href="{{ route('gudang.surat-jalan.show', $pinjam->surat_jalan_kembali_id) }}"
                                                   class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                                    </svg>
                                                    SJ Pengembalian
                                                </a>
                                            @endif
                                        </div>
                                    </template>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <p class="text-gray-500">Belum ada riwayat peminjaman</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($peminjamans) && $peminjamans->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $peminjamans->links() }}
        </div>
    @endif
</div>
