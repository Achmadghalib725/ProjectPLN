@use('Illuminate\Support\Facades\Storage')
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

            @php
                $isAdminView = $isAdmin ?? (Auth::user()?->role === 'admin');
                $isManagerView = $isManager ?? (Auth::user()?->role === 'manager');
                $accessibleGudangIds = $accessibleGudangIds ?? (Auth::user()?->gudang_id ? [Auth::user()->gudang_id] : []);
                $isGudangAsalView = !empty($accessibleGudangIds) && in_array($suratJalan->gudang_asal_id, $accessibleGudangIds, true);
                $isGudangTujuanView = !empty($accessibleGudangIds) && in_array($suratJalan->gudang_tujuan_id, $accessibleGudangIds, true);
                $canEditDraft = !$isManagerView && $isGudangAsalView;
                $canApproveManager = $isAdminView || ($isManagerView && $isGudangAsalView);
            @endphp

            {{-- Header Card --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                        <div class="text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Surat Jalan</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $suratJalan->nomor }}</p>
                        </div>
                        {{-- Buttons: Mobile (full width grid) / Desktop (inline flex) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            {{-- PDF Buttons Row --}}
                            <div class="flex gap-2">
                                <a href="{{ route('admin.surat-jalan.preview', $suratJalan->id) }}"
                                   target="_blank"
                                   class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 flex items-center justify-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span class="hidden sm:inline">Preview PDF</span>
                                    <span class="sm:hidden">Preview</span>
                                </a>
                                <a href="{{ route('admin.surat-jalan.pdf', $suratJalan->id) }}"
                                   class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 flex items-center justify-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </div>
                            @if(in_array($suratJalan->status, ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'], true) && $canEditDraft)
                                {{-- Draft Actions Row --}}
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.surat-jalan.edit', $suratJalan->id) }}"
                                       class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm text-center">
                                        Edit Draft
                                    </a>
                                    <form method="POST" action="{{ route('admin.surat-jalan.request-approval', $suratJalan->id) }}" class="flex-1 sm:flex-none">
                                        @csrf
                                        <button type="submit"
                                                class="w-full bg-pln-primary hover:bg-pln-light active:scale-95 text-white font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm">
                                            {{ in_array($suratJalan->status, ['DITOLAK_PERSETUJUAN', 'DITOLAK'], true) ? 'Ajukan Ulang' : 'Minta Persetujuan' }}
                                        </button>
                                    </form>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button"
                                        @click="$dispatch('open-delete-modal', {
                                            title: 'Hapus Draft Surat Jalan',
                                            message: 'Apakah Anda yakin ingin menghapus draft surat jalan ini? Data tidak dapat dikembalikan.',
                                            action: '{{ route('admin.surat-jalan.destroy', $suratJalan->id) }}'
                                        })"
                                        class="flex-1 sm:flex-none bg-red-500 hover:bg-red-600 active:scale-95 text-white font-semibold py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm">
                                        Hapus Draft
                                    </button>
                                    <a href="{{ route('admin.surat-jalan.index') }}"
                                       class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm text-center">
                                        Kembali
                                    </a>
                                </div>
                            @elseif($suratJalan->status === 'MENUNGGU_PERSETUJUAN' && $canApproveManager)
                                {{-- Approval Actions Row --}}
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.surat-jalan.approve', $suratJalan->id) }}" class="flex-1 sm:flex-none">
                                        @csrf
                                        <button type="submit"
                                                class="w-full bg-pln-primary hover:bg-pln-light active:scale-95 text-white font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm">
                                            Approve & Kirim
                                        </button>
                                    </form>
                                    <button type="button"
                                        @click="$dispatch('open-reject-approval', { action: '{{ route('admin.surat-jalan.reject-approval', $suratJalan->id) }}' })"
                                        class="flex-1 sm:flex-none bg-red-500 hover:bg-red-600 active:scale-95 text-white font-semibold py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm">
                                        Tolak Persetujuan
                                    </button>
                                </div>
                                <a href="{{ route('admin.surat-jalan.index') }}"
                                   class="bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm text-center">
                                    Kembali
                                </a>
                            @else
                                @if($suratJalan->status !== 'SELESAI')
                                    <div class="flex gap-2">
                                        <button type="button"
                                            @click="$dispatch('open-delete-modal', {
                                                title: 'Hapus Surat Jalan',
                                                message: 'Apakah Anda yakin ingin membatalkan surat jalan {{ $suratJalan->nomor }}? {{ in_array($suratJalan->status, ['DIKIRIM', 'DIPERIKSA_PENGIRIM', 'DIPERIKSA_PENERIMA', 'DITERIMA', 'MENUNGGU_DIKEMBALIKAN', 'DIKEMBALIKAN', 'DIPERIKSA']) ? 'Semua pergerakan stok akan di-rollback.' : '' }}',
                                                action: '{{ route('admin.surat-jalan.destroy', $suratJalan->id) }}'
                                            })"
                                            class="flex-1 sm:flex-none bg-red-500 hover:bg-red-600 active:scale-95 text-white font-semibold py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm">
                                            Hapus Surat Jalan
                                        </button>
                                        <a href="{{ route('admin.surat-jalan.index') }}"
                                           class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm text-center">
                                            Kembali
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('admin.surat-jalan.index') }}"
                                       class="bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium py-2.5 sm:py-1.5 px-3 rounded-lg sm:rounded-md transition duration-150 text-sm text-center">
                                        Kembali
                                    </a>
                                @endif
                            @endif
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
                    if ($suratJalan->gudang_tujuan_is_custom) {
                        $steps = [
                            [
                                'label' => 'Dikirim',
                                'desc' => 'Barang dikirim',
                                'detail' => !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                    ? "Dikirim dari <strong>{$sjKirim->gudangAsal->nama}</strong> ke <strong>{$gudangTujuanNama}</strong>"
                                    : null,
                                'time' => !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                    ? $historyTimeText($sjKirimHistory, ['DIKIRIM', 'DIPERIKSA_PENERIMA', 'DIPERIKSA', 'DITERIMA', 'SELESAI'], $sjKirim->waktu_ttd_pembuat ?? $sjKirim->updated_at)
                                    : null,
                                'by' => !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                    ? $sjKirim->pembuat?->name
                                    : null,
                            ],
                            [
                                'label' => 'Selesai',
                                'desc' => 'Transfer selesai',
                                'detail' => $sjKirim->status === 'SELESAI'
                                    ? "Dikirim ke <strong>{$gudangTujuanNama}</strong>"
                                    : null,
                                'time' => $sjKirim->status === 'SELESAI'
                                    ? $historyTimeText($sjKirimHistory, 'SELESAI', $sjKirim->waktu_ttd_penerima ?? $sjKirim->updated_at)
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
                            'SELESAI' => 2,
                            'DITOLAK' => -2,
                        ];
                        $currentStep = $statusIndexMap[$suratStatus] ?? 0;
                    } else {
                        $steps = [
                            [
                                'label' => 'Dikirim',
                                'desc' => 'Barang dikirim',
                                'detail' => !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                    ? "Dikirim dari <strong>{$sjKirim->gudangAsal->nama}</strong> ke <strong>{$gudangTujuanNama}</strong>"
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
                                    ? "Diperiksa oleh Security di <strong>{$gudangTujuanNama}</strong>"
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
                                    ? "Diterima di <strong>{$gudangTujuanNama}</strong>"
                                    : null,
                                'time' => $sjKirim->status === 'SELESAI'
                                    ? $historyTimeText($sjKirimHistory, 'SELESAI', $sjKirim->waktu_ttd_penerima ?? $sjKirim->updated_at)
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
                    $sjKirimHistory = $historyFor($sjKirim);
                    $sjKembaliHistory = $historyFor($sjKembali);

                    $peminjamanStatus = $peminjaman?->status ?? 'DIAJUKAN';
                    $sjKirimStatus = $sjKirim?->status ?? 'DRAFT';
                    $sjKembaliStatus = $sjKembali?->status ?? null;

                    if ($suratJalan->gudang_tujuan_is_custom) {
                        $menungguDikembalikanAt = $historyTime($sjKirimHistory, 'MENUNGGU_DIKEMBALIKAN');
                        $showMenungguDikembalikan = (bool) $menungguDikembalikanAt
                            || $suratStatus === 'MENUNGGU_DIKEMBALIKAN'
                            || $suratStatus === 'SELESAI'
                            || $peminjamanStatus === 'SELESAI';
                        $steps = [
                            [
                                'label' => 'Dikirim',
                                'desc' => 'Barang dikirim ke peminjam',
                                'detail' => $sjKirim && !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                    ? "Dikirim dari <strong>{$gudangPemilikNama}</strong> ke <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $historyTimeText($sjKirimHistory, ['DIKIRIM', 'MENUNGGU_DIKEMBALIKAN', 'DIKEMBALIKAN', 'SELESAI'], $peminjaman?->waktu_kirim),
                                'by' => $sjKirim?->pembuat?->name,
                            ],
                            [
                                'label' => 'Menunggu Dikembalikan',
                                'desc' => 'Menunggu konfirmasi pengembalian',
                                'detail' => $showMenungguDikembalikan
                                    ? "Menunggu pengembalian dari <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $showMenungguDikembalikan
                                    ? $historyTimeText($sjKirimHistory, 'MENUNGGU_DIKEMBALIKAN', $suratJalan->updated_at)
                                    : null,
                                'by' => null,
                            ],
                            [
                                'label' => 'Selesai',
                                'desc' => 'Pengembalian dikonfirmasi',
                                'detail' => ($peminjamanStatus === 'SELESAI' || $suratStatus === 'SELESAI')
                                    ? "Barang telah dikembalikan ke <strong>{$gudangPemilikNama}</strong>"
                                    : null,
                                'time' => $historyTimeText($sjKirimHistory, 'SELESAI', $peminjaman?->waktu_selesai),
                                'by' => $historyActor($sjKirimHistory, 'SELESAI'),
                            ],
                        ];

                        if ($peminjamanStatus === 'SELESAI' || $suratStatus === 'SELESAI') {
                            $currentStep = 3;
                        } elseif ($suratStatus === 'MENUNGGU_DIKEMBALIKAN') {
                            $currentStep = 2;
                        } elseif (!in_array($sjKirimStatus, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)) {
                            $currentStep = 1;
                        } else {
                            $currentStep = 0;
                        }
                    } else {
                        $kirimCheckedAt = $historyTime($sjKirimHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA']);
                        $steps = [
                            [
                                'label' => 'Dikirim',
                                'desc' => 'Barang dikirim ke peminjam',
                                'detail' => $sjKirim && !in_array($sjKirim->status, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true)
                                    ? "Dikirim dari <strong>{$gudangPemilikNama}</strong> ke <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $historyTimeText($sjKirimHistory, ['DIKIRIM', 'DIPERIKSA_PENERIMA', 'DIPERIKSA', 'DITERIMA', 'SELESAI', 'MENUNGGU_DIKEMBALIKAN', 'DIKEMBALIKAN'], $peminjaman?->waktu_kirim),
                                'by' => $sjKirim?->pembuat?->name,
                            ],
                            [
                                'label' => 'Diperiksa',
                                'desc' => 'Security gudang tujuan',
                                'detail' => $sjKirim && $kirimCheckedAt
                                    ? "Diperiksa oleh Security di <strong>{$gudangPeminjamNama}</strong>"
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
                                    ? "Diterima di <strong>{$gudangPeminjamNama}</strong>"
                                    : null,
                                'time' => $historyTimeText($sjKirimHistory, 'DITERIMA', $peminjaman?->waktu_diterima),
                                'by' => $historyActor($sjKirimHistory, 'DITERIMA'),
                            ],
                            [
                                'label' => 'Dikembalikan',
                                'desc' => 'Barang dikembalikan',
                                'detail' => $sjKembali && in_array($sjKembali->status, ['DIKEMBALIKAN', 'DIPERIKSA_PENGIRIM', 'DIPERIKSA_PENERIMA', 'DIPERIKSA', 'SELESAI'])
                                    ? "Dikembalikan dari <strong>{$gudangPeminjamNama}</strong> ke <strong>{$gudangPemilikNama}</strong>"
                                    : null,
                                'time' => $historyTimeText($sjKembaliHistory, ['DIKEMBALIKAN', 'DIPERIKSA_PENERIMA', 'DIPERIKSA', 'SELESAI'], $peminjaman?->waktu_pengembalian),
                                'by' => $sjKembali?->pembuat?->name,
                            ],
                            [
                                'label' => 'Diperiksa',
                                'desc' => 'Security gudang pemilik',
                                'detail' => $sjKembali && in_array($sjKembali->status, ['DIPERIKSA_PENERIMA', 'DIPERIKSA', 'SELESAI'])
                                    ? "Diperiksa oleh Security di <strong>{$gudangPemilikNama}</strong>"
                                    : null,
                                'time' => $sjKembali && in_array($sjKembali->status, ['DIPERIKSA_PENERIMA', 'DIPERIKSA', 'SELESAI'])
                                    ? $historyTimeText($sjKembaliHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA'], $sjKembali->updated_at) : null,
                                'by' => $historyActor($sjKembaliHistory, ['DIPERIKSA_PENERIMA', 'DIPERIKSA']),
                            ],
                            [
                                'label' => 'Selesai',
                                'desc' => 'Peminjaman selesai',
                                'detail' => $peminjaman && $peminjaman->status === 'SELESAI'
                                    ? "Barang telah dikembalikan ke <strong>{$gudangPemilikNama}</strong>"
                                    : null,
                                'time' => $historyTimeText($sjKembaliHistory, 'SELESAI', $peminjaman?->waktu_selesai),
                                'by' => $historyActor($sjKembaliHistory, 'SELESAI'),
                            ],
                        ];

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
                $showBlurredOverlay = in_array($suratStatus, ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN', 'DIPERIKSA_PENGIRIM', 'DITOLAK'], true)
                    || ($tipe === 'PEMINJAMAN' && in_array($returnStatus, ['DIPERIKSA_PENGIRIM', 'MENUNGGU_PERSETUJUAN'], true));
                // Exception: show progress if PEMINJAMAN is already in progress
                $showProgress = !$showBlurredOverlay || ($suratJalan->tipe === 'PEMINJAMAN' && $peminjaman && $peminjaman->status !== 'DIAJUKAN' && $suratStatus !== 'DITOLAK' && !in_array($returnStatus, ['DIPERIKSA_PENGIRIM', 'MENUNGGU_PERSETUJUAN'], true));
            @endphp

            <div id="surat-jalan-progress-container" data-surat-jalan-progress>
            {{-- Riwayat Status - Only show if not DRAFT --}}
            @if($showProgress)
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
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 flex items-center justify-center text-xs sm:text-sm font-bold {{ $circleClass }}">
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
            {{-- DRAFT Status Card with Blurred Progress Background --}}
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

                // Check if PEMINJAMAN with return pending (security check or manager approval)
                $isPeminjamanReturnPending = $tipe === 'PEMINJAMAN' && in_array($returnStatus, ['DIPERIKSA_PENGIRIM', 'MENUNGGU_PERSETUJUAN'], true);

                if ($isPeminjamanReturnPending) {
                    $draftMessage = $returnStatus === 'MENUNGGU_PERSETUJUAN'
                        ? 'Status: MENUNGGU PERSETUJUAN - Surat pengembalian terkait sedang menunggu persetujuan manager.'
                        : 'Status: MENUNGGU PEMERIKSAAN - Surat pengembalian terkait sedang menunggu pemeriksaan security.';
                } else {
                    $draftMessage = $rejectMessage ?? match ($suratStatus) {
                        'MENUNGGU_PERSETUJUAN' => 'Status: MENUNGGU PERSETUJUAN - Menunggu persetujuan manager.',
                        'DIPERIKSA_PENGIRIM' => 'Status: MENUNGGU PERSETUJUAN - Menunggu pemeriksaan security pengirim.',
                        'DITOLAK_PERSETUJUAN' => 'Status: DITOLAK PERSETUJUAN - Silakan perbaiki dan ajukan ulang.',
                        'DITOLAK' => 'Status: DITOLAK - Ditolak oleh security.',
                        default => 'Status: DRAFT - Belum diajukan untuk persetujuan.',
                    };
                }

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
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 flex items-center justify-center text-xs sm:text-sm font-bold bg-white text-gray-400 border-gray-300">
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
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $gudangTujuanNama }}</p>
                            @if($suratJalan->gudang_tujuan_is_custom)
                                <p class="text-xs sm:text-sm text-gray-500 font-normal">{{ $gudangTujuanAlamat }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 font-normal">{{ $gudangTujuanTelepon }}</p>
                            @endif
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <p class="text-xs sm:text-sm text-gray-500">PIC Tujuan</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">
                                {{ $suratJalan->picTujuan->nama ?? $suratJalan->pic_tujuan_custom_nama ?? '-' }}
                                @php
                                    $picNoHp = $suratJalan->picTujuan?->no_hp ?? $suratJalan->pic_tujuan_custom_no_hp;
                                @endphp
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
                        @if(($suratJalan->tipe ?? '') === 'PEMINJAMAN')
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500">Rencana Pengembalian</p>
                                <p class="font-semibold text-sm sm:text-base text-gray-900">
                                    {{ $peminjaman?->batas_waktu_kembali?->format('d M Y') ?? '-' }}
                                </p>
                            </div>
                            @if($peminjaman?->waktu_pengembalian)
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500">Tanggal Dikembalikan</p>
                                <p class="font-semibold text-sm sm:text-base text-gray-900">
                                    {{ $peminjaman->waktu_pengembalian->format('d M Y') }}
                                </p>
                            </div>
                            @endif
                        @endif
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Tipe</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->tipe ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Status</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900">{{ $suratJalan->status ?? '-' }}</p>
                        </div>

                        {{-- Linked Surat Jalan Section --}}
                        @if($suratJalan->tipe === 'PEMINJAMAN' && $peminjaman)
                        <div class="col-span-2">
                            <p class="text-xs sm:text-sm text-gray-500">Surat Pengembalian Terkait</p>
                            @if($peminjaman->suratJalanKembali)
                                <a href="{{ route('admin.surat-jalan.show', $peminjaman->suratJalanKembali->id) }}"
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
                            <a href="{{ route('admin.surat-jalan.show', $peminjaman->suratJalanKirim->id) }}"
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
                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Item Surat Jalan</h3>
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

            {{-- Lampiran Gambar --}}
            @if($suratJalan->attachments->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                    <div class="p-4 sm:p-6 border-b border-gray-100">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">Lampiran Dokumentasi</h3>
                        <p class="text-xs sm:text-sm text-gray-500">{{ $suratJalan->attachments->count() }} gambar</p>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                            @foreach($suratJalan->attachments as $attachment)
                                <div class="relative group">
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                        <img src="{{ Storage::url($attachment->file_path) }}"
                                             alt="{{ $attachment->file_name }}"
                                             class="w-full h-28 sm:h-40 object-cover rounded-lg border border-gray-200 hover:opacity-90 transition">
                                    </a>
                                    <p class="text-[10px] sm:text-xs text-gray-500 mt-1.5 sm:mt-2 truncate">{{ $attachment->file_name }}</p>
                                    @if(in_array($suratJalan->status, ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'], true) && $isGudangAsalView)
                                        <form action="{{ route('admin.surat-jalan.delete-attachment', $attachment->id) }}"
                                              method="POST"
                                              class="absolute top-2 right-2 sm:opacity-0 sm:group-hover:opacity-100 transition">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-500 hover:bg-red-600 active:scale-95 text-white p-1.5 sm:p-1 rounded-lg sm:rounded-md"
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
            @elseif(in_array($suratJalan->status, ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'], true) && $isGudangAsalView)
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl mt-4 sm:mt-6 p-4">
                    <p class="text-yellow-800 text-xs sm:text-sm">
                        <strong>Perhatian:</strong> Belum ada lampiran gambar. Upload minimal 1 gambar sebelum meminta persetujuan.
                        <a href="{{ route('admin.surat-jalan.edit', $suratJalan->id) }}" class="underline font-semibold">Edit draft untuk upload gambar</a>.
                    </p>
                </div>
            @endif

            {{-- Action Buttons for Operator --}}
            @php
                $isGudangTujuan = $isGudangTujuanView;
                $isGudangAsal = $isGudangAsalView;
            @endphp

            {{-- Tombol Terima Barang untuk Operator Gudang Tujuan (status DIPERIKSA) --}}
            @if(in_array($suratJalan->status, ['DIPERIKSA', 'DIPERIKSA_PENERIMA'], true) && $isGudangTujuan && !$isManagerView)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Konfirmasi Penerimaan</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">
                            @if($suratJalan->tipe === 'PENGEMBALIAN')
                                Barang pengembalian telah diperiksa oleh security. Klik tombol di bawah untuk menerima barang dan menyelesaikan proses peminjaman.
                            @else
                                Barang telah diperiksa oleh security. Klik tombol di bawah untuk menerima barang ke gudang Anda.
                            @endif
                        </p>
                        <form method="POST" action="{{ route('admin.surat-jalan.terima', $suratJalan->id) }}"
                              x-data="{ submitting: false }"
                              @submit="submitting = true">
                            @csrf
                            <button type="submit"
                                    :disabled="submitting"
                                    class="w-full sm:w-auto px-4 sm:px-6 py-3 sm:py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 active:scale-[0.98] text-white font-bold text-sm sm:text-base rounded-xl sm:rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-2 sm:gap-3">
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="submitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : '{{ $suratJalan->tipe === "PENGEMBALIAN" ? "Terima & Selesaikan" : "Terima Barang" }}'"></span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Tombol Pengembalian Pinjaman untuk Operator Gudang Peminjam atau Admin (status DITERIMA, tipe PEMINJAMAN) --}}
            @if($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->status === 'DITERIMA' && ($isGudangTujuan || ($isAdmin ?? false)) && $peminjaman && !$peminjaman->surat_jalan_kembali_id && !$isManagerView)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Pengembalian Barang</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">
                            @if($isAdmin ?? false)
                                Mode admin: Anda dapat membuat surat jalan pengembalian untuk peminjaman ini.
                            @else
                                Barang peminjaman sudah diterima. Jika sudah selesai digunakan, Anda dapat membuat surat jalan pengembalian.
                            @endif
                        </p>
                        <button type="button"
                                @click="$dispatch('open-modal', 'return-peminjaman-modal')"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-orange-500 hover:bg-orange-600 active:scale-[0.98] text-white font-bold text-sm sm:text-base rounded-xl sm:rounded-lg shadow-sm transition duration-150 gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            Buat Surat Pengembalian
                        </button>
                    </div>
                </div>
            @endif

            {{-- Tombol Buat Ulang Pengembalian jika surat pengembalian ditolak --}}
            @if($suratJalan->tipe === 'PEMINJAMAN' && $peminjaman && $peminjaman->suratJalanKembali?->status === 'DITOLAK' && ($isGudangTujuan || ($isAdmin ?? false)) && !$isManagerView)
                <div class="bg-red-50 border border-red-200 rounded-xl mt-4 sm:mt-6">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-red-900 mb-3 sm:mb-4">Pengembalian Ditolak</h3>
                        <p class="text-xs sm:text-sm text-red-700 mb-3 sm:mb-4">
                            Surat pengembalian sebelumnya ditolak oleh security. Silakan buat ulang surat pengembalian.
                        </p>
                        <button type="button"
                                @click="$dispatch('open-modal', 'return-peminjaman-modal')"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-red-600 hover:bg-red-700 active:scale-[0.98] text-white font-bold text-sm sm:text-base rounded-xl sm:rounded-lg shadow-sm transition duration-150 gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            Buat Ulang Surat Pengembalian
                        </button>
                    </div>
                </div>
            @endif

            {{-- Konfirmasi Pengembalian Manual untuk Gudang Eksternal --}}
            @if($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->status === 'MENUNGGU_DIKEMBALIKAN' && ($isGudangAsal || $isAdminView) && $suratJalan->gudang_tujuan_is_custom && !$isManagerView)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Konfirmasi Pengembalian</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">
                            Surat jalan ini dikirim ke gudang eksternal. Klik tombol di bawah jika barang sudah dikembalikan.
                        </p>
                        <form method="POST" action="{{ route('admin.surat-jalan.confirm-return', $suratJalan->id) }}"
                              x-data="{ submitting: false }"
                              @submit="submitting = true">
                            @csrf
                            <button type="submit"
                                    :disabled="submitting"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 active:scale-[0.98] text-white font-bold text-sm sm:text-base rounded-xl sm:rounded-lg shadow-sm transition duration-150 gap-2">
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

            {{-- Info Pengembalian Menunggu Diterima (untuk PEMINJAMAN ketika surat kembali sudah DIPERIKSA) --}}
            @if($suratJalan->tipe === 'PEMINJAMAN' && in_array(($peminjaman?->suratJalanKembali?->status ?? ''), ['DIPERIKSA', 'DIPERIKSA_PENERIMA'], true))
                <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mt-4 sm:mt-6">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Pengembalian Menunggu Diterima</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">
                            Surat jalan pengembalian <strong>{{ $peminjaman->suratJalanKembali->nomor }}</strong> sudah diperiksa oleh security dan menunggu untuk diterima.
                            Klik tombol di bawah untuk menerima barang dan menyelesaikan peminjaman.
                        </p>
                        <a href="{{ route('admin.surat-jalan.show', $peminjaman->suratJalanKembali->id) }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-green-600 hover:bg-green-700 active:scale-[0.98] text-white font-bold text-sm sm:text-base rounded-xl sm:rounded-lg shadow-sm transition duration-150 gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Lihat & Terima Pengembalian
                        </a>
                    </div>
                </div>
            @endif

            {{-- Status Info Cards --}}
            @if($suratJalan->status === 'DIKIRIM' || $suratJalan->status === 'DIKEMBALIKAN')
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-blue-100 text-blue-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base text-center">Menunggu pemeriksaan oleh Security</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'MENUNGGU_DIKEMBALIKAN')
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-yellow-100 text-yellow-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base text-center">Menunggu konfirmasi pengembalian</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITERIMA' && $suratJalan->tipe === 'PEMINJAMAN' && !$isGudangTujuan && !$isManagerView)
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-yellow-100 text-yellow-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base text-center">Barang dipinjam - Menunggu pengembalian</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'SELESAI')
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 sm:p-6 text-center mt-4 sm:mt-6">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-2 px-4 sm:px-6 py-3 bg-green-100 text-green-800 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-sm sm:text-base">Surat Jalan telah SELESAI</span>
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
            @endif
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    {{-- Modal Pengembalian Peminjaman --}}
    @if($suratJalan->tipe === 'PEMINJAMAN' && $peminjaman && $peminjaman->status === 'DITERIMA' && (!$peminjaman->surat_jalan_kembali_id || $peminjaman->suratJalanKembali?->status === 'DITOLAK'))
    <x-modal name="return-peminjaman-modal" focusable>
        <div class="p-6"
             x-data="{
                selectedPic: '',
                labelPic: '',
                picOpen: false,
                pics: @js($pics->map(fn($pic) => [
                    'id' => $pic->id,
                    'nama' => $pic->nama,
                    'jabatan' => $pic->jabatan,
                ])->values()),
                customPic: {
                    nama: '',
                    jabatan: '',
                    no_hp: '',
                },
                get isCustomPic() {
                    return this.selectedPic === 'lainnya';
                },
             }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Pengembalian Barang Peminjaman</h3>
                    <p class="text-sm text-gray-500 mt-1">Buat surat jalan pengembalian untuk peminjaman ini.</p>
                    @if($isAdmin ?? false)
                        <p class="text-xs text-emerald-700 mt-2">Mode admin: surat pengembalian akan langsung diselesaikan.</p>
                    @endif
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                        @click="$dispatch('close-modal', 'return-peminjaman-modal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.surat-jalan.return') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @if($isAdmin ?? false)
                    <input type="hidden" name="admin_finish" value="1">
                @endif
                <input type="hidden" name="peminjaman_id" value="{{ $peminjaman->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Peminjaman</label>
                        <input type="text"
                               class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                               value="{{ $peminjaman->kode }}"
                               readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Pemilik (Tujuan)</label>
                        <input type="text"
                               class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                               value="{{ $peminjaman->gudangPemilik->nama ?? '-' }}"
                               readonly>
                    </div>
                    {{-- Custom Combobox PIC Tujuan --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">PIC Tujuan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text"
                                x-model="labelPic"
                                @click="picOpen = true"
                                @click.away="picOpen = false"
                                :required="!isCustomPic"
                                placeholder="Pilih dari daftar..."
                                readonly
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary cursor-pointer">

                            <input type="hidden" name="pic_tujuan_id" :value="selectedPic">

                            <div x-show="picOpen" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <div class="p-2 border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Pilih dari Daftar</div>
                                <template x-for="pic in pics" :key="pic.id">
                                    <button type="button"
                                            @click="selectedPic = pic.id; labelPic = pic.nama; picOpen = false; customPic = { nama: '', jabatan: '', no_hp: '' }"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-pln-primary hover:text-white transition">
                                        <span x-text="pic.nama + (pic.jabatan ? ' (' + pic.jabatan + ')' : '')"></span>
                                    </button>
                                </template>
                                <div class="border-t">
                                    <button type="button"
                                            @click="selectedPic = 'lainnya'; labelPic = 'Lainnya'; picOpen = false"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        Lainnya...
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengiriman</label>
                        <input type="date" name="tanggal_kirim" value="{{ now()->toDateString() }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary text-sm">
                    </div>

                    {{-- Form PIC Lainnya (di dalam grid) --}}
                    <div x-show="isCustomPic" x-cloak class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-gray-900 mb-3">PIC Lainnya</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC <span class="text-red-500">*</span></label>
                                <input type="text" name="pic_custom_nama" x-model="customPic.nama"
                                       :required="isCustomPic"
                                       placeholder="Nama PIC"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                <input type="text" name="pic_custom_jabatan" x-model="customPic.jabatan"
                                       placeholder="Jabatan"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No HP</label>
                                <input type="text" name="pic_custom_no_hp" x-model="customPic.no_hp"
                                       placeholder="No HP"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Driver <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="nama_driver"
                               required
                               placeholder="Contoh: Budi Santoso"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kendaraan <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="jenis_kendaraan"
                               required
                               placeholder="Contoh: Truk Box"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Plat <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="nomor_plat"
                               required
                               placeholder="Contoh: B 1234 CD"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan"
                              rows="3"
                              placeholder="Contoh: Pengembalian barang sesuai peminjaman..."
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50"></textarea>
                </div>

                {{-- Lampiran Gambar --}}
                <div class="border border-dashed border-gray-300 rounded-xl bg-gray-50/50 overflow-hidden" data-camera-capture data-target-input="attachments-return-detail-admin" data-max-files="3">
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Lampiran Foto</span>
                            <span class="text-xs text-gray-400">(Opsional)</span>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full" data-camera-status>0/3</span>
                    </div>

                    {{-- Camera Panel (Hidden by default) --}}
                    <div data-camera-panel class="hidden bg-black">
                        <div class="relative">
                            <video class="w-full h-48 object-cover" playsinline muted></video>
                            <canvas class="hidden"></canvas>
                            <div class="absolute bottom-3 left-0 right-0 flex items-center justify-center gap-3">
                                <button type="button"
                                        data-camera-capture-btn
                                        class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg hover:bg-gray-100 transition-colors">
                                    <div class="w-10 h-10 bg-red-500 rounded-full"></div>
                                </button>
                                <button type="button"
                                        data-camera-close
                                        class="w-10 h-10 bg-gray-800/80 rounded-full flex items-center justify-center text-white hover:bg-gray-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Upload Area --}}
                    <div class="p-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-2">
                                <button type="button" data-camera-open class="md:hidden inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Kamera
                                </button>
                                <label class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-pln-primary rounded-lg hover:bg-pln-light cursor-pointer transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Pilih File
                                    <input type="file"
                                           id="attachments-return-detail-admin"
                                           name="attachments[]"
                                           multiple
                                           accept="image/jpeg,image/jpg,image/png"
                                           capture="environment"
                                           class="hidden">
                                </label>
                            </div>
                            {{-- Info --}}
                            <p class="text-xs text-gray-500">JPG, PNG. Maks 10MB.</p>
                        </div>

                        {{-- Preview Grid (empty by default, populated dynamically) --}}
                        <div class="mt-4 grid grid-cols-3 gap-3 hidden" data-camera-preview></div>

                        {{-- Error message --}}
                        <p class="text-xs text-red-600 mt-2 hidden" data-camera-error></p>

                        {{-- Info note --}}
                        <p class="text-xs text-amber-600 mt-3 flex items-start gap-1">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Jika tidak upload, sistem akan gunakan lampiran dari surat jalan peminjaman awal.</span>
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg border border-gray-200">
                    <div class="p-4">
                        <p class="font-semibold text-gray-900">Barang yang Dikembalikan</p>
                        <p class="text-xs text-gray-500">Jumlah otomatis penuh sesuai peminjaman.</p>
                    </div>

                    {{-- Mobile Card Layout --}}
                    <div class="sm:hidden">
                        @forelse($peminjaman->items as $index => $item)
                            <div class="p-3 bg-white border-t border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-gray-500">Item #{{ $index + 1 }}</span>
                                </div>
                                <p class="font-medium text-sm text-gray-900 mb-2">
                                    {{ $item->item->kode ?? '-' }} - {{ $item->item->nama ?? 'Item' }}
                                </p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="block text-xs text-gray-500">Satuan</span>
                                        <span class="text-sm text-gray-900">{{ $item->item->satuan?->nama ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-gray-500">Jumlah</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $item->jumlah_dipinjam }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-6 text-center text-sm text-gray-500">
                                Tidak ada data item.
                            </div>
                        @endforelse
                    </div>

                    {{-- Desktop Table Layout --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($peminjaman->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->item->kode ?? '-' }} - {{ $item->item->nama ?? 'Item' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->item->satuan?->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->jumlah_dipinjam }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">
                                            Tidak ada data item.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4 border-t sm:flex-row sm:items-center sm:justify-end">
                    <button type="button"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition text-center"
                            @click="$dispatch('close-modal', 'return-peminjaman-modal')">
                        Batal
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-white bg-orange-500 rounded-md hover:bg-orange-600 transition text-center">
                        {{ ($isAdmin ?? false) ? 'Simpan dan Selesaikan (Admin)' : 'Simpan Draft Pengembalian' }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Echo) {
                return;
            }
            const suratJalanId = {{ (int) $suratJalan->id }};
            const refreshProgress = async () => {
                const current = document.querySelector('[data-surat-jalan-progress]');
                if (!current) {
                    return;
                }
                try {
                    const url = new URL(window.location.href);
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

            window.Echo.channel(`surat-jalan.detail.${suratJalanId}`)
                .listen('.SuratJalanStatusUpdated', (payload) => {
                    if (!payload || payload.id !== suratJalanId) {
                        return;
                    }
                    document.querySelectorAll('[data-surat-jalan-status-text]').forEach((node) => {
                        node.textContent = payload.status || '-';
                    });
                    refreshProgress();
                });
        });
    </script>
    <script>
        function setupCameraCapture(wrapper) {
            const inputId = wrapper.dataset.targetInput;
            const input = document.getElementById(inputId);
            const maxFiles = Number(wrapper.dataset.maxFiles || 3);
            const openBtn = wrapper.querySelector('[data-camera-open]');
            const captureBtn = wrapper.querySelector('[data-camera-capture-btn]');
            const closeBtn = wrapper.querySelector('[data-camera-close]');
            const panel = wrapper.querySelector('[data-camera-panel]');
            const video = wrapper.querySelector('video');
            const canvas = wrapper.querySelector('canvas');
            const error = wrapper.querySelector('[data-camera-error]');
            const status = wrapper.querySelector('[data-camera-status]');
            const preview = wrapper.querySelector('[data-camera-preview]');

            // Store collected files separately to prevent browser replacing them
            wrapper._collectedFiles = [];

            const setError = (message) => {
                if (!error) {
                    return;
                }
                if (message) {
                    error.textContent = message;
                    error.classList.remove('hidden');
                } else {
                    error.textContent = '';
                    error.classList.add('hidden');
                }
            };

            const updateStatus = () => {
                if (!status) {
                    return;
                }
                const count = wrapper._collectedFiles.length;
                status.textContent = `${count}/${maxFiles}`;
            };

            const syncToInput = () => {
                if (!input) {
                    return;
                }
                const dataTransfer = new DataTransfer();
                wrapper._collectedFiles.forEach((file) => dataTransfer.items.add(file));
                input.files = dataTransfer.files;
            };

            const clearPreview = () => {
                if (wrapper._objectUrls) {
                    wrapper._objectUrls.forEach((url) => URL.revokeObjectURL(url));
                }
                wrapper._objectUrls = [];
                if (preview) {
                    preview.innerHTML = '';
                }
            };

            const removeFile = (index) => {
                wrapper._collectedFiles.splice(index, 1);
                syncToInput();
                renderPreview();
            };

            const renderPreview = () => {
                updateStatus();
                if (!preview) {
                    return;
                }
                clearPreview();
                const files = wrapper._collectedFiles;

                // Hide preview grid if no files
                if (files.length === 0) {
                    preview.classList.add('hidden');
                    return;
                }

                // Show preview grid and add file previews
                preview.classList.remove('hidden');
                files.forEach((file, index) => {
                    const url = URL.createObjectURL(file);
                    wrapper._objectUrls.push(url);

                    const item = document.createElement('div');
                    item.className = 'aspect-square rounded-lg overflow-hidden border-2 border-gray-200 bg-white relative group';

                    const img = document.createElement('img');
                    img.className = 'w-full h-full object-cover';
                    img.src = url;
                    img.alt = file.name;

                    const overlay = document.createElement('div');
                    overlay.className = 'absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'p-1.5 bg-red-500 hover:bg-red-600 rounded-full text-white';
                    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                    removeBtn.addEventListener('click', () => removeFile(index));

                    overlay.appendChild(removeBtn);
                    item.appendChild(img);
                    item.appendChild(overlay);
                    preview.appendChild(item);
                });
            };

            const isAllowedFile = (file) => {
                const type = (file.type || '').toLowerCase();
                if (type === 'image/jpeg' || type === 'image/png' || type === 'image/jpg') {
                    return true;
                }
                const name = (file.name || '').toLowerCase();
                return name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.png');
            };

            const appendFiles = (files) => {
                if (!files || files.length === 0) {
                    return;
                }
                setError('');
                files.forEach((file) => {
                    if (!isAllowedFile(file)) {
                        setError('Hanya mendukung file JPG/PNG.');
                        return;
                    }
                    if (wrapper._collectedFiles.length < maxFiles) {
                        wrapper._collectedFiles.push(file);
                    }
                });
                if (wrapper._collectedFiles.length > maxFiles) {
                    setError(`Maksimal ${maxFiles} gambar.`);
                    wrapper._collectedFiles = wrapper._collectedFiles.slice(0, maxFiles);
                }
                syncToInput();
                renderPreview();
            };

            const setHighlight = (active) => {
                wrapper.classList.toggle('ring-2', active);
                wrapper.classList.toggle('ring-pln-primary', active);
                wrapper.classList.toggle('border-pln-primary', active);
                wrapper.classList.toggle('bg-blue-50/50', active);
            };

            const normalizeFiles = () => {
                if (!input) {
                    return;
                }
                appendFiles(Array.from(input.files || []));
            };

            const stopCamera = () => {
                if (wrapper._cameraStream) {
                    wrapper._cameraStream.getTracks().forEach((track) => track.stop());
                    wrapper._cameraStream = null;
                }
                if (video) {
                    video.srcObject = null;
                }
                if (panel) {
                    panel.classList.add('hidden');
                }
            };

            const openCamera = async () => {
                setError('');
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setError('Browser tidak mendukung akses kamera.');
                    return;
                }
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                        audio: false,
                    });
                    wrapper._cameraStream = stream;
                    if (video) {
                        video.srcObject = stream;
                        await video.play();
                    }
                    if (panel) {
                        panel.classList.remove('hidden');
                    }
                } catch (err) {
                    setError('Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan.');
                }
            };

            const capturePhoto = () => {
                setError('');
                if (!input) {
                    setError('Input lampiran tidak ditemukan.');
                    return;
                }
                if (!video || !canvas) {
                    setError('Kamera belum siap.');
                    return;
                }
                if (wrapper._collectedFiles.length >= maxFiles) {
                    setError(`Maksimal ${maxFiles} gambar.`);
                    return;
                }
                const width = video.videoWidth || 1280;
                const height = video.videoHeight || 720;
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    setError('Gagal mengambil gambar.');
                    return;
                }
                ctx.drawImage(video, 0, 0, width, height);
                canvas.toBlob((blob) => {
                    if (!blob) {
                        setError('Gagal menyimpan gambar.');
                        return;
                    }
                    const fileName = `camera-${Date.now()}.jpg`;
                    const file = new File([blob], fileName, { type: 'image/jpeg' });
                    wrapper._collectedFiles.push(file);
                    syncToInput();
                    renderPreview();
                }, 'image/jpeg', 0.9);
            };

            if (input) {
                input.addEventListener('change', normalizeFiles);
            }
            if (openBtn) {
                openBtn.addEventListener('click', openCamera);
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', stopCamera);
            }
            if (captureBtn) {
                captureBtn.addEventListener('click', capturePhoto);
            }
            wrapper._dragCounter = 0;
            wrapper.addEventListener('dragenter', (event) => {
                event.preventDefault();
                wrapper._dragCounter += 1;
                setHighlight(true);
            });
            wrapper.addEventListener('dragover', (event) => {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy';
                setHighlight(true);
            });
            wrapper.addEventListener('dragleave', (event) => {
                event.preventDefault();
                wrapper._dragCounter = Math.max(0, wrapper._dragCounter - 1);
                if (wrapper._dragCounter === 0) {
                    setHighlight(false);
                }
            });
            wrapper.addEventListener('drop', (event) => {
                event.preventDefault();
                wrapper._dragCounter = 0;
                setHighlight(false);
                const files = Array.from(event.dataTransfer?.files || []);
                appendFiles(files);
            });
            wrapper._stopCamera = stopCamera;
            renderPreview();
        }

        function initCameraCaptures() {
            document.querySelectorAll('[data-camera-capture]').forEach(setupCameraCapture);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initCameraCaptures();
        });
    </script>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="reject-approval" focusable>
        <div class="p-6"
             x-data="{ formAction: '' }"
             x-on:open-reject-approval.window="formAction = $event.detail.action; $dispatch('open-modal', 'reject-approval')">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Tolak Persetujuan</h2>
                    <p class="text-sm text-gray-600">Masukkan alasan penolakan untuk surat jalan ini.</p>
                </div>
            </div>
            <form method="POST" x-bind:action="formAction" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="reject_reason_admin" class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                    <textarea id="reject_reason_admin"
                              name="alasan"
                              rows="3"
                              required
                              class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200"
                              placeholder="Tuliskan alasan penolakan..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 text-xs font-semibold text-gray-700 uppercase tracking-widest bg-gray-100 rounded-md hover:bg-gray-200"
                            x-on:click="$dispatch('close-modal', 'reject-approval')">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white uppercase tracking-widest bg-red-600 rounded-md hover:bg-red-700">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <x-confirm-delete-modal />
</x-app-layout>
