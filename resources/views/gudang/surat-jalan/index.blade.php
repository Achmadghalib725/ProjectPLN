<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $isAdmin = Auth::user()->role === 'admin';
                $isManager = Auth::user()->role === 'manager';
                $needsGudangSelection = ($selectionGudangs ?? collect())->count() > 0;
                $hasGudangContext = !$needsGudangSelection || !empty($activeGudangId);
                $displayGudangName = $activeGudangName ?? (Auth::user()->gudang?->nama ?? '');
                $headerNotices = [];
                /*
                if ($isAdmin) {
                    $headerNotices[] = 'Mode admin: surat jalan dapat langsung dibuat dan diselesaikan.';
                }
                if ($needsGudangSelection && !$hasGudangContext) {
                    $headerNotices[] = 'Pilih gudang terlebih dulu untuk membuat surat jalan.';
                }
                */
            @endphp
            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)"
                     class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-auto bg-green-500 text-white px-4 sm:px-6 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2 text-sm sm:text-base">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Error ditampilkan di dalam modal popup, auto-open modal saat ada error --}}
            @if($errors->any())
                <script>
                    document.addEventListener('alpine:init', () => {
                        // Dispatch after Alpine is ready
                        setTimeout(() => {
                            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-surat-jalan' }));
                        }, 100);
                    });
                </script>
            @endif

            @if(session('warning'))
                <div class="mb-4 sm:mb-6 bg-yellow-50 border border-yellow-200 text-yellow-900 px-4 py-3 rounded-xl text-sm sm:text-base">
                    <p class="font-semibold">Peringatan stok:</p>
                    @if(is_array(session('warning')))
                        <ul class="list-disc list-inside text-sm mt-1">
                            @foreach(session('warning') as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm mt-1">{{ session('warning') }}</p>
                    @endif
                </div>
            @endif

            {{-- Header --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-4 sm:p-6">
                    @if(count($headerNotices) > 0)
                        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 rounded-lg text-xs sm:text-sm">
                            <div class="flex flex-col gap-1">
                                @foreach($headerNotices as $notice)
                                    <span>{{ $notice }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="flex flex-col gap-3 sm:gap-4 sm:flex-row sm:justify-between sm:items-center">
                        <div class="text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Surat Jalan Barang</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                {{ $displayGudangName }}
                            </p>
                        </div>
                        @if($needsGudangSelection)
                        <form method="GET" action="{{ route('gudang.surat-jalan.index') }}" class="flex items-center gap-2">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Akses Gudang</label>
                            <select name="gudang_id"
                                    onchange="this.form.submit()"
                                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                <option value="">Pilih gudang...</option>
                                @foreach($selectionGudangs as $gudang)
                                    <option value="{{ $gudang->id }}" {{ (string) $activeGudangId === (string) $gudang->id ? 'selected' : '' }}>
                                        {{ $gudang->kode }} - {{ $gudang->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        @endif
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            @if(!$isManager)
                            <button type="button"
                                    class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 {{ $isAdmin ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-pln-primary hover:bg-pln-light' }} active:scale-95 text-white font-semibold rounded-lg sm:rounded-md transition duration-150 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    @if(!$hasGudangContext) disabled @endif
                                    @click="$dispatch('open-modal', 'create-surat-jalan')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>{{ $isAdmin ? 'Buat Surat Jalan (Admin)' : 'Buat Surat Jalan' }}</span>
                            </button>
                            @if($isAdmin && $hasGudangContext)
                                <button type="button"
                                        class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-yellow-500 hover:bg-yellow-600 active:scale-95 text-white font-semibold rounded-lg sm:rounded-md transition duration-150 text-sm"
                                        @click="$dispatch('open-modal', 'return-peminjaman')">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                    <span class="sm:hidden">Pengembalian</span>
                                    <span class="hidden sm:inline">Pengembalian (Admin)</span>
                                </button>
                            @endif
                            @endif
                            <button type="button"
                                    class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-green-600 hover:bg-green-700 active:scale-95 text-white font-semibold rounded-lg sm:rounded-md transition duration-150 text-sm"
                                    @click="$dispatch('open-modal', 'export-excel')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Export Excel</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs Navigation --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs" data-ajax-tabs>
                        <a href="{{ route('gudang.surat-jalan.index', ['tab' => 'keluar']) }}"
                           data-ajax-tab
                           data-ajax-target="#surat-jalan-content"
                           class="w-1/2 py-3 sm:py-4 px-1 text-center border-b-2 font-medium text-xs sm:text-sm {{ $tab === 'keluar' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} active:bg-gray-50 transition">
                            <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Surat Keluar</span>
                            </div>
                        </a>
                        <a href="{{ route('gudang.surat-jalan.index', ['tab' => 'masuk']) }}"
                           data-ajax-tab
                           data-ajax-target="#surat-jalan-content"
                           class="w-1/2 py-3 sm:py-4 px-1 text-center border-b-2 font-medium text-xs sm:text-sm {{ $tab === 'masuk' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} active:bg-gray-50 transition">
                            <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                <span>Surat Masuk</span>
                            </div>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- AJAX Content Container --}}
            <div id="surat-jalan-content">

            {{-- Statistics (SELESAI removed - now in Riwayat page) --}}
            @if($tab === 'keluar')
            {{-- Stats for Surat Keluar - Horizontal scroll on mobile --}}
            <div class="overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 mb-4 sm:mb-6">
                <div class="flex sm:grid sm:grid-cols-4 gap-3 sm:gap-4 min-w-max sm:min-w-0">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-pln-primary rounded-lg p-2.5 sm:p-3">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 sm:ml-5">
                                    <p class="text-xs sm:text-sm font-medium text-gray-500">Total Aktif</p>
                                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-gray-500 rounded-lg p-2.5 sm:p-3">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 sm:ml-5">
                                    <p class="text-xs sm:text-sm font-medium text-gray-500">Draft</p>
                                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['draft'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-500 rounded-lg p-2.5 sm:p-3">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                </div>
                                <div class="ml-3 sm:ml-5">
                                    <p class="text-xs sm:text-sm font-medium text-gray-500">Dikirim</p>
                                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['dikirim'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-2.5 sm:p-3">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 sm:ml-5">
                                    <p class="text-xs sm:text-sm font-medium text-gray-500">Diterima</p>
                                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['diterima'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            {{-- Stats for Surat Masuk - Horizontal scroll on mobile --}}
            <div class="overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 mb-4 sm:mb-6">
                <div class="flex sm:grid sm:grid-cols-3 gap-3 sm:gap-4 min-w-max sm:min-w-0">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-pln-primary rounded-lg p-2.5 sm:p-3">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 sm:ml-5">
                                    <p class="text-xs sm:text-sm font-medium text-gray-500">Total Aktif</p>
                                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[160px] sm:min-w-0">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-2.5 sm:p-3">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 sm:ml-5">
                                    <p class="text-xs sm:text-sm font-medium text-gray-500">Menunggu</p>
                                    <p class="text-lg sm:text-xl font-bold text-yellow-600">{{ $stats['menunggu'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg min-w-[140px] sm:min-w-0">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-500 rounded-lg p-2.5 sm:p-3">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 sm:ml-5">
                                    <p class="text-xs sm:text-sm font-medium text-gray-500">Diterima</p>
                                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['diterima'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Filter & Sort Section --}}
            @php
                $activeFilters = collect(['search', 'status', 'tipe', 'tanggal_mulai', 'tanggal_selesai'])->filter(fn($f) => !empty($filters[$f] ?? null))->count();
                $currentSort = $filters['order_by'] ?? 'terbaru';
            @endphp
            <div class="bg-white overflow-hidden shadow-sm rounded-xl mb-4 sm:mb-6" x-data="{ showFilter: false }">
                {{-- Filter Toggle Button + Quick Sort --}}
                <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
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
                            <a href="{{ route('gudang.surat-jalan.index', ['tab' => $tab]) }}"
                               data-ajax-tab
                               data-ajax-target="#surat-jalan-content"
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
                            <a href="{{ route('gudang.surat-jalan.index', array_merge(request()->query(), ['tab' => $tab, 'order_by' => 'terbaru'])) }}"
                               data-ajax-tab
                               data-ajax-target="#surat-jalan-content"
                               class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $currentSort === 'terbaru' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                Terbaru
                            </a>
                            <a href="{{ route('gudang.surat-jalan.index', array_merge(request()->query(), ['tab' => $tab, 'order_by' => 'terlama'])) }}"
                               data-ajax-tab
                               data-ajax-target="#surat-jalan-content"
                               class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $currentSort === 'terlama' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                Terlama
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Expandable Filter Panel --}}
                <div x-show="showFilter" x-collapse x-cloak>
                    <div class="px-4 pb-4 border-t border-gray-100 pt-4">
                        <form method="GET" action="{{ route('gudang.surat-jalan.index') }}" data-ajax-form data-ajax-target="#surat-jalan-content">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <input type="hidden" name="order_by" value="{{ $currentSort }}">

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
                                               value="{{ $filters['search'] ?? '' }}"
                                               class="block w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition"
                                               placeholder="Contoh: 123/F2206040/YYYY">
                                    </div>
                                </div>

                                {{-- Status Filter --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Status</label>
                                    <select name="status"
                                            class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                                        <option value="">Semua Status</option>
                                        @if($tab === 'keluar')
                                            @foreach(['DRAFT','MENUNGGU_PERSETUJUAN','DITOLAK_PERSETUJUAN','DIKIRIM','MENUNGGU_DIKEMBALIKAN','DIKEMBALIKAN','DIPERIKSA','DITERIMA','DITOLAK','SELESAI'] as $statusOption)
                                                <option value="{{ $statusOption }}" {{ ($filters['status'] ?? '') === $statusOption ? 'selected' : '' }}>
                                                    {{ $statusOption }}
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach(['DIKIRIM','DIKEMBALIKAN','DIPERIKSA','DITERIMA','DITOLAK'] as $statusOption)
                                                <option value="{{ $statusOption }}" {{ ($filters['status'] ?? '') === $statusOption ? 'selected' : '' }}>
                                                    {{ in_array($statusOption, ['DIKIRIM', 'DIKEMBALIKAN']) ? 'MENUNGGU' : $statusOption }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                {{-- Tipe Filter --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Tipe Surat Jalan</label>
                                    <select name="tipe"
                                            class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                                        <option value="">Semua Tipe</option>
                                        @foreach(['TRANSFER','PEMINJAMAN','PENGEMBALIAN'] as $tipeOption)
                                            <option value="{{ $tipeOption }}" {{ ($filters['tipe'] ?? '') === $tipeOption ? 'selected' : '' }}>
                                                {{ $tipeOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date Range --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Mulai</label>
                                    <input type="date"
                                           name="tanggal_mulai"
                                           value="{{ $filters['tanggal_mulai'] ?? '' }}"
                                           class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Selesai</label>
                                    <input type="date"
                                           name="tanggal_selesai"
                                           value="{{ $filters['tanggal_selesai'] ?? '' }}"
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

            {{-- Table --}}
            <div id="surat-jalan-index-table" class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg" data-ajax-container data-surat-jalan-list>
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">
                            {{ $tab === 'keluar' ? 'Daftar Surat Keluar' : 'Daftar Surat Masuk' }}
                        </h3>
                        <div class="text-xs sm:text-sm text-gray-500">{{ $suratJalans->total() }} surat jalan</div>
                    </div>
                </div>

                {{-- Mobile Card View --}}
                <div class="sm:hidden divide-y divide-gray-100">
                    @forelse($suratJalans as $sj)
                        @php
                            $status = $sj->status ?? 'DRAFT';
                            $statusClass = match ($status) {
                                'DRAFT' => 'bg-gray-100 text-gray-800',
                                'MENUNGGU_PERSETUJUAN' => 'bg-orange-100 text-orange-800',
                                'DITOLAK_PERSETUJUAN' => 'bg-red-100 text-red-800',
                                'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                'DIKEMBALIKAN' => 'bg-indigo-100 text-indigo-800',
                                'MENUNGGU_DIKEMBALIKAN' => 'bg-yellow-100 text-yellow-800',
                                'DIPERIKSA' => 'bg-purple-100 text-purple-800',
                                'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                'DITOLAK' => 'bg-red-100 text-red-800',
                                'SELESAI' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                            $tipeLabel = $sj->tipe ?? '-';
                            $tipeClass = match ($tipeLabel) {
                                'PEMINJAMAN' => 'bg-blue-100 text-blue-800',
                                'PENGEMBALIAN' => 'bg-green-100 text-green-800',
                                'TRANSFER' => 'bg-purple-100 text-purple-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <a href="{{ route('gudang.surat-jalan.show', $sj->id) }}" class="block p-4 hover:bg-gray-50 active:bg-gray-100 transition" data-surat-jalan-id="{{ $sj->id }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 text-sm truncate">{{ $sj->nomor ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $sj->tanggal?->format('d M Y') ?? '-' }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusClass }}" data-surat-jalan-status data-base-class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                        {{ $status }}
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $tipeClass }}">
                                            {{ $tipeLabel }}
                                        </span>
                                        @if($tipeLabel === 'PEMINJAMAN')
                                            @if($sj->peminjaman?->suratJalanKembali)
                                                <span class="text-green-500" title="Sudah dikembalikan">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="text-yellow-500" title="Belum dikembalikan">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        @elseif($tipeLabel === 'PENGEMBALIAN' && $sj->peminjamanKembali?->suratJalanKirim)
                                            <span class="text-blue-500" title="Dari: {{ $sj->peminjamanKembali->suratJalanKirim->nomor }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
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
                                <span class="text-pln-primary font-medium flex items-center gap-1">
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
                            <p class="mt-2 font-medium text-sm">Belum ada Surat Jalan</p>
                            <p class="text-xs">Mulai dengan membuat Surat Jalan baru.</p>
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ringkasan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembuat</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($suratJalans as $index => $sj)
                                @php
                                    $status = $sj->status ?? 'DRAFT';
                                    $statusClass = match ($status) {
                                        'DRAFT' => 'bg-gray-100 text-gray-800',
                                        'MENUNGGU_PERSETUJUAN' => 'bg-orange-100 text-orange-800',
                                        'DITOLAK_PERSETUJUAN' => 'bg-red-100 text-red-800',
                                        'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                        'DIKEMBALIKAN' => 'bg-indigo-100 text-indigo-800',
                                        'MENUNGGU_DIKEMBALIKAN' => 'bg-yellow-100 text-yellow-800',
                                        'DIPERIKSA' => 'bg-purple-100 text-purple-800',
                                        'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                        'DITOLAK' => 'bg-red-100 text-red-800',
                                        'SELESAI' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <tr data-surat-jalan-id="{{ $sj->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $suratJalans->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ $sj->nomor ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sj->tanggal?->format('Y-m-d') ?? '-' }}
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
                                        @php
                                            $tipeLabel = $sj->tipe ?? '-';
                                            $tipeClass = match ($tipeLabel) {
                                                'PEMINJAMAN' => 'bg-blue-100 text-blue-800',
                                                'PENGEMBALIAN' => 'bg-green-100 text-green-800',
                                                'TRANSFER' => 'bg-purple-100 text-purple-800',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $tipeClass }}">
                                                {{ $tipeLabel }}
                                            </span>
                                            @if($tipeLabel === 'PEMINJAMAN')
                                                @if($sj->peminjaman?->suratJalanKembali)
                                                    <a href="{{ route('gudang.surat-jalan.show', $sj->peminjaman->suratJalanKembali->id) }}"
                                                       class="text-green-500 hover:text-green-700" title="Sudah dikembalikan - Klik untuk lihat">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </a>
                                                @else
                                                    <span class="text-yellow-500" title="Belum dikembalikan">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </span>
                                                @endif
                                            @elseif($tipeLabel === 'PENGEMBALIAN' && $sj->peminjamanKembali?->suratJalanKirim)
                                                <a href="{{ route('gudang.surat-jalan.show', $sj->peminjamanKembali->suratJalanKirim->id) }}"
                                                   class="text-blue-500 hover:text-blue-700" title="Dari: {{ $sj->peminjamanKembali->suratJalanKirim->nomor }} - Klik untuk lihat">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}" data-surat-jalan-status data-base-class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                            {{ $status }}
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
                                            @if(!empty($sj->id))
                                                <a href="{{ route('gudang.surat-jalan.show', $sj->id) }}"
                                                   class="text-pln-primary hover:text-pln-light"
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
                                            @else
                                                <span class="text-gray-300" title="Belum tersedia">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-10 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="mt-2 font-medium">Belum ada Surat Jalan</p>
                                        <p class="text-sm">Mulai dengan membuat Surat Jalan baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($suratJalans->hasPages())
                    <div class="px-4 sm:px-6 py-4 border-t border-gray-200" data-ajax-pagination>
                        {{ $suratJalans->links() }}
                    </div>
                @endif
            </div>

            </div>{{-- End AJAX Content Container --}}

        </div>
    </div>

    <x-modal name="create-surat-jalan" focusable>
        <div class="p-6"
            x-data="{
                mode: @js(old('mode', 'transfer')),
                items: @js(old('items', [['item_id' => '', 'jumlah' => 1, 'keterangan' => '']])),

                // State untuk Gudang Tujuan
                gudangOpen: false,
                gudangMode: @js(old('gudang_tujuan_mode', 'existing')),
                selectedGudang: @js(old('gudang_tujuan_id', '')),
                labelGudang: '',
                gudangSearch: '',
                allGudangs: @js($gudangs),
                customGudang: {
                    nama: @js(old('gudang_custom_nama', '')),
                    alamat: @js(old('gudang_custom_alamat', '')),
                    telepon: @js(old('gudang_custom_telepon', '')),
                },

                // State untuk PIC Tujuan
                picOpen: false,
                selectedPic: @js(old('pic_tujuan_id', '')),
                labelPic: '',
                picSearch: '',
                allPics: @js(($pics ?? collect())->values()),
                customPic: {
                    nama: @js(old('pic_custom_nama', '')),
                    jabatan: @js(old('pic_custom_jabatan', '')),
                    no_hp: @js(old('pic_custom_no_hp', '')),
                },

                  // Data Pendukung
                  itemUnits: @js(($availableStocks ?? collect())->mapWithKeys(fn($s) => [$s->item_id => ($s->item->satuan ?? '')])),
                  itemStocks: @js(($availableStocks ?? collect())->mapWithKeys(fn($s) => [$s->item_id => (int)($s->jumlah ?? 0)])),
                  asalGudangId: @js($activeGudangId),

                // Error handling
                errors: @js($errors->toArray()),
                submitting: false,

                addRow() { this.items.push({ item_id: '', jumlah: 1, keterangan: '' }); },
                removeRow(i) { if (this.items.length > 1) this.items.splice(i, 1); },

                get filteredGudangs() {
                    return this.allGudangs.filter(g =>
                        g.nama.toLowerCase().includes(this.gudSearch?.toLowerCase() || '') ||
                        g.kode.toLowerCase().includes(this.gudSearch?.toLowerCase() || '')
                    );
                },

                get filteredPics() {
                    if (this.isCustomGudang || isNaN(this.selectedGudang) || this.selectedGudang === '') {
                        return [];
                    }
                    return this.allPics
                        .filter(p => String(p.gudang_id) === String(this.selectedGudang))
                        .filter(p => p.nama.toLowerCase().includes(this.picSearch?.toLowerCase() || ''));
                },
                get isCustomGudang() {
                    return this.gudangMode === 'custom';
                },
                get isCustomPic() {
                    return this.selectedPic === 'lainnya';
                },

                unitFor(id) { return this.itemUnits[id] ?? ''; },
                stockFor(id) { return this.itemStocks[id] ?? 0; },
                init() {
                    if (this.gudangMode === 'custom') {
                        this.labelGudang = 'Lainnya';
                    } else if (this.selectedGudang !== '') {
                        const gudang = this.allGudangs.find(g => String(g.id) === String(this.selectedGudang));
                        if (gudang) {
                            this.labelGudang = gudang.nama;
                        }
                    }

                    if (this.selectedPic === 'lainnya') {
                        this.labelPic = 'Lainnya';
                    } else if (this.selectedPic !== '') {
                        const pic = this.allPics.find(p => String(p.id) === String(this.selectedPic));
                        if (pic) {
                            this.labelPic = pic.nama;
                        }
                    }
                }
            }">

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Buat Surat Jalan Baru</h3>
                <button type="button" @click="$dispatch('close-modal', 'create-surat-jalan')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Error Alert inside Modal --}}
            <template x-if="hasErrors">
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold">Periksa kembali input Anda:</p>
                            <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                                <template x-for="(errorArr, field) in errors" :key="field">
                                    <template x-for="error in (Array.isArray(errorArr) ? errorArr : [errorArr])" :key="error">
                                        <li x-text="error"></li>
                                    </template>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </template>

            @if($isAdmin)
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Mode admin: surat jalan akan langsung diselesaikan saat Anda memilih tombol admin.
                </div>
            @endif

            {{-- Mode Switcher --}}
            <div class="flex gap-3 mb-6">
                <button type="button" @click="mode = 'transfer'" :class="mode === 'transfer' ? 'border-pln-primary bg-pln-primary/5 text-pln-primary' : 'border-gray-200'" class="flex-1 p-3 border rounded-lg text-left transition">
                    <p class="font-bold text-sm">Transfer Barang</p>
                    <p class="text-xs opacity-70">Antar gudang internal</p>
                </button>
                <button type="button" @click="mode = 'peminjaman'" :class="mode === 'peminjaman' ? 'border-pln-primary bg-pln-primary/5 text-pln-primary' : 'border-gray-200'" class="flex-1 p-3 border rounded-lg text-left transition">
                    <p class="font-bold text-sm">Peminjaman</p>
                    <p class="text-xs opacity-70">Wajib tanggal kembali</p>
                </button>
            </div>

              <form method="POST" action="{{ route('gudang.surat-jalan.store') }}" x-ref="createForm" class="space-y-5" enctype="multipart/form-data">
                  @csrf
                  @if($isAdmin && $needsGudangSelection)
                      <input type="hidden" name="gudang_asal_id" value="{{ $activeGudangId }}">
                  @endif
                  <input type="hidden" name="mode" :value="mode">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    {{-- Custom Combobox Gudang Tujuan --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Tujuan</label>
                        <div class="relative">
                            <input type="text" 
                                x-model="labelGudang" 
                                @input="gudangOpen = true; selectedGudang = ''" 
                                @click="gudangOpen = true" 
                                @click.away="gudangOpen = false"
                                placeholder="Cari gudang..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            
                            <input type="hidden" name="gudang_tujuan_mode" :value="gudangMode">
                            <input type="hidden" name="gudang_tujuan_id" :value="selectedGudang">

                            <div x-show="gudangOpen" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <div class="p-2 border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Pilih dari Daftar</div>
                                <template x-for="g in allGudangs.filter(g => g.nama.toLowerCase().includes(labelGudang.toLowerCase()))" :key="g.id">
                                    <button type="button" 
                                            @click="selectedGudang = g.id; labelGudang = g.nama; gudangMode = 'existing'; gudangOpen = false; selectedPic = ''; picSearch=''; customGudang = { nama: '', alamat: '', telepon: '' }; customPic = { nama: '', jabatan: '', no_hp: '' }" 
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-pln-primary hover:text-white transition">
                                        <span x-text="g.kode + ' - ' + g.nama"></span>
                                    </button>
                                </template>
                                <div class="border-t">
                                    <button type="button"
                                            @click="gudangMode = 'custom'; selectedGudang = ''; labelGudang = 'Lainnya'; gudangOpen = false; selectedPic = 'lainnya'; labelPic = 'Lainnya'; picSearch = ''; customGudang = { nama: '', alamat: '', telepon: '' }; customPic = { nama: '', jabatan: '', no_hp: '' }"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        Lainnya...
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="isCustomGudang" class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-gray-900 mb-3">Gudang Lainnya</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Gudang</label>
                                <input type="text" name="gudang_custom_nama" x-model="customGudang.nama"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                <input type="text" name="gudang_custom_alamat" x-model="customGudang.alamat"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No Telp</label>
                                <input type="text" name="gudang_custom_telepon" x-model="customGudang.telepon"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                        </div>
                    </div>

                    {{-- Custom Combobox PIC Tujuan --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">PIC Tujuan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                x-model="labelPic"
                                @click="picOpen = true" 
                                @click.away="picOpen = false"
                                required
                                placeholder="Pilih dari daftar..."
                                readonly
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            
                            <input type="hidden" name="pic_tujuan_id" :value="selectedPic">

                            <div x-show="picOpen" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <div class="p-2 border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Pilih dari Daftar</div>
                                <template x-for="p in filteredPics" :key="p.id">
                                    <button type="button" 
                                            @click="selectedPic = p.id; labelPic = p.nama; picOpen = false" 
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-pln-primary hover:text-white transition">
                                        <span x-text="p.nama + (p.jabatan ? ' ('+p.jabatan+')' : '')"></span>
                                    </button>
                                </template>
                                <div class="border-t" x-show="isCustomGudang || (!isNaN(selectedGudang) && selectedGudang !== '')">
                                    <button type="button"
                                            @click="selectedPic = 'lainnya'; labelPic = 'Lainnya'; picOpen = false"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        Lainnya...
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                  <div x-show="isCustomPic" class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                      <p class="text-sm font-semibold text-gray-900 mb-3">PIC Lainnya</p>
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                          <div>
                              <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC</label>
                                <input type="text" name="pic_custom_nama" x-model="customPic.nama"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                <input type="text" name="pic_custom_jabatan" x-model="customPic.jabatan"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No HP</label>
                                <input type="text" name="pic_custom_no_hp" x-model="customPic.no_hp"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                          </div>
                      </div>
                  </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kirim</label>
                        <input type="date" name="tanggal_kirim" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300">
                    </div>

                    <div x-show="mode === 'peminjaman'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kembali</label>
                        <input type="date" name="tanggal_kembali" min="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Driver <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_driver" placeholder="Nama driver" required
                               value="{{ old('nama_driver') }}"
                               class="w-full rounded-md shadow-sm focus:ring-pln-primary focus:border-pln-primary"
                               :class="getError('nama_driver') ? 'border-red-500' : 'border-gray-300'">
                        <template x-if="getError('nama_driver')">
                            <p class="text-xs text-red-500 mt-1" x-text="getError('nama_driver')"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Plat <span class="text-red-500">*</span></label>
                        <input type="text" name="nomor_plat" placeholder="B 1234 XX" required
                               value="{{ old('nomor_plat') }}"
                               class="w-full rounded-md shadow-sm focus:ring-pln-primary focus:border-pln-primary"
                               :class="getError('nomor_plat') ? 'border-red-500' : 'border-gray-300'">
                        <template x-if="getError('nomor_plat')">
                            <p class="text-xs text-red-500 mt-1" x-text="getError('nomor_plat')"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kendaraan <span class="text-red-500">*</span></label>
                        <input type="text" name="jenis_kendaraan" placeholder="Contoh: Truk Box" required
                               value="{{ old('jenis_kendaraan') }}"
                               class="w-full rounded-md shadow-sm focus:ring-pln-primary focus:border-pln-primary"
                               :class="getError('jenis_kendaraan') ? 'border-red-500' : 'border-gray-300'">
                        <template x-if="getError('jenis_kendaraan')">
                            <p class="text-xs text-red-500 mt-1" x-text="getError('jenis_kendaraan')"></p>
                        </template>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="catatan"
                                  rows="2"
                                  placeholder="Catatan tambahan untuk surat jalan..."
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">{{ old('catatan') }}</textarea>
                    </div>
                </div>

                {{-- Table Items --}}
                <div class="border rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Barang</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase w-24">Jumlah</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(row, idx) in items" :key="idx">
                                <tr>
                                    <td class="px-4 py-2">
                                        <select x-model="row.item_id" :name="`items[${idx}][item_id]`" required class="w-full text-sm rounded-md border-gray-300">
                                            <option value="">Pilih Item Stok...</option>
                                            @foreach($availableStocks as $stock)
                                                <option value="{{ $stock->item_id }}">{{ $stock->item->nama }} (Sisa: {{ $stock->jumlah }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" x-model="row.jumlah" :name="`items[${idx}][jumlah]`" min="1" class="w-full text-sm rounded-md border-gray-300">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" x-model="row.keterangan" :name="`items[${idx}][keterangan]`" placeholder="Opsional..." class="w-full text-sm rounded-md border-gray-300">
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <button type="button" @click="removeRow(idx)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <button type="button" @click="addRow()" class="w-full py-2 bg-gray-50 text-xs font-bold text-pln-primary hover:bg-gray-100 uppercase">+ Tambah Barang</button>
                </div>

                {{-- Lampiran Gambar --}}
                <div class="border rounded-lg p-4 bg-gray-50">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Lampiran Gambar @if($isAdmin)<span class="text-red-500">*</span>@endif <span class="text-gray-400 font-normal">(Maks 3 gambar, maks 10MB/gambar)</span>
                    </label>
                    <input type="file"
                           name="attachments[]"
                           multiple
                           accept="image/jpeg,image/jpg,image/png"
                           @if($isAdmin) required @endif
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-pln-primary file:text-white hover:file:bg-pln-light">
                    <p class="text-xs text-gray-500 mt-2">Format: JPG, JPEG, PNG. @if($isAdmin)Lampiran gambar wajib diupload.@else Gambar wajib diupload sebelum mengirim surat jalan.@endif</p>
                </div>

                <div class="flex flex-col gap-3 pt-4 border-t sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" @click="$dispatch('close-modal', 'create-surat-jalan')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-md">Batal</button>
                    @if(Auth::user()->role === 'admin')
                        <button type="submit"
                                name="admin_finish"
                                value="1"
                                class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-md hover:bg-emerald-700 flex items-center gap-2">
                            Simpan dan Selesaikan (Admin)
                        </button>
                    @endif
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-pln-primary rounded-md hover:bg-pln-light flex items-center gap-2">
                        Simpan Draft
                    </button>
                </div>
            </form>
        </div>
    </x-modal>


    {{-- Modal Pengembalian Peminjaman (Admin Only) --}}
    @if($isAdmin)
    <x-modal name="return-peminjaman" focusable>
        <div class="p-6"
             x-data="{
                selectedPeminjamanId: @js(old('peminjaman_id', '')),
                selectedPic: @js(old('pic_tujuan_id', '')),
                labelPic: '',
                picOpen: false,
                peminjamans: @js(($activePeminjamans ?? collect())->map(fn($p) => [
                    'id' => $p->id,
                    'kode' => $p->kode,
                    'gudang_pemilik_id' => $p->gudang_pemilik_id,
                    'gudang_pemilik_nama' => $p->gudangPemilik->nama ?? '-',
                    'items' => $p->items->map(fn($item) => [
                        'kode' => $item->item->kode ?? '-',
                        'nama' => $item->item->nama ?? 'Item',
                        'satuan' => $item->item->satuan ?? '-',
                        'jumlah' => $item->jumlah_dipinjam,
                    ]),
                ])->values()),
                pics: @js(($pics ?? collect())->map(fn($pic) => [
                    'id' => $pic->id,
                    'nama' => $pic->nama,
                    'jabatan' => $pic->jabatan,
                    'gudang_id' => $pic->gudang_id,
                ])->values()),
                customPic: {
                    nama: @js(old('pic_custom_nama', '')),
                    jabatan: @js(old('pic_custom_jabatan', '')),
                    no_hp: @js(old('pic_custom_no_hp', '')),
                },
                selectedPeminjaman() {
                    return this.peminjamans.find(p => String(p.id) === String(this.selectedPeminjamanId));
                },
                filteredPics() {
                    const peminjaman = this.selectedPeminjaman();
                    if (!peminjaman) return [];
                    return this.pics.filter(pic => String(pic.gudang_id) === String(peminjaman.gudang_pemilik_id));
                },
                get isCustomPic() {
                    return this.selectedPic === 'lainnya';
                },
                handlePeminjamanChange() {
                    const match = this.filteredPics().some(pic => String(pic.id) === String(this.selectedPic));
                    if (!match) {
                        this.selectedPic = '';
                        this.labelPic = '';
                    }
                }
             }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Pengembalian Peminjaman Barang</h3>
                    <p class="text-sm text-gray-500 mt-1">Pilih kode peminjaman, lalu sistem menyiapkan surat jalan pengembalian.</p>
                    <p class="text-xs text-emerald-700 mt-2">Mode admin: surat pengembalian akan langsung diselesaikan.</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                        x-on:click="$dispatch('close-modal', 'return-peminjaman')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('gudang.surat-jalan.return') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="admin_finish" value="1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Peminjaman</label>
                        <select name="peminjaman_id"
                                x-model="selectedPeminjamanId"
                                @change="handlePeminjamanChange()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            <option value="">Pilih kode peminjaman...</option>
                            <template x-for="p in peminjamans" :key="p.id">
                                <option :value="p.id" x-text="p.kode"></option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hanya peminjaman dengan status Diterima yang bisa dikembalikan.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Pemilik</label>
                        <input type="text"
                               class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                               :value="selectedPeminjaman() ? selectedPeminjaman().gudang_pemilik_nama : '-'"
                               readonly>
                    </div>
                    {{-- Custom Combobox PIC Tujuan --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">PIC Tujuan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text"
                                x-model="labelPic"
                                @click="picOpen = true"
                                @click.away="picOpen = false"
                                :required="!isCustomPic"
                                placeholder="Pilih dari daftar..."
                                readonly
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary cursor-pointer">

                            <input type="hidden" name="pic_tujuan_id" :value="selectedPic">

                            <div x-show="picOpen" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <div class="p-2 border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Pilih dari Daftar</div>
                                <template x-for="pic in filteredPics()" :key="pic.id">
                                    <button type="button"
                                            @click="selectedPic = pic.id; labelPic = pic.nama; picOpen = false; customPic = { nama: '', jabatan: '', no_hp: '' }"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-pln-primary hover:text-white transition">
                                        <span x-text="pic.nama + (pic.jabatan ? ' (' + pic.jabatan + ')' : '')"></span>
                                    </button>
                                </template>
                                <div class="border-t" x-show="selectedPeminjamanId !== ''">
                                    <button type="button"
                                            @click="selectedPic = 'lainnya'; labelPic = 'Lainnya'; picOpen = false"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        Lainnya...
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengiriman</label>
                        <input type="date"
                               name="tanggal_kirim"
                               value="{{ old('tanggal_kirim', now()->toDateString()) }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>

                    {{-- Form PIC Lainnya (di dalam grid) --}}
                    <div x-show="isCustomPic" x-cloak class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-gray-900 mb-3">PIC Lainnya</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC <span class="text-red-500">*</span></label>
                                <input type="text" name="pic_custom_nama" x-model="customPic.nama"
                                       :required="isCustomPic"
                                       placeholder="Nama PIC"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                <input type="text" name="pic_custom_jabatan" x-model="customPic.jabatan"
                                       placeholder="Jabatan"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No HP</label>
                                <input type="text" name="pic_custom_no_hp" x-model="customPic.no_hp"
                                       placeholder="No HP"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Driver <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="nama_driver"
                               value="{{ old('nama_driver') }}"
                               required
                               placeholder="Contoh: Budi Santoso"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kendaraan <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="jenis_kendaraan"
                               value="{{ old('jenis_kendaraan') }}"
                               required
                               placeholder="Contoh: Truk Box"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Plat <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="nomor_plat"
                               value="{{ old('nomor_plat') }}"
                               required
                               placeholder="Contoh: B 1234 CD"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan"
                              rows="3"
                              placeholder="Contoh: Pengembalian barang sesuai peminjaman..."
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">{{ old('catatan') }}</textarea>
                </div>

                {{-- Lampiran Gambar --}}
                <div class="border rounded-lg p-4 bg-gray-50">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Lampiran Gambar <span class="text-gray-400 font-normal">(Opsional, Maks 3 gambar, maks 10MB/gambar)</span>
                    </label>
                    <input type="file"
                           name="attachments[]"
                           multiple
                           accept="image/jpeg,image/jpg,image/png"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-pln-primary file:text-white hover:file:bg-pln-light">
                    <p class="text-xs text-gray-500 mt-2">Format: JPG, JPEG, PNG.</p>
                    <p class="text-xs text-amber-600 mt-1">
                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Jika tidak mengupload gambar baru, sistem akan menggunakan lampiran dari surat jalan peminjaman awal.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg border border-gray-200">
                    <div class="p-4">
                        <p class="font-semibold text-gray-900">Barang yang Dikembalikan</p>
                        <p class="text-xs text-gray-500">Jumlah otomatis penuh sesuai peminjaman.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-if="!selectedPeminjaman()">
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">
                                            Pilih kode peminjaman untuk melihat item.
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="selectedPeminjaman()">
                                    <template x-for="(item, idx) in selectedPeminjaman().items" :key="idx">
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900" x-text="item.kode + ' - ' + item.nama"></td>
                                            <td class="px-4 py-3 text-sm text-gray-500" x-text="item.satuan"></td>
                                            <td class="px-4 py-3 text-sm text-gray-900" x-text="item.jumlah"></td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150"
                            x-on:click="$dispatch('close-modal', 'return-peminjaman')">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150">
                        Simpan dan Selesaikan (Admin)
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
    @endif

    {{-- Export Excel Modal --}}
    <x-modal name="export-excel" focusable maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Export Surat Jalan ke Excel</h3>
                <button type="button"
                        @click="$dispatch('close-modal', 'export-excel')"
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="GET"
                  action="{{ route('gudang.surat-jalan.export-excel') }}"
                  x-data="{
                      periode: '1_bulan',
                      showCustom: false,
                      updatePeriode() {
                          this.showCustom = this.periode === 'custom';
                      }
                  }"
                  class="space-y-5">

                {{-- Type Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Surat Jalan</label>
                    <select name="tipe"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                        <option value="ALL">Semua Tipe</option>
                        <option value="TRANSFER">Transfer</option>
                        <option value="PEMINJAMAN">Peminjaman</option>
                        <option value="PENGEMBALIAN">Pengembalian</option>
                    </select>
                </div>

                {{-- Period Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                    <select name="periode"
                            x-model="periode"
                            @change="updatePeriode()"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                        <option value="1_minggu">1 Minggu Terakhir</option>
                        <option value="1_bulan">1 Bulan Terakhir</option>
                        <option value="3_bulan">3 Bulan Terakhir</option>
                        <option value="6_bulan">6 Bulan Terakhir</option>
                        <option value="1_tahun">1 Tahun Terakhir</option>
                        <option value="custom">Custom (Pilih Tanggal)</option>
                    </select>
                </div>

                {{-- Custom Date Range --}}
                <div x-show="showCustom" x-cloak class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date"
                               name="tanggal_mulai"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date"
                               name="tanggal_selesai"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>
                </div>

                {{-- Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-blue-700">
                        <span class="font-medium">Info:</span> Data yang diekspor hanya surat keluar.
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button"
                            @click="$dispatch('close-modal', 'export-excel')"
                            class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-md hover:bg-green-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Excel
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Echo) {
                return;
            }
            const statusClassMap = {
                DRAFT: 'bg-gray-100 text-gray-800',
                MENUNGGU_PERSETUJUAN: 'bg-orange-100 text-orange-800',
                DITOLAK_PERSETUJUAN: 'bg-red-100 text-red-800',
                DIKIRIM: 'bg-blue-100 text-blue-800',
                DIKEMBALIKAN: 'bg-indigo-100 text-indigo-800',
                MENUNGGU_DIKEMBALIKAN: 'bg-yellow-100 text-yellow-800',
                DIPERIKSA: 'bg-purple-100 text-purple-800',
                DITERIMA: 'bg-yellow-100 text-yellow-800',
                DITOLAK: 'bg-red-100 text-red-800',
                SELESAI: 'bg-green-100 text-green-800',
            };
            const gudangIds = Array.from(new Set([
                @if(!empty($activeGudangId))
                    {{ (int) $activeGudangId }},
                @elseif(($selectionGudangs ?? collect())->count() > 0)
                    ...@json($selectionGudangs->pluck('id')->all()),
                @endif
            ].filter(Boolean)));

            const refreshList = async () => {
                const container = document.querySelector('[data-surat-jalan-list]');
                if (!container) {
                    return;
                }
                try {
                    const response = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) {
                        return;
                    }
                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const next = doc.querySelector('[data-surat-jalan-list]');
                    if (next) {
                        container.innerHTML = next.innerHTML;
                    }
                } catch (error) {
                    console.error('Realtime list refresh failed', error);
                }
            };

            const updateStatusBadges = (payload) => {
                const status = payload?.status;
                if (!status) {
                    return;
                }
                const rows = document.querySelectorAll(`[data-surat-jalan-id="${payload.id}"]`);
                if (rows.length === 0) {
                    if (payload?.action === 'created') {
                        refreshList();
                    }
                    return;
                }
                rows.forEach((row) => {
                    row.querySelectorAll('[data-surat-jalan-status]').forEach((badge) => {
                        const baseClass = badge.dataset.baseClass || '';
                        const nextClass = statusClassMap[status] || 'bg-gray-100 text-gray-800';
                        badge.className = `${baseClass} ${nextClass}`.trim();
                        badge.textContent = status;
                    });
                });
                const statusFilter = document.querySelector('select[name="status"]')?.value;
                if (statusFilter && statusFilter !== status) {
                    refreshList();
                }
            };

            gudangIds.forEach((gudangId) => {
                window.Echo.channel(`surat-jalan.gudang.${gudangId}`)
                    .listen('.SuratJalanStatusUpdated', updateStatusBadges);
            });
        });
    </script>
</x-app-layout>
