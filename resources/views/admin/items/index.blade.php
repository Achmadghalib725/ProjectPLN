<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12"
         x-data="{
            showModal: {{ $errors->any() ? 'true' : 'false' }},
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
            openCreate() {
                this.isEdit = false;
                this.form = { id: '', kode: '', nama: '', kategori: '', satuan: '', deskripsi: '' };
                this.actionUrl = '{{ route('admin.items.store') }}';
                this.showModal = true;
            },
            openEdit(item) {
                this.isEdit = true;
                this.form = { ...item };
                this.actionUrl = '{{ url('admin/items') }}/' + item.id;
                this.showModal = true;
            }
         }"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Header Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4">
                        <div class="text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-[#035b71]">Kelola Master Barang</h2>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar semua jenis barang yang terdaftar dalam sistem.</p>
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
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row items-center sm:items-center">
                            <div class="flex-shrink-0 bg-[#035b71] rounded-md p-2 sm:p-3 mb-2 sm:mb-0">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="sm:ml-5 w-full sm:w-0 sm:flex-1 text-center sm:text-left">
                                <dl>
                                    <dt class="text-xs sm:text-sm font-medium text-slate-500 truncate">Total Jenis Barang</dt>
                                    <dd class="text-lg sm:text-xl font-bold text-[#035b71]">{{ $items->total() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row items-center sm:items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-2 sm:p-3 mb-2 sm:mb-0">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <div class="sm:ml-5 w-full sm:w-0 sm:flex-1 text-center sm:text-left">
                                <dl>
                                    <dt class="text-xs sm:text-sm font-medium text-slate-500 truncate">Total Kategori</dt>
                                    <dd class="text-lg sm:text-xl font-bold text-purple-600">{{ $items->pluck('kategori')->unique()->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 flex justify-between items-center" role="alert">
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                         {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            {{-- Main Content Card --}}
            <div id="items-table" class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg border border-slate-200" data-ajax-container>

                {{-- Toolbar (Filter & Search) --}}
                <div class="p-4 sm:p-6 border-b border-slate-200">
                    <form method="GET" action="{{ route('admin.items.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                        {{-- Search --}}
                        <div class="relative w-full md:w-96 group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400 group-focus-within:text-[#035b71] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-slate-300 rounded-md focus:ring-[#035b71] focus:border-[#035b71] transition-all"
                                placeholder="Cari nama atau kode barang...">
                        </div>

                        {{-- Filter Kategori --}}
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <select name="kategori" onchange="this.form.submit()" class="border border-slate-300 text-gray-900 text-sm rounded-md focus:ring-[#035b71] focus:border-[#035b71] block w-full md:w-48 p-2.5 cursor-pointer hover:bg-slate-50 transition-colors">
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
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Informasi Barang</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kategori</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Satuan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($items as $item)
                            <tr class="bg-white hover:bg-cyan-50/30 transition-colors duration-200 group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 group-hover:text-cyan-700 transition-colors">{{ $item->nama }}</div>
                                        <div class="text-xs text-gray-500">Kode: {{ $item->kode }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-purple-100 text-purple-700 border-purple-200">
                                        {{ $item->kategori ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                    {{ $item->satuan }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-3 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button @click='openEdit(@json($item))' class="text-indigo-500 hover:text-indigo-700 p-2 hover:bg-indigo-50 rounded-full transition-all duration-200" title="Edit Barang">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang {{ $item->nama }}?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-full transition-all duration-200" title="Hapus Barang">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
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
                <div class="bg-slate-50 px-4 py-3 border-t border-slate-200 sm:px-6" data-ajax-pagination>
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

                        <form :action="actionUrl" method="POST" class="mt-4 space-y-4">
                            @csrf
                            {{-- Method Spoofing for PUT (only when editing) --}}
                            <template x-if="isEdit">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            {{-- Kode Item --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kode Item</label>
                                <input type="text" name="kode" x-model="form.kode" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Contoh: KBL-001">
                                @error('kode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Nama Item --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Item</label>
                                <input type="text" name="nama" x-model="form.nama" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Masukkan nama item...">
                                @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                                <div class="relative">
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
                                         @click.outside="open = false"
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
                                <div class="relative">
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
                                         @click.outside="open = false"
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
                                <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 transition sm:mt-0 sm:col-start-1 sm:text-sm">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
