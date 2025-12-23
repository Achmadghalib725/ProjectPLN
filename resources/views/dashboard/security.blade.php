<div class="max-w-lg mx-auto" x-data="{
    nomor: '',
    searching: false,
    error: null,
    success: null
}">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 bg-green-500 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>   
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-500 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 bg-yellow-500 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('warning') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-pln-primary to-pln-light rounded-full shadow-lg mb-4">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Konfirmasi Penerimaan Surat Jalan</h1>
 
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 text-center border-l-4 border-green-500">
            <p class="text-3xl font-bold text-green-600">{{ $stats['diterima_hari_ini'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Diterima Hari Ini</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center border-l-4 border-yellow-500">
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['menunggu'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Menunggu Konfirmasi</p>
        </div>
    </div>

    {{-- Search Card --}}
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        {{-- QR Placeholder --}}
        <div class="bg-gradient-to-br from-pln-primary to-pln-light p-6 text-center text-white">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <p class="text-sm text-white/80">Scan QR Code</p>
        </div>

        {{-- Divider --}}
        <div class="relative py-4">
            <div class="absolute inset-0 flex items-center px-6">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white px-4 text-sm text-gray-500">atau input manual</span>
            </div>
        </div>

        {{-- Manual Input Form --}}
        <div class="p-6 pt-0">
            <form action="{{ route('security.search') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="nomor" class="block text-sm font-semibold text-gray-700 mb-2">Nomor Surat Jalan</label>
                    <input type="text"
                           name="nomor"
                           id="nomor"
                           x-model="nomor"
                           placeholder="Contoh: SJ-20241219-001"
                           required
                           autofocus
                           class="w-full px-4 py-4 text-lg rounded-xl border-2 border-gray-200 focus:border-pln-primary focus:ring focus:ring-pln-primary/20 transition">
                </div>
                <button type="submit"
                        :disabled="!nomor || searching"
                        class="w-full bg-pln-primary hover:bg-pln-light disabled:bg-gray-300 text-white font-bold py-4 rounded-xl transition flex items-center justify-center gap-2 text-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari Surat Jalan
                </button>
            </form>
        </div>
    </div>

    {{-- Help Text --}}
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-400">
            Masukkan nomor surat jalan untuk mencari dan mengkonfirmasi penerimaan barang.
        </p>
    </div>
</div>
