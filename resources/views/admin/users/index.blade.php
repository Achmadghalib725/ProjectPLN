<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12"
         x-data="{
            showModal: {{ $errors->any() ? 'true' : 'false' }},
            isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
            actionUrl: '{{ old('_method') === 'PUT' ? (old('id') ? url('admin/users').'/'.old('id') : '') : route('admin.users.store') }}',
            form: {
                id: @js(old('id')),
                name: @js(old('name')),
                username: @js(old('username')),
                no_hp: @js(old('no_hp')),
                role: @js(old('role')),
                jabatan: @js(old('jabatan')),
                gudang_id: @js(old('gudang_id')),
                gudang_ids: @js(old('gudang_ids', [])),
                password: '',
                password_confirmation: '',
                is_active: @js(old('is_active', 1)),
            },
            openCreate() {
                this.isEdit = false;
                this.form = { id: '', name: '', username: '', no_hp: '', role: '', jabatan: '', gudang_id: '', gudang_ids: [], password: '', password_confirmation: '', is_active: 1 };
                this.actionUrl = '{{ route('admin.users.store') }}';
                this.showModal = true;
            },
            openEdit(user) {
                this.isEdit = true;
                this.form = {
                    ...user,
                    password: '',
                    password_confirmation: '',
                    gudang_id: user.gudang_id || '',
                    gudang_ids: (user.managed_gudangs || []).map(gudang => gudang.id),
                };
                this.actionUrl = '{{ url('admin/users') }}/' + user.id;
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
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Kelola User</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Daftar semua pengguna yang memiliki akses ke sistem.</p>
                        </div>
                        <button @click="openCreate()"
                           class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-[#035b71] border border-transparent rounded-lg sm:rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0] active:scale-95 focus:outline-none transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Tambah User Baru
                        </button>
                    </div>
                </div>
            </div>

            <div id="users-table" class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg border border-gray-200" data-ajax-container>
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4" data-ajax-form data-ajax-target="#users-table">
                        <div class="relative w-full md:w-96 group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-[#035b71] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-md focus:ring-[#035b71] focus:border-[#035b71] transition-all"
                                placeholder="Cari nama, username, atau jabatan...">
                        </div>

                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <select name="role" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#035b71] focus:border-[#035b71] block w-full md:w-48 p-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                                <option value="">Semua Role</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="operator_gudang" {{ request('role') == 'operator_gudang' ? 'selected' : '' }}>Tool Man</option>
                                <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="security" {{ request('role') == 'security' ? 'selected' : '' }}>Security</option>
                                <option value="penerima" {{ request('role') == 'penerima' ? 'selected' : '' }}>Penerima</option>
                            </select>

                            <select name="gudang_id" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#035b71] focus:border-[#035b71] block w-full md:w-52 p-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                                <option value="">Semua Gudang</option>
                                @foreach($gudangs as $gudang)
                                    <option value="{{ $gudang->id }}" {{ request('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                        {{ $gudang->nama }}
                                    </option>
                                @endforeach
                            </select>

                            @if(request('search') || request('role') || request('gudang_id'))
                                <a href="{{ route('admin.users.index') }}" class="text-sm text-red-500 hover:text-red-700 font-medium whitespace-nowrap px-3 py-2 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">User Profile</th>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Role & Jabatan</th>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Lokasi Gudang</th>
                                <th scope="col" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-2 text-center text-[11px] font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                            <tr class="bg-white hover:bg-gray-50 transition-colors duration-200 group">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                    @php
                                        $metaParts = array_filter([$user->username ?? null, $user->no_hp ?? null]);
                                    @endphp
                                    <div class="text-xs text-gray-500">{{ $metaParts ? implode(' | ', $metaParts) : '-' }}</div>
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                                        {{ $user->role_display_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $user->jabatan ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600">
                                    @if(in_array($user->role, ['manager', 'admin'], true) && $user->managedGudangs->count() > 0)
                                        <div class="text-gray-700">
                                            {{ implode(', ', $user->managedGudangs->pluck('nama')->all()) }}
                                        </div>
                                    @elseif($user->gudang)
                                        <span class="text-gray-700">{{ $user->gudang->nama }}</span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Tidak ada gudang</span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap">
                                    @if($user->is_active)
                                        <span class="text-green-700 text-xs font-semibold">Aktif</span>
                                    @else
                                        <span class="text-red-700 text-xs font-semibold">Non-Aktif</span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button @click="openEdit(@js($user))" class="text-yellow-600 hover:text-yellow-900 p-1.5 rounded-md transition-all duration-200" title="Edit User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        
                                        @if($user->id !== auth()->id())
                                        <button type="button"
                                            @click="$dispatch('open-delete-modal', {
                                                title: 'Hapus User',
                                                message: 'Apakah Anda yakin ingin menghapus user {{ $user->name }}? Data tidak dapat dikembalikan.',
                                                action: '{{ route('admin.users.destroy', $user->id) }}'
                                            })"
                                            class="text-red-500 hover:text-red-700 p-1.5 rounded-md transition-all duration-200" title="Hapus User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        {{-- BAGIAN YANG DIGANTI AGAR RAPI --}}
                                        <div class="mb-3 text-gray-300">
                                            @include('layouts.partials.nav-icon', ['icon' => 'user-group', 'isActive' => false])
                                        </div>
                                        <p class="text-base font-medium">Belum ada data user ditemukan.</p>
                                        <p class="text-sm">Coba ubah filter pencarian atau tambah user baru.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6" data-ajax-pagination>
                    {{ $users->links() }}
                </div>
            </div>
        </div>

        {{-- Modal Create/Edit --}}
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <span x-text="isEdit ? 'Edit User' : 'Tambah User Baru'"></span>
                        </h3>

                        @if($errors->any())
                            <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form :action="actionUrl" method="POST" class="mt-4 space-y-4">
                            @csrf
                            <template x-if="isEdit">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" name="name" x-model="form.name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" name="username" x-model="form.username" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">No. HP</label>
                                <input type="text" name="no_hp" x-model="form.no_hp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role</label>
                                    <select name="role" x-model="form.role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                        <option value="">Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="operator_gudang">Tool Man</option>
                                        <option value="manager">Manager</option>
                                        <option value="security">Security</option>
                                        <option value="penerima">Penerima</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                                    <input type="text" name="jabatan" x-model="form.jabatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                </div>
                            </div>

                            <div x-show="form.role !== 'manager' && form.role !== 'admin'">
                                <label class="block text-sm font-medium text-gray-700">Lokasi Gudang</label>
                                <select name="gudang_id" x-model="form.gudang_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                                    <option value="">Tidak ada gudang</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}">{{ $gudang->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="form.role === 'manager'" x-cloak>
                                <label class="block text-sm font-medium text-gray-700">Gudang yang Dikelola</label>
                                <div class="mt-1 border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="max-h-48 overflow-y-auto divide-y divide-gray-200">
                                        @foreach($gudangs as $gudang)
                                            <label class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 cursor-pointer">
                                                <span class="text-sm text-gray-900">{{ $gudang->nama }}</span>
                                                <input type="checkbox"
                                                       name="gudang_ids[]"
                                                       value="{{ $gudang->id }}"
                                                       :checked="form.gudang_ids.includes({{ $gudang->id }})"
                                                       @change="form.gudang_ids.includes({{ $gudang->id }}) ? form.gudang_ids = form.gudang_ids.filter(id => id !== {{ $gudang->id }}) : form.gudang_ids.push({{ $gudang->id }})"
                                                       class="h-4 w-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    <span x-show="form.gudang_ids.length === 0">Belum ada gudang dipilih</span>
                                    <span x-show="form.gudang_ids.length > 0" x-text="form.gudang_ids.length + ' gudang dipilih'"></span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" name="password" x-model="form.password" :required="!isEdit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" x-model="form.password_confirmation" :required="!isEdit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm">
                            </div>

                            <template x-if="isEdit && form.role !== 'admin'">
                                <div class="flex items-center">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" :checked="form.is_active == 1 || form.is_active === true" class="h-4 w-4 text-cyan-600 border-gray-300 rounded">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">User Aktif</label>
                                </div>
                            </template>
                            <template x-if="isEdit && form.role === 'admin'">
                                <div class="flex items-center text-sm text-gray-500 bg-gray-50 p-2 rounded">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Admin tidak dapat dinonaktifkan
                                </div>
                            </template>

                            <div class="mt-5 sm:mt-6 flex flex-col-reverse sm:flex-row sm:gap-3">
                                <button type="button" @click="showModal = false" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-gray-700 font-medium hover:bg-gray-50 transition">Batal</button>
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#035b71] text-white font-medium hover:bg-[#00aff0] transition">Simpan</button>
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
