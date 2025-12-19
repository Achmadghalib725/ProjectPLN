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

        /* Info Section */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
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
            margin-bottom: 60px;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 10px;
        }

        .signature-name {
            font-weight: bold;
        }

        .signature-position {
            font-size: 9px;
            color: #666;
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
    </style>
</head>

<body>
    <div class="container">
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
                <div class="company-subtitle">Sistem Manajemen Gudang - {{ $suratJalan->gudangAsal->nama ?? 'Gudang' }}
                </div>
            </div>
            <div class="header-right">
                <div class="doc-title">SURAT JALAN</div>
                <div class="doc-number">{{ $suratJalan->nomor ?? 'DRAFT' }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <div class="info-title">Informasi Pengiriman</div>
                <div class="info-row">
                    <div class="info-label">Tanggal</div>
                    <div class="info-value">:
                        {{ $suratJalan->tanggal ? $suratJalan->tanggal->format('d F Y') : now()->format('d F Y') }}
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
                    <div class="info-value">: {{ $suratJalan->gudangTujuan->nama ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Alamat</div>
                    <div class="info-value">: {{ $suratJalan->gudangTujuan->alamat ?? '-' }}</div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-title">Penerima (PIC)</div>
                <div class="info-row">
                    <div class="info-label">Nama</div>
                    <div class="info-value">: {{ $suratJalan->picTujuan->nama ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jabatan</div>
                    <div class="info-value">: {{ $suratJalan->picTujuan->jabatan ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. HP</div>
                    <div class="info-value">: {{ $suratJalan->picTujuan->no_hp ?? '-' }}</div>
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
                <div class="signature-line">
                    <div class="signature-name">{{ $suratJalan->pembuat->name ?? '________________' }}</div>
                    <div class="signature-position">{{ $suratJalan->gudangAsal->nama ?? '' }}</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Pengantar</div>
                <div class="signature-line">
                    <div class="signature-name">{{ $suratJalan->nama_driver ?? '________________' }}</div>
                    <div class="signature-position">
                        {{ $suratJalan->nomor_plat ? 'Driver - ' . $suratJalan->nomor_plat : 'Driver' }}
                    </div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Penerima</div>
                <div class="signature-line">
                    <div class="signature-name">{{ $suratJalan->picTujuan->nama ?? '________________' }}</div>
                    <div class="signature-position">{{ $suratJalan->picTujuan->jabatan ?? '' }}</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Dokumen ini dicetak pada {{ now()->format('d F Y H:i') }} WIB |
            Sistem Manajemen Gudang PLN
        </div>
    </div>
</body>

</html>
