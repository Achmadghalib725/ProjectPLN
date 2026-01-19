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
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                        <div class="text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Surat Jalan</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $suratJalan->nomor }}</p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <div class="flex gap-2">
                                <a href="{{ route('security.surat-jalan.preview', $suratJalan->id) }}"
                                   target="_blank"
                                   class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 flex items-center justify-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span class="hidden sm:inline">Preview PDF</span>
                                    <span class="sm:hidden">Preview</span>
                                </a>
                                <a href="{{ route('security.surat-jalan.pdf', $suratJalan->id) }}"
                                   class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 flex items-center justify-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </div>
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center justify-center gap-2 bg-gray-200 hover:bg-gray-300 active:scale-95 text-gray-700 font-medium py-2.5 px-4 rounded-lg transition duration-150 text-sm sm:text-base">
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
                $historyFor = function ($surat) {
                    if (!$surat || !$surat->relationLoaded('statusHistories')) {
                        return collect();
                    }
                    return $surat->statusHistories->groupBy('status');
                };
                $historyTime = function ($historyMap, $statuses) {
                    $statusList = is_array($statuses) ? $statuses : [$statuses];
                    foreach ($statusList as $status) {
                        $entry = $historyMap->get($status)?->last();
                        if ($entry && $entry->occurred_at) {
                            return $entry->occurred_at;
                        }
                    }
                    return null;
                };
                $historyTimeText = function ($historyMap, $statuses, $fallback = null) use ($historyTime, $formatWaktu) {
                    $time = $historyTime($historyMap, $statuses) ?? $fallback;
                    return $formatWaktu($time);
                };
                $historyActor = function ($historyMap, $statuses) {
                    $statusList = is_array($statuses) ? $statuses : [$statuses];
                    foreach ($statusList as $status) {
                        $entry = $historyMap->get($status)?->last();
                        if ($entry?->actor?->name) {
                            return $entry->actor->name;
                        }
                    }
                    return null;
                };

                if ($tipe === 'TRANSFER') {
                    // TRANSFER: Dikirim -> Diperiksa -> Selesai
                    $sjKirim = $suratJalan;
                    $sjKirimHistory = $historyFor($sjKirim);
                    $steps = [
                        [
                            'label' => 'Dikirim',
                            'desc' => 'Barang dikirim',
                            'detail' => !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                ? "Dikirim dari <strong>{$sjKirim->gudangAsal->nama}</strong> ke <strong>{$sjKirim->gudangTujuan->nama}</strong>"
                                : null,
                            'time' => !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                ? $historyTimeText($sjKirimHistory, ['DIKIRIM', 'DIPERIKSA_PENERIMA', 'DIPERIKSA', 'DITERIMA', 'SELESAI'], $sjKirim->waktu_ttd_pembuat ?? $sjKirim->updated_at)
                                : null,
                            'by' => !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                ? $sjKirim->pembuat?->name
                                : null,
                        ],
                        [
                            'label' => 'Diperiksa',
                            'desc' => 'Security memeriksa',
                            'detail' => in_array($sjKirim->status, ['DIPERIKSA_PENERIMA', 'DIPERIKSA', 'DITERIMA', 'SELESAI'])
                                ? "Diperiksa oleh Security di <strong>{$sjKirim->gudangTujuan->nama}</strong>"
                                : null,
                            'time' => in_array($sjKirim->status, ['DIPERIKSA_PENERIMA', 'DIPERIKSA', 'DITERIMA', 'SELESAI'])
                                ? $historyTimeText($sjKirimHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA'], $sjKirim->updated_at)
                                : null,
                            'by' => in_array($sjKirim->status, ['DIPERIKSA_PENERIMA', 'DIPERIKSA', 'DITERIMA', 'SELESAI'])
                                ? $historyActor($sjKirimHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA'])
                                : null,
                        ],
                        [
                            'label' => 'Selesai',
                            'desc' => 'Transfer selesai',
                            'detail' => $sjKirim->status === 'SELESAI'
                                ? "Diterima di <strong>{$sjKirim->gudangTujuan->nama}</strong>"
                                : null,
                            'time' => $sjKirim->status === 'SELESAI'
                                ? $historyTimeText($sjKirimHistory, 'SELESAI', $sjKirim->updated_at)
                                : null,
                            'by' => $sjKirim->status === 'SELESAI'
                                ? $historyActor($sjKirimHistory, 'SELESAI')
                                : null,
                        ],
                    ];
                    $statusIndexMap = [
                        'DRAFT' => 0,
                        'MENUNGGU_PERSETUJUAN' => 0,
                        'DITOLAK_PERSETUJUAN' => 0,
                        'DIKIRIM' => 1,
                        'DIPERIKSA_PENGIRIM' => 1,
                        'DIPERIKSA_PENERIMA' => 2,
                        'DIPERIKSA' => 2,
                        'DITERIMA' => 3,
                        'SELESAI' => 3,
                        'DITOLAK' => -2,
                    ];
                    $currentStep = $statusIndexMap[$suratStatus] ?? 0;
                    if ($suratStatus === 'DIPERIKSA_PENGIRIM') {
                        $currentStep = -1;
                    }
                } else {
                    // PEMINJAMAN/PENGEMBALIAN: Alur lengkap sinkronisasi
                    $sjKirim = $peminjaman?->suratJalanKirim;
                    $sjKembali = $peminjaman?->suratJalanKembali;
                    $gudangPemilik = $peminjaman?->gudangPemilik;
                    $gudangPeminjam = $peminjaman?->gudangPeminjam;
                    $sjKirimHistory = $historyFor($sjKirim);
                    $sjKembaliHistory = $historyFor($sjKembali);
                    $kirimCheckedAt = $historyTime($sjKirimHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA']);

                    $steps = [
                        [
                            'label' => 'Dikirim',
                            'desc' => 'Barang dikirim ke peminjam',
                            'detail' => $sjKirim && !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                ? "Dikirim dari <strong>{$gudangPemilik?->nama}</strong> ke <strong>{$gudangPeminjam?->nama}</strong>"
                                : null,
                            'time' => $historyTimeText($sjKirimHistory, ['DIKIRIM', 'DIPERIKSA_PENERIMA', 'DIPERIKSA', 'DITERIMA', 'SELESAI', 'MENUNGGU_DIKEMBALIKAN', 'DIKEMBALIKAN'], $peminjaman?->waktu_kirim),
                            'by' => $sjKirim?->pembuat?->name,
                        ],
                        [
                            'label' => 'Diperiksa',
                            'desc' => 'Security gudang tujuan',
                            'detail' => $sjKirim && $kirimCheckedAt
                                ? "Diperiksa oleh Security di <strong>{$gudangPeminjam?->nama}</strong>"
                                : null,
                            'time' => $kirimCheckedAt
                                ? $formatWaktu($kirimCheckedAt)
                                : null,
                            'by' => $kirimCheckedAt
                                ? $historyActor($sjKirimHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA'])
                                : null,
                        ],
                        [
                            'label' => 'Diterima',
                            'desc' => 'Operator menerima barang',
                            'detail' => $peminjaman && in_array($peminjaman->status, ['DITERIMA', 'DIKEMBALIKAN', 'SELESAI'])
                                ? "Diterima di <strong>{$gudangPeminjam?->nama}</strong>"
                                : null,
                            'time' => $historyTimeText($sjKirimHistory, 'DITERIMA', $peminjaman?->waktu_diterima),
                            'by' => $historyActor($sjKirimHistory, 'DITERIMA'),
                        ],
                        [
                            'label' => 'Dikembalikan',
                            'desc' => 'Barang dikembalikan',
                            'detail' => $sjKembali && in_array($sjKembali->status, ['DIKEMBALIKAN', 'DIPERIKSA_PENGIRIM', 'DIPERIKSA_PENERIMA', 'DIPERIKSA', 'SELESAI'])
                                ? "Dikembalikan dari <strong>{$gudangPeminjam?->nama}</strong> ke <strong>{$gudangPemilik?->nama}</strong>"
                                : null,
                            'time' => $historyTimeText($sjKembaliHistory, ['DIKEMBALIKAN', 'DIPERIKSA_PENERIMA', 'DIPERIKSA', 'SELESAI'], $peminjaman?->waktu_pengembalian),
                            'by' => $sjKembali?->pembuat?->name,
                        ],
                        [
                            'label' => 'Diperiksa',
                            'desc' => 'Security gudang pemilik',
                            'detail' => $sjKembali && in_array($sjKembali->status, ['DIPERIKSA_PENERIMA', 'DIPERIKSA', 'SELESAI'])
                                ? "Diperiksa oleh Security di <strong>{$gudangPemilik?->nama}</strong>"
                                : null,
                            'time' => $sjKembali && in_array($sjKembali->status, ['DIPERIKSA_PENERIMA', 'DIPERIKSA', 'SELESAI'])
                                ? $historyTimeText($sjKembaliHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA'], $sjKembali->updated_at) : null,
                            'by' => $historyActor($sjKembaliHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA']),
                        ],
                        [
                            'label' => 'Selesai',
                            'desc' => 'Peminjaman selesai',
                            'detail' => $peminjaman && $peminjaman->status === 'SELESAI'
                                ? "Barang telah dikembalikan ke <strong>{$gudangPemilik?->nama}</strong>"
                                : null,
                            'time' => $historyTimeText($sjKembaliHistory, 'SELESAI', $peminjaman?->waktu_selesai),
                            'by' => $historyActor($sjKembaliHistory, 'SELESAI'),
                        ],
                    ];

                    // Tentukan current step berdasarkan status
                    $peminjamanStatus = $peminjaman?->status ?? 'DIAJUKAN';
                    $sjKirimStatus = $sjKirim?->status ?? 'DRAFT';
                    $sjKembaliStatus = $sjKembali?->status ?? null;

                    // Map status ke step (step yang SEDANG aktif, bukan yang sudah selesai)
                    if ($peminjamanStatus === 'SELESAI' || $sjKembaliStatus === 'SELESAI') {
                        $currentStep = 6; // Semua selesai (di luar range = semua hijau)
                    } elseif (in_array($sjKembaliStatus, ['DIPERIKSA_PENERIMA', 'DIPERIKSA'], true)) {
                        $currentStep = 5; // Menunggu operator menerima pengembalian
                    } elseif (in_array($sjKembaliStatus, ['DIKEMBALIKAN', 'DIPERIKSA_PENGIRIM'], true) || $peminjamanStatus === 'DIKEMBALIKAN') {
                        $currentStep = 4; // Menunggu security penerima (gudang pemilik)
                    } elseif ($peminjamanStatus === 'DITERIMA' || $sjKirimStatus === 'DITERIMA') {
                        $currentStep = 3; // Menunggu pengembalian barang
                    } elseif (in_array($sjKirimStatus, ['DIPERIKSA_PENERIMA', 'DIPERIKSA'], true) || $peminjamanStatus === 'DIPERIKSA') {
                        $currentStep = 2; // Menunggu operator menerima
                    } elseif (in_array($sjKirimStatus, ['DIKIRIM', 'DIPERIKSA_PENGIRIM'], true) || $peminjamanStatus === 'DIKIRIM') {
                        $currentStep = 1; // Menunggu security penerima (gudang tujuan)
                    } else {
                        $currentStep = 0; // Belum dikirim
                    }

                    // For PEMINJAMAN with DIPERIKSA_PENGIRIM, reset to -1 (first security check)
                    // For PENGEMBALIAN with DIPERIKSA_PENGIRIM, keep the calculated step (return process)
                    if ($tipe === 'PEMINJAMAN' && $suratStatus === 'DIPERIKSA_PENGIRIM') {
                        $currentStep = -1;
                    }

                }

                if ($isRejected) {
                    $periksaIndexes = collect($steps)
                        ->keys()
                        ->filter(fn ($index) => str_starts_with(($steps[$index]['label'] ?? ''), 'Diperiksa'))
                        ->values();

                    if ($periksaIndexes->count() > 1) {
                        $rejectedStep = $suratJalan->tipe === 'PENGEMBALIAN'
                            ? $periksaIndexes->last()
                            : $periksaIndexes->first();
                    } else {
                        $rejectedStep = $periksaIndexes->first();
                    }

                    $currentStep = $rejectedStep === null ? 0 : $rejectedStep;
                }

                $maxStep = count($steps) - 1;
            @endphp

            @php
                // Determine if we should show blurred overlay instead of progress
                $returnStatus = $peminjaman?->suratJalanKembali?->status;
                $showBlurredOverlay = in_array($suratStatus, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN', 'DITOLAK'], true)
                    || ($tipe === 'PENGEMBALIAN' && $suratStatus === 'DIPERIKSA_PENGIRIM')
                    || ($tipe === 'PEMINJAMAN' && in_array($returnStatus, ['DIPERIKSA_PENGIRIM', 'MENUNGGU_PERSETUJUAN'], true));
            @endphp

            <div id="surat-jalan-progress-container" data-surat-jalan-progress>
            {{-- Riwayat Status --}}
            @if(!$showBlurredOverlay)
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
                        @php
                            $rejectTitle = 'Surat Jalan Ditolak oleh Security';
                            $rejectReason = null;
                            $catatanPenolakan = (string) ($suratJalan->catatan_penolakan ?? '');
                            if ($catatanPenolakan !== '' && preg_match('/\\[DITOLAK_(PENGIRIM|PENERIMA):\\s*([^\\]]+)\\]/', $catatanPenolakan, $matches)) {
                                $rejectStage = strtolower($matches[1] ?? '');
                                $rejectReason = trim($matches[2] ?? '');
                                if ($rejectStage === 'pengirim') {
                                    $rejectTitle = 'Surat Jalan Ditolak oleh Security Pengirim';
                                } elseif ($rejectStage === 'penerima') {
                                    $rejectTitle = 'Surat Jalan Ditolak oleh Security Penerima';
                                }
                            }
                        @endphp
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6">
                            <div class="flex items-start gap-2 text-red-700 text-sm sm:text-base">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <span class="font-semibold">{{ $rejectTitle }}</span>
                                    @if($rejectReason)
                                        <p class="text-xs sm:text-sm text-red-700 mt-1">Alasan: {{ $rejectReason }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Horizontal Progress Bar - Scrollable on Mobile --}}
                    <div class="overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
                        @php
                            $totalSteps = count($steps);
                            $progressStep = $isRejected ? $currentStep + 1 : $currentStep;
                            // Calculate progress bar width to reach center of last completed step
                            // Each step container is (100/totalSteps)% wide, circles are centered
                            // For currentStep = C, last completed = C-1, center position = (C-0.5)/totalSteps * 100
                            if ($progressStep <= 0) {
                                $progressWidth = 0;
                            } elseif ($progressStep >= $totalSteps) {
                                $progressWidth = 100;
                            } else {
                                $progressWidth = (($progressStep - 0.5) / $totalSteps) * 100;
                            }
                        @endphp
                        <div class="relative min-w-[500px] sm:min-w-0">
                            <div class="absolute top-[22px] sm:top-[26px] left-0 right-0 h-1 bg-gray-200 rounded-full"></div>
                            <div class="absolute top-[22px] sm:top-[26px] left-0 h-1 {{ $isRejected ? 'bg-red-500' : 'bg-green-500' }} rounded-full transition-all duration-500"
                                 style="width: {{ $progressWidth }}%"></div>

                            <div class="relative flex justify-between pt-[6px]">
                                @foreach($steps as $index => $step)
                                    @php
                                        $isCompleted = $currentStep > $index;
                                        $isActive = $currentStep === $index;
                                        $isPending = $currentStep < $index;
                                        $useRejectedStyle = $isRejected && ($isCompleted || $isActive);

                                        if ($useRejectedStyle) {
                                            if ($isActive) {
                                                $circleClass = 'bg-red-600 text-white border-red-600 ring-4 ring-red-300/30';
                                                $labelClass = 'text-red-700 font-bold';
                                            } else {
                                                $circleClass = 'bg-red-500 text-white border-red-500';
                                                $labelClass = 'text-red-700 font-semibold';
                                            }
                                        } elseif ($isCompleted) {
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
                                        <span class="mt-2 text-[10px] sm:text-xs text-center {{ $labelClass }} leading-tight">
                                            {{ ($isRejected && ($step['label'] ?? '') === 'Diperiksa' && $index === $currentStep) ? 'Ditolak' : $step['label'] }}
                                        </span>
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
                                    $useRejectedStyle = $isRejected && ($isCompleted || $isActive);
                                    $circleClass = $useRejectedStyle
                                        ? ($isActive ? 'bg-red-600 ring-4 ring-red-300/30' : 'bg-red-500')
                                        : ($isCompleted ? 'bg-green-500' : ($isActive ? 'bg-pln-primary ring-4 ring-pln-primary/20' : 'bg-gray-300'));
                                    $lineClass = $isCompleted
                                        ? ($isRejected ? 'bg-red-500' : 'bg-green-500')
                                        : 'bg-gray-200';
                                    $labelClass = $useRejectedStyle
                                        ? 'text-red-700'
                                        : ($isCompleted ? 'text-green-700' : ($isActive ? 'text-pln-primary' : 'text-gray-500'));
                                    $badgeClass = $useRejectedStyle
                                        ? 'bg-red-100 text-red-700'
                                        : ($isCompleted ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700');
                                    $badgeText = $useRejectedStyle && $isActive ? 'Ditolak' : ($isCompleted ? 'Selesai' : 'Proses');
                                @endphp
                                <div class="flex gap-3 sm:gap-4 {{ !$isCompleted && !$isActive ? 'opacity-40' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full {{ $circleClass }}"></div>
                                        @if($index < count($steps) - 1)
                                            <div class="w-0.5 h-full min-h-[36px] sm:min-h-[40px] {{ $lineClass }}"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 pb-3 sm:pb-4">
                                        <div class="flex flex-wrap items-center gap-1 sm:gap-2">
                                            <span class="font-semibold text-sm sm:text-base {{ $labelClass }}">
                                                {{ ($isRejected && ($step['label'] ?? '') === 'Diperiksa' && $index === $currentStep) ? 'Ditolak' : $step['label'] }}
                                            </span>
                                            @if($isCompleted)
                                                <span class="text-[10px] sm:text-xs {{ $badgeClass }} px-1.5 sm:px-2 py-0.5 rounded-full">{{ $badgeText }}</span>
                                            @elseif($isActive)
                                                <span class="text-[10px] sm:text-xs {{ $badgeClass }} px-1.5 sm:px-2 py-0.5 rounded-full animate-pulse">{{ $badgeText }}</span>
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
            @else
            {{-- DRAFT/MENUNGGU_PERSETUJUAN/DIPERIKSA_PENGIRIM Status Card with Blurred Progress Background --}}
            @php
                $rejectMessage = null;
                $catatanPenolakan = (string) ($suratJalan->catatan_penolakan ?? '');

                if ($suratStatus === 'DITOLAK') {
                    // Security rejection
                    $rejectStageLabel = 'security';
                    $rejectReason = null;
                    if ($catatanPenolakan !== '' && preg_match('/\\[DITOLAK_(PENGIRIM|PENERIMA):\\s*([^\\]]+)\\]/', $catatanPenolakan, $matches)) {
                        $rejectStage = strtolower($matches[1] ?? '');
                        $rejectReason = trim($matches[2] ?? '');
                        if ($rejectStage === 'pengirim') {
                            $rejectStageLabel = 'security pengirim';
                        } elseif ($rejectStage === 'penerima') {
                            $rejectStageLabel = 'security penerima';
                        }
                    }
                    $rejectMessage = 'Status: DITOLAK - Ditolak oleh ' . $rejectStageLabel . '.';
                    if ($rejectReason) {
                        $rejectMessage .= ' Alasan: ' . $rejectReason . '.';
                    }
                } elseif ($suratStatus === 'DITOLAK_PERSETUJUAN') {
                    // Manager rejection
                    $rejectReason = null;
                    if ($catatanPenolakan !== '' && preg_match('/\\[DITOLAK PERSETUJUAN:\\s*([^\\]]+)\\]/', $catatanPenolakan, $matches)) {
                        $rejectReason = trim($matches[1] ?? '');
                    }
                    $rejectMessage = 'Status: DITOLAK PERSETUJUAN - Ditolak oleh manager.';
                    if ($rejectReason) {
                        $rejectMessage .= ' Alasan: ' . $rejectReason . '.';
                    } else {
                        $rejectMessage .= ' Silakan perbaiki dan ajukan ulang.';
                    }
                }

                // Custom message for PENGEMBALIAN with DIPERIKSA_PENGIRIM
                $returnStatus = $peminjaman?->suratJalanKembali?->status;
                if ($tipe === 'PENGEMBALIAN' && $suratStatus === 'DIPERIKSA_PENGIRIM') {
                    $draftMessage = 'Status: MENUNGGU PEMERIKSAAN - Menunggu pemeriksaan security pengirim (Gudang Peminjam).';
                } elseif ($tipe === 'PEMINJAMAN' && $returnStatus === 'DIPERIKSA_PENGIRIM') {
                    $draftMessage = 'Status: MENUNGGU PEMERIKSAAN PENGEMBALIAN - Surat pengembalian terkait sedang menunggu pemeriksaan security.';
                } elseif ($tipe === 'PEMINJAMAN' && $returnStatus === 'MENUNGGU_PERSETUJUAN') {
                    $draftMessage = 'Status: MENUNGGU PERSETUJUAN - Surat pengembalian terkait sedang menunggu persetujuan manager.';
                } else {
                    $draftMessage = $rejectMessage ?? match ($suratStatus) {
                        'MENUNGGU_PERSETUJUAN' => 'Status: MENUNGGU PERSETUJUAN - Menunggu persetujuan manager.',
                        'DIPERIKSA_PENGIRIM' => 'Status: MENUNGGU PEMERIKSAAN - Menunggu pemeriksaan security pengirim.',
                        'DITOLAK_PERSETUJUAN' => 'Status: DITOLAK PERSETUJUAN - Silakan perbaiki dan ajukan ulang.',
                        'DITOLAK' => 'Status: DITOLAK - Ditolak oleh security.',
                        default => 'Status: DRAFT - Belum diajukan untuk persetujuan.',
                    };
                }

                // Check if PEMINJAMAN with return pending (security check or manager approval)
                $isPeminjamanReturnPending = $tipe === 'PEMINJAMAN' && in_array($returnStatus, ['DIPERIKSA_PENGIRIM', 'MENUNGGU_PERSETUJUAN'], true);

                $draftIcon = $isPeminjamanReturnPending ? 'clock' : match ($suratStatus) {
                    'MENUNGGU_PERSETUJUAN' => 'clock',
                    'DIPERIKSA_PENGIRIM' => 'clock',
                    'DITOLAK_PERSETUJUAN' => 'x-circle',
                    'DITOLAK' => 'x-circle',
                    default => 'document',
                };

                $draftBgClass = $isPeminjamanReturnPending ? 'bg-orange-50 border-orange-200' : match ($suratStatus) {
                    'MENUNGGU_PERSETUJUAN' => 'bg-orange-50 border-orange-200',
                    'DIPERIKSA_PENGIRIM' => 'bg-orange-50 border-orange-200',
                    'DITOLAK_PERSETUJUAN' => 'bg-red-50 border-red-200',
                    'DITOLAK' => 'bg-red-50 border-red-200',
                    default => 'bg-gray-50 border-gray-200',
                };

                $draftTextClass = $isPeminjamanReturnPending ? 'text-orange-800' : match ($suratStatus) {
                    'MENUNGGU_PERSETUJUAN' => 'text-orange-800',
                    'DIPERIKSA_PENGIRIM' => 'text-orange-800',
                    'DITOLAK_PERSETUJUAN' => 'text-red-800',
                    'DITOLAK' => 'text-red-800',
                    default => 'text-gray-700',
                };

                $draftIconBgClass = $isPeminjamanReturnPending ? 'bg-orange-100 text-orange-600' : match ($suratStatus) {
                    'MENUNGGU_PERSETUJUAN' => 'bg-orange-100 text-orange-600',
                    'DIPERIKSA_PENGIRIM' => 'bg-orange-100 text-orange-600',
                    'DITOLAK_PERSETUJUAN' => 'bg-red-100 text-red-600',
                    'DITOLAK' => 'bg-red-100 text-red-600',
                    default => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <div class="relative bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6">
                {{-- Blurred Progress Steps Background --}}
                <div class="p-4 sm:p-6 blur-[2px] opacity-30 select-none pointer-events-none">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">Riwayat Status</h3>
                    </div>
                    <div class="relative">
                        <div class="absolute top-[16px] sm:top-[20px] left-0 right-0 h-1 bg-gray-200 rounded-full"></div>
                        <div class="relative flex justify-between">
                            @foreach($steps as $index => $step)
                                <div class="flex flex-col items-center" style="width: {{ 100 / count($steps) }}%">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 flex items-center justify-center text-xs sm:text-sm font-bold bg-white text-gray-400 border-gray-300 z-10">
                                        {{ $index + 1 }}
                                    </div>
                                    <span class="mt-2 text-[10px] sm:text-xs text-center text-gray-400 leading-tight">
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Overlay Status Message --}}
                <div class="absolute inset-0 flex items-center justify-center p-4">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-5 sm:px-8 py-4 {{ $draftBgClass }} border rounded-2xl shadow-lg backdrop-blur-sm">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full {{ $draftIconBgClass }} flex items-center justify-center flex-shrink-0">
                            @if($draftIcon === 'clock')
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif($draftIcon === 'x-circle')
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @endif
                        </div>
                        <span class="font-semibold text-sm sm:text-base text-center {{ $draftTextClass }}">{{ $draftMessage }}</span>
                    </div>
                </div>
            </div>
            @endif
            </div>

            {{-- Info Card --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Gudang Asal</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->gudangAsal->nama ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Gudang Tujuan</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->gudangTujuan->nama ?? '-' }}</p>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <p class="text-xs sm:text-sm text-gray-500">PIC Tujuan</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">
                                {{ $suratJalan->picTujuan->nama ?? $suratJalan->pic_tujuan_custom_nama ?? '-' }}
                                @php
                                    $picJabatan = $suratJalan->picTujuan?->jabatan ?? $suratJalan->pic_tujuan_custom_jabatan;
                                    $picNoHp = $suratJalan->picTujuan?->no_hp ?? $suratJalan->pic_tujuan_custom_no_hp;
                                @endphp
                                @if(!empty($picJabatan))
                                    <span class="text-xs sm:text-sm text-gray-500 font-normal">({{ $picJabatan }})</span>
                                @endif
                                @if(!empty($picNoHp))
                                    <span class="text-xs sm:text-sm text-gray-500 font-normal">({{ $picNoHp }})</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Tanggal</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->tanggal?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Nama Driver</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->nama_driver ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Jenis Kendaraan</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->jenis_kendaraan ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Nomor Plat</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->nomor_plat ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Tipe</p>
                            @php
                                $tipeBadge = match($suratJalan->tipe) {
                                    'TRANSFER' => 'bg-blue-100 text-blue-800',
                                    'PEMINJAMAN' => 'bg-purple-100 text-purple-800',
                                    'PENGEMBALIAN' => 'bg-teal-100 text-teal-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $tipeBadge }}">
                                {{ $suratJalan->tipe }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Status</p>
                            @php
                                // Check if PEMINJAMAN has pending return
                                $returnStatusForBadge = $peminjaman?->suratJalanKembali?->status;
                                $hasPendingReturn = $suratJalan->tipe === 'PEMINJAMAN' && in_array($returnStatusForBadge, ['MENUNGGU_PERSETUJUAN', 'DIPERIKSA_PENGIRIM'], true);

                                if ($hasPendingReturn) {
                                    // Override status display for PEMINJAMAN with pending return
                                    $statusBadge = 'bg-orange-100 text-orange-800';
                                    $statusLabel = $returnStatusForBadge === 'MENUNGGU_PERSETUJUAN'
                                        ? 'Menunggu Persetujuan'
                                        : 'Menunggu Pemeriksaan';
                                } else {
                                    $statusBadge = match($suratJalan->status) {
                                        'DRAFT' => 'bg-gray-100 text-gray-800',
                                        'MENUNGGU_PERSETUJUAN' => 'bg-orange-100 text-orange-800',
                                        'DITOLAK_PERSETUJUAN' => 'bg-red-100 text-red-800',
                                        'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                        'DIPERIKSA_PENGIRIM' => 'bg-cyan-100 text-cyan-800',
                                        'DIPERIKSA_PENERIMA' => 'bg-indigo-100 text-indigo-800',
                                        'DIPERIKSA' => 'bg-indigo-100 text-indigo-800',
                                        'DITERIMA' => 'bg-emerald-100 text-emerald-800',
                                        'MENUNGGU_DIKEMBALIKAN' => 'bg-amber-100 text-amber-800',
                                        'DIKEMBALIKAN' => 'bg-teal-100 text-teal-800',
                                        'SELESAI' => 'bg-green-100 text-green-800',
                                        'DITOLAK' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    $statusLabel = match($suratJalan->status) {
                                        'DRAFT' => 'Draft',
                                        'MENUNGGU_PERSETUJUAN' => 'Menunggu Persetujuan',
                                        'DITOLAK_PERSETUJUAN' => 'Persetujuan Ditolak',
                                        'DIKIRIM' => 'Dikirim',
                                        'DIPERIKSA_PENGIRIM' => 'Menunggu Pemeriksaan',
                                        'DIPERIKSA_PENERIMA' => 'Diperiksa Penerima',
                                        'DIPERIKSA' => 'Diperiksa',
                                        'DITERIMA' => 'Diterima',
                                        'MENUNGGU_DIKEMBALIKAN' => 'Menunggu Dikembalikan',
                                        'DIKEMBALIKAN' => 'Dikembalikan',
                                        'SELESAI' => 'Selesai',
                                        'DITOLAK' => 'Ditolak',
                                        default => $suratJalan->status
                                    };
                                }
                            @endphp
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusBadge }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        {{-- Linked Surat Jalan Section --}}
                        @if($suratJalan->tipe === 'PEMINJAMAN' && $peminjaman)
                        <div class="col-span-2">
                            <p class="text-xs sm:text-sm text-gray-500">Surat Pengembalian Terkait</p>
                            @if($peminjaman->suratJalanKembali)
                                <a href="{{ route('security.show', $peminjaman->suratJalanKembali->id) }}"
                                   class="inline-flex items-center gap-2 mt-1 text-sm font-medium text-green-600 hover:text-green-800 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <span>{{ $peminjaman->suratJalanKembali->nomor }}</span>

                                </a>
                            @else
                                <p class="inline-flex items-center gap-2 mt-1 text-sm text-yellow-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Belum ada surat pengembalian</span>
                                </p>
                            @endif
                        </div>
                        @elseif($suratJalan->tipe === 'PENGEMBALIAN' && $peminjaman && $peminjaman->suratJalanKirim)
                        <div class="col-span-2">
                            <p class="text-xs sm:text-sm text-gray-500">Surat Peminjaman Asal</p>
                            <a href="{{ route('security.show', $peminjaman->suratJalanKirim->id) }}"
                               class="inline-flex items-center gap-2 mt-1 text-sm font-medium text-blue-600 hover:text-blue-800 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <span>{{ $peminjaman->suratJalanKirim->nomor }}</span>

                            </a>
                        </div>
                        @endif

                        <div class="col-span-2">
                            <p class="text-xs sm:text-sm text-gray-500">Catatan</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->catatan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Daftar Item</h3>
                </div>
                @php
                    $canCheckItems = in_array($suratJalan->status, ['DIKIRIM', 'DIKEMBALIKAN', 'DIPERIKSA_PENGIRIM'], true);
                    $hasSecurityCheck = $suratJalan->items->contains(fn ($row) => $row->checked_by_security !== null);
                @endphp

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
                            @if($canCheckItems)
                                <label class="mt-3 flex items-center gap-2 text-xs text-gray-600">
                                        <input type="checkbox"
                                               name="checked_items[]"
                                               value="{{ $item->id }}"
                                               form="security-approve-form"
                                               data-security-check
                                               data-security-check-group="mobile"
                                               class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    Barang sesuai
                                </label>
                            @elseif($hasSecurityCheck)
                                <div class="mt-3 text-xs">
                                    @if($item->checked_by_security === true)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-700 font-semibold">Sesuai</span>
                                    @elseif($item->checked_by_security === false)
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-red-700 font-semibold">Tidak Sesuai</span>
                                    @else
                                        <span class="text-gray-400 italic">Belum diperiksa</span>
                                    @endif
                                </div>
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
                                @if($canCheckItems)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Checklist</th>
                                @elseif($hasSecurityCheck)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemeriksaan</th>
                                @endif
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
                                    @if($canCheckItems)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <label class="inline-flex items-center gap-2">
                                                <input type="checkbox"
                                               name="checked_items[]"
                                               value="{{ $item->id }}"
                                               form="security-approve-form"
                                               data-security-check
                                               data-security-check-group="desktop"
                                               class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-xs">Sesuai</span>
                                            </label>
                                        </td>
                                    @elseif($hasSecurityCheck)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($item->checked_by_security === true)
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-700 font-semibold">Sesuai</span>
                                            @elseif($item->checked_by_security === false)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-red-700 font-semibold">Tidak Sesuai</span>
                                            @else
                                                <span class="text-gray-400 italic">Belum diperiksa</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canCheckItems || $hasSecurityCheck ? 4 : 3 }}" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada item.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Info Box for PEMINJAMAN - When return is pending security check or manager approval --}}
            @if($suratJalan->tipe === 'PEMINJAMAN' && $peminjaman && $peminjaman->suratJalanKembali && in_array($peminjaman->suratJalanKembali->status, ['DIPERIKSA_PENGIRIM', 'MENUNGGU_PERSETUJUAN'], true))
            @php
                $returnInfoMessage = $peminjaman->suratJalanKembali->status === 'MENUNGGU_PERSETUJUAN'
                    ? 'sedang menunggu persetujuan manager.'
                    : 'sedang menunggu pemeriksaan security pengirim.';
            @endphp
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 sm:p-6 mt-4 sm:mt-6">
                <div class="flex items-start sm:items-center gap-3 text-orange-800">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-semibold text-sm sm:text-base">
                        Surat Pengembalian Terkait (<a href="{{ route('security.show', $peminjaman->suratJalanKembali->id) }}" class="underline hover:text-orange-900">{{ $peminjaman->suratJalanKembali->nomor }}</a>) {{ $returnInfoMessage }}
                    </span>
                </div>
            </div>
            @endif

            {{-- Action Buttons --}}
            @php
                $userGudangId = auth()->user()->gudang_id;

                // Determine if this surat can be confirmed by security
                // DIPERIKSA_PENGIRIM: security gudang_asal (pengirim) melakukan pemeriksaan awal
                // DIKIRIM: security gudang_tujuan (penerima) melakukan pemeriksaan
                // DIKEMBALIKAN (PENGEMBALIAN): security gudang_pemilik (penerima) melakukan pemeriksaan
                $canShowConfirmation = false;
                $expectedGudangId = null;
                $checkStage = null;

                if ($suratJalan->status === 'DIPERIKSA_PENGIRIM') {
                    // Pemeriksaan awal oleh security pengirim
                    $canShowConfirmation = true;
                    $expectedGudangId = $suratJalan->gudang_asal_id;
                    $checkStage = 'pengirim';
                } elseif ($suratJalan->status === 'DIKIRIM' && in_array($suratJalan->tipe, ['PEMINJAMAN', 'TRANSFER'])) {
                    // Pemeriksaan oleh security penerima
                    $canShowConfirmation = true;
                    $expectedGudangId = $suratJalan->gudang_tujuan_id;
                    $checkStage = 'penerima';
                } elseif ($suratJalan->status === 'DIKEMBALIKAN' && $suratJalan->tipe === 'PENGEMBALIAN') {
                    // Pemeriksaan oleh security penerima (gudang pemilik)
                    $canShowConfirmation = true;
                    $expectedGudangId = $suratJalan->gudang_tujuan_id;
                    if ($peminjaman?->gudang_pemilik_id) {
                        $expectedGudangId = $peminjaman->gudang_pemilik_id;
                    }
                    $checkStage = 'penerima';
                }
                // Note: PEMINJAMAN with DIKEMBALIKAN should NOT show confirmation button
                // because the action should be done on the PENGEMBALIAN surat

                $canApprove = $canShowConfirmation && $userGudangId === $expectedGudangId;
                $displayGudangLabel = $checkStage === 'pengirim' ? 'Asal' : 'Tujuan';
                $displayGudangName = $checkStage === 'pengirim'
                    ? ($suratJalan->gudangAsal->nama ?? '-')
                    : (($suratJalan->tipe === 'PENGEMBALIAN' && $peminjaman?->gudangPemilik?->nama)
                        ? $peminjaman->gudangPemilik->nama
                        : ($suratJalan->gudangTujuan->nama ?? '-'));
            @endphp
            @if($canShowConfirmation)
                @if($canApprove)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4">Konfirmasi Pemeriksaan</h3>
                        <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
                            {{-- Terima Button --}}
                            <form id="security-approve-form" action="{{ route('security.terima', $suratJalan->id) }}" method="POST" class="flex-1"
                                  x-data="{ submitting: false }"
                                  @submit="submitting = true">
                                @csrf
                                <button type="submit"
                                        id="security-approve-button"
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
                @else
                {{-- Security tidak bisa approve karena bukan gudang tujuannya --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 sm:p-6 mt-4 sm:mt-6">
                    <div class="flex items-start sm:items-center gap-3 text-yellow-800">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0 mt-0.5 sm:mt-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base">
                            Anda tidak dapat mengkonfirmasi surat jalan ini karena gudang {{ $checkStage === 'pengirim' ? 'asal' : 'tujuan' }} bukan gudang Anda.
                            <span class="text-yellow-600">(Gudang {{ $displayGudangLabel }}: {{ $displayGudangName }})</span>
                        </span>
                    </div>
                </div>
                @endif
            @elseif($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->status === 'DIKEMBALIKAN' && $peminjaman && $peminjaman->suratJalanKembali)
                {{-- PEMINJAMAN dengan status DIKEMBALIKAN - arahkan ke surat pengembalian --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-6 mt-4 sm:mt-6">
                    <div class="flex items-start sm:items-center gap-3 text-blue-800">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base">
                            Barang sedang dalam proses pengembalian - Konfirmasi dilakukan pada
                            <a href="{{ route('security.show', $peminjaman->suratJalanKembali->id) }}"
                               class="underline hover:text-blue-900">Surat Pengembalian</a>
                        </span>
                    </div>
                </div>
            @endif

            @if($canCheckItems && $suratJalan->items->count() > 0)
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const checks = Array.from(document.querySelectorAll('[data-security-check]'));
                        const button = document.getElementById('security-approve-button');
                        if (!button || checks.length === 0) return;

                        const updateButton = () => {
                            const currentGroup = window.matchMedia('(min-width: 640px)').matches ? 'desktop' : 'mobile';
                            const scopedChecks = checks.filter((input) => input.dataset.securityCheckGroup === currentGroup);
                            const checkedCount = scopedChecks.filter((input) => input.checked).length;
                            button.disabled = scopedChecks.length === 0 || checkedCount !== scopedChecks.length;
                            if (button.disabled) {
                                button.setAttribute('title', 'Semua item harus ditandai sesuai sebelum konfirmasi.');
                            } else {
                                button.removeAttribute('title');
                            }
                        };

                        checks.forEach((input) => input.addEventListener('change', updateButton));
                        window.addEventListener('resize', updateButton);
                        updateButton();
                    });
                </script>
            @endif
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script>
        (function() {
            const suratJalanId = {{ (int) $suratJalan->id }};

            const refreshProgress = async () => {
                const current = document.querySelector('[data-surat-jalan-progress]');
                if (!current) {
                    return;
                }
                try {
                    // Always use GET route for refresh
                    const url = new URL(`{{ route('security.show', $suratJalan->id) }}`);
                    url.searchParams.set('no_cache', '1');
                    const response = await fetch(url.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) {
                        return;
                    }
                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const next = doc.querySelector('[data-surat-jalan-progress]');
                    if (next) {
                        current.innerHTML = next.innerHTML;
                    }
                } catch (error) {
                    console.error('Realtime refresh failed', error);
                }
            };

            const initEchoListener = () => {
                if (!window.Echo) {
                    console.log('[Security] Echo not ready, retrying in 500ms...');
                    setTimeout(initEchoListener, 500);
                    return;
                }

                console.log('[Security] Subscribing to channel: surat-jalan.detail.' + suratJalanId);

                window.Echo.channel(`surat-jalan.detail.${suratJalanId}`)
                    .listen('.SuratJalanStatusUpdated', (payload) => {
                        console.log('[Security] Received event:', payload);
                        if (!payload || payload.id !== suratJalanId) {
                            return;
                        }
                        document.querySelectorAll('[data-surat-jalan-status-text]').forEach((node) => {
                            node.textContent = payload.status || '-';
                        });
                        refreshProgress();
                    });
            };

            // Wait for DOM and then try to init Echo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initEchoListener);
            } else {
                initEchoListener();
            }
        })();
    </script>
</x-app-layout>
