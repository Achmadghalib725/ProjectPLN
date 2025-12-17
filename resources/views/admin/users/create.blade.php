<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}" class="text-white hover:text-[#ffff00] transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ __('Tambah Pengguna Baru') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <x-slot name="title">
        Tambah Pengguna Baru - Kelola Pengguna
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0] @error('name') border-red-500 @enderror"
                                required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0] @error('email') border-red-500 @enderror"
                                required>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password" id="password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0] @error('password') border-red-500 @enderror"
                                    required>
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Min. 8 karakter</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0]"
                                    required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                            <select name="role" id="role"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0] @error('role') border-red-500 @enderror"
                                required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="operator_gudang" {{ old('role') == 'operator_gudang' ? 'selected' : '' }}>Operator Gudang</option>
                                <option value="security" {{ old('role') == 'security' ? 'selected' : '' }}>Security</option>
                            </select>
                            @error('role')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="gudang_id" class="block text-sm font-medium text-gray-700 mb-2">Gudang / Unit Kerja</label>
                            <select name="gudang_id" id="gudang_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0] @error('gudang_id') border-red-500 @enderror">
                                <option value="">-- Tidak Ada (Untuk Admin) --</option>
                                @foreach($gudangs as $gudang)
                                    <option value="{{ $gudang->id }}" {{ old('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                        {{ $gudang->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gudang_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Kosongkan untuk role Admin</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
                                <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0] @error('jabatan') border-red-500 @enderror">
                                @error('jabatan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0] @error('no_hp') border-red-500 @enderror"
                                    placeholder="08xxxxxxxxxx">
                                @error('no_hp')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-[#00aff0] shadow-sm focus:ring-[#00aff0]">
                                <span class="ml-2 text-sm text-gray-600">Akun Aktif</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-2 bg-[#00aff0] text-white rounded-lg hover:bg-[#00aff0]/90 transition font-bold">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
