<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }} : {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Nama Lengkap')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <hr class="my-2 border-gray-200">
                                <p class="text-sm text-gray-500 mb-2">Kosongkan password jika tidak ingin mengganti.</p>
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('Password Baru (Opsional)')" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <hr class="my-2 border-gray-200">
                            </div>

                            <div>
                                <x-input-label for="role" :value="__('Role')" />
                                <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="operator_gudang" {{ $user->role == 'operator_gudang' ? 'selected' : '' }}>Operator Gudang</option>
                                    <option value="security" {{ $user->role == 'security' ? 'selected' : '' }}>Security</option>
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="gudang_id" :value="__('Penempatan Gudang')" />
                                <select id="gudang_id" name="gudang_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Pilih Gudang (Opsional untuk Admin) --</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ $user->gudang_id == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama_gudang }} - {{ $gudang->lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('gudang_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="jabatan" :value="__('Jabatan')" />
                                <x-text-input id="jabatan" class="block mt-1 w-full" type="text" name="jabatan" :value="old('jabatan', $user->jabatan)" />
                            </div>

                            <div>
                                <x-input-label for="no_hp" :value="__('No Handphone')" />
                                <x-text-input id="no_hp" class="block mt-1 w-full" type="text" name="no_hp" :value="old('no_hp', $user->no_hp)" />
                            </div>

                            <div class="flex items-center mt-4">
                                <input id="is_active" type="checkbox" value="1" name="is_active" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500" {{ $user->is_active ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 text-sm font-medium text-gray-900">User Aktif</label>
                                <input type="hidden" name="is_active" value="0" disabled> 
                                {{-- *Catatan: Checkbox HTML hanya mengirim value jika dicentang. Validasi Laravel 'boolean' akan handle ini otomatis jika field ada, atau gunakan $request->has('is_active') di controller jika ingin lebih spesifik --}}
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Update User') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>