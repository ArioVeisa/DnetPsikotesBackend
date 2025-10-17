<?php

namespace App\Http\Controllers\Results;

use App\Http\Controllers\Controller;
use App\Models\CaasResult;
use App\Models\CandidateTest;
use App\Models\DiscResult;
use App\Models\TelitiResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        $data = CandidateTest::with(['candidate', 'test'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $data = CandidateTest::with(['candidate', 'test'])
            ->findOrFail($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate test not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function download($candidate_test_id)
    {
        $candidateTest = CandidateTest::with('candidate', 'test')->findOrFail($candidate_test_id);

        $teliti = TelitiResult::where('candidate_test_id', $candidate_test_id)->get();
        $caas   = CaasResult::where('candidate_test_id', $candidate_test_id)->get();
        $disc   = DiscResult::where('candidate_test_id', $candidate_test_id)->get();

        $data = [
            'candidate_test' => $candidateTest,
            'teliti' => $teliti,
            'caas'   => $caas,
            'disc'   => $disc,
        ];

        // Render ke view
        $pdf = Pdf::loadView('results.report', $data);

        // Nama file otomatis
        $fileName = 'Result_' . ($candidateTest->candidate->name ?? 'Unknown') . '.pdf';
        return $pdf->download($fileName);
    }
}
