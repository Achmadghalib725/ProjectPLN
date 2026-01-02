<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Surat Jalan - {{ $suratJalan->nomor ?? 'Draft' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            padding: 20px 30px;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #035b71;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            vertical-align: middle;
            text-align: right;
        }

        .company-logo {
            height: 45px;
            width: auto;
            margin-bottom: 5px;
        }

        .company-subtitle {
            font-size: 10px;
            color: #666;
        }

        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #035b71;
            margin-bottom: 5px;
        }

        .doc-number {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }

        .qr-image {
            width: 90px;
            height: 90px;
            margin-bottom: 6px;
        }

        /* Info Section */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .intro-section {
            margin-bottom: 18px;
            font-size: 11px;
            color: #333;
            text-align: justify;
        }

        .intro-section strong {
            color: #035b71;
        }

        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }

        .info-box:last-child {
            padding-right: 0;
            padding-left: 15px;
        }

        .info-title {
            font-size: 11px;
            font-weight: bold;
            color: #035b71;
            text-transform: uppercase;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .info-label {
            display: table-cell;
            width: 100px;
            color: #666;
            font-size: 10px;
        }

        .info-value {
            display: table-cell;
            font-weight: 500;
        }

        /* Type Badge */
        .type-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .type-transfer {
            background: #e9d5ff;
            color: #7c3aed;
        }

        .type-peminjaman {
            background: #dbeafe;
            color: #2563eb;
        }

        .type-pengembalian {
            background: #dcfce7;
            color: #16a34a;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #035b71;
            margin-bottom: 10px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table th {
            background: #035b71;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .items-table th:first-child {
            width: 40px;
            text-align: center;
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 10px;
        }

        .items-table td:first-child {
            text-align: center;
            color: #666;
        }

        .items-table tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Notes */
        .notes-section {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 25px;
        }

        .notes-title {
            font-size: 10px;
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }

        .notes-content {
            font-size: 10px;
            color: #333;
        }

        /* Signatures */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 30px;
        }

        .signature-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 10px;
        }

        .signature-title {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            margin-bottom: 0px;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 10px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-position {
            font-size: 9px;
            color: #666;
        }

        .signature-heading {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .signature-qr {
            height: 86px;
            margin-bottom: 6px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #999;
            text-align: center;
        }

        /* Return Date Info */
        .return-info {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .return-info-text {
            font-size: 10px;
            color: #92400e;
        }

        .return-info-date {
            font-weight: bold;
            color: #d97706;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            font-family: Helvetica, Arial, sans-serif;
            color: #000;
            opacity: 0.07;
            letter-spacing: 8px;
            z-index: 0;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="watermark">SURAT JALAN</div>
    <div class="container">
        @php
            $peminjaman = $peminjaman ?? null;
            $buildQrWithLogo = function (string $url, int $size = 90) {
                $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size, 1),
                    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                );
                $writer = new \BaconQrCode\Writer($renderer);
                $qrSvg = $writer->writeString($url);

                $logoPath = public_path('Logo_PLN.png');
                if (file_exists($logoPath)) {
                    $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                    $logoSize = (int) round($size * 0.26);
                    $logoPos = ($size - $logoSize) / 2;
                    $logoSvg = '<image x="' . $logoPos . '" y="' . $logoPos . '" width="' . $logoSize . '" height="' . $logoSize . '" href="' . $logoData . '" />';
                    if (str_contains($qrSvg, '</svg>')) {
                        $qrSvg = str_replace('</svg>', $logoSvg . '</svg>', $qrSvg);
                    }
                }

                return 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
            };
        @endphp
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @php
                    $logoPath = public_path('Logo_PLN_800.png');
                    $logoData = '';
                    if (file_exists($logoPath)) {
                        $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                    }
                @endphp
                @if($logoData)
                    <img src="{{ $logoData }}" alt="PLN Logo" class="company-logo">
                @else
                    <div style="font-size: 18px; font-weight: bold; color: #035b71;">PT PLN (Persero)</div>
                @endif
                <div class="company-subtitle">ULPLTD/G Tanjung Karang
                </div>
            </div>
            <div class="header-right">
                @php
                    $qrImage = null;
                    if (!empty($suratJalan->id) && !empty($suratJalan->qr_token)) {
                        $qrUrl = route('security.qr', ['id' => $suratJalan->id, 'token' => $suratJalan->qr_token]);
                        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(90, 1),
                            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                        );
                        $writer = new \BaconQrCode\Writer($renderer);
                        $qrSvg = $writer->writeString($qrUrl);
                        $qrImage = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
                    }
                @endphp
                @if($qrImage)
                    <img src="{{ $qrImage }}" alt="QR Surat Jalan" class="qr-image">
                @endif
                <div class="doc-number">{{ $suratJalan->nomor ?? 'DRAFT' }}</div>
            </div>
        </div>

        <!-- Info Section -->
        @php
            $tanggalSurat = $suratJalan->tanggal ? $suratJalan->tanggal->format('d F Y') : now()->format('d F Y');
            $hariSurat = ($suratJalan->tanggal ?? now())->locale('id')->translatedFormat('l');
            $gudangTujuanNama = $suratJalan->gudang_tujuan_is_custom
                ? ($suratJalan->gudang_tujuan_custom_nama ?? 'Gudang Lainnya')
                : ($suratJalan->gudangTujuan->nama ?? '-');
            $gudangTujuanAlamat = $suratJalan->gudang_tujuan_is_custom
                ? ($suratJalan->gudang_tujuan_custom_alamat ?? '-')
                : ($suratJalan->gudangTujuan->alamat ?? '-');
            $lamaPinjamText = null;
            if (($suratJalan->tipe ?? '') === 'PENGEMBALIAN' && $peminjaman) {
                $mulaiPinjam = $peminjaman->waktu_diterima
                    ?? $peminjaman->waktu_ttd_penerima
                    ?? $peminjaman->waktu_kirim
                    ?? $peminjaman->waktu_pengajuan
                    ?? $peminjaman->created_at;
                $selesaiPinjam = $peminjaman->waktu_pengembalian
                    ?? $peminjaman->waktu_selesai
                    ?? $suratJalan->created_at
                    ?? $suratJalan->tanggal;

                if ($mulaiPinjam && $selesaiPinjam) {
                    $mulaiPinjam = $mulaiPinjam instanceof \Carbon\CarbonInterface
                        ? $mulaiPinjam
                        : \Carbon\Carbon::parse($mulaiPinjam);
                    $selesaiPinjam = $selesaiPinjam instanceof \Carbon\CarbonInterface
                        ? $selesaiPinjam
                        : \Carbon\Carbon::parse($selesaiPinjam);

                    if ($selesaiPinjam->lessThan($mulaiPinjam)) {
                        $selesaiPinjam = $mulaiPinjam->copy();
                    }

                    $totalMinutes = $mulaiPinjam->diffInMinutes($selesaiPinjam);
                    $days = intdiv($totalMinutes, 1440);
                    $hours = intdiv($totalMinutes % 1440, 60);
                    $minutes = $totalMinutes % 60;

                    $lamaPinjamText = sprintf('%d hari %d jam %d menit', $days, $hours, $minutes);
                }
            }
        @endphp

        <div class="intro-section">
            Pada hari ini, <strong>{{ $hariSurat }}</strong> tanggal <strong>{{ $tanggalSurat }}</strong>, kami yang bertanda tangan di bawah ini menyatakan telah dilakukan pengiriman barang sesuai Surat Jalan dengan rincian informasi berikut.
            @if(($suratJalan->tipe ?? '') === 'PENGEMBALIAN' && $lamaPinjamText)
                Dengan ini mengembalikan barang yang dipinjam dari <strong>{{ $gudangTujuanNama }}</strong> yang sudah dipinjam selama <strong>{{ $lamaPinjamText }}</strong>.
            @endif
        </div>

        <div class="info-section">
            <div class="info-box">
                <div class="info-title">Informasi Pengiriman</div>
                <div class="info-row">
                    <div class="info-label">Tanggal</div>
                    <div class="info-value">:
                        {{ $tanggalSurat }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipe</div>
                    <div class="info-value">:
                        @php
                            $tipe = $suratJalan->tipe ?? 'TRANSFER';
                            $tipeClass = match ($tipe) {
                                'PEMINJAMAN' => 'type-peminjaman',
                                'PENGEMBALIAN' => 'type-pengembalian',
                                default => 'type-transfer',
                            };
                        @endphp
                        <span class="">{{ $tipe }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">: {{ $suratJalan->status ?? 'DRAFT' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nama Driver</div>
                    <div class="info-value">: {{ $suratJalan->nama_driver ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jenis Kendaraan</div>
                    <div class="info-value">: {{ $suratJalan->jenis_kendaraan ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nomor Plat</div>
                    <div class="info-value">: {{ $suratJalan->nomor_plat ?? '-' }}</div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-title">Gudang Asal</div>
                <div class="info-row">
                    <div class="info-label">Nama</div>
                    <div class="info-value">: {{ $suratJalan->gudangAsal->nama ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Alamat</div>
                    <div class="info-value">: {{ $suratJalan->gudangAsal->alamat ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-box">
                <div class="info-title">Gudang Tujuan</div>
                <div class="info-row">
                    <div class="info-label">Nama</div>
                    <div class="info-value">: {{ $gudangTujuanNama }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Alamat</div>
                    <div class="info-value">: {{ $gudangTujuanAlamat }}</div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-title">Penerima (PIC)</div>
                <div class="info-row">
                    <div class="info-label">Nama</div>
                    @php
                        $picNama = $suratJalan->picTujuan->nama ?? $suratJalan->pic_tujuan_custom_nama ?? '-';
                        $picJabatan = $suratJalan->picTujuan->jabatan ?? $suratJalan->pic_tujuan_custom_jabatan ?? '-';
                        $picNoHp = $suratJalan->picTujuan->no_hp ?? $suratJalan->pic_tujuan_custom_no_hp ?? '-';
                    @endphp
                    <div class="info-value">: {{ $picNama }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jabatan</div>
                    <div class="info-value">: {{ $picJabatan }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. HP</div>
                    <div class="info-value">: {{ $picNoHp }}</div>
                </div>
            </div>
        </div>

        <!-- Return Date for Peminjaman -->
        @if($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->tanggal_kembali)
            <div class="return-info">
                <div class="return-info-text">
                    <strong>PEMINJAMAN:</strong> Barang harus dikembalikan paling lambat
                    <span class="return-info-date">{{ $suratJalan->tanggal_kembali->format('d F Y') }}</span>
                </div>
            </div>
        @endif

        <!-- Items Table -->
        <div class="items-section">
            <div class="section-title">Daftar Barang</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th class="text-center">Jumlah</th>
                        <th>Satuan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suratJalan->items ?? [] as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->item->kode ?? '-' }}</td>
                            <td>{{ $item->item->nama ?? '-' }}</td>
                            <td>{{ $item->item->kategori ?? '-' }}</td>
                            <td class="text-center"><strong>{{ number_format($item->jumlah) }}</strong></td>
                            <td>{{ $item->item->satuan ?? '-' }}</td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 20px; color: #999;">
                                Tidak ada barang
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($suratJalan->items && $suratJalan->items->count() > 0)
                <div style="text-align: right; font-size: 11px; color: #666;">
                    Total: <strong>{{ $suratJalan->items->count() }}</strong> jenis barang,
                    <strong>{{ number_format($suratJalan->items->sum('jumlah')) }}</strong> unit
                </div>
            @endif
        </div>

        <!-- Notes -->
        @if($suratJalan->catatan)
            <div class="notes-section">
                <div class="notes-title">Catatan:</div>
                <div class="notes-content">{{ $suratJalan->catatan }}</div>
            </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Pengirim</div>
                @php
                    $pengirimQr = null;
                    if (!empty($suratJalan->id) && !empty($suratJalan->qr_token) && $suratJalan->waktu_ttd_pembuat) {
                        $pengirimUrl = route('surat-jalan.signature', [
                            'id' => $suratJalan->id,
                            'token' => $suratJalan->qr_token,
                            'role' => 'pengirim',
                        ]);
                        $pengirimQr = $buildQrWithLogo($pengirimUrl, 80);
                    }

                    $managerSigner = $suratJalan->gudangAsal
                        ? $suratJalan->gudangAsal->managers()->where('role', 'manager')->orderBy('name')->first()
                        : null;
                    $isDraftLike = in_array(($suratJalan->status ?? ''), ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'], true);
                    $pengirimSigner = ($isDraftLike && $managerSigner)
                        ? $managerSigner
                        : ($suratJalan->ttdPembuat ?? $managerSigner ?? $suratJalan->pembuat);
                    $pengirimJabatan = $pengirimSigner?->jabatan ?? '';
                    $pengirimJabatanLines = $pengirimJabatan
                        ? preg_split("/\r\n|\r|\n/", $pengirimJabatan)
                        : [];
                @endphp
                <div class="signature-line">
                    @if(!empty($pengirimJabatanLines))
                        <div class="signature-heading">
                            @foreach($pengirimJabatanLines as $line)
                                @if(trim($line) !== '')
                                    <div>{{ strtoupper(trim($line)) }}</div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <div class="signature-qr">
                        @if($pengirimQr)
                            <img src="{{ $pengirimQr }}" alt="QR TTD Pengirim" style="width: 80px; height: 80px;">
                        @endif
                    </div>
                    <div class="signature-name">{{ $pengirimSigner?->name ?? '________________' }}</div>
                    <div class="signature-position">{{ $suratJalan->gudangAsal->nama ?? '' }}</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Penerima</div>
                @php
                    $penerimaQr = null;
                    if (!empty($suratJalan->id) && !empty($suratJalan->qr_token) && $suratJalan->waktu_ttd_penerima) {
                        $penerimaUrl = route('surat-jalan.signature', [
                            'id' => $suratJalan->id,
                            'token' => $suratJalan->qr_token,
                            'role' => 'penerima',
                        ]);
                        $penerimaQr = $buildQrWithLogo($penerimaUrl, 80);
                    }

                    $penerimaJabatan = $suratJalan->ttdPenerima?->jabatan
                        ?? $suratJalan->picTujuan?->jabatan
                        ?? $suratJalan->pic_tujuan_custom_jabatan
                        ?? '';
                    $penerimaJabatanLines = $penerimaJabatan
                        ? preg_split("/\r\n|\r|\n/", $penerimaJabatan)
                        : [];
                @endphp
                <div class="signature-line">
                    @if(!empty($penerimaJabatanLines))
                        <div class="signature-heading">
                            @foreach($penerimaJabatanLines as $line)
                                @if(trim($line) !== '')
                                    <div>{{ strtoupper(trim($line)) }}</div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <div class="signature-qr">
                        @if($penerimaQr)
                            <img src="{{ $penerimaQr }}" alt="QR TTD Penerima" style="width: 80px; height: 80px;">
                        @endif
                    </div>
                    <div class="signature-name">{{ $suratJalan->ttdPenerima->name ?? $suratJalan->picTujuan->nama ?? $suratJalan->pic_tujuan_custom_nama ?? '________________' }}</div>
                    <div class="signature-position">{{ $suratJalan->picTujuan->jabatan ?? $suratJalan->pic_tujuan_custom_jabatan ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page 2: Attachments -->
    @if($suratJalan->attachments && $suratJalan->attachments->count() > 0)
        <div style="page-break-before: always;"></div>
        <div class="container">
            <!-- Header Page 2 -->
            <div class="header">
                <div class="header-left">
                    @php
                        $logoPath = public_path('Logo_PLN_800.png');
                    @endphp
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo PLN" class="company-logo">
                    @endif
                    <div class="company-subtitle">ULPLTD/G Tanjung Karang</div>
                </div>
                <div class="header-right">
                    <div class="doc-title">LAMPIRAN SURAT JALAN</div>
                    <div class="doc-number">{{ $suratJalan->nomor ?? 'DRAFT' }}</div>
                </div>
            </div>

            <!-- Attachment Content -->
            <div style="margin-top: 20px;">
                <div style="font-size: 12px; font-weight: bold; color: #035b71; margin-bottom: 15px; text-transform: uppercase; border-bottom: 1px solid #adadadff; padding-bottom: 8px;">
                    Dokumentasi Barang ({{ $suratJalan->attachments->count() }} Gambar)
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        @foreach($suratJalan->attachments as $index => $attachment)
                            <td style="width: 50%; vertical-align: top; padding: 10px; {{ $index % 2 == 0 ? 'padding-left: 0;' : 'padding-right: 0;' }}">
                                @php
                                    $imagePath = storage_path('app/public/' . $attachment->file_path);
                                @endphp
                                @if(file_exists($imagePath))
                                    <div style="border: 1px solid #ddd; border-radius: 5px; overflow: hidden; background: #f9f9f9;">
                                        <img src="{{ $imagePath }}"
                                             style="width: 100%; max-height: 280px; object-fit: contain; display: block;"
                                             alt="{{ $attachment->file_name }}">
                                        <div style="font-size: 9px; color: #666; padding: 8px; text-align: center; background: #f0f0f0; border-top: 1px solid #ddd;">
                                            <strong>Gambar {{ $index + 1 }}:</strong> {{ Str::limit($attachment->file_name, 40) }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                            @if($index % 2 == 1 && $index < $suratJalan->attachments->count() - 1)
                                </tr><tr>
                            @endif
                        @endforeach
                        @if($suratJalan->attachments->count() % 2 == 1)
                            <td style="width: 50%;"></td>
                        @endif
                    </tr>
                </table>
            </div>
        </div>
    @endif
</body>

</html>
