<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi TTD Surat Jalan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #111;
            margin: 0;
            padding: 24px;
        }
        .card {
            max-width: 520px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
        }
        .header img {
            max-width: 220px;
            height: auto;
        }
        .header-subtitle {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 300;
            color: #111;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-top: 14px;
        }
        .value {
            font-size: 14px;
            font-weight: 600;
            margin-top: 4px;
        }
        .badge {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #111;
            color: #fff;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <img src="{{ asset('Logo_PLN_800.png') }}" alt="Logo PLN">
            <div class="header-subtitle">ULPLTD/G Tanjung Karang</div>
        </div>
        <div class="title">DATA SURAT JALAN VALID</div>

        <div class="label">Judul</div>
        <div class="value">Surat Jalan</div>

        <div class="label">Nomor Surat</div>
        <div class="value">{{ $suratJalan->nomor ?? '-' }}</div>

        <div class="label">Tanggal Surat</div>
        <div class="value">{{ $suratJalan->tanggal?->translatedFormat('d F Y') ?? '-' }}</div>

        <div class="label">Nama</div>
        <div class="value">{{ $nama }}</div>

        <div class="label">Tanggal Approval</div>
        <div class="value">{{ $waktuApproval->translatedFormat('d F Y H:i') }} WIB</div>
    </div>
</body>
</html>
