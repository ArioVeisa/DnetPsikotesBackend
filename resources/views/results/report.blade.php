<!DOCTYPE html>
<html>
<head>
    <title>Hasil Psikotes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; line-height: 1.6; }
        h1, h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        .section { margin-bottom: 30px; }
    </style>
</head>
<body>
    <h1>Laporan Hasil Psikotes</h1>

    <p><strong>Nama Kandidat:</strong> {{ $candidate_test->candidate->name ?? '-' }}</p>
    <p><strong>Nama Tes:</strong> {{ $candidate_test->test->name ?? '-' }}</p>
    <p><strong>Status:</strong> {{ ucfirst($candidate_test->status) }}</p>

    {{-- ===================== Teliti ===================== --}}
    <div class="section">
        <h2>Tes Teliti</h2>
        @if($teliti->isNotEmpty())
            <table>
                <tr>
                    <th>Section</th>
                    <th>Score</th>
                    <th>Total Questions</th>
                    <th>Category</th>
                </tr>
                @foreach($teliti as $t)
                    <tr>
                        <td>{{ $t->section_id }}</td>
                        <td>{{ $t->score }}</td>
                        <td>{{ $t->total_questions }}</td>
                        <td>{{ $t->category }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <p>Tidak ada hasil Teliti.</p>
        @endif
    </div>

    {{-- ===================== CAAS ===================== --}}
    <div class="section">
        <h2>Tes CAAS</h2>
        @if($caas->isNotEmpty())
            <table>
                <tr>
                    <th>Concern</th>
                    <th>Control</th>
                    <th>Curiosity</th>
                    <th>Confidence</th>
                    <th>Total</th>
                    <th>Category</th>
                </tr>
                @foreach($caas as $c)
                    <tr>
                        <td>{{ $c->concern }}</td>
                        <td>{{ $c->control }}</td>
                        <td>{{ $c->curiosity }}</td>
                        <td>{{ $c->confidence }}</td>
                        <td>{{ $c->total }}</td>
                        <td>{{ $c->category }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <p>Tidak ada hasil CAAS.</p>
        @endif
    </div>

    {{-- ===================== DISC ===================== --}}
    <div class="section">
        <h2>Tes DISC</h2>
        @if($disc->isNotEmpty())
            @foreach($disc as $d)
                <p><strong>Tipe Dominan:</strong> {{ $d->dominant_type }}</p>
                <p><strong>Interpretasi:</strong> {{ $d->interpretation }}</p>
            @endforeach
        @else
            <p>Tidak ada hasil DISC.</p>
        @endif
    </div>

    <p><em>Generated on {{ now()->format('d M Y, H:i') }}</em></p>
</body>
</html>
