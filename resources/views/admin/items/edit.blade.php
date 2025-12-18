<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Header Section dengan Visual Inisial --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                        {{ strtoupper(substr($item->satuan ?? 'ITM', 0, 3)) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                            Edit Profil Item
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Mengubah spesifikasi: <span class="font-semibold text-cyan-600">{{ $item->nama }}</span></p>
                    </div>
                </div>
                <a href="{{ route('admin.items.index') }}" 
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
                    {{-- Error & Session Messages --}}
                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-xl">
                            <div class="flex">
                                <div class="flex-shrink-0 text-red-400">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-bold text-red-800 uppercase tracking-wide">Validasi Gagal</h3>
                                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.items.update', $item->id) }}" enctype="multipart/form-data" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Kode Item --}}
                            <div class="space-y-2">
                                <x-input-label for="kode" :value="'Kode Item *'" class="text-gray-700 font-semibold" />
                                <x-text-input id="kode" name="kode" type="text" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" :value="old('kode', $item->kode)" required />
                                <p class="text-[10px] text-gray-400 italic">Kode unik identifikasi sistem</p>
                            </div>

                            {{-- Nama Item --}}
                            <div class="space-y-2">
                                <x-input-label for="nama" :value="'Nama Item *'" class="text-gray-700 font-semibold" />
                                <x-text-input id="nama" name="nama" type="text" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" :value="old('nama', $item->nama)" required />
                            </div>

                            {{-- Kategori --}}
                            <div class="space-y-2">
                                <x-input-label for="kategori" :value="'Kategori *'" class="text-gray-700 font-semibold" />
                                <x-text-input id="kategori" name="kategori" type="text" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" :value="old('kategori', $item->kategori)" required />
                            </div>

                            {{-- Satuan --}}
                            <div class="space-y-2">
                                <x-input-label for="satuan" :value="'Satuan *'" class="text-gray-700 font-semibold" />
                                <x-text-input id="satuan" name="satuan" type="text" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" :value="old('satuan', $item->satuan)" required />
                            </div>

                            {{-- Deskripsi --}}
                            <div class="col-span-1 md:col-span-2 space-y-2">
                                <x-input-label for="deskripsi" :value="'Deskripsi (Opsional)'" class="text-gray-700 font-semibold" />
                                <textarea id="deskripsi" name="deskripsi" rows="3" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl shadow-sm transition-all">{{ old('deskripsi', $item->deskripsi) }}</textarea>
                            </div>

                            {{-- Bagian Gambar --}}
                            <div class="col-span-1 md:col-span-2 space-y-4 pt-4 border-t border-gray-50">
                                <x-input-label for="gambar" :value="'Media & Gambar Item'" class="text-gray-700 font-semibold" />
                                
                                <div class="flex flex-col md:flex-row gap-8 items-start">
                                    {{-- Current Image --}}
                                    @if($item->gambar_path)
                                        <div class="relative group">
                                            <p class="text-xs font-bold text-gray-400 mb-2 uppercase tracking-widest">Gambar Saat Ini</p>
                                            <div class="overflow-hidden rounded-2xl border-4 border-gray-50 shadow-md">
                                                <img src="{{ asset('storage/' . $item->gambar_path) }}"
                                                     alt="{{ $item->nama }}"
                                                     class="h-40 w-40 object-cover group-hover:scale-110 transition-transform duration-500"
                                                     id="currentImage">
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Upload New & Preview --}}
                                    <div class="flex-grow space-y-3">
                                        <p class="text-xs font-bold text-gray-400 mb-2 uppercase tracking-widest">Upload Baru</p>
                                        <input type="file" id="gambar" name="gambar" accept="image/*"
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100 cursor-pointer transition-all"
                                               onchange="previewImage(event)" />
                                        <p class="text-[10px] text-gray-400">Format: JPG, PNG. Maksimal 2MB. Pilih file untuk mengganti gambar lama.</p>
                                        
                                        <div id="imagePreview" class="mt-4 hidden animate-pulse">
                                            <div class="inline-block p-2 bg-white rounded-2xl border-2 border-dashed border-cyan-200">
                                                <img id="preview" src="" alt="Preview" class="h-32 w-32 object-cover rounded-xl">
                                            </div>
                                            <p class="text-[10px] text-cyan-600 font-bold mt-1">Preview Gambar Baru</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end mt-10 pt-6 border-t border-gray-100 gap-4">
                            <button type="button" onclick="window.location='{{ route('admin.items.index') }}'" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 text-sm font-bold text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-xl shadow-md hover:shadow-lg hover:from-blue-700 hover:to-cyan-600 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Update Data Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Preview Tetap Dipertahankan --}}
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                    const currentImage = document.getElementById('currentImage');
                    if (currentImage) {
                        currentImage.classList.add('opacity-40', 'grayscale');
                    }
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</x-app-layout>