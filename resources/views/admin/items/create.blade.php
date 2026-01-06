<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Header Section dengan Design Gradient --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                        {{ __('Tambah Item Baru') }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Tambahkan item baru ke dalam master data barang sistem.</p>
                </div>
                <a href="{{ route('admin.items.index') }}" 
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Master Data
                </a>
            </div>

            {{-- Info Box dengan Desain Modern --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0 text-blue-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 font-medium">
                            Item yang ditambahkan akan masuk ke master data dan bisa digunakan oleh semua gudang.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Error Messages --}}
            @if($errors->any())
                <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-r-xl shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0 text-red-400">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-red-800 uppercase tracking-wide">Validasi Gagal ({{ $errors->count() }} Error)</h3>
                            <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Form Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/50 overflow-hidden">
                <div class="p-8">
                    <form method="POST" action="{{ route('admin.items.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Nama Item --}}
                            <div class="space-y-2">
                                <x-input-label for="nama" :value="'Nama Item *'" class="text-gray-700 font-semibold" />
                                <x-text-input id="nama" name="nama" type="text" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" :value="old('nama')" required placeholder="Masukkan nama item..." />
                                <x-input-error :messages="$errors->get('nama')" />
                            </div>

                            {{-- Kode Item (Opsional) --}}
                            <div class="space-y-2">
                                <x-input-label for="kode" :value="'Kode Item (Opsional)'" class="text-gray-700 font-semibold" />
                                <x-text-input id="kode" name="kode" type="text" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" :value="old('kode')" placeholder="Contoh: KBL-001" />
                                <p class="text-[10px] text-gray-400 italic">Jika tidak diisi, kode akan dibuat otomatis oleh sistem sesuai kategori barang.</p>
                                <x-input-error :messages="$errors->get('kode')" />
                            </div>

                            {{-- Kategori (Combobox) --}}
                            <div class="space-y-2" x-data="{
                                open: false,
                                search: '{{ old('kategori') }}',
                                options: @js($items->pluck('kategori')->unique()->filter()->values()),
                                get filtered() {
                                    if (!this.search) return this.options;
                                    return this.options.filter(opt => opt.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                select(value) {
                                    this.search = value;
                                    this.open = false;
                                }
                            }">
                                <x-input-label for="kategori" :value="'Kategori *'" class="text-gray-700 font-semibold" />
                                <div class="relative" @click.outside="open = false">
                                    <input type="text"
                                           id="kategori"
                                           name="kategori"
                                           x-model="search"
                                           @focus="open = true"
                                           @click="open = true"
                                           @input="open = true"
                                           @keydown.escape="open = false"
                                           @keydown.tab="open = false"
                                           autocomplete="off"
                                           class="block w-full border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl shadow-sm bg-gray-50/50 transition-all"
                                           placeholder="Ketik atau pilih kategori..."
                                           required />
                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open && filtered.length > 0"
                                         x-transition
                                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="option in filtered" :key="option">
                                            <div @click="select(option)"
                                                 class="px-4 py-2 cursor-pointer hover:bg-cyan-50 text-sm text-gray-700 hover:text-cyan-700"
                                                 x-text="option"></div>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 italic">Ketik untuk mencari atau membuat kategori baru</p>
                                <x-input-error :messages="$errors->get('kategori')" />
                            </div>

                            {{-- Satuan (Combobox) --}}
                            <div class="space-y-2" x-data="{
                                open: false,
                                search: '{{ old('satuan') }}',
                                options: @js($items->pluck('satuan')->unique()->filter()->values()),
                                get filtered() {
                                    if (!this.search) return this.options;
                                    return this.options.filter(opt => opt.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                select(value) {
                                    this.search = value;
                                    this.open = false;
                                }
                            }">
                                <x-input-label for="satuan" :value="'Satuan *'" class="text-gray-700 font-semibold" />
                                <div class="relative" @click.outside="open = false">
                                    <input type="text"
                                           id="satuan"
                                           name="satuan"
                                           x-model="search"
                                           @focus="open = true"
                                           @click="open = true"
                                           @input="open = true"
                                           @keydown.escape="open = false"
                                           @keydown.tab="open = false"
                                           autocomplete="off"
                                           class="block w-full border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl shadow-sm bg-gray-50/50 transition-all"
                                           placeholder="Ketik atau pilih satuan..."
                                           required />
                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open && filtered.length > 0"
                                         x-transition
                                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="option in filtered" :key="option">
                                            <div @click="select(option)"
                                                 class="px-4 py-2 cursor-pointer hover:bg-cyan-50 text-sm text-gray-700 hover:text-cyan-700"
                                                 x-text="option"></div>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 italic">Ketik untuk mencari atau membuat satuan baru</p>
                                <x-input-error :messages="$errors->get('satuan')" />
                            </div>

                            {{-- Deskripsi --}}
                            <div class="col-span-1 md:col-span-2 space-y-2">
                                <x-input-label for="deskripsi" :value="'Deskripsi (Opsional)'" class="text-gray-700 font-semibold" />
                                <textarea id="deskripsi" name="deskripsi" rows="3" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm transition-all placeholder-gray-400" placeholder="Deskripsi detail tentang item...">{{ old('deskripsi') }}</textarea>
                                <x-input-error :messages="$errors->get('deskripsi')" />
                            </div>

                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end mt-10 pt-6 border-t border-gray-100 gap-4">
                            <button type="button" onclick="window.location='{{ route('admin.items.index') }}'" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 text-sm font-bold text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-xl shadow-md hover:shadow-lg hover:from-blue-700 hover:to-cyan-600 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Item Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
