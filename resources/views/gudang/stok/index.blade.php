<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)"
                     class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Inventaris Gudang</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ Auth::user()->gudang->nama ?? 'Gudang Saya' }}
                            </p>
                        </div>
                        <button type="button"
                            @click="$dispatch('open-modal', 'create-stock')"
                            class="inline-flex items-center px-4 py-2 bg-[#035b71] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0] focus:bg-[#00aff0] active:bg-[#024a5c] focus:outline-none focus:ring-2 focus:ring-[#035b71] focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Item Baru
                        </button>
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Total Items --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-[#035b71] rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Item</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ $totalItems }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Stock Units --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-[#00aff0] rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Unit Stok</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ number_format($totalUnits) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Low Stock Alerts --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 {{ $lowStockCount > 0 ? 'bg-red-500' : 'bg-green-500' }} rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Stok Rendah</dt>
                                    <dd class="text-lg font-bold {{ $lowStockCount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $lowStockCount }}
                                        @if($lowStockCount === 0)
                                            <span class="text-xs text-gray-500">Semua aman</span>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search and Filter --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('gudang.stok.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Item</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Nama atau kode item..."
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select name="kategori"
                                    id="kategori"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('kategori') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 bg-[#035b71] hover:bg-[#00aff0] text-white font-medium py-2 px-4 rounded-md transition duration-150">
                                Cari
                            </button>
                            @if(request('search') || request('kategori'))
                                <a href="{{ route('gudang.stok.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Stock Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Item</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Item</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Minimum</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($stocks as $index => $stock)
                                <tr class="{{ $stock->jumlah < $stock->stok_minimum ? 'bg-red-50 border-l-4 border-red-500' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $stocks->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $stock->item->kode }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $stock->item->nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stock->item->satuan }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $stock->jumlah < $stock->stok_minimum ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ number_format($stock->jumlah) }}
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
                                            <a href="{{ route('gudang.stok.show', $stock->id) }}"
                                               class="text-[#035b71] hover:text-[#00aff0]"
                                               title="Lihat Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <button type="button"
                                                x-data
                                                @click="$dispatch('set-edit-stock', {
                                                    id: {{ $stock->id }},
                                                    kode: '{{ $stock->item->kode }}',
                                                    nama: '{{ $stock->item->nama }}',
                                                    satuan: '{{ $stock->item->satuan }}',
                                                    kategori: '{{ $stock->item->kategori ?? '-' }}',
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
                                                    @click="$dispatch('open-modal', 'confirm-delete'); window.deleteStockUrl = '{{ route('gudang.stok.destroy', $stock->id) }}'"
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
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">
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
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $stocks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="confirm-delete" focusable>
        <div class="p-6" x-data>
            <h2 class="text-lg font-bold text-gray-900">Hapus Item dari Inventaris?</h2>
            <p class="mt-2 text-sm text-gray-600">
                Item ini akan dihapus dari inventaris gudang Anda. Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                        x-on:click="$dispatch('close-modal', 'confirm-delete')">
                    Batal
                </button>
                <form x-bind:action="window.deleteStockUrl" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </x-modal>

    {{-- Create Stock Modal --}}
    <x-modal name="create-stock" focusable>
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Tambah Item Baru ke Gudang</h2>
                    <p class="text-sm text-gray-600">{{ Auth::user()->gudang->nama ?? 'Gudang Saya' }}</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                        x-on:click="$dispatch('close-modal', 'create-stock')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4 rounded-r">
                <p class="text-sm text-blue-700">
                    Item yang ditambahkan akan langsung masuk ke inventaris gudang dengan jumlah stok yang diisi.
                </p>
            </div>

            <form method="POST" action="{{ route('gudang.stok.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="item_id" class="block text-sm font-medium text-gray-700">Pilih Item *</label>
                    <select name="item_id" id="item_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                        <option value="">-- Pilih Item --</option>
                        @foreach($availableItems as $item)
                            <option value="{{ $item->id }}">{{ $item->kode }} - {{ $item->nama }}</option>
                        @endforeach
                    </select>
                    @if($availableItems->isEmpty())
                        <p class="mt-2 text-sm text-yellow-600">Semua item sudah ada di gudang Anda.</p>
                    @endif
                </div>

                <div>
                    <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah Stok Awal *</label>
                    <input type="number" name="jumlah" id="jumlah" min="0" value="0" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                           placeholder="Masukkan jumlah stok awal">
                    <p class="mt-1 text-xs text-gray-500">Jumlah item yang akan ditambahkan ke gudang</p>
                </div>

                <div>
                    <label for="stok_minimum" class="block text-sm font-medium text-gray-700">Stok Minimum *</label>
                    <input type="number" name="stok_minimum" id="stok_minimum" min="0" value="0" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                           placeholder="Masukkan stok minimum">
                    <p class="mt-1 text-xs text-gray-500">Peringatan akan muncul jika stok di bawah nilai ini</p>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan (Opsional)</label>
                    <textarea name="keterangan" id="keterangan" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                              placeholder="Catatan tambahan..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                            x-on:click="$dispatch('close-modal', 'create-stock')">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-[#035b71] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0]">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Edit Stock Modal --}}
    <x-modal name="edit-stock" focusable>
        <div class="p-6" x-data="{
            adjustmentType: 'add',
            stock: { kode: '', nama: '', satuan: '', kategori: '-', jumlah: 0, stok_minimum: 0, url: '' }
        }" @set-edit-stock.window="stock = $event.detail; adjustmentType = 'add'; $dispatch('open-modal', 'edit-stock')">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Sesuaikan Stok</h2>
                    <p class="text-sm text-gray-600" x-text="stock.nama"></p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                        x-on:click="$dispatch('close-modal', 'edit-stock')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Item Info --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-gray-500">Kode Item</p>
                        <p class="font-medium text-gray-900" x-text="stock.kode || '-'"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Satuan</p>
                        <p class="font-medium text-gray-900" x-text="stock.satuan || '-'"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Kategori</p>
                        <p class="font-medium text-gray-900" x-text="stock.kategori || '-'"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Stok Saat Ini</p>
                        <p class="font-bold text-[#035b71] text-lg" x-text="stock.jumlah"></p>
                    </div>
                </div>
            </div>

            <form method="POST" x-bind:action="stock.url" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Minimum Stock --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stok Minimum *</label>
                    <input type="number" name="stok_minimum" min="0" required
                           x-bind:value="stock.stok_minimum"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                    <p class="mt-1 text-xs text-gray-500">Batas minimum stok untuk peringatan</p>
                </div>

                {{-- Divider --}}
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-sm font-medium text-gray-700 mb-3">Penyesuaian Jumlah Stok <span class="text-gray-400 font-normal">(Opsional)</span></p>
                </div>

                {{-- Adjustment Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Penyesuaian</label>
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="adjustment_type" value="add" x-model="adjustmentType"
                                   class="w-4 h-4 text-[#035b71] border-gray-300 focus:ring-[#035b71]">
                            <span class="ml-2 flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah (IN)
                            </span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="adjustment_type" value="subtract" x-model="adjustmentType"
                                   class="w-4 h-4 text-[#035b71] border-gray-300 focus:ring-[#035b71]">
                            <span class="ml-2 flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                                Kurangi (OUT)
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Warning for Subtract --}}
                <div x-show="adjustmentType === 'subtract'" x-transition
                     class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-r">
                    <p class="text-sm text-yellow-700">
                        Pastikan jumlah pengurangan tidak melebihi stok tersedia (<span x-text="stock.jumlah"></span> <span x-text="stock.satuan"></span>).
                    </p>
                </div>

                {{-- Adjustment Quantity --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah Penyesuaian</label>
                    <input type="number" name="adjustment_quantity" min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                           placeholder="Kosongkan jika tidak mengubah jumlah stok">
                    <p class="mt-1 text-xs text-gray-500">
                        <span x-show="adjustmentType === 'add'">Jumlah yang akan ditambahkan ke stok</span>
                        <span x-show="adjustmentType === 'subtract'">Jumlah yang akan dikurangi dari stok</span>
                    </p>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Alasan Penyesuaian</label>
                    <textarea name="keterangan" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                              placeholder="Wajib diisi jika mengubah jumlah stok..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Diperlukan untuk audit jika ada perubahan jumlah</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                            x-on:click="$dispatch('close-modal', 'edit-stock')">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-[#035b71] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0]">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
