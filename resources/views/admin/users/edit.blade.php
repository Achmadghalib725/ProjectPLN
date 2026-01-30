<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen" x-data="{ role: '{{ old('role', $user->role) }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                            Edit Profil User
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Mengubah informasi untuk user: <span class="font-semibold text-cyan-600">{{ $user->name }}</span></p>
                    </div>
                </div>
                <a href="{{ route('admin.users.index') }}" 
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            {{-- Form Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/50 overflow-hidden">
                <div class="p-8">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Nama --}}
                            <div class="space-y-2">
                                <x-input-label for="name" :value="__('Nama Lengkap')" class="text-gray-700 font-semibold" />
                                <x-text-input id="name" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="name" :value="old('name', $user->name)" required />
                                <x-input-error :messages="$errors->get('name')" />
                            </div>

                            {{-- Email --}}
                            <div class="space-y-2">
                                <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-semibold" />
                                <x-text-input id="email" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="email" name="email" :value="old('email', $user->email)" required />
                                <x-input-error :messages="$errors->get('email')" />
                            </div>

                            {{-- Keamanan Separator --}}
                            <div class="col-span-1 md:col-span-2 flex items-center gap-4 py-2">
                                <div class="h-px bg-gray-100 flex-grow"></div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Keamanan (Opsional)</span>
                                <div class="h-px bg-gray-100 flex-grow"></div>
                            </div>

                            {{-- Password Baru --}}
                            <div class="space-y-2">
                                <x-input-label for="password" :value="__('Password Baru')" class="text-gray-700 font-semibold" />
                                <x-text-input id="password" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="password" name="password" placeholder="Kosongkan jika tidak diganti" />
                                <x-input-error :messages="$errors->get('password')" />
                            </div>

                            {{-- Konfirmasi Password --}}
                            <div class="space-y-2">
                                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-gray-700 font-semibold" />
                                <x-text-input id="password_confirmation" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="password" name="password_confirmation" />
                            </div>

                            {{-- Detail Pekerjaan Separator --}}
                            <div class="col-span-1 md:col-span-2 flex items-center gap-4 py-2">
                                <div class="h-px bg-gray-100 flex-grow"></div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Detail Pekerjaan & Status</span>
                                <div class="h-px bg-gray-100 flex-grow"></div>
                            </div>

                            {{-- Role --}}
                            <div class="space-y-2">
                                <x-input-label for="role" :value="__('Role Akses')" class="text-gray-700 font-semibold" />
                                <select id="role" name="role" x-model="role" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm bg-gray-50/50 transition-all cursor-pointer">
                                    <option value="operator_gudang" {{ old('role', $user->role) == 'operator_gudang' ? 'selected' : '' }}>Tool Man</option>
                                    <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Manager</option>
                                    <option value="security" {{ old('role', $user->role) == 'security' ? 'selected' : '' }}>Security</option>
                                    <option value="penerima" {{ old('role', $user->role) == 'penerima' ? 'selected' : '' }}>Penerima</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <x-input-error :messages="$errors->get('role')" />
                            </div>

                            {{-- Gudang --}}
                            <div class="space-y-2" x-show="role !== 'manager' && role !== 'admin'">
                                <x-input-label for="gudang_id" :value="__('Penempatan Gudang')" class="text-gray-700 font-semibold" />
                                <select id="gudang_id" name="gudang_id" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm bg-gray-50/50 transition-all cursor-pointer">
                                    <option value="">-- Pilih Gudang --</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ old('gudang_id', $user->gudang_id) == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('gudang_id')" />
                            </div>

                            {{-- Gudang Manager --}}
                            <div class="space-y-2" x-show="role === 'manager'">
                                <x-input-label for="gudang_ids" :value="__('Gudang yang Dikelola')" class="text-gray-700 font-semibold" />
                                <select id="gudang_ids" name="gudang_ids[]"
                                    multiple
                                    class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm bg-gray-50/50 transition-all cursor-pointer">
                                    @php
                                        $selectedGudangs = old('gudang_ids', $user->managedGudangs->pluck('id')->all());
                                    @endphp
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ in_array($gudang->id, $selectedGudangs) ? 'selected' : '' }}>
                                            {{ $gudang->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('gudang_ids')" />
                            </div>

                            {{-- Jabatan --}}
                            <div class="space-y-2">
                                <x-input-label for="jabatan" :value="__('Jabatan')" class="text-gray-700 font-semibold" />
                                <x-text-input id="jabatan" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="jabatan" :value="old('jabatan', $user->jabatan)" />
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-2">
                                <x-input-label for="no_hp" :value="__('No Handphone')" class="text-gray-700 font-semibold" />
                                <x-text-input id="no_hp" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="no_hp" :value="old('no_hp', $user->no_hp)" />
                            </div>

                            {{-- Status Aktif --}}
                            <div class="col-span-1 md:col-span-2">
                                <label class="inline-flex items-center cursor-pointer p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:bg-gray-100 transition-colors w-full md:w-auto">
                                    <div class="relative">
                                        <input type="checkbox" id="is_active" value="1" name="is_active" class="sr-only peer" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-cyan-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-600"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-bold text-gray-700 uppercase tracking-wide">Status User Aktif</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-10 pt-6 border-t border-gray-100 gap-4">
                            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Batal</a>
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 text-sm font-bold text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-xl shadow-md hover:shadow-lg hover:from-blue-700 hover:to-cyan-600 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Update Data User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
