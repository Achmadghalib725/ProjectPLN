<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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

            @if(session('error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-auto bg-red-500 text-white px-4 sm:px-6 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2 text-sm sm:text-base">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 sm:mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
                    <p class="font-semibold">Periksa input:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        @foreach($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Header Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4">
                        <div class="text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Barang</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                {{ Auth::user()->gudang->nama ?? 'Gudang Saya' }}
                            </p>
                        </div>
                        @if($tab === 'stok')
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button type="button"
                                @click="$dispatch('open-modal', 'create-item')"
                                class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-white border border-gray-300 rounded-lg sm:rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 active:scale-95 focus:outline-none transition ease-in-out duration-150">
                                Buat Item Baru
                            </button>
                            <button type="button"
                                @click="$dispatch('open-modal', 'create-stock')"
                                class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-[#035b71] border border-transparent rounded-lg sm:rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0] active:scale-95 focus:outline-none transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Item
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Tab Navigation --}}
                <div class="border-t border-gray-200">
                    <nav class="flex" data-ajax-tabs>
                        <a href="{{ route('gudang.stok.index', ['tab' => 'stok']) }}"
                           data-ajax-tab
                           data-ajax-target="#stok-content"
                           class="flex-1 py-4 px-4 border-b-2 font-medium text-sm text-center transition-colors
                               {{ $tab === 'stok' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span>Kelola Stok</span>
                            </div>
                        </a>
                        <a href="{{ route('gudang.stok.index', ['tab' => 'dipinjamkan']) }}"
                           data-ajax-tab
                           data-ajax-target="#stok-content"
                           class="flex-1 py-4 px-4 border-b-2 font-medium text-sm text-center transition-colors
                               {{ $tab === 'dipinjamkan' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span>Dipinjamkan</span>
                            </div>
                        </a>
                        <a href="{{ route('gudang.stok.index', ['tab' => 'pinjaman']) }}"
                           data-ajax-tab
                           data-ajax-target="#stok-content"
                           class="flex-1 py-4 px-4 border-b-2 font-medium text-sm text-center transition-colors
                               {{ $tab === 'pinjaman' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span>Pinjaman</span>
                            </div>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Tab Content --}}
            <div id="stok-content" data-ajax-container>
                @if($tab === 'stok')
                    {{-- KELOLA STOK TAB --}}
                    @include('gudang.stok.tab-stok')
                @elseif($tab === 'dipinjamkan')
                    {{-- DIPINJAMKAN TAB --}}
                    @include('gudang.stok.tab-dipinjamkan')
                @elseif($tab === 'pinjaman')
                    {{-- PINJAMAN TAB --}}
                    @include('gudang.stok.tab-pinjaman')
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

    {{-- Create Item Modal --}}
    <x-modal name="create-item" focusable>
        <div class="p-6" x-data="{
            search: @js(old('search_term', '')),
            items: @js($allItems),
            availableIds: @js($availableItems->pluck('id')->values()),
            selectionError: '',
            get normalizedSearch() {
                return this.search.trim().toLowerCase();
            },
            get results() {
                const term = this.normalizedSearch;
                if (!term) return [];
                return this.items.filter(item => {
                    const name = (item.nama || '').toLowerCase();
                    const code = (item.kode || '').toLowerCase();
                    return name.includes(term) || code.includes(term);
                }).slice(0, 15);
            },
            get hasSearch() {
                return this.normalizedSearch.length > 0;
            },
            get allowCreate() {
                return this.hasSearch && this.results.length === 0;
            },
            selectExisting(item) {
                this.selectionError = '';
                if (!this.availableIds.includes(item.id)) {
                    this.selectionError = 'Item ini sudah ada di gudang Anda.';
                    return;
                }

                const select = document.getElementById('item_id');
                if (select) {
                    select.value = item.id;
                    select.dispatchEvent(new Event('change'));
                }

                this.$dispatch('close-modal', 'create-item');
                this.$dispatch('open-modal', 'create-stock');
            }
        }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Tambah Item Baru</h2>
                    <p class="text-sm text-gray-600">Wajib cari item terlebih dahulu</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                        x-on:click="$dispatch('close-modal', 'create-item')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="border border-gray-200 p-3 mb-4 rounded-lg">
                <p class="text-sm text-gray-700">
                    Cari item terlebih dahulu untuk menghindari duplikasi. Jika belum ada, form tambah item akan muncul.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="item_search" class="block text-sm font-medium text-gray-700">Cari Item *</label>
                    <input type="text" id="item_search" x-model="search"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                           placeholder="Nama atau kode item">
                </div>

                <div x-show="hasSearch && results.length > 0" class="border border-gray-200 rounded-lg divide-y">
                    <div class="px-4 py-2 text-xs text-gray-600 font-medium">
                        Item dengan nama/kode mirip sudah ada. Klik item untuk menambahkan stok.
                    </div>
                    <template x-for="item in results" :key="item.id">
                        <button type="button"
                                @click="selectExisting(item)"
                                class="px-4 py-3 text-left text-sm text-gray-700 transition hover:bg-gray-50"
                                :class="availableIds.includes(item.id) ? 'cursor-pointer' : 'opacity-60 cursor-not-allowed'"
                                :disabled="!availableIds.includes(item.id)">
                            <div class="font-semibold" x-text="item.nama"></div>
                            <div class="text-xs text-gray-500" x-text="`${item.kode || '-'} • ${item.kategori || '-'} • ${item.satuan || '-'}`"></div>
                            <div class="text-xs text-gray-500 mt-1" x-show="!availableIds.includes(item.id)">
                                Sudah ada di gudang
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="selectionError" class="text-xs text-red-600" x-text="selectionError"></div>

                <div x-show="hasSearch && results.length === 0" class="border border-gray-200 p-3 rounded-lg">
                    <p class="text-sm text-gray-700 font-medium">
                        Tidak ada hasil yang cocok. Anda bisa menambah item baru.
                    </p>
                </div>

                <div x-show="!hasSearch" class="border border-gray-200 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        Mulai ketik nama atau kode untuk melihat item yang sudah ada.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('gudang.items.store') }}" x-show="allowCreate" class="space-y-6 mt-6">
                @csrf
                <input type="hidden" name="search_term" x-bind:value="search">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <x-input-label for="nama" :value="'Nama Item *'" class="text-gray-700 font-semibold" />
                        <x-text-input id="nama" name="nama" type="text"
                            class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all"
                            :value="old('nama')" required placeholder="Masukkan nama item..." />
                        <x-input-error :messages="$errors->get('nama')" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="kode" :value="'Kode Item (Opsional)'" class="text-gray-700 font-semibold" />
                        <x-text-input id="kode" name="kode" type="text"
                            class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all"
                            :value="old('kode')" placeholder="Contoh: KBL-001" />
                        <p class="text-[10px] text-gray-400 italic">Jika tidak diisi, kode akan dibuat otomatis oleh sistem sesuai kategori barang.</p>
                        <x-input-error :messages="$errors->get('kode')" />
                    </div>

                    <div class="space-y-2" x-data="{
                        open: false,
                        search: '{{ old('kategori') }}',
                        options: @js($allItems->pluck('kategori')->unique()->filter()->values()),
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

                    <div class="space-y-2" x-data="{
                        open: false,
                        search: '{{ old('satuan') }}',
                        options: @js($allItems->pluck('satuan')->unique()->filter()->values()),
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

                    <div class="col-span-1 md:col-span-2 space-y-2">
                        <x-input-label for="deskripsi" :value="'Deskripsi (Opsional)'" class="text-gray-700 font-semibold" />
                        <textarea id="deskripsi" name="deskripsi" rows="3"
                            class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm transition-all placeholder-gray-400"
                            placeholder="Deskripsi detail tentang item...">{{ old('deskripsi') }}</textarea>
                        <x-input-error :messages="$errors->get('deskripsi')" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                            x-on:click="$dispatch('close-modal', 'create-item')">
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
                            <option value="{{ $item->id }}">{{ $item->nama }} ({{ ucfirst($item->kategori) }})</option>
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
            adjustmentQuantity: null,
            reason: '',
            stock: { kode: '', nama: '', satuan: '', kategori: '-', jumlah: 0, stok_minimum: 0, url: '' }
        }" @set-edit-stock.window="stock = $event.detail; adjustmentType = 'add'; adjustmentQuantity = null; reason = ''; $dispatch('open-modal', 'edit-stock')">
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
