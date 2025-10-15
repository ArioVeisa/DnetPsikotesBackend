<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Tes Selesai</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .notification-box {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .candidate-info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Tes Psikotes Selesai!</h1>
            <p>Notifikasi Otomatis Sistem DWP</p>
        </div>
        
        <div class="content">
            <div class="notification-box">
                <h2 style="margin-top: 0; color: #28a745;">✅ Tes Telah Diselesaikan</h2>
                <p>Seorang kandidat telah berhasil menyelesaikan tes psikotes. Berikut adalah detail lengkapnya:</p>
            </div>

            <div class="candidate-info">
                <h3 style="margin-top: 0; color: #1976d2;">📋 Informasi Kandidat</h3>
                
                <div class="info-row">
                    <span class="info-label">👤 Nama Kandidat:</span>
                    <span class="info-value"><strong>{{ $candidateName }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">📧 Email:</span>
                    <span class="info-value">{{ $candidateEmail }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">🏢 Posisi:</span>
                    <span class="info-value">{{ $candidatePosition }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">📝 Nama Tes:</span>
                    <span class="info-value"><strong>{{ $testName }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">🎯 Target Posisi:</span>
                    <span class="info-value">{{ $targetPosition }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">📊 Skor:</span>
                    <span class="info-value"><strong>{{ $score }}/100</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">⏰ Waktu Selesai:</span>
                    <span class="info-value">{{ $completedAt }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">📈 Status:</span>
                    <span class="info-value">
                        <span class="status-badge">SELESAI</span>
                    </span>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resultLink }}" class="cta-button">
                    🔍 Lihat Hasil Lengkap
                </a>
            </div>

            <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #856404;">💡 Informasi Penting:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li>Hasil tes dapat dilihat melalui link di atas</li>
                    <li>Data kandidat telah tersimpan di sistem</li>
                    <li>Log aktivitas telah diperbarui di dashboard</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>DWP Psikotes System</strong></p>
            <p>Email ini dikirim secara otomatis oleh sistem.</p>
            <p>Jangan balas email ini. Untuk bantuan, hubungi tim IT.</p>
        </div>
    </div>
</body>
</html>
