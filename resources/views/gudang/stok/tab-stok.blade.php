{{-- Statistics Cards --}}
<div class="grid grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
    {{-- Total Jenis Barang --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-center">
                <div class="flex-shrink-0 bg-[#035b71] rounded-md p-2 sm:p-3 mb-2 sm:mb-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="sm:ml-5 w-full sm:w-0 sm:flex-1 text-center sm:text-left">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Jenis Barang</dt>
                        <dd class="text-lg sm:text-xl font-bold text-[#035b71]">{{ $totalItems }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Sedang Dipinjam --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-center">
                <div class="flex-shrink-0 bg-[#00aff0] rounded-md p-2 sm:p-3 mb-2 sm:mb-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="sm:ml-5 w-full sm:w-0 sm:flex-1 text-center sm:text-left">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Sedang Dipinjam</dt>
                        <dd class="text-lg sm:text-xl font-bold text-gray-900">{{ number_format($totalBorrowed) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Stok Rendah --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-center">
                <div class="flex-shrink-0 bg-amber-500 rounded-md p-2 sm:p-3 mb-2 sm:mb-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="sm:ml-5 w-full sm:w-0 sm:flex-1 text-center sm:text-left">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Stok Rendah</dt>
                        <dd class="text-lg sm:text-xl font-bold text-gray-900">{{ $lowStockCount }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter & Sort Section --}}
@php
    $activeFilters = collect(['search', 'kategori', 'tipe'])->filter(fn($f) => !empty(request($f)))->count();
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
                <a href="{{ route('gudang.stok.index', ['tab' => 'stok']) }}"
                   class="text-sm text-gray-500 hover:text-red-600 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Hapus Filter
                </a>
            @endif
        </div>

        {{-- Quick Tipe Toggle --}}
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Tipe:</span>
            <div class="inline-flex rounded-lg border border-gray-200 p-1 bg-gray-50">
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('tipe'), ['tab' => 'stok'])) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ !request('tipe') ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Semua
                </a>
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('tipe'), ['tab' => 'stok', 'tipe' => 'mekanik'])) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ request('tipe') === 'mekanik' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Mekanik
                </a>
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('tipe'), ['tab' => 'stok', 'tipe' => 'listrik'])) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ request('tipe') === 'listrik' ? 'bg-white text-[#035b71] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Listrik
                </a>
            </div>
        </div>
    </div>

    {{-- Expandable Filter Panel --}}
    <div x-show="showFilter" x-collapse x-cloak>
        <div class="px-4 pb-4 border-t border-gray-100 pt-4">
            <form method="GET" action="{{ route('gudang.stok.index') }}" data-ajax-form data-ajax-target="#stok-content">
                <input type="hidden" name="tab" value="stok">
                @if(request('tipe'))
                    <input type="hidden" name="tipe" value="{{ request('tipe') }}">
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Search Input --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Cari Item</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   class="block w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition"
                                   placeholder="Nama atau kode item...">
                        </div>
                    </div>

                    {{-- Kategori Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Kategori</label>
                        <select name="kategori"
                                class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('kategori') == $category->id ? 'selected' : '' }}>
                                    {{ $category->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tipe Filter (in expanded panel) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tipe Barang</label>
                        <select name="tipe"
                                class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71] transition">
                            <option value="">Semua Tipe</option>
                            <option value="mekanik" {{ request('tipe') === 'mekanik' ? 'selected' : '' }}>Mekanik</option>
                            <option value="listrik" {{ request('tipe') === 'listrik' ? 'selected' : '' }}>Listrik</option>
                        </select>
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

{{-- Stock Table --}}
<div class="bg-white overflow-hidden shadow-sm rounded-lg sm:rounded-lg">
    {{-- Mobile Card View --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @forelse($stocks as $index => $stock)
            @php
                $isLowStock = $stock->jumlah < $stock->stok_minimum;
                $ownQty = $stock->own_qty ?? $stock->jumlah;
                $borrowedQty = $stock->borrowed_qty ?? 0;
                $statusClass = $isLowStock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
                $tipeClass = ($stock->item->tipe ?? 'mekanik') === 'listrik' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800';
            @endphp
            <a href="{{ route('gudang.stok.show', $stock->id) }}" class="block p-4 transition-colors hover:bg-[#e6f7fb] active:bg-[#cfeff7]">
                {{-- Header: Nama + Status --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 text-sm truncate">{{ $stock->item->nama }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $stock->item->kode ?? '-' }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusClass }}">
                            {{ $isLowStock ? 'RENDAH' : 'AMAN' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $tipeClass }}">
                            {{ strtoupper($stock->item->tipe ?? 'mekanik') }}
                        </span>
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <p class="text-gray-400">Kategori</p>
                        <p class="text-gray-700 truncate">{{ $stock->item->kategori?->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Satuan</p>
                        <p class="text-gray-700 truncate">{{ $stock->item->satuan?->nama ?? '-' }}</p>
                    </div>
                </div>

                {{-- Stok Info --}}
                <div class="mt-2 grid grid-cols-3 gap-2 text-xs">
                    <div>
                        <p class="text-gray-400">Stok Sendiri</p>
                        <p class="font-bold {{ $isLowStock ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($ownQty) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Pinjaman</p>
                        <p class="text-gray-700">{{ number_format($borrowedQty) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Minimum</p>
                        <p class="text-gray-700">{{ number_format($stock->stok_minimum) }}</p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="mt-2 flex items-center justify-between text-xs">
                    <button type="button"
                        x-data
                        @click.prevent.stop="$dispatch('set-edit-stock', {
                            id: {{ $stock->id }},
                            kode: '{{ $stock->item->kode }}',
                            nama: '{{ $stock->item->nama }}',
                            satuan: '{{ $stock->item->satuan?->nama ?? '-' }}',
                            kategori: '{{ $stock->item->kategori?->nama ?? '-' }}',
                            jumlah: {{ $stock->jumlah }},
                            stok_minimum: {{ $stock->stok_minimum }},
                            url: '{{ route('gudang.stok.update', $stock->id) }}'
                        })"
                        class="text-yellow-600 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
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
                <p class="mt-2 font-medium text-sm">Tidak ada data stok</p>
                <p class="text-xs">
                    @if(request('search') || request('kategori'))
                        Tidak ditemukan hasil untuk pencarian Anda.
                    @else
                        Tambahkan item baru untuk memulai.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Item</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Sendiri</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Pinjaman</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Minimum</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($stocks as $index => $stock)
                    <tr class="cursor-pointer {{ $stock->jumlah < $stock->stok_minimum ? 'bg-red-50 border-l-4 border-red-500' : '' }}"
                        data-row-link="{{ route('gudang.stok.show', $stock->id) }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $stocks->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $stock->item->nama }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($stock->item->tipe ?? 'mekanik') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($stock->item->kategori?->nama ?? '-') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $stock->item->satuan?->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $stock->jumlah < $stock->stok_minimum ? 'text-red-600' : 'text-gray-900' }}">
                            {{ number_format($stock->own_qty ?? $stock->jumlah) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($stock->borrowed_qty ?? 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($stock->stok_minimum) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($stock->jumlah < $stock->stok_minimum)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Rendah
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Aman
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    x-data
                                    @click="$dispatch('set-edit-stock', {
                                        id: {{ $stock->id }},
                                        kode: '{{ $stock->item->kode }}',
                                        nama: '{{ $stock->item->nama }}',
                                        satuan: '{{ $stock->item->satuan?->nama ?? '-' }}',
                                        kategori: '{{ $stock->item->kategori?->nama ?? '-' }}',
                                        jumlah: {{ $stock->jumlah }},
                                        stok_minimum: {{ $stock->stok_minimum }},
                                        url: '{{ route('gudang.stok.update', $stock->id) }}'
                                    })"
                                    class="text-yellow-600 hover:text-yellow-900"
                                    title="Edit Stok">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if($stock->jumlah == 0)
                                    <button type="button"
                                        x-data
                                        @click="$dispatch('open-delete-stock', '{{ route('gudang.stok.destroy', $stock->id) }}')"
                                        class="text-red-600 hover:text-red-900"
                                        title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-gray-300" title="Stok harus 0 untuk hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-10 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="mt-2 font-medium">Tidak ada data stok</p>
                            <p class="text-sm">
                                @if(request('search') || request('kategori'))
                                    Tidak ditemukan hasil untuk pencarian Anda.
                                @else
                                    Tambahkan item baru untuk memulai inventaris gudang.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($stocks->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6" data-ajax-pagination>
            {{ $stocks->links() }}
        </div>
    @endif
</div>
