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
                <h2 class="text-2xl font-bold text-gray-900 mt-2">Tambah Item Baru ke Gudang</h2>
                <p class="text-sm text-gray-600 mt-1">{{ Auth::user()->gudang->nama ?? 'Gudang Saya' }}</p>
            </div>

            {{-- Error Messages --}}
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Ada {{ $errors->count() }} error dalam form:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Success/Error Flash Messages --}}
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Info Box --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Item yang ditambahkan akan langsung masuk ke inventaris gudang Anda dengan jumlah stok yang diisi.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('gudang.stok.store') }}" class="space-y-6">
                        @csrf

                        {{-- Item Selection --}}
                        <div>
                            <x-input-label for="item_id" :value="'Pilih Item *'" />
                            <select name="item_id" id="item_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                                <option value="">-- Pilih Item --</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->kode }} - {{ $item->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('item_id')" class="mt-2" />
                            @if($availableItems->isEmpty())
                                <p class="mt-2 text-sm text-yellow-600">
                                    Semua item sudah ada di gudang Anda. Tidak ada item baru yang dapat ditambahkan.
                                </p>
                            @endif
                        </div>

                        {{-- Initial Quantity --}}
                        <div>
                            <x-input-label for="jumlah" :value="'Jumlah Stok Awal *'" />
                            <x-text-input id="jumlah" name="jumlah" type="number" min="0" step="1"
                                          class="mt-1 block w-full" :value="old('jumlah', 0)" required
                                          placeholder="Masukkan jumlah stok awal" />
                            <x-input-error :messages="$errors->get('jumlah')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Jumlah item yang akan ditambahkan ke gudang</p>
                        </div>

                        {{-- Minimum Stock --}}
                        <div>
                            <x-input-label for="stok_minimum" :value="'Stok Minimum *'" />
                            <x-text-input id="stok_minimum" name="stok_minimum" type="number" min="0" step="1"
                                          class="mt-1 block w-full" :value="old('stok_minimum', 0)" required
                                          placeholder="Masukkan stok minimum" />
                            <x-input-error :messages="$errors->get('stok_minimum')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Peringatan akan muncul jika stok di bawah nilai ini</p>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <x-input-label for="keterangan" :value="'Keterangan (Opsional)'" />
                            <textarea id="keterangan" name="keterangan" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                                      placeholder="Catatan atau keterangan tambahan...">{{ old('keterangan') }}</textarea>
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
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
                                Simpan
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Help Text --}}
            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Tips:</h3>
                <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                    <li>Pastikan item yang dipilih benar sesuai dengan barang fisik yang akan dimasukkan</li>
                    <li>Jumlah stok awal adalah jumlah barang yang ada saat ini di gudang</li>
                    <li>Stok minimum digunakan sebagai ambang batas peringatan stok rendah</li>
                    <li>Setiap penambahan item akan tercatat dalam riwayat pergerakan stok</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
