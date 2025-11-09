<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Tes</title>
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
        .validity-info {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 6px;
            padding: 18px;
            margin: 25px 0;
            font-size: 14px;
            color: #78350f;
        }
        .validity-info strong {
            color: #92400e;
        }
        .timezone-note {
            font-size: 12px;
            color: #78350f;
            margin-top: 8px;
        }
        .start-button {
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
        }
        .start-button:hover {
            background-color: #1d4ed8;
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

        <!-- Greeting -->
        <div class="greeting">
            Pelanggan yang terhormat <strong>{{ $candidate->name }}</strong>,
        </div>

        <!-- Test Information -->
        <div class="test-info">
            <p>Anda telah diundang untuk mengikuti tes <span class="test-name">{{ $testName }}</span></p>
        </div>

        <!-- Test Validity Information -->
        @if($startDate || $endDate)
        <div class="validity-info">
            <p style="margin: 0 0 8px 0;">
                Tombol uji akan valid 
                @if($startDate)
                    Dari <strong>{{ \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d M Y, h:i A') }}</strong>
                @endif
                @if($startDate && $endDate)
                    Hingga
                @elseif($endDate)
                    Hingga
                @endif
                @if($endDate)
                    <strong>{{ \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d M Y, h:i A') }}</strong>
                @endif
            </p>
            <p class="timezone-note">
                (Waktu sesuai <strong>Asia/Jakarta</strong> zona waktu, pastikan tanggal dan waktu sudah sesuai dengan zona waktu Anda)
            </p>
        </div>
        @endif

        <!-- Start Test Instruction -->
        <p style="margin: 25px 0 10px 0; font-size: 15px; color: #374151;">
            Klik tombol yang ditampilkan di bawah ini untuk memulai uji.
        </p>

        <!-- Start Test Button -->
        <a href="{{ $testLink }}" class="start-button">
            MULAI UJI
        </a>

        <!-- Closing -->
        <div class="closing">
            Semoga berhasil!
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>Jika Anda mengalami kendala, silakan hubungi tim HRD.</p>
        </div>
    </div>
</body>
</html>