<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
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
                <div class="mb-4 sm:mb-6 bg-red-50 border border-red-200 text-red-900 px-4 py-3 rounded-xl text-sm sm:text-base">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header Card --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Surat Jalan</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $suratJalan->nomor }}</p>
                        </div>
                        <div class="flex justify-center sm:justify-end">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 active:scale-95 text-gray-700 font-medium py-2.5 px-4 rounded-lg transition duration-150 text-sm sm:text-base">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Progress Status --}}
            @php
                $tipe = strtoupper($suratJalan->tipe ?? '');
                $suratStatus = strtoupper($suratJalan->status ?? 'DRAFT');
                $isRejected = $suratStatus === 'DITOLAK';
                $isPeminjaman = in_array($tipe, ['PEMINJAMAN', 'PENGEMBALIAN']);

                // Helper untuk format waktu
                $formatWaktu = fn($waktu) => $waktu ? \Carbon\Carbon::parse($waktu)->format('d M Y, H:i') : null;

                if ($tipe === 'TRANSFER') {
                    // TRANSFER: Dikirim -> Diperiksa -> Selesai
                    $sjKirim = $suratJalan;
                    $steps = [
                        [
                            'label' => 'Dikirim',
                            'desc' => 'Barang dikirim',
                            'detail' => $sjKirim->status !== 'DRAFT'
                                ? "Dikirim dari <strong>{$sjKirim->gudangAsal->nama}</strong> ke <strong>{$sjKirim->gudangTujuan->nama}</strong>"
                                : null,
                            'time' => $sjKirim->status !== 'DRAFT' ? $formatWaktu($sjKirim->updated_at) : null,
                            'by' => $sjKirim->status !== 'DRAFT' ? $sjKirim->pembuat?->name : null,
                        ],
                        [
                            'label' => 'Diperiksa',
                            'desc' => 'Security memeriksa',
                            'detail' => in_array($sjKirim->status, ['DIPERIKSA', 'SELESAI'])
                                ? "Diperiksa oleh Security di <strong>{$sjKirim->gudangTujuan->nama}</strong>"
                                : null,
                            'time' => in_array($sjKirim->status, ['DIPERIKSA', 'SELESAI']) ? $formatWaktu($sjKirim->updated_at) : null,
                            'by' => null,
                        ],
                        [
                            'label' => 'Selesai',
                            'desc' => 'Transfer selesai',
                            'detail' => $sjKirim->status === 'SELESAI'
                                ? "Diterima di <strong>{$sjKirim->gudangTujuan->nama}</strong>"
                                : null,
                            'time' => $sjKirim->status === 'SELESAI' ? $formatWaktu($sjKirim->updated_at) : null,
                            'by' => null,
                        ],
                    ];
                    $statusIndexMap = [
                        'DRAFT' => -1,
                        'DIKIRIM' => 0,
                        'DIPERIKSA' => 1,
                        'DITERIMA' => 2,
                        'SELESAI' => 2,
                        'DITOLAK' => -2,
                    ];
                    $currentStep = $statusIndexMap[$suratStatus] ?? -1;
                } else {
                    // PEMINJAMAN/PENGEMBALIAN: Alur lengkap sinkronisasi
                    $sjKirim = $peminjaman?->suratJalanKirim;
                    $sjKembali = $peminjaman?->suratJalanKembali;
                    $gudangPemilik = $peminjaman?->gudangPemilik;
                    $gudangPeminjam = $peminjaman?->gudangPeminjam;

                    $steps = [
                        [
                            'label' => 'Dikirim',
                            'desc' => 'Barang dikirim ke peminjam',
                            'detail' => $sjKirim && $sjKirim->status !== 'DRAFT'
                                ? "Dikirim dari <strong>{$gudangPemilik?->nama}</strong> ke <strong>{$gudangPeminjam?->nama}</strong>"
                                : null,
                            'time' => $peminjaman?->waktu_kirim ? $formatWaktu($peminjaman->waktu_kirim) : null,
                            'by' => $sjKirim?->pembuat?->name,
                        ],
                        [
                            'label' => 'Diperiksa',
                            'desc' => 'Security gudang tujuan',
                            'detail' => $sjKirim && in_array($sjKirim->status, ['DIPERIKSA', 'DITERIMA', 'SELESAI'])
                                ? "Diperiksa oleh Security di <strong>{$gudangPeminjam?->nama}</strong>"
                                : null,
                            'time' => $sjKirim && in_array($sjKirim->status, ['DIPERIKSA', 'DITERIMA', 'SELESAI'])
                                ? $formatWaktu($sjKirim->updated_at) : null,
                            'by' => null,
                        ],
                        [
                            'label' => 'Diterima',
                            'desc' => 'Operator menerima barang',
                            'detail' => $peminjaman && in_array($peminjaman->status, ['DITERIMA', 'DIKEMBALIKAN', 'SELESAI'])
                                ? "Diterima di <strong>{$gudangPeminjam?->nama}</strong>"
                                : null,
                            'time' => $peminjaman?->waktu_diterima ? $formatWaktu($peminjaman->waktu_diterima) : null,
                            'by' => null,
                        ],
                        [
                            'label' => 'Dikembalikan',
                            'desc' => 'Barang dikembalikan',
                            'detail' => $sjKembali && in_array($sjKembali->status, ['DIKEMBALIKAN', 'DIPERIKSA', 'SELESAI'])
                                ? "Dikembalikan dari <strong>{$gudangPeminjam?->nama}</strong> ke <strong>{$gudangPemilik?->nama}</strong>"
                                : null,
                            'time' => $peminjaman?->waktu_pengembalian ? $formatWaktu($peminjaman->waktu_pengembalian) : null,
                            'by' => $sjKembali?->pembuat?->name,
                        ],
                        [
                            'label' => 'Diperiksa',
                            'desc' => 'Security gudang pemilik',
                            'detail' => $sjKembali && in_array($sjKembali->status, ['DIPERIKSA', 'SELESAI'])
                                ? "Diperiksa oleh Security di <strong>{$gudangPemilik?->nama}</strong>"
                                : null,
                            'time' => $sjKembali && in_array($sjKembali->status, ['DIPERIKSA', 'SELESAI'])
                                ? $formatWaktu($sjKembali->updated_at) : null,
                            'by' => null,
                        ],
                        [
                            'label' => 'Selesai',
                            'desc' => 'Peminjaman selesai',
                            'detail' => $peminjaman && $peminjaman->status === 'SELESAI'
                                ? "Barang telah dikembalikan ke <strong>{$gudangPemilik?->nama}</strong>"
                                : null,
                            'time' => $peminjaman?->waktu_selesai ? $formatWaktu($peminjaman->waktu_selesai) : null,
                            'by' => null,
                        ],
                    ];

                    // Tentukan current step berdasarkan status
                    $peminjamanStatus = $peminjaman?->status ?? 'DIAJUKAN';
                    $sjKirimStatus = $sjKirim?->status ?? 'DRAFT';
                    $sjKembaliStatus = $sjKembali?->status ?? null;

                    // Map status ke step (step yang SEDANG aktif, bukan yang sudah selesai)
                    if ($peminjamanStatus === 'SELESAI' || $sjKembaliStatus === 'SELESAI') {
                        $currentStep = 6; // Semua selesai (di luar range = semua hijau)
                    } elseif ($sjKembaliStatus === 'DIPERIKSA') {
                        $currentStep = 5; // Sedang di step Selesai (menunggu operator approve)
                    } elseif ($sjKembaliStatus === 'DIKEMBALIKAN' || $peminjamanStatus === 'DIKEMBALIKAN') {
                        $currentStep = 4; // Sedang di step Diperiksa pengembalian (menunggu security)
                    } elseif ($peminjamanStatus === 'DITERIMA' || $sjKirimStatus === 'DITERIMA') {
                        $currentStep = 3; // Sedang di step Dikembalikan (menunggu pengembalian)
                    } elseif ($sjKirimStatus === 'DIPERIKSA' || $peminjamanStatus === 'DIPERIKSA') {
                        $currentStep = 2; // Sedang di step Diterima (menunggu operator approve)
                    } elseif ($sjKirimStatus === 'DIKIRIM' || $peminjamanStatus === 'DIKIRIM') {
                        $currentStep = 1; // Sedang di step Diperiksa (menunggu security)
                    } else {
                        $currentStep = 0; // Belum dikirim
                    }

                    // Handle rejection
                    if ($isRejected || $peminjaman?->status === 'DITOLAK') {
                        $isRejected = true;
                    }
                }

                $maxStep = count($steps) - 1;
            @endphp

            {{-- Riwayat Status - Only show if not DRAFT --}}
            @if($suratStatus !== 'DRAFT')
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6" x-data="{ showDetail: false }">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">Riwayat Status</h3>
                        <button @click="showDetail = !showDetail"
                                class="text-xs sm:text-sm text-pln-primary hover:text-pln-primary/80 font-medium flex items-center gap-1 transition active:scale-95">
                            <span x-text="showDetail ? 'Sembunyikan' : 'Lihat Detail'"></span>
                            <svg class="w-4 h-4 transition-transform" :class="showDetail ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    @if($isRejected)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6">
                            <div class="flex items-center gap-2 text-red-700 text-sm sm:text-base">
                                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Surat Jalan Ditolak</span>
                            </div>
                        </div>
                    @endif

                    {{-- Horizontal Progress Bar - Scrollable on Mobile --}}
                    <div class="overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
                        <div class="relative min-w-[500px] sm:min-w-0">
                            <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 rounded-full"></div>
                            <div class="absolute top-5 left-0 h-1 bg-green-500 rounded-full transition-all duration-500"
                                 style="width: {{ $currentStep > 0 ? min((($currentStep - 1) / $maxStep) * 100, 100) : 0 }}%"></div>

                            <div class="relative flex justify-between">
                                @foreach($steps as $index => $step)
                                    @php
                                        $isCompleted = $currentStep > $index;
                                        $isActive = $currentStep === $index;
                                        $isPending = $currentStep < $index;

                                        if ($isCompleted) {
                                            $circleClass = 'bg-green-500 text-white border-green-500';
                                            $labelClass = 'text-green-700 font-semibold';
                                        } elseif ($isActive) {
                                            $circleClass = 'bg-pln-primary text-white border-pln-primary ring-4 ring-pln-primary/20';
                                            $labelClass = 'text-pln-primary font-bold';
                                        } else {
                                            $circleClass = 'bg-white text-gray-400 border-gray-300';
                                            $labelClass = 'text-gray-400';
                                        }
                                    @endphp
                                    <div class="flex flex-col items-center" style="width: {{ 100 / count($steps) }}%">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 flex items-center justify-center text-xs sm:text-sm font-bold {{ $circleClass }} z-10">
                                            @if($isCompleted)
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        <span class="mt-2 text-[10px] sm:text-xs text-center {{ $labelClass }} leading-tight">{{ $step['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Scroll Hint for Mobile --}}
                    <p class="text-[10px] text-gray-400 text-center mt-2 sm:hidden">Geser untuk melihat semua status</p>

                    {{-- Timeline Detail (Collapsible) --}}
                    <div x-show="showDetail"
                         x-collapse
                         x-cloak
                         class="border-t mt-4 sm:mt-6 pt-4 sm:pt-6">
                        <div class="space-y-3 sm:space-y-4">
                            @foreach($steps as $index => $step)
                                @php
                                    $isCompleted = $currentStep > $index;
                                    $isActive = $currentStep === $index;
                                    $hasDetail = !empty($step['detail']) || !empty($step['time']);
                                @endphp
                                <div class="flex gap-3 sm:gap-4 {{ !$isCompleted && !$isActive ? 'opacity-40' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full {{ $isCompleted ? 'bg-green-500' : ($isActive ? 'bg-pln-primary ring-4 ring-pln-primary/20' : 'bg-gray-300') }}"></div>
                                        @if($index < count($steps) - 1)
                                            <div class="w-0.5 h-full min-h-[36px] sm:min-h-[40px] {{ $isCompleted ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 pb-3 sm:pb-4">
                                        <div class="flex flex-wrap items-center gap-1 sm:gap-2">
                                            <span class="font-semibold text-sm sm:text-base {{ $isCompleted ? 'text-green-700' : ($isActive ? 'text-pln-primary' : 'text-gray-500') }}">
                                                {{ $step['label'] }}
                                            </span>
                                            @if($isCompleted)
                                                <span class="text-[10px] sm:text-xs bg-green-100 text-green-700 px-1.5 sm:px-2 py-0.5 rounded-full">Selesai</span>
                                            @elseif($isActive)
                                                <span class="text-[10px] sm:text-xs bg-blue-100 text-blue-700 px-1.5 sm:px-2 py-0.5 rounded-full animate-pulse">Proses</span>
                                            @endif
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-500">{{ $step['desc'] }}</p>
                                        @if($hasDetail)
                                            <div class="mt-2 text-xs sm:text-sm bg-gray-50 rounded-lg p-2 sm:p-3">
                                                @if($step['detail'])
                                                    <p class="text-gray-700">{!! $step['detail'] !!}</p>
                                                @endif
                                                @if($step['time'])
                                                    <p class="text-gray-500 mt-1 flex flex-wrap items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        <span>{{ $step['time'] }}</span>
                                                        @if($step['by'])
                                                            <span>oleh <strong>{{ $step['by'] }}</strong></span>
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        @elseif($isActive)
                                            <div class="mt-2 text-xs sm:text-sm bg-blue-50 rounded-lg p-2 sm:p-3 text-blue-700">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Menunggu proses...
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Info Cards --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        {{-- Informasi Pengiriman --}}
                        <div>
                            <h3 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Informasi Pengiriman</h3>
                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                <div class="col-span-2 sm:col-span-1">
                                    <dt class="text-xs sm:text-sm text-gray-500">Tipe</dt>
                                    <dd class="mt-1">
                                        @php
                                            $tipeBadge = match($suratJalan->tipe) {
                                                'TRANSFER' => 'bg-blue-100 text-blue-800',
                                                'PEMINJAMAN' => 'bg-purple-100 text-purple-800',
                                                'PENGEMBALIAN' => 'bg-orange-100 text-orange-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs sm:text-sm font-semibold rounded-full {{ $tipeBadge }}">
                                            {{ $suratJalan->tipe }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <dt class="text-xs sm:text-sm text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        @php
                                            $statusBadge = match($suratJalan->status) {
                                                'DRAFT' => 'bg-gray-100 text-gray-800',
                                                'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                                'DIPERIKSA' => 'bg-yellow-100 text-yellow-800',
                                                'DITERIMA' => 'bg-green-100 text-green-800',
                                                'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                                                'SELESAI' => 'bg-green-100 text-green-800',
                                                'DITOLAK' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs sm:text-sm font-semibold rounded-full {{ $statusBadge }}">
                                            {{ $suratJalan->status }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs sm:text-sm text-gray-500">Tanggal Kirim</dt>
                                    <dd class="mt-1 text-sm sm:text-base text-gray-900 font-medium">{{ \Carbon\Carbon::parse($suratJalan->tanggal)->format('d M Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs sm:text-sm text-gray-500">Nama Driver</dt>
                                    <dd class="mt-1 text-sm sm:text-base text-gray-900 font-medium">{{ $suratJalan->nama_driver ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs sm:text-sm text-gray-500">Jenis Kendaraan</dt>
                                    <dd class="mt-1 text-sm sm:text-base text-gray-900 font-medium">{{ $suratJalan->jenis_kendaraan ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs sm:text-sm text-gray-500">Nomor Plat</dt>
                                    <dd class="mt-1 text-sm sm:text-base text-gray-900 font-medium">{{ $suratJalan->nomor_plat ?? '-' }}</dd>
                                </div>
                                @if($suratJalan->catatan)
                                <div class="col-span-2">
                                    <dt class="text-xs sm:text-sm text-gray-500">Catatan</dt>
                                    <dd class="mt-1 text-sm sm:text-base text-gray-900">{{ $suratJalan->catatan }}</dd>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Gudang Info --}}
                        <div class="border-t pt-4 sm:pt-6">
                            <h3 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Rute Pengiriman</h3>
                            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4">
                                <div class="flex-1 w-full bg-blue-50 rounded-xl p-3 sm:p-4 text-center">
                                    <p class="text-[10px] sm:text-xs text-blue-600 uppercase font-medium">Asal</p>
                                    <p class="text-sm sm:text-base font-bold text-blue-900 mt-1">{{ $suratJalan->gudangAsal->nama ?? '-' }}</p>
                                </div>
                                <div class="flex-shrink-0 rotate-90 sm:rotate-0">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </div>
                                <div class="flex-1 w-full bg-green-50 rounded-xl p-3 sm:p-4 text-center">
                                    <p class="text-[10px] sm:text-xs text-green-600 uppercase font-medium">Tujuan</p>
                                    <p class="text-sm sm:text-base font-bold text-green-900 mt-1">{{ $suratJalan->gudangTujuan->nama ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Daftar Item</h3>
                </div>

                {{-- Mobile Cards View --}}
                <div class="sm:hidden divide-y divide-gray-100">
                    @forelse($suratJalan->items as $item)
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 text-sm">{{ $item->item->nama ?? 'Item' }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item->item->kode ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-pln-primary/10 text-pln-primary">
                                        {{ $item->jumlah }} unit
                                    </span>
                                </div>
                            </div>
                            @if($item->keterangan)
                                <p class="text-xs text-gray-500 mt-2 bg-gray-50 rounded-lg p-2">{{ $item->keterangan }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 text-sm">
                            Belum ada item.
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($suratJalan->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->item->kode ?? '-' }} - {{ $item->item->nama ?? 'Item' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->jumlah }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada item.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Action Buttons --}}
            @if(in_array($suratJalan->status, ['DIKIRIM', 'DIKEMBALIKAN']))
                <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4">Konfirmasi Pemeriksaan</h3>
                        <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
                            {{-- Terima Button --}}
                            <form action="{{ route('security.terima', $suratJalan->id) }}" method="POST" class="flex-1"
                                  x-data="{ submitting: false }"
                                  @submit="submitting = true">
                                @csrf
                                <button type="submit"
                                        :disabled="submitting"
                                        class="w-full px-4 sm:px-6 py-4 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold text-base sm:text-lg rounded-xl shadow-sm transition duration-150 flex items-center justify-center gap-2 sm:gap-3 active:scale-[0.98]">
                                    <svg x-show="!submitting" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <svg x-show="submitting" class="animate-spin w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Diperiksa'"></span>
                                </button>
                            </form>

                            {{-- Tolak Button --}}
                            <div x-data="{ showModal: false }" class="sm:w-auto">
                                <button type="button"
                                        @click="showModal = true"
                                        class="w-full px-4 sm:px-6 py-4 bg-red-600 hover:bg-red-700 text-white font-bold text-base sm:text-lg rounded-xl shadow-sm transition duration-150 flex items-center justify-center gap-2 sm:gap-3 active:scale-[0.98]">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Tolak
                                </button>

                                {{-- Reject Modal --}}
                                <div x-show="showModal"
                                     x-cloak
                                     class="fixed inset-0 z-50 overflow-y-auto"
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0">
                                    <div class="flex items-end sm:items-center justify-center min-h-screen px-4 pb-20 sm:pb-0">
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 z-0" @click="showModal = false"></div>
                                        <div class="relative bg-white rounded-t-2xl sm:rounded-xl shadow-xl w-full max-w-md p-4 sm:p-6 z-10">
                                            {{-- Modal Handle for Mobile --}}
                                            <div class="sm:hidden w-12 h-1 bg-gray-300 rounded-full mx-auto mb-4"></div>

                                            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4">Tolak Surat Jalan</h3>
                                            <form action="{{ route('security.tolak', $suratJalan->id) }}" method="POST"
                                                  x-data="{ submitting: false }"
                                                  @submit="submitting = true">
                                                @csrf
                                                <div class="mb-4">
                                                    <label for="alasan" class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                                                    <textarea name="alasan"
                                                              id="alasan"
                                                              rows="3"
                                                              required
                                                              class="w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 text-sm sm:text-base"
                                                              placeholder="Masukkan alasan penolakan..."></textarea>
                                                </div>
                                                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                                                    <button type="button"
                                                            @click="showModal = false"
                                                            class="w-full sm:w-auto px-4 py-3 sm:py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl active:scale-[0.98] transition">
                                                        Batal
                                                    </button>
                                                    <button type="submit"
                                                            :disabled="submitting"
                                                            class="w-full sm:w-auto px-4 py-3 sm:py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-medium rounded-xl flex items-center justify-center gap-2 active:scale-[0.98] transition">
                                                        <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Tolak'"></span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DIPERIKSA')
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-green-100 text-green-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base text-center">Surat Jalan sudah DIPERIKSA - Menunggu konfirmasi operator</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITERIMA')
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-green-100 text-green-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base">Surat Jalan sudah DITERIMA oleh operator</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITOLAK')
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-red-100 text-red-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base">Surat Jalan telah DITOLAK</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DRAFT')
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-gray-100 text-gray-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base">Surat Jalan masih DRAFT, belum bisa dikonfirmasi</span>
                    </div>
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-blue-100 text-blue-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base">Surat Jalan berstatus {{ $suratJalan->status }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>
