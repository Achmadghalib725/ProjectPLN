<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen"
         x-data="{
            showModal: {{ $errors->any() ? 'true' : 'false' }},
            isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
            actionUrl: '{{ old('_method') === 'PUT' ? (old('id') ? url('admin/users').'/'.old('id') : '') : route('admin.users.store') }}',
            form: {
                id: @json(old('id')),
                name: @json(old('name')),
                username: @json(old('username')),
                no_hp: @json(old('no_hp')),
                role: @json(old('role')),
                jabatan: @json(old('jabatan')),
                gudang_id: @json(old('gudang_id')),
                password: '',
                password_confirmation: '',
                is_active: @json(old('is_active', 1)),
            },
            openCreate() {
                this.isEdit = false;
                this.form = { id: '', name: '', username: '', no_hp: '', role: '', jabatan: '', gudang_id: '', password: '', password_confirmation: '', is_active: 1 };
                this.actionUrl = '{{ route('admin.users.store') }}';
                this.showModal = true;
            },
            openEdit(user) {
                this.isEdit = true;
                // Populate form with user data
                this.form = { ...user, password: '', password_confirmation: '', gudang_id: user.gudang_id || '' };
                // Construct update URL manually since we are in JS
                this.actionUrl = '{{ url('admin/users') }}/' + user.id;
                this.showModal = true;
            }
         }"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Header Section with Gradient --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                        Kelola User
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Daftar semua pengguna yang memiliki akses ke sistem.</p>
                </div>
                <button @click="openCreate()" 
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-lg shadow-md hover:shadow-lg hover:from-blue-700 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah User Baru
                </button>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 flex justify-between items-center" role="alert">
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                        <span class="font-medium">Berhasil!</span> {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            {{-- Main Content Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/50 overflow-hidden">
                
                {{-- Toolbar (Filter & Search) --}}
                <div class="p-6 border-b border-gray-100 bg-white">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        {{-- Search --}}
                        <div class="relative w-full md:w-96 group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-cyan-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-200 rounded-lg bg-gray-50 focus:ring-cyan-500 focus:border-cyan-500 transition-all focus:bg-white"
                                placeholder="Cari nama, username, atau jabatan...">
                        </div>

                        {{-- Filters --}}
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <select name="role" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full md:w-48 p-2.5 cursor-pointer hover:bg-gray-100 transition-colors">
                                <option value="">Semua Role</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="operator_gudang" {{ request('role') == 'operator_gudang' ? 'selected' : '' }}>Operator Gudang</option>
                                <option value="security" {{ request('role') == 'security' ? 'selected' : '' }}>Security</option>
                            </select>

                            @if(request('search') || request('role'))
                                <a href="{{ route('admin.users.index') }}" class="text-sm text-red-500 hover:text-red-700 font-medium whitespace-nowrap px-3 py-2 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    Reset Filter
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50/80 border-b border-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">User Profile</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Role & Jabatan</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Lokasi Gudang</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-center font-semibold tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                            <tr class="bg-white hover:bg-cyan-50/30 transition-colors duration-200 group">
                                
                                {{-- Kolom Nama & Email --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white font-bold text-lg shadow-sm group-hover:scale-110 transition-transform duration-200">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 group-hover:text-cyan-700 transition-colors">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->jabatan ?? '-' }}</div>
                                            @if($user->no_hp)
                                                <div class="flex items-center mt-1 text-[10px] text-gray-400">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                    {{ $user->no_hp }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom Role --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col items-start gap-1">
                                        @php
                                            $roleColors = [
                                                'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                'operator_gudang' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'security' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            ];
                                            $colorClass = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $colorClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                        <span class="text-xs text-gray-500 font-medium">
                                            {{ $user->jabatan ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Kolom Gudang --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if($user->gudang)
                                        <div class="flex items-center text-gray-700 bg-gray-50 px-3 py-1 rounded-md border border-gray-100 inline-block">
                                            <svg class="w-4 h-4 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            <span class="font-medium">{{ $user->gudang->nama }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Tidak ada gudang</span>
                                    @endif
                                </td>

                                {{-- Kolom Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($user->is_active)
                                            <div class="h-2.5 w-2.5 rounded-full bg-green-500 mr-2 animate-pulse"></div>
                                            <span class="text-green-700 text-xs font-bold bg-green-100 px-2 py-0.5 rounded-md">Aktif</span>
                                        @else
                                            <div class="h-2.5 w-2.5 rounded-full bg-red-500 mr-2"></div>
                                            <span class="text-red-700 text-xs font-bold bg-red-100 px-2 py-0.5 rounded-md">Non-Aktif</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Kolom Aksi --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-3 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button @click='openEdit(@json($user))' class="text-indigo-500 hover:text-indigo-700 p-2 hover:bg-indigo-50 rounded-full transition-all duration-200" title="Edit User">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        
                                        @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}? Data tidak dapat dikembalikan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-full transition-all duration-200" title="Hapus User">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-1a6 6 0 00-9-5.197M12 7a4 4 0 11-8 0 4 4 0 018 0zm9 4a4 4 0 10-8 0 4 4 0 008 0zM6 21v-1a6 6 0 0112 0v1"></path></svg>
                                        <p class="text-base font-medium">Belum ada data user ditemukan.</p>
                                        <p class="text-sm">Coba ubah filter pencarian atau tambah user baru.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>
        </div>

        {{-- Modal Create/Edit User --}}
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
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            <span x-text="isEdit ? 'Edit User' : 'Tambah User Baru'"></span>
                        </h3>
                        
                        <form :action="actionUrl" method="POST" class="mt-4 space-y-4">
                            @csrf
                            {{-- Method Spoofing for PUT (only when editing) --}}
                            <template x-if="isEdit">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            {{-- Nama --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" name="name" x-model="form.name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Username --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" name="username" x-model="form.username" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm" pattern="[a-z0-9_]+" title="Username hanya boleh huruf kecil, angka, dan underscore">
                                @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- No HP --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">No. HP</label>
                                <input type="text" name="no_hp" x-model="form.no_hp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                {{-- Role --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role</label>
                                    <select name="role" x-model="form.role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                        <option value="">Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="operator_gudang">Operator Gudang</option>
                                        <option value="security">Security/Pemliharaan </option>
                                    </select>
                                </div>

                                {{-- Jabatan --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                                    <input type="text" name="jabatan" x-model="form.jabatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                </div>
                            </div>

                            {{-- Gudang (Optional/Conditional) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lokasi Gudang</label>
                                <select name="gudang_id" x-model="form.gudang_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                    <option value="">Tidak ada gudang</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}">{{ $gudang->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Password --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Password <span x-show="isEdit" class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah)</span>
                                </label>
                                <input type="password" name="password" x-model="form.password" :required="!isEdit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Konfirmasi Password --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Konfirmasi Password <span x-show="isEdit" class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah)</span>
                                </label>
                                <input type="password" name="password_confirmation" x-model="form.password_confirmation" :required="!isEdit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                @error('password_confirmation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Status --}}
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1" x-model="form.is_active" :checked="form.is_active == 1" class="h-4 w-4 text-cyan-600 focus:ring-cyan-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">User Aktif</label>
                            </div>

                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-cyan-600 text-base font-medium text-white hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 sm:col-start-2 sm:text-sm">Simpan</button>
                                <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 sm:mt-0 sm:col-start-1 sm:text-sm">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>