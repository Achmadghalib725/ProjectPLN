{{-- Statistics Cards --}}
<div class="grid grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-5">
        <div class="flex flex-col sm:flex-row items-center sm:items-center gap-2 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="text-center sm:text-left">
                <p class="text-xs sm:text-sm text-gray-500">Pinjaman Aktif</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalAktif ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-5">
        <div class="flex flex-col sm:flex-row items-center sm:items-center gap-2 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 {{ ($totalOverdue ?? 0) > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 {{ ($totalOverdue ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-center sm:text-left">
                <p class="text-xs sm:text-sm text-gray-500">Harus Dikembalikan</p>
                <p class="text-xl sm:text-2xl font-bold {{ ($totalOverdue ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $totalOverdue ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Overdue Alert --}}
@if(($totalOverdue ?? 0) > 0)
    <div class="bg-red-50 border border-red-200 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6">
        <div class="flex items-start gap-2 sm:gap-3">
            <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h4 class="text-xs sm:text-sm font-semibold text-red-800">Perhatian!</h4>
                <p class="text-xs sm:text-sm text-red-700">Anda memiliki {{ $totalOverdue }} pinjaman yang sudah melewati batas waktu. Segera kembalikan.</p>
            </div>
        </div>
    </div>
@endif

{{-- Filter & Search Section --}}
@php
    $activeFilters = collect(['search', 'status', 'sort'])->filter(fn($f) => !empty(request($f)))->count();
@endphp
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
                <span>Filter & Search</span>
                @if($activeFilters > 0)
                    <span class="bg-[#035b71] text-white text-xs px-2 py-0.5 rounded-full">{{ $activeFilters }}</span>
                @endif
                <svg class="w-4 h-4 transition-transform duration-200" :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            @if($activeFilters > 0)
                <a href="{{ route('gudang.stok.index', ['tab' => 'pinjaman']) }}"
                   data-ajax-tab data-ajax-target="#stok-content"
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
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('sort'), ['tab' => 'pinjaman', 'sort' => 'terbaru'])) }}"
                   data-ajax-tab data-ajax-target="#stok-content"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ request('sort', 'terbaru') === 'terbaru' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terbaru
                </a>
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('sort'), ['tab' => 'pinjaman', 'sort' => 'terlama'])) }}"
                   data-ajax-tab data-ajax-target="#stok-content"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ request('sort') === 'terlama' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Terlama
                </a>
            </div>
        </div>
    </div>

    {{-- Expandable Filter Panel --}}
    <div x-show="showFilter" x-collapse x-cloak>
        <div class="px-4 pb-4 border-t border-gray-100 pt-4 bg-white">
            <form method="GET" action="{{ route('gudang.stok.index') }}" class="space-y-4" data-ajax-form data-ajax-target="#stok-content">
                <input type="hidden" name="tab" value="pinjaman">
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Search --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Pencarian</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari nomor, gudang, atau item..."
                               class="w-full rounded-lg border-gray-300 text-sm bg-white focus:border-[#035b71] focus:ring focus:ring-[#035b71]/20">
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm bg-white focus:border-[#035b71] focus:ring focus:ring-[#035b71]/20">
                            <option value="">Semua Status</option>
                            <option value="DIKIRIM" {{ request('status') === 'DIKIRIM' ? 'selected' : '' }}>Dikirim</option>
                            <option value="DIPERIKSA" {{ request('status') === 'DIPERIKSA' ? 'selected' : '' }}>Diperiksa</option>
                            <option value="DITERIMA" {{ request('status') === 'DITERIMA' ? 'selected' : '' }}>Diterima</option>
                            <option value="DIKEMBALIKAN" {{ request('status') === 'DIKEMBALIKAN' ? 'selected' : '' }}>Dikembalikan</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    {{-- Apply Button --}}
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full px-6 py-2.5 bg-[#035b71] text-white rounded-lg hover:bg-[#00aff0] transition font-medium text-sm">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Mobile Card View --}}
    <div class="sm:hidden">
        @forelse($peminjamans as $peminjaman)
            @php
                $activeStatuses = ['DITERIMA', 'DIKEMBALIKAN'];
                $isActiveStatus = in_array($peminjaman->status, $activeStatuses);
                $isOverdue = $peminjaman->batas_waktu_kembali &&
                             $peminjaman->batas_waktu_kembali->isPast() &&
                             $isActiveStatus;
                $remainingItems = $peminjaman->items
                    ->map(function ($item) {
                        $base = (int) ($item->jumlah_diterima ?? $item->jumlah_dipinjam);
                        $returned = (int) ($item->jumlah_dikembalikan ?? 0);
                        $item->remaining_qty = max(0, $base - $returned);
                        return $item;
                    })
                    ->filter(fn ($item) => $item->remaining_qty > 0)
                    ->values();
                $itemCount = $remainingItems->count();
                $totalQty = $remainingItems->sum('remaining_qty');
                $canReturn = $peminjaman->status === 'DITERIMA'
                    && $remainingItems->isNotEmpty()
                    && (
                        !$peminjaman->suratJalanKembali
                        || in_array($peminjaman->suratJalanKembali->status, ['SELESAI', 'DITOLAK'], true)
                    );
                $statusColor = match($peminjaman->status) {
                    'DIKIRIM' => 'bg-blue-100 text-blue-800',
                    'DIPERIKSA' => 'bg-cyan-100 text-cyan-800',
                    'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                    'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                    'SELESAI' => 'bg-green-100 text-green-800',
                    default => 'bg-gray-100 text-gray-800'
                };
            @endphp
            <div class="p-4 border-b border-gray-200 {{ $isOverdue ? 'bg-red-50 border-l-4 border-l-red-500' : '' }}">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 text-sm">{{ $peminjaman->kode }}</h3>
                        <p class="text-xs text-gray-500">{{ $peminjaman->waktu_kirim?->format('d M Y') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                        {{ $peminjaman->status }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                    <div class="bg-gray-50 rounded p-2">
                        <p class="text-gray-500">Dipinjam Dari</p>
                        <p class="font-medium text-gray-900 truncate">{{ $peminjaman->gudangPemilik->nama ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded p-2">
                        <p class="text-gray-500">Item</p>
                        <p class="font-medium text-gray-900">{{ $itemCount }} item ({{ $totalQty }} unit)</p>
                    </div>
                </div>

                @if($peminjaman->batas_waktu_kembali)
                    <div class="mb-3 text-xs">
                        <span class="text-gray-500">Batas Waktu:</span>
                        <span class="font-medium {{ $isOverdue ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $peminjaman->batas_waktu_kembali->format('d M Y') }}
                        </span>
                        @if($isOverdue)
                            <span class="text-red-600 font-medium ml-1">(Terlambat)</span>
                        @endif
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    {{-- Dropdown Surat Jalan --}}
                    @if($peminjaman->suratJalanKirim || $peminjaman->suratJalanKembali)
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button @click="open = !open"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-pln-primary bg-pln-primary/10 rounded-lg hover:bg-pln-primary/20 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                SJ
                                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-transition
                                 class="absolute right-0 bottom-full mb-1 w-40 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50">
                                @if($peminjaman->suratJalanKirim)
                                    <a href="{{ route('gudang.surat-jalan.show', $peminjaman->suratJalanKirim->id) }}"
                                       class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        SJ Pengiriman
                                    </a>
                                @endif
                                @if($peminjaman->suratJalanKembali)
                                    <a href="{{ route('gudang.surat-jalan.show', $peminjaman->suratJalanKembali->id) }}"
                                       class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                        <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        SJ Pengembalian
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            SJ
                        </span>
                    @endif

                    {{-- Tombol Kembalikan --}}
                    @if($canReturn)
                        <a href="{{ route('gudang.surat-jalan.index') }}?open_return=1&peminjaman_id={{ $peminjaman->id }}"
                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 transition"
                           title="Kembalikan Barang">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-300 cursor-not-allowed"
                              title="Tidak dapat dikembalikan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="font-medium">Tidak ada barang pinjaman</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dipinjam Dari</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durasi Pinjam</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batas Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($peminjamans as $peminjaman)
                    @php
                        $activeStatuses = ['DITERIMA', 'DIKEMBALIKAN'];
                        $isActiveStatus = in_array($peminjaman->status, $activeStatuses);
                        $isOverdue = $peminjaman->batas_waktu_kembali &&
                                     $peminjaman->batas_waktu_kembali->isPast() &&
                                     $isActiveStatus;
                        $startDate = $peminjaman->waktu_diterima;
                        $endDate = $peminjaman->status === 'SELESAI' ? $peminjaman->waktu_selesai : now();
                        $canCalculateDuration = $startDate !== null;
                    @endphp
                    <tr class="{{ $isOverdue ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $peminjaman->kode }}</div>
                            <div class="text-xs text-gray-500">{{ $peminjaman->waktu_kirim?->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $peminjaman->gudangPemilik->nama ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $remainingItems = $peminjaman->items
                                    ->map(function ($item) {
                                        $base = (int) ($item->jumlah_diterima ?? $item->jumlah_dipinjam);
                                        $returned = (int) ($item->jumlah_dikembalikan ?? 0);
                                        $item->remaining_qty = max(0, $base - $returned);
                                        return $item;
                                    })
                                    ->filter(fn ($item) => $item->remaining_qty > 0)
                                    ->values();
                                $itemCount = $remainingItems->count();
                                $totalQty = $remainingItems->sum('remaining_qty');
                            @endphp
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
                                        class="inline-flex items-center gap-2 text-sm text-pln-primary hover:text-pln-primary/80 font-medium bg-pln-primary/5 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
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
                                            @if($remainingItems->isEmpty())
                                                <div class="text-xs text-gray-500 px-2 py-2">Semua item sudah dikembalikan.</div>
                                            @else
                                                @foreach($remainingItems as $item)
                                                    <div class="text-sm text-gray-900 flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                                        <div class="flex-1 min-w-0 mr-2">
                                                            <span class="font-medium">{{ $item->item->kode ?? '-' }}</span>
                                                            <span class="text-gray-500 block text-xs truncate">{{ $item->item->nama ?? '-' }}</span>
                                                        </div>
                                                        <span class="text-pln-primary font-bold bg-pln-primary/10 px-2 py-0.5 rounded shrink-0">{{ $item->remaining_qty }}</span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($canCalculateDuration)
                                @php
                                    $totalMinutes = $startDate->diffInMinutes($endDate);
                                    $days = floor($totalMinutes / (60 * 24));
                                    $hours = floor(($totalMinutes % (60 * 24)) / 60);
                                    $minutes = $totalMinutes % 60;

                                    $durationParts = [];
                                    if ($days > 0) $durationParts[] = $days . ' hari';
                                    if ($hours > 0) $durationParts[] = $hours . ' jam';
                                    if ($minutes > 0 && $days < 1) $durationParts[] = $minutes . ' menit';
                                    $durationText = !empty($durationParts) ? implode(', ', $durationParts) : '0 menit';
                                @endphp
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $durationText }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Sejak {{ $startDate->format('d M Y') }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400 italic">Belum diterima</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($peminjaman->batas_waktu_kembali)
                                @php
                                    $deadline = $peminjaman->batas_waktu_kembali;
                                    $now = now();
                                    $diffMinutes = $now->diffInMinutes($deadline, false);
                                    $isOverdueCalc = $diffMinutes < 0 && $isActiveStatus;
                                    $absDiffMinutes = abs($diffMinutes);

                                    $deadlineDays = floor($absDiffMinutes / (60 * 24));
                                    $deadlineHours = floor(($absDiffMinutes % (60 * 24)) / 60);
                                    $deadlineMinutes = $absDiffMinutes % 60;

                                    $deadlineParts = [];
                                    if ($deadlineDays > 0) $deadlineParts[] = $deadlineDays . ' hari';
                                    if ($deadlineHours > 0) $deadlineParts[] = $deadlineHours . ' jam';
                                    if ($deadlineMinutes > 0 && $deadlineDays < 1) $deadlineParts[] = $deadlineMinutes . ' menit';
                                    $deadlineText = !empty($deadlineParts) ? implode(', ', $deadlineParts) : '0 menit';
                                @endphp
                                <div class="text-sm font-medium {{ $isOverdueCalc ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $deadline->format('d M Y') }}
                                </div>
                                @if($isOverdueCalc)
                                    <div class="flex items-center gap-1 text-xs text-red-600 font-medium mt-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Terlambat {{ $deadlineText }}
                                    </div>
                                @elseif($isActiveStatus && $diffMinutes > 0)
                                    <div class="text-xs {{ $deadlineDays <= 3 ? 'text-yellow-600 font-medium' : 'text-gray-500' }} mt-1">
                                        Sisa {{ $deadlineText }}
                                    </div>
                                @endif
                            @else
                                <span class="text-sm text-gray-400 italic">Tidak ada batas</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($peminjaman->status) {
                                    'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                    'DIPERIKSA' => 'bg-cyan-100 text-cyan-800',
                                    'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                    'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                                    'SELESAI' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $peminjaman->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Dropdown Surat Jalan --}}
                                @if($peminjaman->suratJalanKirim || $peminjaman->suratJalanKembali)
                                    <div x-data="{
                                            open: false,
                                            position: { top: 0, left: 0 },
                                            openAbove: false,
                                            updatePosition() {
                                                const rect = this.$refs.trigger.getBoundingClientRect();
                                                const spaceBelow = window.innerHeight - rect.bottom;
                                                this.openAbove = spaceBelow < 120;
                                                if (this.openAbove) {
                                                    this.position = { top: rect.top - 4, left: rect.right - 160 };
                                                } else {
                                                    this.position = { top: rect.bottom + 4, left: rect.right - 160 };
                                                }
                                            }
                                         }"
                                         x-init="$watch('open', value => { if (value) updatePosition(); })"
                                         @click.away="open = false">
                                        <button x-ref="trigger"
                                                @click="open = !open"
                                                type="button"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-pln-primary bg-pln-primary/10 rounded-lg hover:bg-pln-primary/20 transition">
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
                                                 class="w-40 bg-white rounded-lg shadow-2xl border border-gray-200 py-1">
                                                @if($peminjaman->suratJalanKirim)
                                                    <a href="{{ route('gudang.surat-jalan.show', $peminjaman->suratJalanKirim->id) }}"
                                                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                        </svg>
                                                        SJ Pengiriman
                                                    </a>
                                                @endif
                                                @if($peminjaman->suratJalanKembali)
                                                    <a href="{{ route('gudang.surat-jalan.show', $peminjaman->suratJalanKembali->id) }}"
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
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Surat Jalan
                                    </span>
                                @endif

                                {{-- Tombol Kembalikan --}}
                                @if($peminjaman->status === 'DITERIMA' && !$peminjaman->suratJalanKembali)
                                    <a href="{{ route('gudang.surat-jalan.index') }}?open_return=1&peminjaman_id={{ $peminjaman->id }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 transition"
                                       title="Kembalikan Barang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                    </a>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-300 cursor-not-allowed"
                                          title="Tidak dapat dikembalikan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-gray-500">Tidak ada barang pinjaman</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($peminjamans->hasPages())
        <div class="px-6 py-4 border-t border-gray-100" data-ajax-pagination>
            {{ $peminjamans->links() }}
        </div>
    @endif
</div>
