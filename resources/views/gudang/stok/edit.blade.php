<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('gudang.stok.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Inventaris
                </a>
                <h2 class="text-2xl font-bold text-gray-900 mt-2">Sesuaikan Stok</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $stock->item->nama }}</p>
            </div>

            {{-- Current Stock Display --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Item</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Kode Item</p>
                            <p class="font-medium text-gray-900">{{ $stock->item->kode }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Nama Item</p>
                            <p class="font-medium text-gray-900">{{ $stock->item->nama }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Satuan</p>
                            <p class="font-medium text-gray-900">{{ $stock->item->satuan?->nama ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kategori</p>
                            <p class="font-medium text-gray-900">{{ $stock->item->kategori?->nama ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Stok Saat Ini</p>
                                <p class="text-3xl font-bold text-[#035b71]">{{ number_format($stock->jumlah) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Terakhir Diupdate</p>
                                <p class="text-sm font-medium text-gray-900">{{ $stock->updated_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Adjustment Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ adjustmentType: '{{ old('adjustment_type', 'add') }}' }">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Form Penyesuaian Stok</h3>

                    <form method="POST" action="{{ route('gudang.stok.update', $stock->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Adjustment Type --}}
                        <div>
                            <x-input-label :value="'Tipe Penyesuaian *'" class="mb-3" />
                            <div class="flex gap-6">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio"
                                           name="adjustment_type"
                                           value="add"
                                           x-model="adjustmentType"
                                           class="w-4 h-4 text-[#035b71] border-gray-300 focus:ring-[#035b71]"
                                           {{ old('adjustment_type', 'add') === 'add' ? 'checked' : '' }}>
                                    <span class="ml-2 flex items-center">
                                        <svg class="w-5 h-5 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span class="font-medium">Tambah Stok (IN)</span>
                                    </span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio"
                                           name="adjustment_type"
                                           value="subtract"
                                           x-model="adjustmentType"
                                           class="w-4 h-4 text-[#035b71] border-gray-300 focus:ring-[#035b71]"
                                           {{ old('adjustment_type') === 'subtract' ? 'checked' : '' }}>
                                    <span class="ml-2 flex items-center">
                                        <svg class="w-5 h-5 mr-1 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                        <span class="font-medium">Kurangi Stok (OUT)</span>
                                    </span>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('adjustment_type')" class="mt-2" />
                        </div>

                        {{-- Warning for Subtract --}}
                        <div x-show="adjustmentType === 'subtract'"
                             x-transition
                             class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Pastikan jumlah pengurangan tidak melebihi stok tersedia ({{ number_format($stock->jumlah) }} {{ $stock->item->satuan?->nama ?? '-' }}).
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Adjustment Quantity --}}
                        <div>
                            <x-input-label for="adjustment_quantity" :value="'Jumlah Penyesuaian *'" />
                            <x-text-input id="adjustment_quantity" name="adjustment_quantity" type="number" min="1" step="1"
                                          class="mt-1 block w-full" :value="old('adjustment_quantity')" required
                                          placeholder="Masukkan jumlah yang akan ditambah/dikurangi" />
                            <x-input-error :messages="$errors->get('adjustment_quantity')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">
                                <span x-show="adjustmentType === 'add'">Jumlah yang akan ditambahkan ke stok saat ini</span>
                                <span x-show="adjustmentType === 'subtract'">Jumlah yang akan dikurangi dari stok saat ini</span>
                            </p>
                        </div>

                        {{-- New Minimum Stock --}}
                        <div>
                            <x-input-label for="stok_minimum" :value="'Stok Minimum *'" />
                            <x-text-input id="stok_minimum" name="stok_minimum" type="number" min="0" step="1"
                                          class="mt-1 block w-full" :value="old('stok_minimum', $stock->stok_minimum)" required />
                            <x-input-error :messages="$errors->get('stok_minimum')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Anda dapat mengubah nilai stok minimum jika diperlukan</p>
                        </div>

                        {{-- Reason/Notes --}}
                        <div>
                            <x-input-label for="keterangan" :value="'Alasan Penyesuaian *'" />
                            <textarea id="keterangan" name="keterangan" rows="3" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                                      placeholder="Jelaskan alasan penyesuaian stok untuk keperluan audit...">{{ old('keterangan') }}</textarea>
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500 font-medium">Wajib diisi untuk keperluan audit trail</p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-4 pt-4">
                            <x-secondary-button type="button" onclick="window.location='{{ route('gudang.stok.index') }}'">
                                Batal
                            </x-secondary-button>
                            <x-primary-button class="bg-[#035b71] hover:bg-[#00aff0]">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Penyesuaian
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Help Text --}}
            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Panduan Penyesuaian Stok:</h3>
                <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                    <li><strong>Tambah Stok (IN):</strong> Gunakan saat menerima barang baru, pengembalian, atau koreksi kelebihan</li>
                    <li><strong>Kurangi Stok (OUT):</strong> Gunakan saat barang keluar, rusak, hilang, atau koreksi kekurangan</li>
                    <li>Setiap penyesuaian akan dicatat dalam riwayat pergerakan stok</li>
                    <li>Alasan penyesuaian wajib diisi untuk keperluan audit dan pelacakan</li>
                    <li>Stok tidak boleh bernilai negatif</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
