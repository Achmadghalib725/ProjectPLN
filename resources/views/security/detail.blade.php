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

            {{-- Header Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Detail Surat Jalan</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $suratJalan->nomor }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('dashboard') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150">
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
                                <span class="font-semibold">Surat Jalan Ditolak</span>
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
            @endif

            {{-- Info Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Informasi Pengiriman</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm text-gray-500">Tipe</dt>
                                    <dd class="mt-1">
                                        @php
                                            $tipeBadge = match($suratJalan->tipe) {
                                                'TRANSFER' => 'bg-blue-100 text-blue-800',
                                                'PEMINJAMAN' => 'bg-purple-100 text-purple-800',
                                                'PENGEMBALIAN' => 'bg-orange-100 text-orange-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-sm font-semibold rounded-full {{ $tipeBadge }}">
                                            {{ $suratJalan->tipe }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Tanggal Kirim</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ \Carbon\Carbon::parse($suratJalan->tanggal)->format('d F Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Nama Driver</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->nama_driver ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Jenis Kendaraan</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->jenis_kendaraan ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Nomor Plat</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->nomor_plat ?? '-' }}</dd>
                                </div>
                                @if($suratJalan->catatan)
                                <div>
                                    <dt class="text-sm text-gray-500">Catatan</dt>
                                    <dd class="mt-1 text-gray-900">{{ $suratJalan->catatan }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Ringkasan</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm text-gray-500">Tanggal</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->tanggal?->format('Y-m-d') ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Tipe</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->tipe ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Status</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->status ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Catatan</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->catatan ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
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

            {{-- Action Buttons --}}
            @if(in_array($suratJalan->status, ['DIKIRIM', 'DIKEMBALIKAN']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Konfirmasi Pemeriksaan</h3>
                        <div class="flex flex-col sm:flex-row gap-4">
                            {{-- Terima Button --}}
                            <form action="{{ route('security.terima', $suratJalan->id) }}" method="POST" class="flex-1"
                                  x-data="{ submitting: false }"
                                  @submit="submitting = true">
                                @csrf
                                <button type="submit"
                                        :disabled="submitting"
                                        class="w-full px-6 py-4 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold text-lg rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-3">
                                    <svg x-show="!submitting" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <svg x-show="submitting" class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Diperiksa'"></span>
                                </button>
                            </form>

                            {{-- Tolak Button --}}
                            <div x-data="{ showModal: false }" class="flex-1 sm:flex-none">
                                <button type="button"
                                        @click="showModal = true"
                                        class="w-full px-6 py-4 bg-red-600 hover:bg-red-700 text-white font-bold text-lg rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <div class="flex items-center justify-center min-h-screen px-4">
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 z-0" @click="showModal = false"></div>
                                        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                                            <h3 class="text-lg font-bold text-gray-900 mb-4">Tolak Surat Jalan</h3>
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
                                                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                                                              placeholder="Masukkan alasan penolakan..."></textarea>
                                                </div>
                                                <div class="flex justify-end gap-3">
                                                    <button type="button"
                                                            @click="showModal = false"
                                                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg">
                                                        Batal
                                                    </button>
                                                    <button type="submit"
                                                            :disabled="submitting"
                                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-medium rounded-lg flex items-center gap-2">
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
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan ini sudah DIPERIKSA - Menunggu konfirmasi operator</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITERIMA')
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan ini sudah DITERIMA oleh operator</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITOLAK')
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-red-100 text-red-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan ini telah DITOLAK</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DRAFT')
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan masih berstatus DRAFT, belum bisa dikonfirmasi</span>
                    </div>
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-blue-100 text-blue-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan ini berstatus {{ $suratJalan->status }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
