<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12"
         x-data="{
            showModal: {{ $errors->any() ? 'true' : 'false' }},
            isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
            actionUrl: '{{ old('_method') === 'PUT' ? (old('id') ? url('admin/pics').'/'.old('id') : '') : route('admin.pics.store') }}',
            form: {
                id: @js(old('id')),
                nama: @js(old('nama')),
                role: @js(old('role', 'penerima')),
                username: @js(old('username')),
                jabatan: @js(old('jabatan')),
                no_hp: @js(old('no_hp')),
                gudang_id: @js(old('gudang_id')),
            },
            openCreate() {
                this.isEdit = false;
                this.form = { id: '', nama: '', role: 'penerima', username: '', jabatan: '', no_hp: '', gudang_id: '' };
                this.actionUrl = '{{ route('admin.pics.store') }}';
                this.showModal = true;
            },
            openEdit(pic) {
                this.isEdit = true;
                this.form = { ...pic };
                this.actionUrl = '{{ url('admin/pics') }}/' + pic.id;
                this.showModal = true;
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
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Kelola PIC</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Daftar semua Person In Charge (PIC) untuk pengisian surat jalan.</p>
                        </div>
                        <button @click="openCreate()"
                           class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-[#035b71] border border-transparent rounded-lg sm:rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0] active:scale-95 focus:outline-none transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Tambah PIC Baru
                        </button>
                    </div>
                </div>
            </div>

            {{-- Main Content Card --}}
            <div id="pics-table" class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg border border-gray-200" data-ajax-container>

                {{-- Toolbar (Filter & Search) --}}
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('admin.pics.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4" data-ajax-form data-ajax-target="#pics-table">

                        {{-- Search --}}
                        <div class="relative w-full md:w-96 group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-[#035b71] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-md focus:ring-[#035b71] focus:border-[#035b71] transition-all"
                                placeholder="Cari nama, jabatan, atau no HP...">
                        </div>

                        {{-- Filters --}}
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <select name="gudang_id" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#035b71] focus:border-[#035b71] block w-full md:w-48 p-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                                <option value="">Semua Gudang</option>
                                @foreach($gudangs as $gudang)
                                    <option value="{{ $gudang->id }}" {{ request('gudang_id') == $gudang->id ? 'selected' : '' }}>{{ $gudang->nama }}</option>
                                @endforeach
                            </select>

                            @if(request('search') || request('gudang_id'))
                                <a href="{{ route('admin.pics.index') }}" class="text-sm text-red-500 hover:text-red-700 font-medium whitespace-nowrap px-3 py-2 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
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
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Nama PIC</th>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">No HP</th>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Lokasi Gudang</th>
                                <th scope="col" class="px-4 py-2 text-center text-[11px] font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pics as $pic)
                            <tr class="bg-white hover:bg-gray-50 transition-colors duration-200 group">

                                {{-- Kolom Nama --}}
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $pic->nama }}</div>
                                </td>

                                {{-- Kolom Jabatan --}}
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600">
                                    @if($pic->jabatan)
                                        <span class="text-gray-700">{{ $pic->jabatan }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada jabatan</span>
                                    @endif
                                </td>

                                {{-- Kolom No HP --}}
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600">
                                    @if($pic->no_hp)
                                        <span class="text-gray-700">{{ $pic->no_hp }}</span>
                                    @else
                                        <span class="text-gray-400 italic">-</span>
                                    @endif
                                </td>

                                {{-- Kolom Gudang --}}
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600">
                                    @if($pic->gudang)
                                        <span class="text-gray-700">{{ $pic->gudang->nama }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada gudang</span>
                                    @endif
                                </td>

                                {{-- Kolom Aksi --}}
                                <td class="px-4 py-2 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button @click="openEdit(@js($pic))" class="text-yellow-600 hover:text-yellow-900 p-1.5 rounded-md transition-all duration-200" title="Edit PIC">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <button type="button"
                                            @click="$dispatch('open-delete-modal', {
                                                title: 'Hapus PIC',
                                                message: 'Apakah Anda yakin ingin menghapus PIC {{ $pic->nama }}? Data tidak dapat dikembalikan.',
                                                action: '{{ route('admin.pics.destroy', $pic->id) }}'
                                            })"
                                            class="text-red-500 hover:text-red-700 p-1.5 rounded-md transition-all duration-200" title="Hapus PIC">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-1a6 6 0 00-9-5.197M12 7a4 4 0 11-8 0 4 4 0 018 0zm9 4a4 4 0 10-8 0 4 4 0 008 0zM6 21v-1a6 6 0 0112 0v1"></path></svg>
                                        <p class="text-base font-medium">Belum ada data PIC ditemukan.</p>
                                        <p class="text-sm">Coba ubah filter pencarian atau tambah PIC baru.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6" data-ajax-pagination>
                    {{ $pics->links() }}
                </div>
            </div>
        </div>

        {{-- Modal Create/Edit PIC --}}
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
                            <span x-text="isEdit ? 'Edit PIC' : 'Tambah PIC Baru'"></span>
                        </h3>

                        @if($errors->any())
                            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form :action="actionUrl" method="POST" class="mt-4 space-y-4" autocomplete="off">
                            @csrf
                            {{-- Method Spoofing for PUT (only when editing) --}}
                            <template x-if="isEdit">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            {{-- Nama Lengkap --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" name="nama" x-model="form.nama" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Masukkan nama lengkap PIC...">
                                @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Jabatan --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                                <input type="text" name="jabatan" x-model="form.jabatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Contoh: Supervisor, Manager, dll">
                                @error('jabatan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Akun Login --}}
                            <template x-if="!isEdit">
                                <div class="space-y-4">
                                    <div class="rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-xs text-blue-700">
                                        Bagian ini dipakai untuk membuat akun login PIC.
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Role Akun</label>
                                        <select name="role" x-model="form.role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                            <option value="">-- Pilih Role --</option>
                                            <option value="penerima">Penerima</option>
                                            <option value="operator_gudang">Operator Gudang</option>
                                        </select>
                                        @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Username</label>
                                        <input type="text" name="username" x-model="form.username" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="contoh: pic_k3" autocomplete="off">
                                        @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Password</label>
                                        <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Masukkan password" autocomplete="new-password">
                                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="Ulangi password" autocomplete="new-password">
                                        @error('password_confirmation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </template>

                            {{-- No HP --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">No Handphone</label>
                                <input type="text" name="no_hp" x-model="form.no_hp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" placeholder="0812xxxx">
                                @error('no_hp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Gudang --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lokasi Gudang</label>
                                <select name="gudang_id" x-model="form.gudang_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                    <option value="">-- Pilih Gudang (Opsional) --</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}">{{ $gudang->nama }} {{ $gudang->kode ? '('.$gudang->kode.')' : '' }}</option>
                                    @endforeach
                                </select>
                                @error('gudang_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-delete-modal />
</x-app-layout>
