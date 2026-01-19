<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('gudang.stok.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Inventaris
                </a>
                <div class="flex justify-between items-start mt-2">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Detail Stok Item</h2>
                        <p class="text-sm text-gray-600 mt-1">{{ $stock->gudang->nama }}</p>
                    </div>
                    <button type="button"
                            @click="$dispatch('open-modal', 'edit-stock')"
                            class="inline-flex items-center px-4 py-2 bg-[#035b71] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0] focus:bg-[#00aff0] active:bg-[#024a5c] focus:outline-none focus:ring-2 focus:ring-[#035b71] focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Stok
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Item Details Card --}}
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Item</h3>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Kode Item</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $stock->item->kode }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Nama Item</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $stock->item->nama }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Kategori</p>
                                    <p class="font-medium text-gray-900">{{ $stock->item->kategori?->nama ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Satuan</p>
                                    <p class="font-medium text-gray-900">{{ $stock->item->satuan?->nama ?? '-' }}</p>
                                </div>
                            </div>

                            @if($stock->item->deskripsi)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <p class="text-sm text-gray-600 mb-1">Deskripsi</p>
                                    <p class="text-gray-900">{{ $stock->item->deskripsi }}</p>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                {{-- Stock Information Card --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Stok</h3>

                            {{-- Current Stock --}}
                            <div class="mb-6">
                                <p class="text-sm text-gray-600 mb-2">Stok Sendiri</p>
                                <p class="text-4xl font-bold text-[#035b71]">
                                    {{ number_format($stock->own_qty ?? $stock->jumlah) }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">{{ $stock->item->satuan?->nama ?? '-' }}</p>
                            </div>

                            <div class="mb-6">
                                <p class="text-sm text-gray-600 mb-2">Stok Pinjaman</p>
                                <p class="text-2xl font-bold text-gray-700">
                                    {{ number_format($stock->borrowed_qty ?? 0) }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">{{ $stock->item->satuan?->nama ?? '-' }}</p>
                            </div>

                            {{-- Minimum Stock --}}
                            <div class="mb-6">
                                <p class="text-sm text-gray-600 mb-2">Stok Minimum</p>
                                <p class="text-2xl font-bold text-gray-700">
                                    {{ number_format($stock->stok_minimum) }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">{{ $stock->item->satuan?->nama ?? '-' }}</p>
                            </div>

                            {{-- Status Badge --}}
                            <div class="mb-6">
                                <p class="text-sm text-gray-600 mb-2">Status</p>
                                @if($stock->jumlah < $stock->stok_minimum)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Stok Rendah
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Stok Aman
                                    </span>
                                @endif
                            </div>

                            {{-- Last Updated --}}
                            <div class="pt-6 border-t border-gray-200">
                                <p class="text-sm text-gray-600 mb-1">Terakhir Diupdate</p>
                                <p class="font-medium text-gray-900">{{ $stock->updated_at->format('d M Y') }}</p>
                                <p class="text-sm text-gray-500">{{ $stock->updated_at->format('H:i') }} WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Movement History --}}
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Pergerakan Stok</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
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
                                        <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <p class="mt-2 font-medium">Belum ada riwayat pergerakan stok</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($movements->hasPages())
                        <div class="mt-4">
                            {{ $movements->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Stock Modal --}}
    <x-modal name="edit-stock" focusable>
        <div class="p-6" x-data="{ adjustmentType: 'add', adjustmentQuantity: null, reason: '' }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Sesuaikan Stok</h2>
                    <p class="text-sm text-gray-600">{{ $stock->item->nama }}</p>
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
                        <p class="font-medium text-gray-900">{{ $stock->item->kode }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Satuan</p>
                        <p class="font-medium text-gray-900">{{ $stock->item->satuan?->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Kategori</p>
                        <p class="font-medium text-gray-900">{{ $stock->item->kategori?->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Stok Saat Ini</p>
                        <p class="font-bold text-[#035b71] text-lg">{{ number_format($stock->jumlah) }}</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('gudang.stok.update', $stock->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Minimum Stock --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stok Minimum *</label>
                    <input type="number" name="stok_minimum" min="0" required value="{{ $stock->stok_minimum }}"
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
                        Pastikan jumlah pengurangan tidak melebihi stok tersedia ({{ number_format($stock->jumlah) }} {{ $stock->item->satuan?->nama ?? '-' }}).
                    </p>
                </div>

                {{-- Adjustment Quantity --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah Penyesuaian</label>
                    <input type="number" name="adjustment_quantity" min="1"
                           x-model.number="adjustmentQuantity"
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
                              x-model="reason"
                              x-bind:required="adjustmentQuantity && adjustmentQuantity > 0"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                              placeholder="Wajib diisi jika mengubah jumlah stok..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Diperlukan untuk audit jika ada perubahan jumlah</p>
                    <p class="mt-2 text-xs text-red-600" x-show="adjustmentQuantity && adjustmentQuantity > 0 && !reason">
                        Alasan wajib diisi jika mengubah jumlah stok.
                    </p>
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
