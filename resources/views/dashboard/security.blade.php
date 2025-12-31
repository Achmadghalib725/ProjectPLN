{{-- Load QR Scanner Library --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<div class="w-full max-w-lg lg:max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{
    nomor: '',
    searching: false,
    scannerActive: false,
    scanner: null,

    async startScanner() {
        if (this.scannerActive) return;

        try {
            this.scanner = new Html5Qrcode('qr-reader');
            this.scannerActive = true;

            await this.scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => {
                    this.stopScanner();
                    window.location.href = decodedText;
                },
                (errorMessage) => {}
            );
        } catch (err) {
            console.error('Scanner error:', err);
            this.scannerActive = false;
            alert('Gagal mengakses kamera. Pastikan Anda memberikan izin kamera.');
        }
    },

    async stopScanner() {
        if (this.scanner && this.scannerActive) {
            try {
                await this.scanner.stop();
                this.scanner.clear();
            } catch (e) {}
            this.scannerActive = false;
        }
    }
}">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-500 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-500 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-4 bg-yellow-500 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('warning') }}</span>
        </div>
    @endif

    {{-- Header - Mobile Optimized --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-pln-primary to-pln-light rounded-full shadow-lg mb-3">
            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Konfirmasi Surat Jalan</h1>
        <p class="text-sm text-gray-500 mt-1">Scan QR atau input nomor manual</p>
    </div>

    {{-- Stats Cards - Mobile Grid --}}
    <div class="grid grid-cols-2 gap-3 mb-5">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-green-500">
            <p class="text-2xl sm:text-3xl font-bold text-green-600">{{ $stats['diterima_hari_ini'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Diterima Hari Ini</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-yellow-500">
            <p class="text-2xl sm:text-3xl font-bold text-yellow-600">{{ $stats['menunggu'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Menunggu</p>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        {{-- QR Scanner Section --}}
        <div class="bg-gradient-to-br from-pln-primary to-pln-light p-5 sm:p-6 text-center text-white">
            {{-- Scanner Idle --}}
            <div x-show="!scannerActive" class="flex flex-col items-center space-y-4">
                <div class="flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 bg-white/20 rounded-2xl">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <button @click="startScanner()"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 bg-white text-pln-primary font-bold px-6 py-3.5 rounded-xl hover:bg-gray-100 active:scale-95 transition shadow-lg text-sm sm:text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Buka Kamera & Scan QR
                </button>
                <p class="text-xs sm:text-sm text-white/80">Arahkan kamera ke QR Code surat jalan</p>
            </div>

            {{-- Active Scanner --}}
            <div x-show="scannerActive" x-cloak class="space-y-4">
                <div id="qr-reader" class="mx-auto rounded-xl overflow-hidden bg-black" style="width: 100%; max-width: 280px;"></div>
                <div class="flex items-center justify-center gap-2 text-white/90">
                    <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    <span class="text-sm">Memindai QR Code...</span>
                </div>
                <button @click="stopScanner()"
                        type="button"
                        class="inline-flex items-center gap-2 bg-red-500/90 text-white font-medium px-5 py-2.5 rounded-lg hover:bg-red-600 active:scale-95 transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Tutup Kamera
                </button>
            </div>
        </div>

        {{-- Divider --}}
        <div class="relative py-4">
            <div class="absolute inset-0 flex items-center px-6">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white px-4 text-xs sm:text-sm text-gray-500">atau input manual</span>
            </div>
        </div>

        {{-- Manual Input Form --}}
        <div class="p-5 sm:p-6 pt-0">
            <form action="{{ route('security.search') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="nomor" class="block text-sm font-semibold text-gray-700 mb-2">Nomor Surat Jalan</label>
                    <input type="text"
                           name="nomor"
                           id="nomor"
                           x-model="nomor"
                           placeholder="Contoh: 123/F2206040/YYYY"
                           required
                           class="w-full px-4 py-3.5 text-base rounded-xl border-2 border-gray-200 focus:border-pln-primary focus:ring focus:ring-pln-primary/20 transition">
                </div>
                <button type="submit"
                        :disabled="!nomor || searching"
                        class="w-full bg-pln-primary hover:bg-pln-light disabled:bg-gray-300 text-white font-bold py-3.5 rounded-xl transition active:scale-[0.98] flex items-center justify-center gap-2 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari Surat Jalan
                </button>
            </form>
        </div>
    </div>

    {{-- Help Text --}}
    <div class="mt-5 text-center">
        <p class="text-xs text-gray-400">
            Scan QR Code atau input nomor untuk konfirmasi penerimaan barang.
        </p>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    /* Custom styles for QR reader */
    #qr-reader video { border-radius: 0.75rem; }
    #qr-reader__scan_region { background: transparent !important; }
    #qr-reader__dashboard { display: none !important; }
</style>
