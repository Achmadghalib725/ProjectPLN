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
        .audit-section {
            margin-top: 16px;
            padding: 14px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .audit-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
            margin-bottom: 10px;
        }
        .audit-item {
            font-size: 11px;
            color: #555;
            margin-bottom: 6px;
        }
        .audit-item span {
            color: #888;
        }
        .audit-hash {
            font-family: monospace;
            font-size: 10px;
            color: #333;
            word-break: break-all;
            background: #fff;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 4px;
        }
        .audit-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .audit-status.valid {
            background: #d4edda;
            color: #155724;
        }
        .audit-status.invalid {
            background: #f8d7da;
            color: #721c24;
        }
        .audit-status.unknown {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <img src="{{ asset('Logo_PLN_800.png') }}" alt="Logo PLN">
            <div class="header-subtitle">ULPLTD/G Tanjung Karang</div>
        </div>

        @if($isHashValid === true)
            <div class="title">DATA SURAT JALAN VALID</div>
        @elseif($isHashValid === false)
            <div class="title" style="color: #dc3545;">DATA SURAT JALAN TIDAK VALID</div>
            <div style="font-size: 12px; color: #dc3545; margin-bottom: 12px;">Dokumen telah dimodifikasi setelah ditandatangani</div>
        @else
            <div class="title">DATA SURAT JALAN</div>
        @endif

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

        @if($isAuditMode)
            <div class="audit-section">
                <div class="audit-title">Informasi Audit</div>

                <div class="audit-item">
                    <span>Status Verifikasi:</span>
                    @if($isHashValid === true)
                        <span class="audit-status valid">TERVERIFIKASI</span>
                    @elseif($isHashValid === false)
                        <span class="audit-status invalid">TIDAK VALID</span>
                    @else
                        <span class="audit-status unknown">TIDAK TERSEDIA</span>
                    @endif
                </div>

                @if($signatureMetadata)
                    @if(isset($signatureMetadata['ip_address']))
                        <div class="audit-item"><span>IP Address:</span> {{ $signatureMetadata['ip_address'] }}</div>
                    @endif
                    @if(isset($signatureMetadata['signed_at']))
                        <div class="audit-item"><span>Waktu Signing:</span> {{ $signatureMetadata['signed_at'] }}</div>
                    @endif
                    @if(isset($signatureMetadata['user_agent']))
                        <div class="audit-item"><span>User Agent:</span> {{ Str::limit($signatureMetadata['user_agent'], 50) }}</div>
                    @endif
                @endif

                @if($signatureHash)
                    <div class="audit-item"><span>Hash Dokumen (SHA-256):</span></div>
                    <div class="audit-hash">{{ $signatureHash }}</div>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
