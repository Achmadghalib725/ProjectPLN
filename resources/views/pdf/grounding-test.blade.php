<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Surat Hasil Uji Grounding</title>
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
            width: 110px;
            color: #666;
            font-size: 10px;
        }

        .info-value {
            display: table-cell;
            font-weight: 500;
        }

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

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 10px;
            vertical-align: top;
        }

        .items-table .col-no { width: 6%; text-align: center; }
        .items-table .col-titik { width: 40%; }
        .items-table .col-kriteria { width: 22%; }
        .items-table .col-hasil { width: 22%; }

        .items-table tr:nth-child(even) {
            background: #f9fafb;
        }

        .notes-section {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 5px;
            margin-top: 15px;
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

        .attachment-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            background: #f9f9f9;
        }

        .attachment-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }

        .attachment-caption {
            font-size: 9px;
            color: #666;
            padding: 8px;
            text-align: center;
            background: #f0f0f0;
            border-top: 1px solid #ddd;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 70px;
            font-weight: bold;
            font-family: Helvetica, Arial, sans-serif;
            color: #000;
            opacity: 0.06;
            letter-spacing: 6px;
            z-index: 0;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="watermark">HASIL UJI GROUNDING</div>
    <div class="container">
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
                <div class="company-subtitle">ULPLTD/G Tanjung Karang</div>
            </div>
            <div class="header-right">
                <div class="doc-title">Surat Hasil Uji Grounding</div>
                <div class="doc-number">
                    Tanggal: {{ $groundingTest->tanggal?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-box">
                <div class="info-title">Informasi Uji</div>
                <div class="info-row">
                    <div class="info-label">Tanggal</div>
                    <div class="info-value">:
                        {{ $groundingTest->tanggal?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Dibuat Oleh</div>
                    <div class="info-value">:
                        {{ $groundingTest->nama_pembuat ?? $groundingTest->creator->name ?? '-' }}
                    </div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-title">Ringkasan</div>
                <div class="info-row">
                    <div class="info-label">Total Titik</div>
                    <div class="info-value">: {{ $groundingTest->items->count() }} titik</div>
                </div>
            </div>
        </div>

        <div class="items-section">
            <div class="section-title">Daftar Titik Ukur Grounding</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th class="col-titik">Titik Ukur Grounding</th>
                        <th class="col-kriteria">Kriteria</th>
                        <th class="col-hasil">Hasil Uji</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groundingTest->items as $index => $item)
                        <tr>
                            <td class="col-no">{{ $index + 1 }}</td>
                            <td class="col-titik">{{ $item->titik_ukur }}</td>
                            <td class="col-kriteria">{{ $item->kriteria }}</td>
                            <td class="col-hasil">{{ $item->hasil_uji }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #999;">
                                Tidak ada data titik ukur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($groundingTest->catatan)
            <div class="notes-section">
                <div class="notes-title">Catatan:</div>
                <div class="notes-content">{{ $groundingTest->catatan }}</div>
            </div>
        @endif
    </div>

    @php
        $attachments = $groundingTest->items->filter(fn ($item) => !empty($item->attachment_path));
    @endphp
    @if($attachments->isNotEmpty())
        <div style="page-break-before: always;"></div>
        <div class="container">
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
                <div class="doc-title">Lampiran Hasil Uji</div>
                <div class="doc-number">Tanggal: {{ $groundingTest->tanggal?->locale('id')->translatedFormat('d F Y') ?? '-' }}</div>
            </div>
        </div>

            <div style="margin-top: 20px;">
                <div style="font-size: 12px; font-weight: bold; color: #035b71; margin-bottom: 15px; text-transform: uppercase; border-bottom: 1px solid #adadadff; padding-bottom: 8px;">
                    Dokumentasi Titik Ukur ({{ $attachments->count() }} Gambar)
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        @foreach($attachments as $index => $item)
                            <td style="width: 50%; vertical-align: top; padding: 10px; {{ $index % 2 == 0 ? 'padding-left: 0;' : 'padding-right: 0;' }}">
                                @php
                                    $imagePath = storage_path('app/public/' . $item->attachment_path);
                                @endphp
                                @if(file_exists($imagePath))
                                    <div class="attachment-box">
                                        <img src="{{ $imagePath }}"
                                             class="attachment-image"
                                             alt="{{ $item->attachment_name }}">
                                        <div class="attachment-caption">
                                            <strong>{{ $item->titik_ukur }}</strong>
                                            @if($item->attachment_name)
                                                - {{ Str::limit($item->attachment_name, 40) }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                            @if($index % 2 == 1 && $index < $attachments->count() - 1)
                                </tr><tr>
                            @endif
                        @endforeach
                        @if($attachments->count() % 2 == 1)
                            <td style="width: 50%;"></td>
                        @endif
                    </tr>
                </table>
            </div>
        </div>
    @endif
</body>

</html>
