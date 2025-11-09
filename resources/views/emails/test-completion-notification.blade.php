<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Penyelesaian Tes</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo {
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 32px;
            font-weight: bold;
            color: #000;
            margin: 0;
            line-height: 1.2;
        }
        .logo-subtext {
            font-size: 14px;
            color: #000;
            margin: 5px 0 0 0;
            font-weight: normal;
        }
        .header {
            text-align: center;
            margin: 30px 0;
        }
        .header-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }
        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            margin: 0;
        }
        .greeting {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }
        .test-info {
            background-color: #f8f9fa;
            border-left: 4px solid #2563eb;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .test-info p {
            margin: 10px 0;
            font-size: 15px;
        }
        .test-name {
            font-weight: bold;
            color: #1e293b;
            font-size: 16px;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 12px 0;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }
        .info-value {
            color: #1e293b;
            font-size: 14px;
            text-align: right;
            font-weight: 500;
        }
        .status-badge {
            background-color: #10b981;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .button {
            display: block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            margin: 30px auto;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: background-color 0.3s;
            max-width: 300px;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .next-steps {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .next-steps-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 15px;
        }
        .next-steps-list {
            margin: 10px 0;
            padding-left: 20px;
            color: #1e293b;
            font-size: 14px;
        }
        .next-steps-list li {
            margin: 8px 0;
        }
        .closing {
            text-align: center;
            font-size: 16px;
            color: #1e293b;
            margin-top: 30px;
            font-weight: 600;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Logo -->
        <div class="logo">
            <p class="logo-text">DWP</p>
            <p class="logo-subtext">Dutakom Wibawa Putra</p>
        </div>

        <!-- Header -->
        <div class="header">
            <div class="header-icon">🎉</div>
            <h1 class="header-title">Notifikasi Penyelesaian Tes</h1>
        </div>

        <!-- Greeting -->
        <div class="greeting">
            Halo Admin,
        </div>

        <p style="font-size: 15px; color: #374151; margin-bottom: 25px;">
            Seorang kandidat telah berhasil menyelesaikan tes mereka. Berikut adalah detail lengkapnya:
        </p>

        <!-- Test Information -->
        <div class="test-info">
            <p>Kandidat telah menyelesaikan tes <span class="test-name">{{ $testName }}</span></p>
        </div>

        <!-- Test Details Box -->
        <div class="info-box">
            <div class="info-box-title">📋 Informasi Tes</div>
            <div class="info-row">
                <span class="info-label">Nama Tes:</span>
                <span class="info-value">{{ $testName }}</span>
            </div>
            @if($targetPosition)
            <div class="info-row">
                <span class="info-label">Posisi Target:</span>
                <span class="info-value">{{ $targetPosition }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Waktu Penyelesaian:</span>
                <span class="info-value">{{ $completedAt }}</span>
            </div>
            @if($score)
            <div class="info-row">
                <span class="info-label">Skor:</span>
                <span class="info-value">{{ $score }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge">SELESAI</span>
                </span>
            </div>
        </div>

        <!-- Candidate Information -->
        <div class="info-box">
            <div class="info-box-title">👤 Informasi Kandidat</div>
            <div class="info-row">
                <span class="info-label">Nama:</span>
                <span class="info-value">{{ $candidateName }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $candidateEmail }}</span>
            </div>
            @if($candidatePosition)
            <div class="info-row">
                <span class="info-label">Posisi:</span>
                <span class="info-value">{{ $candidatePosition }}</span>
            </div>
            @endif
        </div>

        <!-- View Results Button -->
        @if($resultLink)
        <a href="{{ $resultLink }}" class="button">
            LIHAT HASIL TES
        </a>
        @else
        <a href="{{ config('app.url') }}/results" class="button">
            LIHAT DASHBOARD HASIL
        </a>
        @endif

        <!-- Next Steps -->
        <div class="next-steps">
            <div class="next-steps-title">Langkah Selanjutnya:</div>
            <ul class="next-steps-list">
                <li>Tinjau hasil tes kandidat</li>
                <li>Generate laporan detail jika diperlukan</li>
                <li>Hubungi kandidat untuk tindak lanjut jika diperlukan</li>
            </ul>
        </div>

        <!-- Closing -->
        <div class="closing">
            Terima kasih!
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Ini adalah notifikasi otomatis dari DWP Psikotes System.</p>
            <p>Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>