<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                        {{ substr($pic->nama, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                            Edit Data PIC
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Mengubah informasi untuk PIC: <span class="font-semibold text-cyan-600">{{ $pic->nama }}</span></p>
                    </div>
                </div>
                <a href="{{ route('admin.pics.index') }}"
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
                    <form action="{{ route('admin.pics.update', $pic->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Nama --}}
                            <div class="space-y-2">
                                <x-input-label for="nama" :value="__('Nama Lengkap')" class="text-gray-700 font-semibold" />
                                <x-text-input id="nama" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="nama" :value="old('nama', $pic->nama)" required />
                                <x-input-error :messages="$errors->get('nama')" />
                            </div>

                            {{-- Jabatan --}}
                            <div class="space-y-2">
                                <x-input-label for="jabatan" :value="__('Jabatan')" class="text-gray-700 font-semibold" />
                                <x-text-input id="jabatan" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="jabatan" :value="old('jabatan', $pic->jabatan)" placeholder="Contoh: Supervisor, Manager, dll" />
                                <x-input-error :messages="$errors->get('jabatan')" />
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-2">
                                <x-input-label for="no_hp" :value="__('No Handphone')" class="text-gray-700 font-semibold" />
                                <x-text-input id="no_hp" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="text" name="no_hp" :value="old('no_hp', $pic->no_hp)" placeholder="0812xxxx" />
                                <x-input-error :messages="$errors->get('no_hp')" />
                            </div>

                            {{-- Gudang --}}
                            <div class="space-y-2">
                                <x-input-label for="gudang_id" :value="__('Lokasi Gudang')" class="text-gray-700 font-semibold" />
                                <select id="gudang_id" name="gudang_id" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm bg-gray-50/50 transition-all cursor-pointer">
                                    <option value="">-- Pilih Gudang --</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ old('gudang_id', $pic->gudang_id) == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama }}
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Update Data PIC
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
