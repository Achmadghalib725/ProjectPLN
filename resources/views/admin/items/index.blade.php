<x-app-layout>
        <div class="py-4 sm:py-8 lg:py-12"
         x-data="{
            showModal: {{ $errors->any() ? 'true' : 'false' }},
            showDetail: false,
            isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
            actionUrl: '{{ old('_method') === 'PUT' ? (old('id') ? url('admin/items').'/'.old('id') : '') : route('admin.items.store') }}',
            form: {
                id: @json(old('id')),
                kode: @json(old('kode')),
                nama: @json(old('nama')),
                kategori: @json(old('kategori')),
                satuan: @json(old('satuan')),
                deskripsi: @json(old('deskripsi')),
            },
            searchTerm: @json(old('search_term')),
            allItems: @js($allItems),
            detail: {
                kode: '',
                nama: '',
                kategori: '',
                satuan: '',
                deskripsi: '',
            },
            get normalizedSearch() {
                return (this.searchTerm || '').trim().toLowerCase();
            },
            get hasSearch() {
                return this.normalizedSearch.length > 0;
            },
            get searchResults() {
                const term = this.normalizedSearch;
                if (!term) return [];
                return this.allItems.filter(item => {
                    const name = (item.nama || '').toLowerCase();
                    const code = (item.kode || '').toLowerCase();
                    return name.includes(term) || code.includes(term);
                }).slice(0, 15);
            },
            get allowCreate() {
                return this.hasSearch && this.searchResults.length === 0;
            },
            openCreate() {
                this.isEdit = false;
                this.form = { id: '', kode: '', nama: '', kategori: '', satuan: '', deskripsi: '' };
                this.searchTerm = '';
                this.actionUrl = '{{ route('admin.items.store') }}';
                this.showDetail = false;
                this.showModal = true;
            },
            openEdit(item) {
                this.isEdit = true;
                this.form = { ...item };
                this.searchTerm = '';
                this.actionUrl = '{{ url('admin/items') }}/' + item.id;
                this.showDetail = false;
                this.showModal = true;
            },
            openDetail(item) {
                this.detail = {
                    kode: item.kode || '-',
                    nama: item.nama || '-',
                    kategori: item.kategori || '-',
                    satuan: item.satuan || '-',
                    deskripsi: item.deskripsi || '',
                };
                this.showModal = false;
                this.showDetail = true;
            },
            selectExisting(item) {
                this.openEdit(item);
            }
         }"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            {{-- Header Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4">
                        <div class="text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Kelola Master Barang</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Daftar semua jenis barang yang terdaftar dalam sistem.</p>
                        </div>
                        <button @click="openCreate()"
                           class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-[#035b71] border border-transparent rounded-lg sm:rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0] active:scale-95 focus:outline-none transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Tambah Barang Baru
                        </button>
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 gap-4 sm:gap-6">
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
                                    <dd class="text-lg sm:text-xl font-bold text-[#035b71]">{{ $items->total() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row items-center sm:items-center">
                            <div class="flex-shrink-0 bg-[#00aff0] rounded-md p-2 sm:p-3 mb-2 sm:mb-0">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <div class="sm:ml-5 w-full sm:w-0 sm:flex-1 text-center sm:text-left">
                                <dl>
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Kategori</dt>
                                    <dd class="text-lg sm:text-xl font-bold text-gray-900">{{ $items->pluck('kategori')->unique()->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Card --}}
            <div id="items-table" class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg border border-gray-200" data-ajax-container>

                {{-- Toolbar (Filter & Search) --}}
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('admin.items.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                        {{-- Search --}}
                        <div class="relative w-full md:w-96 group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-[#035b71] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-md focus:ring-[#035b71] focus:border-[#035b71] transition-all"
                                placeholder="Cari nama barang...">
                        </div>

                        {{-- Filter Kategori --}}
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <select name="kategori" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#035b71] focus:border-[#035b71] block w-full md:w-48 p-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                                <option value="">Semua Kategori</option>
                                @foreach($items->pluck('kategori')->unique()->filter() as $cat)
                                    <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>

                            @if(request('search') || request('kategori'))
                                <a href="{{ route('admin.items.index') }}" class="text-sm text-red-500 hover:text-red-700 font-medium whitespace-nowrap px-3 py-2 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Informasi Barang</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($items as $item)
                            <tr class="bg-white hover:bg-cyan-50/30 transition-colors duration-200 group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900 group-hover:text-cyan-700 transition-colors">{{ $item->nama }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ucfirst($item->kategori ?? '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                    {{ $item->satuan }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-3 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button @click='openDetail(@json($item))' class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-full transition-all duration-200" title="Lihat Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button @click='openEdit(@json($item))' class="text-yellow-600 hover:text-yellow-900 p-2 hover:bg-yellow-50 rounded-full transition-all duration-200" title="Edit Barang">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button type="button"
                                            @click="$dispatch('open-delete-modal', {
                                                title: 'Hapus Barang',
                                                message: 'Apakah Anda yakin ingin menghapus barang {{ $item->nama }}? Data tidak dapat dikembalikan.',
                                                action: '{{ route('admin.items.destroy', $item->id) }}'
                                            })"
                                            class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-full transition-all duration-200" title="Hapus Barang">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        <p class="text-base font-medium">Belum ada data barang ditemukan.</p>
                                        <p class="text-sm">Coba ubah filter pencarian atau tambah barang baru.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6" data-ajax-pagination>
                    {{ $items->links() }}
                </div>
            </div>
        </div>

        {{-- Modal Create/Edit Item --}}
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                {{-- Backdrop --}}
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                            <span x-text="isEdit ? 'Edit Barang' : 'Tambah Barang Baru'"></span>
                        </h3>

                        <div x-show="!isEdit" class="space-y-4">
                            <div class="border border-gray-200 p-3 rounded-lg">
                                <p class="text-sm text-gray-700">
                                    Cari item terlebih dahulu untuk menghindari duplikasi. Jika belum ada, form tambah item akan muncul.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cari Item *</label>
                                <input type="text" x-model="searchTerm"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                                    placeholder="Nama atau kode item">
                                @error('search_term') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div x-show="hasSearch && searchResults.length > 0" class="border border-gray-200 rounded-lg divide-y">
                                <div class="px-4 py-2 text-xs text-gray-600 font-medium">
                                    Item dengan nama/kode mirip sudah ada. Klik item untuk membuka data.
                                </div>
                                <template x-for="item in searchResults" :key="item.id">
                                    <button type="button"
                                            @click="selectExisting(item)"
                                            class="px-4 py-3 text-left text-sm text-gray-700 transition hover:bg-gray-50">
                                        <div class="font-semibold" x-text="item.nama"></div>
                                        <div class="text-xs text-gray-500" x-text="`${item.kode || '-'} • ${item.kategori || '-'} • ${item.satuan || '-'}`"></div>
                                    </button>
                                </template>
                            </div>

                            <div x-show="hasSearch && searchResults.length === 0" class="border border-gray-200 p-3 rounded-lg">
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

                        <form :action="actionUrl" method="POST" class="mt-4 space-y-4" x-show="isEdit || allowCreate" x-cloak>
                            @csrf
                            {{-- Method Spoofing for PUT (only when editing) --}}
                            <template x-if="isEdit">
                                <input type="hidden" name="_method" value="PUT">
                            </template>
                            <template x-if="!isEdit">
                                <input type="hidden" name="search_term" x-bind:value="searchTerm">
                            </template>

                            {{-- Nama Item --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Item</label>
                                <input type="text" name="nama" x-model="form.nama" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Masukkan nama item...">
                                @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Kode Item (Opsional) --}}
                            <div x-show="!isEdit">
                                <label class="block text-sm font-medium text-gray-700">Kode Item (Opsional)</label>
                                <input type="text" name="kode" x-model="form.kode" :disabled="isEdit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Contoh: KBL-001">
                                <p class="text-[10px] text-gray-400 italic mt-1">Jika tidak diisi, kode akan dibuat otomatis oleh sistem.</p>
                                @error('kode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Kategori --}}
                            <div x-data="{
                                open: false,
                                search: form.kategori || '',
                                options: @js($categories),
                                get filtered() {
                                    if (!this.search) return this.options;
                                    return this.options.filter(opt => opt && opt.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                select(value) {
                                    this.search = value;
                                    $dispatch('update-kategori', value);
                                    this.open = false;
                                }
                            }" @update-kategori.window="form.kategori = $event.detail">
                                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                                <div class="relative" @click.outside="open = false">
                                    <input type="text"
                                           name="kategori"
                                           x-model="search"
                                           @focus="open = true; search = form.kategori"
                                           @input="form.kategori = search; open = true"
                                           @keydown.escape="open = false"
                                           @keydown.tab="open = false"
                                           required
                                           autocomplete="off"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                                           placeholder="Ketik atau pilih kategori...">
                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open && filtered.length > 0"
                                         x-transition
                                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="option in filtered" :key="option">
                                            <div @click="select(option)"
                                                 class="px-4 py-2 cursor-pointer hover:bg-cyan-50 text-sm text-gray-700 hover:text-cyan-700"
                                                 x-text="option"></div>
                                        </template>
                                    </div>
                                </div>
                                @error('kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Satuan --}}
                            <div x-data="{
                                open: false,
                                search: form.satuan || '',
                                options: @js($satuans),
                                get filtered() {
                                    if (!this.search) return this.options;
                                    return this.options.filter(opt => opt && opt.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                select(value) {
                                    this.search = value;
                                    $dispatch('update-satuan', value);
                                    this.open = false;
                                }
                            }" @update-satuan.window="form.satuan = $event.detail">
                                <label class="block text-sm font-medium text-gray-700">Satuan</label>
                                <div class="relative" @click.outside="open = false">
                                    <input type="text"
                                           name="satuan"
                                           x-model="search"
                                           @focus="open = true; search = form.satuan"
                                           @input="form.satuan = search; open = true"
                                           @keydown.escape="open = false"
                                           @keydown.tab="open = false"
                                           required
                                           autocomplete="off"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                                           placeholder="Ketik atau pilih satuan...">
                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open && filtered.length > 0"
                                         x-transition
                                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="option in filtered" :key="option">
                                            <div @click="select(option)"
                                                 class="px-4 py-2 cursor-pointer hover:bg-cyan-50 text-sm text-gray-700 hover:text-cyan-700"
                                                 x-text="option"></div>
                                        </template>
                                    </div>
                                </div>
                                @error('satuan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Deskripsi --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Deskripsi (Opsional)</label>
                                <textarea name="deskripsi" x-model="form.deskripsi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Deskripsi detail tentang item..."></textarea>
                                @error('deskripsi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#035b71] text-base font-medium text-white hover:bg-[#00aff0] transition sm:col-start-2 sm:text-sm">Simpan</button>
                                <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 transition sm:mt-0 sm:col-start-1 sm:text-sm">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Detail Item --}}
        <div x-show="showDetail" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="detail-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div x-show="showDetail"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDetail = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div x-show="showDetail"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="detail-title">Detail Barang</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-600" @click="showDetail = false">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Kode Item</p>
                                <p class="font-medium text-gray-900" x-text="detail.kode"></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Nama Item</p>
                                <p class="font-medium text-gray-900" x-text="detail.nama"></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Kategori</p>
                                <p class="font-medium text-gray-900" x-text="detail.kategori"></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Satuan</p>
                                <p class="font-medium text-gray-900" x-text="detail.satuan"></p>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-gray-200">
                            <p class="text-gray-500 text-sm mb-1">Keterangan</p>
                            <p class="text-gray-900 text-sm" x-text="detail.deskripsi || 'Belum ada keterangan.'"></p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="showDetail = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:ml-3 sm:w-auto sm:text-sm">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-delete-modal />
</x-app-layout>
