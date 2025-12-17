<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('admin.items.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Master Data
                </a>
                <h2 class="text-2xl font-bold text-gray-900 mt-2">Tambah Item Baru</h2>
                <p class="text-sm text-gray-600 mt-1">Tambahkan item ke master data barang</p>
            </div>

            {{-- Error Messages --}}
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Ada {{ $errors->count() }} error dalam form:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Info Box --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Item yang ditambahkan akan masuk ke master data dan bisa digunakan oleh semua gudang.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.items.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- Kode Item --}}
                        <div>
                            <x-input-label for="kode" :value="'Kode Item *'" />
                            <x-text-input id="kode" name="kode" type="text"
                                          class="mt-1 block w-full" :value="old('kode')" required
                                          placeholder="Contoh: KBL-001" />
                            <x-input-error :messages="$errors->get('kode')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Kode unik untuk identifikasi item</p>
                        </div>

                        {{-- Nama Item --}}
                        <div>
                            <x-input-label for="nama" :value="'Nama Item *'" />
                            <x-text-input id="nama" name="nama" type="text"
                                          class="mt-1 block w-full" :value="old('nama')" required
                                          placeholder="Masukkan nama item" />
                            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <x-input-label for="kategori" :value="'Kategori *'" />
                            <x-text-input id="kategori" name="kategori" type="text"
                                          class="mt-1 block w-full" :value="old('kategori')" required
                                          placeholder="Contoh: Kabel, Trafo, Proteksi, dll" />
                            <x-input-error :messages="$errors->get('kategori')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Kategori untuk pengelompokan item</p>
                        </div>

                        {{-- Satuan --}}
                        <div>
                            <x-input-label for="satuan" :value="'Satuan *'" />
                            <x-text-input id="satuan" name="satuan" type="text"
                                          class="mt-1 block w-full" :value="old('satuan')" required
                                          placeholder="Contoh: unit, roll, batang, pcs" />
                            <x-input-error :messages="$errors->get('satuan')" class="mt-2" />
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <x-input-label for="deskripsi" :value="'Deskripsi (Opsional)'" />
                            <textarea id="deskripsi" name="deskripsi" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50"
                                      placeholder="Deskripsi detail tentang item...">{{ old('deskripsi') }}</textarea>
                            <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                        </div>

                        {{-- Gambar --}}
                        <div>
                            <x-input-label for="gambar" :value="'Gambar Item (Opsional)'" />
                            <input type="file"
                                   id="gambar"
                                   name="gambar"
                                   accept="image/jpeg,image/png,image/jpg"
                                   class="mt-1 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-[#035b71] file:text-white
                                          hover:file:bg-[#00aff0]
                                          cursor-pointer"
                                   onchange="previewImage(event)" />
                            <x-input-error :messages="$errors->get('gambar')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG. Maksimal 2MB</p>

                            {{-- Image Preview --}}
                            <div id="imagePreview" class="mt-3 hidden">
                                <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                                <img id="preview" src="" alt="Preview" class="max-w-xs rounded-lg shadow-md">
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-4 pt-4">
                            <x-secondary-button type="button" onclick="window.location='{{ route('admin.items.index') }}'">
                                Batal
                            </x-secondary-button>
                            <x-primary-button class="bg-[#035b71] hover:bg-[#00aff0]">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Item
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript for Image Preview --}}
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</x-app-layout>
