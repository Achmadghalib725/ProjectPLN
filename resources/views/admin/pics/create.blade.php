<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                        Tambah PIC Baru
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Buat data Person In Charge (PIC) baru untuk pengisian surat jalan.</p>
                </div>
                <a href="{{ route('admin.pics.index') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Daftar
                </a>
            </div>

            {{-- Form Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/50 overflow-hidden">
                <div class="p-8">
                    @if($errors->any())
                        <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <form action="{{ route('admin.pics.store') }}" method="POST" autocomplete="off">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Nama Lengkap --}}
                            <div class="space-y-2">
                                <x-input-label for="nama" :value="__('Nama Lengkap')" class="text-gray-700 font-semibold" />
                                <x-text-input id="nama" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="nama" :value="old('nama')" required autofocus placeholder="Masukkan nama lengkap PIC..." />
                                <x-input-error :messages="$errors->get('nama')" />
                            </div>

                            {{-- Jabatan --}}
                            <div class="space-y-2">
                                <x-input-label for="jabatan" :value="__('Jabatan')" class="text-gray-700 font-semibold" />
                                <x-text-input id="jabatan" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="jabatan" :value="old('jabatan')" placeholder="Contoh: Supervisor, Manager, dll" />
                                <x-input-error :messages="$errors->get('jabatan')" />
                            </div>

                            {{-- Akun Login --}}
                            <div class="space-y-2 md:col-span-2">
                                <div class="rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-xs text-blue-700">
                                    Bagian ini dipakai untuk membuat akun login PIC.
                                </div>
                            </div>

                            {{-- Role Akun --}}
                            <div class="space-y-2">
                                <x-input-label for="role" :value="__('Role Akun')" class="text-gray-700 font-semibold" />
                                <select id="role" name="role" required class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm bg-gray-50/50 transition-all cursor-pointer">
                                    <option value="">-- Pilih Role --</option>
                                    <option value="penerima" {{ old('role') === 'penerima' ? 'selected' : '' }}>Penerima</option>
                                    <option value="operator_gudang" {{ old('role') === 'operator_gudang' ? 'selected' : '' }}>Tool Man</option>
                                </select>
                                <x-input-error :messages="$errors->get('role')" />
                            </div>

                            {{-- Username --}}
                            <div class="space-y-2">
                                <x-input-label for="username" :value="__('Username')" class="text-gray-700 font-semibold" />
                                <x-text-input id="username" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="username" :value="old('username')" placeholder="contoh: pic_k3" />
                                <x-input-error :messages="$errors->get('username')" />
                            </div>

                            {{-- Password --}}
                            <div class="space-y-2">
                                <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-semibold" />
                                <x-text-input id="password" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="password" name="password" placeholder="Masukkan password" />
                                <x-input-error :messages="$errors->get('password')" />
                            </div>

                            {{-- Konfirmasi Password --}}
                            <div class="space-y-2">
                                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-gray-700 font-semibold" />
                                <x-text-input id="password_confirmation" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="password" name="password_confirmation" placeholder="Ulangi password" />
                                <x-input-error :messages="$errors->get('password_confirmation')" />
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-2">
                                <x-input-label for="no_hp" :value="__('No Handphone')" class="text-gray-700 font-semibold" />
                                <x-text-input id="no_hp" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="no_hp" :value="old('no_hp')" placeholder="0812xxxx" />
                                <x-input-error :messages="$errors->get('no_hp')" />
                            </div>

                            {{-- Gudang --}}
                            <div class="space-y-2">
                                <x-input-label for="gudang_id" :value="__('Lokasi Gudang')" class="text-gray-700 font-semibold" />
                                <select id="gudang_id" name="gudang_id" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm bg-gray-50/50 transition-all cursor-pointer">
                                    <option value="">-- Pilih Gudang (Opsional) --</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ old('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama }} {{ $gudang->kode ? '('.$gudang->kode.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('gudang_id')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-10 pt-6 border-t border-gray-100 gap-4">
                            <a href="{{ route('admin.pics.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Batal</a>
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 text-sm font-bold text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-xl shadow-md hover:shadow-lg hover:from-blue-700 hover:to-cyan-600 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Simpan PIC
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
