@use('Illuminate\Support\Facades\Storage')
<x-app-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)"
                     class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-900 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Detail Surat Jalan</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $suratJalan->nomor }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- PDF Buttons --}}
                            <a href="{{ route('gudang.surat-jalan.preview', $suratJalan->id) }}"
                               target="_blank"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Preview PDF
                            </a>
                            <a href="{{ route('gudang.surat-jalan.pdf', $suratJalan->id) }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download PDF
                            </a>
                            @if($suratJalan->status === 'DRAFT' && Auth::user()?->gudang_id === $suratJalan->gudang_asal_id)
                                <a href="{{ route('gudang.surat-jalan.edit', $suratJalan->id) }}"
                                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 text-sm">
                                    Edit Draft
                                </a>
                                <form method="POST" action="{{ route('gudang.surat-jalan.approve', $suratJalan->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 text-sm">
                                        Approve & Kirim
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('gudang.surat-jalan.destroy', $suratJalan->id) }}"
                                      onsubmit="return confirm('Hapus draft surat jalan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1.5 px-3 rounded-md transition duration-150 text-sm">
                                        Hapus Draft
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('gudang.surat-jalan.index') }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 text-sm">
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $tipe = strtoupper($suratJalan->tipe ?? '');
                $suratStatus = strtoupper($suratJalan->status ?? 'DRAFT');
                $isRejected = $suratStatus === 'DITOLAK';
                $isPeminjaman = in_array($tipe, ['PEMINJAMAN', 'PENGEMBALIAN']);
                $gudangTujuanNama = $suratJalan->gudang_tujuan_is_custom
                    ? ($suratJalan->gudang_tujuan_custom_nama ?? 'Gudang Lainnya')
                    : ($suratJalan->gudangTujuan->nama ?? '-');
                $gudangTujuanAlamat = $suratJalan->gudang_tujuan_is_custom
                    ? ($suratJalan->gudang_tujuan_custom_alamat ?? '-')
                    : ($suratJalan->gudangTujuan->alamat ?? '-');
                $gudangTujuanTelepon = $suratJalan->gudang_tujuan_is_custom
                    ? ($suratJalan->gudang_tujuan_custom_telepon ?? '-')
                    : ($suratJalan->gudangTujuan->telepon ?? '-');

                // Helper untuk format waktu
                $formatWaktu = fn($waktu) => $waktu ? \Carbon\Carbon::parse($waktu)->format('d M Y, H:i') : null;

                if ($tipe === 'TRANSFER') {
                    // TRANSFER: Dikirim -> Diperiksa -> Selesai
                    $sjKirim = $suratJalan;
                    if ($suratJalan->gudang_tujuan_is_custom) {
                        $steps = [
                            [
                                'label' => 'Dikirim',
                                'desc' => 'Barang dikirim',
                                'detail' => $sjKirim->status !== 'DRAFT'
                                    ? "Dikirim dari <strong>{$sjKirim->gudangAsal->nama}</strong> ke <strong>{$gudangTujuanNama}</strong>"
                                    : null,
                                'time' => $sjKirim->status !== 'DRAFT' ? $formatWaktu($sjKirim->updated_at) : null,
                                'by' => $sjKirim->status !== 'DRAFT' ? $sjKirim->pembuat?->name : null,
                            ],
                            [
                                'label' => 'Selesai',
                                'desc' => 'Transfer selesai',
                                'detail' => $sjKirim->status === 'SELESAI'
                                    ? "Dikirim ke <strong>{$gudangTujuanNama}</strong>"
                                    : null,
                                'time' => $sjKirim->status === 'SELESAI' ? $formatWaktu($sjKirim->updated_at) : null,
                                'by' => null,
                            ],
                        ];
                        $statusIndexMap = [
                            'DRAFT' => -1,
                            'DIKIRIM' => 0,
                            'SELESAI' => 1,
                            'DITOLAK' => -2,
                        ];
                        $currentStep = $statusIndexMap[$suratStatus] ?? -1;
                    } else {
                        $steps = [
                            [
                                'label' => 'Dikirim',
                                'desc' => 'Barang dikirim',
                                'detail' => $sjKirim->status !== 'DRAFT'
                                    ? "Dikirim dari <strong>{$sjKirim->gudangAsal->nama}</strong> ke <strong>{$gudangTujuanNama}</strong>"
                                    : null,
                                'time' => $sjKirim->status !== 'DRAFT' ? $formatWaktu($sjKirim->updated_at) : null,
                                'by' => $sjKirim->status !== 'DRAFT' ? $sjKirim->pembuat?->name : null,
                            ],
                            [
                                'label' => 'Diperiksa',
                                'desc' => 'Security memeriksa',
                                'detail' => in_array($sjKirim->status, ['DIPERIKSA', 'SELESAI'])
                                    ? "Diperiksa oleh Security di <strong>{$gudangTujuanNama}</strong>"
                                    : null,
                                'time' => in_array($sjKirim->status, ['DIPERIKSA', 'SELESAI']) ? $formatWaktu($sjKirim->updated_at) : null,
                                'by' => null,
                            ],
                            [
                                'label' => 'Selesai',
                                'desc' => 'Transfer selesai',
                                'detail' => $sjKirim->status === 'SELESAI'
                                    ? "Diterima di <strong>{$gudangTujuanNama}</strong>"
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
                    }
                } else {
                    // PEMINJAMAN/PENGEMBALIAN: Alur lengkap sinkronisasi
                    $sjKirim = $peminjaman?->suratJalanKirim;
                    $sjKembali = $peminjaman?->suratJalanKembali;
                    $gudangPemilik = $peminjaman?->gudangPemilik;
                    $gudangPeminjam = $peminjaman?->gudangPeminjam;
                    $gudangPemilikNama = $gudangPemilik?->nama ?? $suratJalan->gudangAsal->nama ?? '-';
                    $gudangPeminjamNama = $peminjaman?->gudang_peminjam_is_custom
                        ? ($peminjaman->gudang_peminjam_custom_nama ?? 'Gudang Lainnya')
                        : ($gudangPeminjam?->nama ?? '-');

                    $peminjamanStatus = $peminjaman?->status ?? 'DIAJUKAN';
                    $sjKirimStatus = $sjKirim?->status ?? 'DRAFT';
                    $sjKembaliStatus = $sjKembali?->status ?? null;

                    if ($suratJalan->gudang_tujuan_is_custom) {
                        $steps = [
                            [
                                'label' => 'Dikirim',
                                'desc' => 'Barang dikirim ke peminjam',
                                'detail' => $sjKirim && $sjKirim->status !== 'DRAFT'
                                    ? "Dikirim dari <strong>{$gudangPemilikNama}</strong> ke <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $peminjaman?->waktu_kirim ? $formatWaktu($peminjaman->waktu_kirim) : null,
                                'by' => $sjKirim?->pembuat?->name,
                            ],
                            [
                                'label' => 'Menunggu Dikembalikan',
                                'desc' => 'Menunggu konfirmasi pengembalian',
                                'detail' => $suratStatus === 'MENUNGGU_DIKEMBALIKAN'
                                    ? "Menunggu pengembalian dari <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $suratStatus === 'MENUNGGU_DIKEMBALIKAN' ? $formatWaktu($suratJalan->updated_at) : null,
                                'by' => null,
                            ],
                            [
                                'label' => 'Selesai',
                                'desc' => 'Pengembalian dikonfirmasi',
                                'detail' => $peminjamanStatus === 'SELESAI'
                                    ? "Barang telah dikembalikan ke <strong>{$gudangPemilikNama}</strong>"
                                    : null,
                                'time' => $peminjaman?->waktu_selesai ? $formatWaktu($peminjaman->waktu_selesai) : null,
                                'by' => null,
                            ],
                        ];

                        if ($peminjamanStatus === 'SELESAI' || $suratStatus === 'SELESAI') {
                            $currentStep = 3;
                        } elseif ($suratStatus === 'MENUNGGU_DIKEMBALIKAN') {
                            $currentStep = 1;
                        } elseif ($sjKirimStatus !== 'DRAFT') {
                            $currentStep = 0;
                        } else {
                            $currentStep = 0;
                        }
                    } else {
                        $steps = [
                            [
                                'label' => 'Dikirim',
                                'desc' => 'Barang dikirim ke peminjam',
                                'detail' => $sjKirim && $sjKirim->status !== 'DRAFT'
                                    ? "Dikirim dari <strong>{$gudangPemilikNama}</strong> ke <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $peminjaman?->waktu_kirim ? $formatWaktu($peminjaman->waktu_kirim) : null,
                                'by' => $sjKirim?->pembuat?->name,
                            ],
                            [
                                'label' => 'Diperiksa',
                                'desc' => 'Security gudang tujuan',
                                'detail' => $sjKirim && in_array($sjKirim->status, ['DIPERIKSA', 'DITERIMA', 'SELESAI'])
                                    ? "Diperiksa oleh Security di <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $sjKirim && in_array($sjKirim->status, ['DIPERIKSA', 'DITERIMA', 'SELESAI'])
                                    ? $formatWaktu($sjKirim->updated_at) : null,
                                'by' => null,
                            ],
                            [
                                'label' => 'Diterima',
                                'desc' => 'Operator menerima barang',
                                'detail' => $peminjaman && in_array($peminjaman->status, ['DITERIMA', 'DIKEMBALIKAN', 'SELESAI'])
                                    ? "Diterima di <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $peminjaman?->waktu_diterima ? $formatWaktu($peminjaman->waktu_diterima) : null,
                                'by' => null,
                            ],
                            [
                                'label' => 'Dikembalikan',
                                'desc' => 'Barang dikembalikan',
                                'detail' => $sjKembali && in_array($sjKembali->status, ['DIKEMBALIKAN', 'DIPERIKSA', 'SELESAI'])
                                    ? "Dikembalikan dari <strong>{$gudangPeminjamNama}</strong> ke <strong>{$gudangPemilikNama}</strong>"
                                    : null,
                                'time' => $peminjaman?->waktu_pengembalian ? $formatWaktu($peminjaman->waktu_pengembalian) : null,
                                'by' => $sjKembali?->pembuat?->name,
                            ],
                            [
                                'label' => 'Diperiksa',
                                'desc' => 'Security gudang pemilik',
                                'detail' => $sjKembali && in_array($sjKembali->status, ['DIPERIKSA', 'SELESAI'])
                                    ? "Diperiksa oleh Security di <strong>{$gudangPemilikNama}</strong>"
                                    : null,
                                'time' => $sjKembali && in_array($sjKembali->status, ['DIPERIKSA', 'SELESAI'])
                                    ? $formatWaktu($sjKembali->updated_at) : null,
                                'by' => null,
                            ],
                            [
                                'label' => 'Selesai',
                                'desc' => 'Peminjaman selesai',
                                'detail' => $peminjaman && $peminjaman->status === 'SELESAI'
                                    ? "Barang telah dikembalikan ke <strong>{$gudangPemilikNama}</strong>"
                                    : null,
                                'time' => $peminjaman?->waktu_selesai ? $formatWaktu($peminjaman->waktu_selesai) : null,
                                'by' => null,
                            ],
                        ];

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
                    }

                    // Handle rejection
                    if ($isRejected || $peminjaman?->status === 'DITOLAK') {
                        $isRejected = true;
                    }
                }

                $maxStep = count($steps) - 1;
            @endphp

            {{-- Riwayat Status - Only show if not DRAFT --}}
            @if($suratStatus !== 'DRAFT' || ($isPeminjaman && $peminjaman && $peminjaman->status !== 'DIAJUKAN'))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="{ showDetail: false }">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Riwayat Status</h3>
                        <button @click="showDetail = !showDetail"
                                class="text-sm text-pln-primary hover:text-pln-primary/80 font-medium flex items-center gap-1 transition">
                            <span x-text="showDetail ? 'Sembunyikan Detail' : 'Lihat Detail'"></span>
                            <svg class="w-4 h-4 transition-transform" :class="showDetail ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    @if($isRejected)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center gap-2 text-red-700">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Surat Jalan Ditolak oleh Security</span>
                            </div>
                        </div>
                    @endif

                    {{-- Horizontal Progress Bar --}}
                    <div class="relative">
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
                                    <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-bold {{ $circleClass }} z-10">
                                        @if($isCompleted)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <span class="mt-2 text-xs text-center {{ $labelClass }}">{{ $step['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Timeline Detail (Collapsible) --}}
                    <div x-show="showDetail"
                         x-collapse
                         x-cloak
                         class="border-t mt-6 pt-6">
                        <div class="space-y-4">
                            @foreach($steps as $index => $step)
                                @php
                                    $isCompleted = $currentStep > $index;
                                    $isActive = $currentStep === $index;
                                    $hasDetail = !empty($step['detail']) || !empty($step['time']);
                                @endphp
                                <div class="flex gap-4 {{ !$isCompleted && !$isActive ? 'opacity-40' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <div class="w-3 h-3 rounded-full {{ $isCompleted ? 'bg-green-500' : ($isActive ? 'bg-pln-primary ring-4 ring-pln-primary/20' : 'bg-gray-300') }}"></div>
                                        @if($index < count($steps) - 1)
                                            <div class="w-0.5 h-full min-h-[40px] {{ $isCompleted ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold {{ $isCompleted ? 'text-green-700' : ($isActive ? 'text-pln-primary' : 'text-gray-500') }}">
                                                {{ $step['label'] }}
                                            </span>
                                            @if($isCompleted)
                                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Selesai</span>
                                            @elseif($isActive)
                                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full animate-pulse">Proses Saat Ini</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $step['desc'] }}</p>
                                        @if($hasDetail)
                                            <div class="mt-2 text-sm bg-gray-50 rounded-lg p-3">
                                                @if($step['detail'])
                                                    <p class="text-gray-700">{!! $step['detail'] !!}</p>
                                                @endif
                                                @if($step['time'])
                                                    <p class="text-gray-500 mt-1">
                                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $step['time'] }}
                                                        @if($step['by'])
                                                            <span class="ml-2">oleh <strong>{{ $step['by'] }}</strong></span>
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        @elseif($isActive)
                                            <div class="mt-2 text-sm bg-blue-50 rounded-lg p-3 text-blue-700">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            {{-- DRAFT Status Card --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center mb-6">
                <div class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-semibold">Status: DRAFT - Menunggu persetujuan untuk dikirim</span>
                </div>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Gudang Asal</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->gudangAsal->nama ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Gudang Tujuan</p>
                            <p class="font-semibold text-gray-900">{{ $gudangTujuanNama }}</p>
                            @if($suratJalan->gudang_tujuan_is_custom)
                                <p class="text-sm text-gray-500 font-normal">{{ $gudangTujuanAlamat }}</p>
                                <p class="text-sm text-gray-500 font-normal">{{ $gudangTujuanTelepon }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">PIC Tujuan</p>
                            <p class="font-semibold text-gray-900">
                                {{ $suratJalan->picTujuan->nama ?? '-' }}
                                @if(!empty($suratJalan->picTujuan?->no_hp))
                                    <span class="text-sm text-gray-500 font-normal">({{ $suratJalan->picTujuan->no_hp }})</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->tanggal?->format('Y-m-d') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nama Driver</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->nama_driver ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jenis Kendaraan</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->jenis_kendaraan ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nomor Plat</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->nomor_plat ?? '-' }}</p>
                        </div>
                        @if(($suratJalan->tipe ?? '') === 'PEMINJAMAN')
                            <div>
                                <p class="text-sm text-gray-500">Rencana Pengembalian</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $peminjaman?->batas_waktu_kembali?->format('Y-m-d') ?? '-' }}
                                </p>
                            </div>
                            @if($peminjaman?->waktu_pengembalian)
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Dikembalikan</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $peminjaman->waktu_pengembalian->format('Y-m-d') }}
                                </p>
                            </div>
                            @endif
                        @endif
                        <div>
                            <p class="text-sm text-gray-500">Tipe</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->tipe ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->status ?? '-' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Catatan</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->catatan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Item Surat Jalan</h3>
                </div>
                <div class="overflow-x-auto">
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

            {{-- Lampiran Gambar --}}
            @if($suratJalan->attachments->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Lampiran Dokumentasi</h3>
                        <p class="text-sm text-gray-500">{{ $suratJalan->attachments->count() }} gambar</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($suratJalan->attachments as $attachment)
                                <div class="relative group">
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                        <img src="{{ Storage::url($attachment->file_path) }}"
                                             alt="{{ $attachment->file_name }}"
                                             class="w-full h-40 object-cover rounded-lg border border-gray-200 hover:opacity-90 transition">
                                    </a>
                                    <p class="text-xs text-gray-500 mt-2 truncate">{{ $attachment->file_name }}</p>
                                    @if($suratJalan->status === 'DRAFT' && Auth::user()?->gudang_id === $suratJalan->gudang_asal_id)
                                        <form action="{{ route('gudang.surat-jalan.delete-attachment', $attachment->id) }}"
                                              method="POST"
                                              class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-md"
                                                    onclick="return confirm('Hapus lampiran ini?')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DRAFT' && Auth::user()?->gudang_id === $suratJalan->gudang_asal_id)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg mt-6 p-4">
                    <p class="text-yellow-800 text-sm">
                        <strong>Perhatian:</strong> Belum ada lampiran gambar. Upload minimal 1 gambar sebelum mengirim surat jalan.
                        <a href="{{ route('gudang.surat-jalan.edit', $suratJalan->id) }}" class="underline font-semibold">Edit draft untuk upload gambar</a>.
                    </p>
                </div>
            @endif

            {{-- Action Buttons for Operator --}}
            @php
                $userGudangId = Auth::user()?->gudang_id;
                $isGudangTujuan = $userGudangId === $suratJalan->gudang_tujuan_id;
                $isGudangAsal = $userGudangId === $suratJalan->gudang_asal_id;
            @endphp

            {{-- Tombol Terima Barang untuk Operator Gudang Tujuan (status DIPERIKSA) --}}
            @if($suratJalan->status === 'DIPERIKSA' && $isGudangTujuan)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Konfirmasi Penerimaan</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            @if($suratJalan->tipe === 'PENGEMBALIAN')
                                Barang pengembalian telah diperiksa oleh security. Klik tombol di bawah untuk menerima barang dan menyelesaikan proses peminjaman.
                            @else
                                Barang telah diperiksa oleh security. Klik tombol di bawah untuk menerima barang ke gudang Anda.
                            @endif
                        </p>
                        <form method="POST" action="{{ route('gudang.surat-jalan.terima', $suratJalan->id) }}"
                              x-data="{ submitting: false }"
                              @submit="submitting = true">
                            @csrf
                            <button type="submit"
                                    :disabled="submitting"
                                    class="w-full sm:w-auto px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-3">
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="submitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : '{{ $suratJalan->tipe === "PENGEMBALIAN" ? "Terima & Selesaikan Peminjaman" : "Terima Barang" }}'"></span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Tombol Pengembalian Pinjaman untuk Operator Gudang Peminjam (status DITERIMA, tipe PEMINJAMAN) --}}
            @if($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->status === 'DITERIMA' && $isGudangTujuan)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Pengembalian Barang</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Barang peminjaman sudah diterima. Jika sudah selesai digunakan, Anda dapat membuat surat jalan pengembalian.
                        </p>
                        <a href="{{ route('gudang.surat-jalan.index') }}?open_return=1&peminjaman_id={{ $peminjaman?->id }}"
                           class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg shadow-sm transition duration-150 gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            Pengembalian Pinjaman
                        </a>
                    </div>
                </div>
            @endif

            {{-- Konfirmasi Pengembalian Manual untuk Gudang Eksternal --}}
            @if($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->status === 'MENUNGGU_DIKEMBALIKAN' && $isGudangAsal && $suratJalan->gudang_tujuan_is_custom)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Konfirmasi Pengembalian</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Surat jalan ini dikirim ke gudang eksternal. Klik tombol di bawah jika barang sudah dikembalikan.
                        </p>
                        <form method="POST" action="{{ route('gudang.surat-jalan.confirm-return', $suratJalan->id) }}"
                              x-data="{ submitting: false }"
                              @submit="submitting = true">
                            @csrf
                            <button type="submit"
                                    :disabled="submitting"
                                    class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold rounded-lg shadow-sm transition duration-150 gap-2">
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="submitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : 'Barang Sudah Dikembalikan'"></span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Status Info Cards --}}
            @if($suratJalan->status === 'DIKIRIM' || $suratJalan->status === 'DIKEMBALIKAN')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-blue-100 text-blue-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Menunggu pemeriksaan oleh Security</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'MENUNGGU_DIKEMBALIKAN')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-yellow-100 text-yellow-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Menunggu konfirmasi pengembalian</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITERIMA' && $suratJalan->tipe === 'PEMINJAMAN' && !$isGudangTujuan)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-yellow-100 text-yellow-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Barang dipinjam - Menunggu pengembalian</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'SELESAI')
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan telah SELESAI</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITOLAK')
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-red-100 text-red-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan telah DITOLAK</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
