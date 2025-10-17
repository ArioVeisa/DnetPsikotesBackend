<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Psikotes</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #222;
            line-height: 1.5;
            background: white;
            font-size: 12px;
        }

        .page {
            width: 190mm;
            margin: 0 auto;
            background: white;
        }

        /* ===== HEADER ===== */
        .header {
            background-color: #1e40af;
            color: white;
            padding: 15mm 10mm;
            text-align: center;
            border-bottom: 4px solid #7c3aed;
        }

        .avatar-simple {
            width: 25mm;
            height: 25mm;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            margin: 0 auto 5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16pt;
            font-weight: bold;
            border: 2px solid white;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 3mm;
        }

        .header-meta {
            font-size: 10pt;
            opacity: 0.95;
            margin: 2mm 0;
        }

        .status-badge {
            background: #10b981;
            color: white;
            display: inline-block;
            padding: 3mm 6mm;
            border-radius: 10mm;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 4mm;
        }

        /* ===== CONTENT ===== */
        .content {
            padding: 10mm;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: space-between;
            gap: 5mm;
            margin-bottom: 10mm;
        }

        .summary-box {
            flex: 1;
            background: #f3f4f6;
            border: 0.3mm solid #d1d5db;
            border-top: 3mm solid #7c3aed;
            padding: 6mm;
            text-align: center;
            border-radius: 2mm;
        }

        .summary-label {
            font-size: 9pt;
            color: #6b7280;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3mm;
        }

        .summary-value {
            font-size: 18pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 2mm;
        }

        .badge {
            font-size: 8pt;
            padding: 1.5mm 4mm;
            border-radius: 10mm;
            font-weight: bold;
            display: inline-block;
        }

        .badge-high {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-excellent {
            background: #c7d2fe;
            color: #3730a3;
        }

        .badge-dominan {
            background: #fef3c7;
            color: #b45309;
        }

        /* Section */
        .section {
            margin-bottom: 8mm;
            page-break-inside: avoid;
        }

        .section-header {
            background: #f9fafb;
            border-left: 3mm solid #7c3aed;
            padding: 4mm;
            margin-bottom: 4mm;
            border-radius: 1mm;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 1mm;
        }

        .section-subtitle {
            font-size: 9pt;
            color: #6b7280;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3mm;
            font-size: 9pt;
        }

        th {
            background: #1e40af;
            color: white;
            padding: 3mm;
            text-align: center;
            font-weight: bold;
            border: 0.2mm solid #1e40af;
        }

        td {
            padding: 3mm;
            border: 0.2mm solid #d1d5db;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-bold {
            font-weight: bold;
            color: #1a1a1a;
        }

        /* DISC Section */
        .disc-container {
            display: flex;
            gap: 5mm;
            margin-top: 4mm;
        }

        .disc-box {
            flex: 1;
            background: #f3f4f6;
            border: 0.3mm solid #d1d5db;
            border-left: 3mm solid #7c3aed;
            padding: 5mm;
            border-radius: 1mm;
        }

        .disc-type-header {
            display: flex;
            align-items: center;
            gap: 4mm;
            margin-bottom: 3mm;
        }

        .disc-avatar-small {
            width: 15mm;
            height: 15mm;
            background: #1e40af;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12pt;
            font-weight: bold;
            flex-shrink: 0;
        }

        .disc-info-text h3 {
            font-size: 10pt;
            font-weight: bold;
            color: #1a1a1a;
            margin: 0;
        }

        .disc-info-text p {
            font-size: 8pt;
            color: #6b7280;
            margin: 1mm 0 0 0;
        }

        .disc-text {
            background: white;
            border-left: 2mm solid #7c3aed;
            padding: 4mm;
            border-radius: 1mm;
            font-size: 9pt;
            line-height: 1.4;
            color: #374151;
        }

        .disc-text strong {
            color: #1e40af;
            display: block;
            margin-bottom: 2mm;
        }

        /* Footer */
        .footer {
            background: #f9fafb;
            border-top: 0.3mm solid #d1d5db;
            padding: 5mm 10mm;
            font-size: 8pt;
            color: #6b7280;
            text-align: center;
        }

        .footer p {
            margin: 1mm 0;
        }

        /* Print-safe */
        @media print {
            body { margin: 0; padding: 0; }
            .page { margin: 0; width: 100%; }
            .section, .summary-section, .header, .footer { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER -->
        <div class="header">
            <div class="avatar-simple">
                {{ strtoupper(substr($candidate_test->candidate->name, 0, 2)) }}
            </div>
            <h1>{{ $candidate_test->candidate->name }}</h1>
            <div class="header-meta">{{ $candidate_test->test->name }}</div>
            <div class="header-meta">{{ \Carbon\Carbon::parse($candidate_test->created_at)->format('d F Y') }}</div>
            <span class="status-badge">✓ Selesai</span>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <!-- SUMMARY -->
            <div class="summary-section">
                <div class="summary-box">
                    <div class="summary-label">Career Adaptability</div>
                    <div class="summary-value">{{ $caas->sum('total_score') }}</div>
                    <span class="badge badge-high">High</span>
                </div>
                <div class="summary-box">
                    <div class="summary-label">Fast Accuracy</div>
                    <div class="summary-value">
                        {{ $teliti->sum('score') }}/{{ $teliti->sum('total_questions') }}
                    </div>
                    <span class="badge badge-excellent">Excellent</span>
                </div>
                <div class="summary-box">
                    <div class="summary-label">Tipe Kepribadian</div>
                    <div class="summary-value" style="color: #7c3aed;">
                        {{ strtoupper($disc->first()->type ?? 'N/A') }}
                    </div>
                    <span class="badge badge-dominan">{{ ucfirst($disc->first()->description ?? 'Dominan') }}</span>
                </div>
            </div>

            <!-- TES TELITI -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title">■ Tes Teliti (Ketelitian)</div>
                    <div class="section-subtitle">Pengukuran Akurasi & Kecepatan</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Skor</th>
                            <th>Total Soal</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-bold">{{ $teliti->sum('score') }}</td>
                            <td>{{ $teliti->sum('total_questions') }}</td>
                            <td><span class="badge badge-excellent">Excellent</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- CAAS -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title">● Career Adapt-Abilities (CAAS)</div>
                    <div class="section-subtitle">Kemampuan Adaptasi Karir</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Concern</th>
                            <th>Control</th>
                            <th>Curiosity</th>
                            <th>Confidence</th>
                            <th>Total</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $concern = $caas->sum('concern');
                            $control = $caas->sum('control');
                            $curiosity = $caas->sum('curiosity');
                            $confidence = $caas->sum('confidence');
                            $total = $caas->sum('total_score');
                        @endphp
                        <tr>
                            <td class="text-bold">{{ $concern }}</td>
                            <td class="text-bold">{{ $control }}</td>
                            <td class="text-bold">{{ $curiosity }}</td>
                            <td class="text-bold">{{ $confidence }}</td>
                            <td class="text-bold" style="color: #7c3aed;">{{ $total }}</td>
                            <td><span class="badge badge-high">High</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- DISC -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title">◆ Profil DISC</div>
                    <div class="section-subtitle">Tipe Kepribadian & Perilaku</div>
                </div>
                <div class="disc-container">
                    <div class="disc-box">
                        <div class="disc-type-header">
                            <div class="disc-avatar-small">
                                {{ strtoupper($disc->first()->type ?? 'D') }}
                            </div>
                            <div class="disc-info-text">
                                <h3>{{ ucfirst($disc->first()->name ?? 'Dominant') }}</h3>
                                <p>Tipe Kepribadian Utama</p>
                            </div>
                        </div>
                    </div>
                    <div class="disc-box">
                        <div class="disc-text">
                            <strong>Karakteristik:</strong>
                            {{ $disc->first()->description ?? 'Orang ini cenderung tegas dan berorientasi pada hasil. Memiliki kepemimpinan yang kuat, fokus pada pencapaian target, dan tidak takut mengambil risiko.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p>Laporan Hasil Psikotes</p>
            <p>Dibuat pada {{ \Carbon\Carbon::parse($candidate_test->created_at)->translatedFormat('d F Y, H:i') }} WIB</p>
            <p>© {{ date('Y') }} Sistem Psikotes | Dokumen Rahasia</p>
        </div>
    </div>
</body>
</html>
