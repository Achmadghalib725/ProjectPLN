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

            {{-- Header + Tabs Navigation --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6">
                {{-- Header --}}
                <div class="p-4 sm:p-6 border-b border-gray-100">
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
                        <form method="GET" action="{{ route('admin.surat-jalan.index') }}" class="flex items-center gap-2">
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
                {{-- Tabs --}}
                <nav class="flex" aria-label="Tabs" data-ajax-tabs>
                    <a href="{{ route('admin.surat-jalan.index', array_merge(request()->query(), ['tab' => 'keluar'])) }}"
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
                    <a href="{{ route('admin.surat-jalan.index', array_merge(request()->query(), ['tab' => 'masuk'])) }}"
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
                            <span>Filter & Search</span>
                            @if($activeFilters > 0)
                                <span class="bg-[#035b71] text-white text-xs px-2 py-0.5 rounded-full">{{ $activeFilters }}</span>
                            @endif
                            <svg class="w-4 h-4 transition-transform duration-200" :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        @if($activeFilters > 0)
                            <a href="{{ route('admin.surat-jalan.index', ['tab' => $tab]) }}"
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
                            <a href="{{ route('admin.surat-jalan.index', array_merge(request()->query(), ['tab' => $tab, 'order_by' => 'terbaru'])) }}"
                               data-ajax-tab
                               data-ajax-target="#surat-jalan-content"
                               class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $currentSort === 'terbaru' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                Terbaru
                            </a>
                            <a href="{{ route('admin.surat-jalan.index', array_merge(request()->query(), ['tab' => $tab, 'order_by' => 'terlama'])) }}"
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
                        <form method="GET" action="{{ route('admin.surat-jalan.index') }}" data-ajax-form data-ajax-target="#surat-jalan-content">
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
                                               placeholder="Contoh: 705/SJ251223/2025">
                                    </div>
                                </div>

                                {{-- Status Filter --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Status</label>
                                    <select name="status"
                                            class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                                        <option value="">Semua Status</option>
                                        @if($tab === 'keluar')
                                            @foreach(['DRAFT','MENUNGGU_PERSETUJUAN','DITOLAK_PERSETUJUAN','DIKIRIM','DIPERIKSA_PENGIRIM','DIPERIKSA_PENERIMA','MENUNGGU_DIKEMBALIKAN','DIKEMBALIKAN','DIPERIKSA','DITERIMA','DITOLAK','SELESAI'] as $statusOption)
                                                <option value="{{ $statusOption }}" {{ ($filters['status'] ?? '') === $statusOption ? 'selected' : '' }}>
                                                    {{ $statusOption }}
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach(['DIKIRIM','DIPERIKSA_PENGIRIM','DIPERIKSA_PENERIMA','DIKEMBALIKAN','DIPERIKSA','DITERIMA','DITOLAK'] as $statusOption)
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
            <div id="surat-jalan-index-table" class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg" data-ajax-container>
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
                                'DIPERIKSA_PENGIRIM' => 'bg-cyan-100 text-cyan-800',
                                'DIPERIKSA_PENERIMA' => 'bg-purple-100 text-purple-800',
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
                        <a href="{{ route('admin.surat-jalan.show', $sj->id) }}" class="block p-4 transition-colors hover:bg-[#e6f7fb] active:bg-[#cfeff7]">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 text-sm truncate">{{ $sj->nomor ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $sj->tanggal?->format('d M Y') ?? '-' }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusClass }}">
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
                                        'DIPERIKSA_PENGIRIM' => 'bg-cyan-100 text-cyan-800',
                                        'DIPERIKSA_PENERIMA' => 'bg-purple-100 text-purple-800',
                                        'DIKEMBALIKAN' => 'bg-indigo-100 text-indigo-800',
                                        'MENUNGGU_DIKEMBALIKAN' => 'bg-yellow-100 text-yellow-800',
                                        'DIPERIKSA' => 'bg-purple-100 text-purple-800',
                                        'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                        'DITOLAK' => 'bg-red-100 text-red-800',
                                        'SELESAI' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $rowLink = !empty($sj->id) ? route('admin.surat-jalan.show', $sj->id) : null;
                                @endphp
                                <tr @if($rowLink) data-row-link="{{ $rowLink }}" class="cursor-pointer transition-colors hover:bg-[#e6f7fb]" @endif>
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
                                                    <a href="{{ route('admin.surat-jalan.show', $sj->peminjaman->suratJalanKembali->id) }}"
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
                                                <a href="{{ route('admin.surat-jalan.show', $sj->peminjamanKembali->suratJalanKirim->id) }}"
                                                   class="text-blue-500 hover:text-blue-700" title="Dari: {{ $sj->peminjamanKembali->suratJalanKirim->nomor }} - Klik untuk lihat">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
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
                                                <a href="{{ route('admin.surat-jalan.pdf', $sj->id) }}"
                                                   class="text-green-600 hover:text-green-800"
                                                   title="Download PDF">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                                @if($sj->status !== 'SELESAI')
                                                    <button type="button"
                                                        @click="$dispatch('open-delete-modal', {
                                                            title: 'Batalkan Surat Jalan',
                                                            message: 'Apakah Anda yakin ingin membatalkan surat jalan {{ $sj->nomor }}? {{ in_array($sj->status, ['DIKIRIM', 'DIPERIKSA_PENGIRIM', 'DIPERIKSA_PENERIMA', 'DITERIMA', 'MENUNGGU_DIKEMBALIKAN', 'DIKEMBALIKAN', 'DIPERIKSA']) ? 'Semua pergerakan stok akan di-rollback.' : '' }}',
                                                            action: '{{ route('admin.surat-jalan.destroy', $sj->id) }}'
                                                        })"
                                                        class="text-red-500 hover:text-red-700"
                                                        title="Batalkan Surat Jalan">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
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
                  itemUnits: @js(($availableStocks ?? collect())->mapWithKeys(fn($s) => [$s->item_id => ($s->item->satuan?->nama ?? '')])),
                  itemStocks: @js(($availableStocks ?? collect())->mapWithKeys(fn($s) => [$s->item_id => (int)($s->jumlah ?? 0)])),
                  itemsCatalog: @js(($availableStocks ?? collect())->map(fn($s) => [
                      'id' => $s->item_id,
                      'nama' => $s->item->nama,
                      'kode' => $s->item->kode ?? '',
                      'stok' => (int) ($s->jumlah ?? 0),
                  ])),
                  asalGudangId: @js($activeGudangId),

                // Error handling
                errors: @js($errors->toArray()),
                submitting: false,
                get hasErrors() {
                    return Object.keys(this.errors || {}).length > 0;
                },
                getError(field) {
                    const error = this.errors?.[field];
                    if (!error) {
                        return '';
                    }
                    return Array.isArray(error) ? (error[0] ?? '') : error;
                },

                newItemRow(data = {}) {
                    const itemId = data.item_id ?? '';
                    return {
                        item_id: itemId,
                        jumlah: data.jumlah ?? 1,
                        keterangan: data.keterangan ?? '',
                        search: this.itemLabel(itemId),
                        open: false,
                    };
                },
                addRow() { this.items.push(this.newItemRow()); },
                removeRow(i) { if (this.items.length > 1) this.items.splice(i, 1); },
                itemLabel(id) {
                    if (!id) return '';
                    const item = this.itemsCatalog.find(i => String(i.id) === String(id));
                    if (!item) return '';
                    return item.kode ? `${item.nama} (${item.kode})` : item.nama;
                },
                filteredItems(term) {
                    const q = (term ?? '').toLowerCase().trim();
                    if (!q) return this.itemsCatalog;
                    return this.itemsCatalog.filter(item =>
                        item.nama.toLowerCase().includes(q) ||
                        (item.kode || '').toLowerCase().includes(q)
                    );
                },
                selectItem(row, item) {
                    row.item_id = item.id;
                    row.search = this.itemLabel(item.id);
                    row.open = false;
                },
                hasSearch(row) {
                    return (row.search ?? '').trim() !== '';
                },
                itemErrorMessage(row) {
                    if (row.item_id || !this.hasSearch(row)) {
                        return '';
                    }
                    return this.filteredItems(row.search).length === 0
                        ? 'Barang tidak ditemukan.'
                        : 'Pilih barang dari daftar.';
                },
                get hasInvalidItems() {
                    return this.items.some(row => !row.item_id);
                },

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
                    this.items = this.items.map(row => this.newItemRow(row));
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

              <form method="POST" action="{{ route('admin.surat-jalan.store') }}" x-ref="createForm" class="space-y-5" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" name="tab" value="{{ $tab }}">
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
                        <input type="date" name="tanggal_kirim" value="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300">
                    </div>

                    <div x-show="mode === 'peminjaman'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kembali</label>
                        <input type="date" name="tanggal_kembali" class="w-full rounded-md border-gray-300">
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

                {{-- Items - Mobile Card Layout --}}
                <div class="sm:hidden border rounded-lg overflow-visible">
                    <div class="bg-gray-50 px-3 py-2 border-b">
                        <span class="text-xs font-bold text-gray-500 uppercase">Daftar Barang</span>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <template x-for="(row, idx) in items" :key="idx">
                            <div class="p-3 bg-white">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <span class="text-xs font-semibold text-gray-500">Item #<span x-text="idx + 1"></span></span>
                                    <button type="button" @click="removeRow(idx)" class="text-red-500 hover:text-red-700 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                {{-- Barang --}}
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Barang <span class="text-red-500">*</span></label>
                                    <div class="relative" @click.away="row.open = false">
                                        <input type="text"
                                               x-model="row.search"
                                               @input="row.open = true; row.item_id = ''"
                                               @focus="row.open = true"
                                               placeholder="Ketik untuk mencari barang..."
                                               class="w-full text-sm rounded-md border-gray-300 py-2.5">
                                        <select x-model="row.item_id" :name="`items[${idx}][item_id]`" required class="hidden">
                                            <option value="">Pilih Item Stok...</option>
                                            @foreach($availableStocks as $stock)
                                                <option value="{{ $stock->item_id }}">{{ $stock->item->nama }} (Sisa: {{ $stock->jumlah }})</option>
                                            @endforeach
                                        </select>
                                        <div x-show="row.open"
                                             x-cloak
                                             @wheel.stop
                                             @touchmove.stop
                                             class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto overscroll-contain">
                                            <template x-for="item in filteredItems(row.search)" :key="item.id">
                                                <button type="button"
                                                        @click="selectItem(row, item)"
                                                        class="w-full text-left px-3 py-2.5 text-sm hover:bg-pln-primary hover:text-white transition">
                                                    <div class="font-medium" x-text="item.nama"></div>
                                                    <div class="text-xs opacity-70" x-text="(item.kode ? item.kode + ' • ' : '') + 'Sisa: ' + item.stok"></div>
                                                </button>
                                            </template>
                                            <div x-show="filteredItems(row.search).length === 0" class="px-3 py-2 text-sm text-gray-500">
                                                Item tidak ditemukan.
                                            </div>
                                        </div>
                                    </div>
                                    <template x-if="itemErrorMessage(row)">
                                        <p class="mt-1 text-xs text-red-500" x-text="itemErrorMessage(row)"></p>
                                    </template>
                                </div>
                                {{-- Jumlah & Keterangan --}}
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah <span class="text-red-500">*</span></label>
                                        <input type="number" x-model="row.jumlah" :name="`items[${idx}][jumlah]`" min="1" class="w-full text-sm rounded-md border-gray-300 py-2.5">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                                        <input type="text" x-model="row.keterangan" :name="`items[${idx}][keterangan]`" placeholder="Opsional..." class="w-full text-sm rounded-md border-gray-300 py-2.5">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addRow()" class="w-full py-3 bg-gray-50 text-sm font-bold text-pln-primary hover:bg-gray-100 uppercase border-t">+ Tambah Barang</button>
                </div>

                {{-- Items - Desktop Table Layout --}}
                <div class="hidden sm:block border rounded-lg overflow-visible">
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
                                        <div class="relative" @click.away="row.open = false">
                                            <input type="text"
                                                   x-model="row.search"
                                                   @input="row.open = true; row.item_id = ''"
                                                   @focus="row.open = true"
                                                   placeholder="Cari barang..."
                                                   class="w-full text-sm rounded-md border-gray-300">
                                            <select x-model="row.item_id" :name="`items[${idx}][item_id]`" required class="hidden">
                                                <option value="">Pilih Item Stok...</option>
                                                @foreach($availableStocks as $stock)
                                                    <option value="{{ $stock->item_id }}">{{ $stock->item->nama }} (Sisa: {{ $stock->jumlah }})</option>
                                                @endforeach
                                            </select>
                                            <div x-show="row.open"
                                                 x-cloak
                                                 @wheel.stop
                                                 @touchmove.stop
                                                 class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto overscroll-contain">
                                                <template x-for="item in filteredItems(row.search)" :key="item.id">
                                                    <button type="button"
                                                            @click="selectItem(row, item)"
                                                            class="w-full text-left px-3 py-2 text-xs hover:bg-pln-primary hover:text-white transition">
                                                        <div class="font-medium" x-text="item.nama"></div>
                                                        <div class="text-[10px] opacity-70" x-text="(item.kode ? item.kode + ' • ' : '') + 'Sisa: ' + item.stok"></div>
                                                    </button>
                                                </template>
                                                <div x-show="filteredItems(row.search).length === 0" class="px-3 py-2 text-xs text-gray-500">
                                                    Item tidak ditemukan.
                                                </div>
                                            </div>
                                        </div>
                                        <template x-if="itemErrorMessage(row)">
                                            <p class="mt-1 text-xs text-red-500" x-text="itemErrorMessage(row)"></p>
                                        </template>
                                    </td>
                                    <td class="px-4 py-2 w-24">
                                        <input type="number" x-model="row.jumlah" :name="`items[${idx}][jumlah]`" min="1" class="w-full text-sm rounded-md border-gray-300">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" x-model="row.keterangan" :name="`items[${idx}][keterangan]`" placeholder="Opsional..." class="w-full text-sm rounded-md border-gray-300">
                                    </td>
                                    <td class="px-4 py-2 text-right w-16">
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
                <div class="border border-dashed border-gray-300 rounded-xl bg-gray-50/50 overflow-hidden" data-camera-capture data-target-input="attachments-create-admin" data-max-files="3">
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Lampiran Foto @if($isAdmin)<span class="text-red-500">*</span>@endif</span>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full" data-camera-status>0/3</span>
                    </div>

                    {{-- Camera Panel (Hidden by default) --}}
                    <div data-camera-panel class="hidden bg-black">
                        <div class="relative">
                            <video class="w-full h-48 object-cover" playsinline muted></video>
                            <canvas class="hidden"></canvas>
                            <div class="absolute bottom-3 left-0 right-0 flex items-center justify-center gap-3">
                                <button type="button"
                                        data-camera-capture-btn
                                        class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg hover:bg-gray-100 transition-colors">
                                    <div class="w-10 h-10 bg-red-500 rounded-full"></div>
                                </button>
                                <button type="button"
                                        data-camera-close
                                        class="w-10 h-10 bg-gray-800/80 rounded-full flex items-center justify-center text-white hover:bg-gray-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Upload Area --}}
                    <div class="p-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        data-camera-open
                                        class="md:hidden inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Kamera
                                </button>
                                <label class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-pln-primary rounded-lg hover:bg-pln-light cursor-pointer transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Pilih File
                                    <input type="file"
                                           id="attachments-create-admin"
                                           name="attachments[]"
                                           multiple
                                           accept="image/jpeg,image/jpg,image/png"
                                           @if($isAdmin) required @endif
                                           class="hidden">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">JPG, PNG. Maks 10MB.@if($isAdmin) Wajib diupload.@endif</p>
                        </div>
                        <p class="text-xs text-red-600 hidden mt-2" data-camera-error></p>

                        {{-- Preview Grid --}}
                        <div class="mt-4 grid grid-cols-3 gap-3" data-camera-preview>
                            <div class="aspect-square rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center" data-placeholder>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="aspect-square rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center" data-placeholder>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="aspect-square rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center" data-placeholder>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4 border-t sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" @click="$dispatch('close-modal', 'create-surat-jalan')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-md">Batal</button>
                    @if(Auth::user()->role === 'admin')
                        <button type="submit"
                                name="admin_finish"
                                value="1"
                                :disabled="hasInvalidItems"
                                class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-md hover:bg-emerald-700 flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            Simpan dan Selesaikan (Admin)
                        </button>
                    @endif
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
                tanggalKirim: @js(old('tanggal_kirim', now()->toDateString())),
                peminjamans: @js(($activePeminjamans ?? collect())->map(fn($p) => [
                    'id' => $p->id,
                    'kode' => $p->kode,
                    'gudang_pemilik_id' => $p->gudang_pemilik_id,
                    'gudang_pemilik_nama' => $p->gudangPemilik->nama ?? '-',
                    'batas_waktu_kembali' => $p->batas_waktu_kembali?->format('Y-m-d'),
                    'tanggal_kirim' => $p->suratJalanKirim?->tanggal?->format('Y-m-d')
                        ?? $p->waktu_kirim?->format('Y-m-d'),
                    'items' => $p->items->map(fn($item) => [
                        'kode' => $item->item->kode ?? '-',
                        'nama' => $item->item->nama ?? 'Item',
                        'satuan' => $item->item->satuan?->nama ?? '-',
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
                    const peminjaman = this.selectedPeminjaman();
                    if (peminjaman?.batas_waktu_kembali) {
                        this.tanggalKirim = peminjaman.batas_waktu_kembali;
                    } else if (peminjaman?.tanggal_kirim) {
                        this.tanggalKirim = peminjaman.tanggal_kirim;
                    }
                }
             }"
             x-init="handlePeminjamanChange()"
             x-effect="selectedPeminjamanId && handlePeminjamanChange()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Pengembalian Peminjaman Barang</h3>
                    <p class="text-sm text-gray-500 mt-1">Pilih kode peminjaman, lalu sistem menyiapkan surat jalan pengembalian.</p>
                    <p class="text-xs text-emerald-700 mt-2">Surat pengembalian akan langsung diselesaikan.</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                        x-on:click="$dispatch('close-modal', 'return-peminjaman')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.surat-jalan.return') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="admin_finish" value="1">
                <input type="hidden" name="tab" value="{{ $tab }}">
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
                               x-model="tanggalKirim"
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
                <div class="border border-dashed border-gray-300 rounded-xl bg-gray-50/50 overflow-hidden" data-camera-capture data-target-input="attachments-return-admin" data-max-files="3">
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Lampiran Foto <span class="text-gray-400 font-normal">(Opsional)</span></span>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full" data-camera-status>0/3</span>
                    </div>

                    {{-- Camera Panel (Hidden by default) --}}
                    <div data-camera-panel class="hidden bg-black">
                        <div class="relative">
                            <video class="w-full h-48 object-cover" playsinline muted></video>
                            <canvas class="hidden"></canvas>
                            <div class="absolute bottom-3 left-0 right-0 flex items-center justify-center gap-3">
                                <button type="button"
                                        data-camera-capture-btn
                                        class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg hover:bg-gray-100 transition-colors">
                                    <div class="w-10 h-10 bg-red-500 rounded-full"></div>
                                </button>
                                <button type="button"
                                        data-camera-close
                                        class="w-10 h-10 bg-gray-800/80 rounded-full flex items-center justify-center text-white hover:bg-gray-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Upload Area --}}
                    <div class="p-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        data-camera-open
                                        class="md:hidden inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Kamera
                                </button>
                                <label class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-pln-primary rounded-lg hover:bg-pln-light cursor-pointer transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Pilih File
                                    <input type="file"
                                           id="attachments-return-admin"
                                           name="attachments[]"
                                           multiple
                                           accept="image/jpeg,image/jpg,image/png"
                                           class="hidden">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">JPG, PNG. Maks 10MB.</p>
                        </div>
                        <p class="text-xs text-red-600 hidden mt-2" data-camera-error></p>

                        {{-- Preview Grid --}}
                        <div class="mt-4 grid grid-cols-3 gap-3" data-camera-preview>
                            <div class="aspect-square rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center" data-placeholder>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="aspect-square rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center" data-placeholder>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="aspect-square rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center" data-placeholder>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Info --}}
                        <p class="text-xs text-amber-600 mt-3 flex items-start gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Jika tidak mengupload gambar baru, sistem akan menggunakan lampiran dari surat jalan peminjaman awal.
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg border border-gray-200">
                    <div class="p-4">
                        <p class="font-semibold text-gray-900">Barang yang Dikembalikan</p>
                        <p class="text-xs text-gray-500">Jumlah otomatis penuh sesuai peminjaman.</p>
                    </div>

                    {{-- Mobile Card Layout --}}
                    <div class="sm:hidden">
                        <template x-if="!selectedPeminjaman()">
                            <div class="px-4 py-6 text-center text-sm text-gray-500">
                                Pilih kode peminjaman untuk melihat item.
                            </div>
                        </template>
                        <template x-if="selectedPeminjaman()">
                            <div class="divide-y divide-gray-200">
                                <template x-for="(item, idx) in selectedPeminjaman().items" :key="idx">
                                    <div class="p-3 bg-white">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-semibold text-gray-500">Item #<span x-text="idx + 1"></span></span>
                                        </div>
                                        <p class="font-medium text-sm text-gray-900 mb-2" x-text="item.kode + ' - ' + item.nama"></p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <span class="block text-xs text-gray-500">Satuan</span>
                                                <span class="text-sm text-gray-900" x-text="item.satuan"></span>
                                            </div>
                                            <div>
                                                <span class="block text-xs text-gray-500">Jumlah</span>
                                                <span class="text-sm font-semibold text-gray-900" x-text="item.jumlah"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Desktop Table Layout --}}
                    <div class="hidden sm:block overflow-x-auto">
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

                <div class="flex flex-col gap-3 pt-4 border-t sm:flex-row sm:items-center sm:justify-end">
                    <button type="button"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition text-center"
                            x-on:click="$dispatch('close-modal', 'return-peminjaman')">
                        Batal
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition text-center">
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
            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-900">Export Excel</h3>
                <button type="button"
                        @click="$dispatch('close-modal', 'export-excel')"
                        class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <form method="GET"
                  action="{{ route('admin.surat-jalan.export-excel') }}"
                  x-data="{
                      periode: '1_bulan',
                      showCustom: false,
                      updatePeriode() {
                          this.showCustom = this.periode === 'custom';
                      }
                  }">

                <div class="space-y-4">
                    {{-- Type Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Surat Jalan</label>
                        <select name="tipe"
                                class="w-full rounded-lg border-gray-300 bg-white text-sm focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71]">
                            <option value="ALL">Semua Tipe</option>
                            <option value="TRANSFER">Transfer</option>
                            <option value="PEMINJAMAN">Peminjaman</option>
                            <option value="PENGEMBALIAN">Pengembalian</option>
                        </select>
                    </div>

                    {{-- Period Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Periode</label>
                        <select name="periode"
                                x-model="periode"
                                @change="updatePeriode()"
                                class="w-full rounded-lg border-gray-300 bg-white text-sm focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71]">
                            <option value="1_minggu">1 Minggu Terakhir</option>
                            <option value="1_bulan">1 Bulan Terakhir</option>
                            <option value="3_bulan">3 Bulan Terakhir</option>
                            <option value="6_bulan">6 Bulan Terakhir</option>
                            <option value="1_tahun">1 Tahun Terakhir</option>
                            <option value="custom">Custom (Pilih Tanggal)</option>
                        </select>
                    </div>

                    {{-- Custom Date Range --}}
                    <div x-show="showCustom" x-collapse x-cloak>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                                <input type="date"
                                       name="tanggal_mulai"
                                       required
                                       class="w-full rounded-lg border-gray-300 bg-white text-sm focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                                <input type="date"
                                       name="tanggal_selesai"
                                       required
                                       class="w-full rounded-lg border-gray-300 bg-white text-sm focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71]">
                            </div>
                        </div>
                    </div>

                    {{-- Info --}}
                    <p class="text-xs text-gray-500">Data yang diekspor mencakup semua surat jalan dengan status SELESAI.</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="button"
                            @click="$dispatch('close-modal', 'export-excel')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
    {{-- Delete/Cancel Confirmation Modal --}}
    <x-confirm-delete-modal />
    <script>
        function setupCameraCapture(wrapper) {
            const inputId = wrapper.dataset.targetInput;
            const input = document.getElementById(inputId);
            const maxFiles = Number(wrapper.dataset.maxFiles || 3);
            const openBtn = wrapper.querySelector('[data-camera-open]');
            const captureBtn = wrapper.querySelector('[data-camera-capture-btn]');
            const closeBtn = wrapper.querySelector('[data-camera-close]');
            const panel = wrapper.querySelector('[data-camera-panel]');
            const video = wrapper.querySelector('video');
            const canvas = wrapper.querySelector('canvas');
            const error = wrapper.querySelector('[data-camera-error]');
            const status = wrapper.querySelector('[data-camera-status]');
            const preview = wrapper.querySelector('[data-camera-preview]');

            // Store collected files separately to prevent browser replacing them
            wrapper._collectedFiles = [];

            const setError = (message) => {
                if (!error) {
                    return;
                }
                if (message) {
                    error.textContent = message;
                    error.classList.remove('hidden');
                } else {
                    error.textContent = '';
                    error.classList.add('hidden');
                }
            };

            // Sync collected files to the input element
            const syncToInput = () => {
                if (!input) return;
                const dataTransfer = new DataTransfer();
                wrapper._collectedFiles.forEach((file) => dataTransfer.items.add(file));
                input.files = dataTransfer.files;
            };

            const updateStatus = () => {
                if (!status) {
                    return;
                }
                const count = wrapper._collectedFiles.length;
                status.textContent = `${count}/${maxFiles}`;
            };

            const clearPreview = () => {
                if (wrapper._objectUrls) {
                    wrapper._objectUrls.forEach((url) => URL.revokeObjectURL(url));
                }
                wrapper._objectUrls = [];
                if (preview) {
                    preview.innerHTML = '';
                }
            };

            const removeFile = (index) => {
                wrapper._collectedFiles.splice(index, 1);
                syncToInput();
                renderPreview();
            };

            const renderPreview = () => {
                updateStatus();
                if (!preview) {
                    return;
                }
                clearPreview();
                const files = wrapper._collectedFiles;
                const placeholders = preview.querySelectorAll('[data-placeholder]');

                // Hide placeholders based on file count
                placeholders.forEach((ph, idx) => {
                    if (idx < files.length) {
                        ph.classList.add('hidden');
                    } else {
                        ph.classList.remove('hidden');
                    }
                });

                // Add file previews before placeholders
                files.forEach((file, index) => {
                    const url = URL.createObjectURL(file);
                    wrapper._objectUrls.push(url);

                    const item = document.createElement('div');
                    item.className = 'aspect-square rounded-lg overflow-hidden border-2 border-gray-200 bg-white relative group';

                    const img = document.createElement('img');
                    img.className = 'w-full h-full object-cover';
                    img.src = url;
                    img.alt = file.name;

                    const overlay = document.createElement('div');
                    overlay.className = 'absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'p-1.5 bg-red-500 hover:bg-red-600 rounded-full text-white';
                    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                    removeBtn.addEventListener('click', () => removeFile(index));

                    overlay.appendChild(removeBtn);
                    item.appendChild(img);
                    item.appendChild(overlay);

                    // Insert before first placeholder
                    const firstPlaceholder = preview.querySelector('[data-placeholder]');
                    if (firstPlaceholder) {
                        preview.insertBefore(item, firstPlaceholder);
                    } else {
                        preview.appendChild(item);
                    }
                });
            };

            const isAllowedFile = (file) => {
                const type = (file.type || '').toLowerCase();
                if (type === 'image/jpeg' || type === 'image/png' || type === 'image/jpg') {
                    return true;
                }
                const name = (file.name || '').toLowerCase();
                return name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.png');
            };

            const appendFiles = (files) => {
                if (!files || files.length === 0) {
                    return;
                }
                setError('');
                files.forEach((file) => {
                    if (!isAllowedFile(file)) {
                        setError('Hanya mendukung file JPG/PNG.');
                        return;
                    }
                    if (wrapper._collectedFiles.length < maxFiles) {
                        wrapper._collectedFiles.push(file);
                    }
                });
                if (wrapper._collectedFiles.length > maxFiles) {
                    setError(`Maksimal ${maxFiles} gambar.`);
                    wrapper._collectedFiles = wrapper._collectedFiles.slice(0, maxFiles);
                }
                syncToInput();
                renderPreview();
            };

            const setHighlight = (active) => {
                wrapper.classList.toggle('ring-2', active);
                wrapper.classList.toggle('ring-pln-primary', active);
                wrapper.classList.toggle('border-pln-primary', active);
                wrapper.classList.toggle('bg-blue-50/50', active);
            };

            // Merge new files with existing collected files
            const normalizeFiles = () => {
                if (!input) {
                    return;
                }
                appendFiles(Array.from(input.files || []));
            };

            const stopCamera = () => {
                if (wrapper._cameraStream) {
                    wrapper._cameraStream.getTracks().forEach((track) => track.stop());
                    wrapper._cameraStream = null;
                }
                if (video) {
                    video.srcObject = null;
                }
                if (panel) {
                    panel.classList.add('hidden');
                }
                if (openBtn) {
                    openBtn.classList.remove('hidden');
                }
                if (captureBtn) {
                    captureBtn.classList.add('hidden');
                }
                if (closeBtn) {
                    closeBtn.classList.add('hidden');
                }
            };

            const openCamera = async () => {
                setError('');
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setError('Browser tidak mendukung akses kamera.');
                    return;
                }
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                        audio: false,
                    });
                    wrapper._cameraStream = stream;
                    if (video) {
                        video.srcObject = stream;
                        await video.play();
                    }
                    if (panel) {
                        panel.classList.remove('hidden');
                    }
                    if (openBtn) {
                        openBtn.classList.add('hidden');
                    }
                    if (captureBtn) {
                        captureBtn.classList.remove('hidden');
                    }
                    if (closeBtn) {
                        closeBtn.classList.remove('hidden');
                    }
                } catch (err) {
                    setError('Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan.');
                }
            };

            const capturePhoto = () => {
                setError('');
                if (!input) {
                    setError('Input lampiran tidak ditemukan.');
                    return;
                }
                if (!video || !canvas) {
                    setError('Kamera belum siap.');
                    return;
                }
                if (wrapper._collectedFiles.length >= maxFiles) {
                    setError(`Maksimal ${maxFiles} gambar.`);
                    return;
                }
                const width = video.videoWidth || 1280;
                const height = video.videoHeight || 720;
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    setError('Gagal mengambil gambar.');
                    return;
                }
                ctx.drawImage(video, 0, 0, width, height);
                canvas.toBlob((blob) => {
                    if (!blob) {
                        setError('Gagal menyimpan gambar.');
                        return;
                    }
                    const fileName = `camera-${Date.now()}.jpg`;
                    const file = new File([blob], fileName, { type: 'image/jpeg' });
                    wrapper._collectedFiles.push(file);
                    syncToInput();
                    renderPreview();
                }, 'image/jpeg', 0.9);
            };

            if (input) {
                input.addEventListener('change', normalizeFiles);
            }
            if (openBtn) {
                openBtn.addEventListener('click', openCamera);
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', stopCamera);
            }
            if (captureBtn) {
                captureBtn.addEventListener('click', capturePhoto);
            }
            wrapper._dragCounter = 0;
            wrapper.addEventListener('dragenter', (event) => {
                event.preventDefault();
                wrapper._dragCounter += 1;
                setHighlight(true);
            });
            wrapper.addEventListener('dragover', (event) => {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy';
                setHighlight(true);
            });
            wrapper.addEventListener('dragleave', (event) => {
                event.preventDefault();
                wrapper._dragCounter = Math.max(0, wrapper._dragCounter - 1);
                if (wrapper._dragCounter === 0) {
                    setHighlight(false);
                }
            });
            wrapper.addEventListener('drop', (event) => {
                event.preventDefault();
                wrapper._dragCounter = 0;
                setHighlight(false);
                const files = Array.from(event.dataTransfer?.files || []);
                appendFiles(files);
            });
            wrapper._stopCamera = stopCamera;
            renderPreview();
        }

        function initCameraCaptures() {
            document.querySelectorAll('[data-camera-capture]').forEach(setupCameraCapture);
        }

        document.addEventListener('DOMContentLoaded', initCameraCaptures);
        window.addEventListener('close-modal', () => {
            document.querySelectorAll('[data-camera-capture]').forEach((wrapper) => {
                if (wrapper._stopCamera) {
                    wrapper._stopCamera();
                }
            });
        });
    </script>
</x-app-layout>
