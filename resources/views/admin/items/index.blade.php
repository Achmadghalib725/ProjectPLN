<x-app-layout>
        <div class="py-4 sm:py-8 lg:py-12"
         x-data="{
            showModal: {{ $errors->any() ? 'true' : 'false' }},
            showDetail: false,
            showMetaModal: false,
            metaTab: 'kategori',
            metaLoading: false,
            metaError: '',
            metaSuccess: '',
            categories: [],
            units: [],
            categoryOptions: @js($categories),
            unitOptions: @js($satuans),
            newCategoryName: '',
            newUnitName: '',
            showDeleteConfirm: false,
            deleteTarget: { type: '', id: null, nama: '' },
            kategoriOpen: false,
            kategoriSearch: '',
            satuanOpen: false,
            satuanSearch: '',
            isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
            actionUrl: '{{ old('_method') === 'PUT' ? (old('id') ? url('admin/items').'/'.old('id') : '') : route('admin.items.store') }}',
            form: {
                id: @js(old('id')),
                kode: @js(old('kode')),
                nama: @js(old('nama')),
                kategori: @js(old('kategori')),
                satuan: @js(old('satuan')),
                deskripsi: @js(old('deskripsi')),
            },
            searchTerm: @js(old('search_term')),
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
                this.kategoriSearch = '';
                this.satuanSearch = '';
                this.actionUrl = '{{ route('admin.items.store') }}';
                this.showDetail = false;
                this.showModal = true;
            },
            openEdit(item) {
                this.isEdit = true;
                this.form = { ...item };
                this.searchTerm = '';
                this.kategoriSearch = item.kategori || '';
                this.satuanSearch = item.satuan || '';
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
            },
            async loadCategories() {
                try {
                    const res = await fetch('{{ route('admin.item-categories.index') }}');
                    this.categories = await res.json();
                } catch (e) {
                    console.error(e);
                }
            },
            async loadUnits() {
                try {
                    const res = await fetch('{{ route('admin.item-units.index') }}');
                    this.units = await res.json();
                } catch (e) {
                    console.error(e);
                }
            },
            async addCategory() {
                if (!this.newCategoryName.trim()) return;
                this.metaLoading = true;
                this.metaError = '';
                try {
                    const res = await fetch('{{ route('admin.item-categories.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ nama: this.newCategoryName })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.categories.push(data.data);
                        this.categories.sort((a, b) => a.nama.localeCompare(b.nama));
                        // Update categoryOptions for combobox and filter
                        this.categoryOptions.push(data.data.nama);
                        this.categoryOptions.sort((a, b) => a.localeCompare(b));
                        this.newCategoryName = '';
                        this.metaSuccess = data.message;
                        setTimeout(() => this.metaSuccess = '', 3000);
                    } else {
                        this.metaError = data.message || 'Gagal menambahkan kategori';
                    }
                } catch (e) {
                    this.metaError = e.message || 'Terjadi kesalahan';
                }
                this.metaLoading = false;
            },
            confirmDeleteCategory(cat) {
                this.deleteTarget = { type: 'kategori', id: cat.id, nama: cat.nama };
                this.showDeleteConfirm = true;
            },
            async deleteCategory(id) {
                this.metaLoading = true;
                this.metaError = '';
                try {
                    const res = await fetch(`{{ url('admin/item-categories') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        const deletedNama = this.categories.find(c => c.id === id)?.nama;
                        this.categories = this.categories.filter(c => c.id !== id);
                        // Update categoryOptions for combobox and filter
                        if (deletedNama) {
                            this.categoryOptions = this.categoryOptions.filter(n => n.toLowerCase() !== deletedNama.toLowerCase());
                        }
                        this.metaSuccess = data.message;
                        setTimeout(() => this.metaSuccess = '', 3000);
                    } else {
                        this.metaError = data.message || 'Gagal menghapus kategori';
                    }
                } catch (e) {
                    this.metaError = e.message || 'Terjadi kesalahan';
                }
                this.metaLoading = false;
                this.showDeleteConfirm = false;
            },
            async addUnit() {
                if (!this.newUnitName.trim()) return;
                this.metaLoading = true;
                this.metaError = '';
                try {
                    const res = await fetch('{{ route('admin.item-units.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ nama: this.newUnitName })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.units.push(data.data);
                        this.units.sort((a, b) => a.nama.localeCompare(b.nama));
                        // Update unitOptions for combobox
                        this.unitOptions.push(data.data.nama);
                        this.unitOptions.sort((a, b) => a.localeCompare(b));
                        this.newUnitName = '';
                        this.metaSuccess = data.message;
                        setTimeout(() => this.metaSuccess = '', 3000);
                    } else {
                        this.metaError = data.message || 'Gagal menambahkan satuan';
                    }
                } catch (e) {
                    this.metaError = e.message || 'Terjadi kesalahan';
                }
                this.metaLoading = false;
            },
            confirmDeleteUnit(unit) {
                this.deleteTarget = { type: 'satuan', id: unit.id, nama: unit.nama };
                this.showDeleteConfirm = true;
            },
            async deleteUnit(id) {
                this.metaLoading = true;
                this.metaError = '';
                try {
                    const res = await fetch(`{{ url('admin/item-units') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        const deletedNama = this.units.find(u => u.id === id)?.nama;
                        this.units = this.units.filter(u => u.id !== id);
                        // Update unitOptions for combobox
                        if (deletedNama) {
                            this.unitOptions = this.unitOptions.filter(n => n.toLowerCase() !== deletedNama.toLowerCase());
                        }
                        this.metaSuccess = data.message;
                        setTimeout(() => this.metaSuccess = '', 3000);
                    } else {
                        this.metaError = data.message || 'Gagal menghapus satuan';
                    }
                } catch (e) {
                    this.metaError = e.message || 'Terjadi kesalahan';
                }
                this.metaLoading = false;
                this.showDeleteConfirm = false;
            },
            executeDelete() {
                if (this.deleteTarget.type === 'kategori') {
                    this.deleteCategory(this.deleteTarget.id);
                } else {
                    this.deleteUnit(this.deleteTarget.id);
                }
            },
            openMetaModal() {
                this.showMetaModal = true;
                this.loadCategories();
                this.loadUnits();
            },
            get filteredKategori() {
                const search = (this.kategoriSearch || '').toLowerCase();
                if (!search) return this.categoryOptions;
                return this.categoryOptions.filter(opt => opt && opt.toLowerCase().includes(search));
            },
            get filteredSatuan() {
                const search = (this.satuanSearch || '').toLowerCase();
                if (!search) return this.unitOptions;
                return this.unitOptions.filter(opt => opt && opt.toLowerCase().includes(search));
            },
            selectKategori(value) {
                this.kategoriSearch = value;
                this.form.kategori = value;
                this.kategoriOpen = false;
            },
            selectSatuan(value) {
                this.satuanSearch = value;
                this.form.satuan = value;
                this.satuanOpen = false;
            }
         }"
         x-init="$watch('showMetaModal', value => { if (value) { loadCategories(); loadUnits(); } })"
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
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button @click="showMetaModal = true"
                               class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-white border border-gray-300 rounded-lg sm:rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 active:scale-95 focus:outline-none transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Kelola Kategori & Satuan
                            </button>
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
                                    <dd class="text-lg sm:text-xl font-bold text-gray-900">{{ $totalCategories }}</dd>
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
                                <template x-for="cat in categoryOptions" :key="cat">
                                    <option :value="cat" :selected="'{{ request('kategori') }}' === cat" x-text="cat"></option>
                                </template>
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
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Informasi Barang</th>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                <th scope="col" class="px-4 py-2 text-center text-[11px] font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($items as $item)
                            <tr class="bg-white hover:bg-gray-50 transition-colors duration-200 group">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $item->nama }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->kode ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600">
                                    {{ ucfirst($item->kategori ?? '-') }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600">
                                    {{ $item->satuan }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button @click='openDetail(@json($item))' class="text-blue-600 hover:text-blue-900 p-1.5 rounded-md transition-all duration-200" title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button @click='openEdit(@json($item))' class="text-yellow-600 hover:text-yellow-900 p-1.5 rounded-md transition-all duration-200" title="Edit Barang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button type="button"
                                            @click="$dispatch('open-delete-modal', {
                                                title: 'Hapus Barang',
                                                message: 'Apakah Anda yakin ingin menghapus barang {{ $item->nama }}? Data tidak dapat dikembalikan.',
                                                action: '{{ route('admin.items.destroy', $item->id) }}'
                                            })"
                                            class="text-red-500 hover:text-red-700 p-1.5 rounded-md transition-all duration-200" title="Hapus Barang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                            {{-- Kode Item --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kode Item <span x-show="isEdit">*</span><span x-show="!isEdit">(Opsional)</span></label>
                                <input type="text" name="kode" x-model="form.kode" :required="isEdit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Contoh: KBL-001">
                                <p x-show="!isEdit" class="text-[10px] text-gray-400 italic mt-1">Jika tidak diisi, kode akan dibuat otomatis oleh sistem.</p>
                                @error('kode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Kategori --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                                <div class="relative" @click.outside="kategoriOpen = false">
                                    <input type="text"
                                           name="kategori"
                                           x-model="kategoriSearch"
                                           @focus="kategoriOpen = true; kategoriSearch = form.kategori || ''"
                                           @input="form.kategori = kategoriSearch; kategoriOpen = true"
                                           @keydown.escape="kategoriOpen = false"
                                           @keydown.tab="kategoriOpen = false"
                                           required
                                           autocomplete="off"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                                           placeholder="Ketik atau pilih kategori...">
                                    <button type="button" @click="kategoriOpen = !kategoriOpen" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="kategoriOpen && filteredKategori.length > 0"
                                         x-transition
                                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="option in filteredKategori" :key="option">
                                            <div @click="selectKategori(option)"
                                                 class="px-4 py-2 cursor-pointer hover:bg-cyan-50 text-sm text-gray-700 hover:text-cyan-700"
                                                 x-text="option"></div>
                                        </template>
                                    </div>
                                </div>
                                @error('kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Satuan --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Satuan</label>
                                <div class="relative" @click.outside="satuanOpen = false">
                                    <input type="text"
                                           name="satuan"
                                           x-model="satuanSearch"
                                           @focus="satuanOpen = true; satuanSearch = form.satuan || ''"
                                           @input="form.satuan = satuanSearch; satuanOpen = true"
                                           @keydown.escape="satuanOpen = false"
                                           @keydown.tab="satuanOpen = false"
                                           required
                                           autocomplete="off"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                                           placeholder="Ketik atau pilih satuan...">
                                    <button type="button" @click="satuanOpen = !satuanOpen" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="satuanOpen && filteredSatuan.length > 0"
                                         x-transition
                                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="option in filteredSatuan" :key="option">
                                            <div @click="selectSatuan(option)"
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

        {{-- Modal Kelola Kategori & Satuan --}}
        <div x-show="showMetaModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="meta-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div x-show="showMetaModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showMetaModal = false; metaError = ''; metaSuccess = '';" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div x-show="showMetaModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="meta-modal-title">Kelola Kategori & Satuan</h3>

                        {{-- Tabs --}}
                        <div class="border-b border-gray-200 mb-4">
                            <nav class="-mb-px flex space-x-6">
                                <button type="button" @click="metaTab = 'kategori'; metaError = '';"
                                        :class="metaTab === 'kategori' ? 'border-cyan-500 text-cyan-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition">
                                    Kategori
                                </button>
                                <button type="button" @click="metaTab = 'satuan'; metaError = '';"
                                        :class="metaTab === 'satuan' ? 'border-cyan-500 text-cyan-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition">
                                    Satuan
                                </button>
                            </nav>
                        </div>

                        {{-- Error Message --}}
                        <div x-show="metaError" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
                            <span x-text="metaError"></span>
                        </div>

                        {{-- Success Message --}}
                        <div x-show="metaSuccess" x-cloak class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                            <span x-text="metaSuccess"></span>
                        </div>

                        {{-- Tab Kategori --}}
                        <div x-show="metaTab === 'kategori'" x-cloak>
                            {{-- Add Input --}}
                            <div class="flex gap-2 mb-4">
                                <input type="text" x-model="newCategoryName" @keydown.enter="addCategory()"
                                       placeholder="Nama kategori baru..."
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                <button type="button" @click="addCategory()" :disabled="metaLoading || !newCategoryName.trim()"
                                        class="inline-flex items-center px-4 py-2 bg-[#035b71] text-white text-sm font-medium rounded-md hover:bg-[#00aff0] disabled:opacity-50 disabled:cursor-not-allowed transition">
                                    Tambah
                                </button>
                            </div>

                            {{-- List --}}
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="max-h-60 overflow-y-auto divide-y divide-gray-200">
                                    <template x-for="cat in categories" :key="cat.id">
                                        <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm text-gray-900" x-text="cat.nama"></span>
                                                <span class="text-xs text-gray-500" x-text="'(' + cat.items_count + ' item)'"></span>
                                            </div>
                                            <button type="button" @click="cat.items_count === 0 && confirmDeleteCategory(cat)"
                                                    :disabled="metaLoading || cat.items_count > 0"
                                                    :class="cat.items_count > 0 ? 'text-gray-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700'"
                                                    class="disabled:opacity-50" :title="cat.items_count > 0 ? 'Tidak dapat dihapus karena sedang digunakan' : 'Hapus'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <div x-show="categories.length === 0" class="px-4 py-8 text-center text-gray-500 text-sm">
                                        Belum ada kategori
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">* Kategori yang sudah digunakan tidak dapat dihapus</p>
                        </div>

                        {{-- Tab Satuan --}}
                        <div x-show="metaTab === 'satuan'" x-cloak>
                            {{-- Add Input --}}
                            <div class="flex gap-2 mb-4">
                                <input type="text" x-model="newUnitName" @keydown.enter="addUnit()"
                                       placeholder="Nama satuan baru..."
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                <button type="button" @click="addUnit()" :disabled="metaLoading || !newUnitName.trim()"
                                        class="inline-flex items-center px-4 py-2 bg-[#035b71] text-white text-sm font-medium rounded-md hover:bg-[#00aff0] disabled:opacity-50 disabled:cursor-not-allowed transition">
                                    Tambah
                                </button>
                            </div>

                            {{-- List --}}
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="max-h-60 overflow-y-auto divide-y divide-gray-200">
                                    <template x-for="unit in units" :key="unit.id">
                                        <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm text-gray-900" x-text="unit.nama"></span>
                                                <span class="text-xs text-gray-500" x-text="'(' + unit.items_count + ' item)'"></span>
                                            </div>
                                            <button type="button" @click="unit.items_count === 0 && confirmDeleteUnit(unit)"
                                                    :disabled="metaLoading || unit.items_count > 0"
                                                    :class="unit.items_count > 0 ? 'text-gray-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700'"
                                                    class="disabled:opacity-50" :title="unit.items_count > 0 ? 'Tidak dapat dihapus karena sedang digunakan' : 'Hapus'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <div x-show="units.length === 0" class="px-4 py-8 text-center text-gray-500 text-sm">
                                        Belum ada satuan
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">* Satuan yang sudah digunakan tidak dapat dihapus</p>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="showMetaModal = false; metaError = ''; metaSuccess = '';" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- Delete Confirmation Modal (Separate) --}}
        <div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 z-[60] overflow-y-auto px-4 py-6 sm:px-0">
            {{-- Backdrop --}}
            <div x-show="showDeleteConfirm"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transform transition-all" @click="showDeleteConfirm = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            {{-- Modal --}}
            <div class="min-h-screen flex items-start justify-center pt-[15vh] sm:pt-[20vh]">
                <div x-show="showDeleteConfirm"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full sm:max-w-md mx-4 sm:mx-auto">
                    <div class="p-6">
                        {{-- Header with Icon --}}
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-bold text-gray-900">Hapus <span x-text="deleteTarget.type === 'kategori' ? 'Kategori' : 'Satuan'"></span></h2>
                                <p class="mt-2 text-sm text-gray-600">Apakah Anda yakin ingin menghapus <span x-text="deleteTarget.type"></span> <strong x-text="deleteTarget.nama"></strong>? Data tidak dapat dikembalikan.</p>
                            </div>
                            {{-- Close Button --}}
                            <button type="button" @click="showDeleteConfirm = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="showDeleteConfirm = false"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                            <button type="button" @click="executeDelete()"
                                    :disabled="metaLoading"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 disabled:opacity-50 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-delete-modal />
</x-app-layout>
