<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateTest;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CandidateController extends Controller
{
    /**
     * Menampilkan daftar kandidat dengan filter berdasarkan role user
     */
    public function index()
    {
       $candidates = Candidate::all();
        return response()->json($candidates);
    }

    /**
     * Menyimpan kandidat baru dengan validasi duplikat NIK (FR-008)
     */
   public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|unique:candidates|max:16',
            'name' => 'required|max:100',
            'email' => 'required|email|unique:candidates',
            'phone_number' => 'required|max:20',
            'position' => 'required|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female',
            'department' => 'required|max:100'
        ]);

        // Validasi duplikat NIK dalam 1 tahun terakhir (FR-008)
         $duplicateCheck = Candidate::where('nik', $request->nik)
                              ->where('created_at', '>=', now()->subYear())
                              ->first();
    
        if ($duplicateCheck) {
            return response()->json([
                'success' => false,
                'warning' => 'Kandidat dengan NIK ini sudah mengikuti tes dalam 1 tahun terakhir!',
                'previous_test' => $duplicateCheck->created_at,
                'can_continue' => false
            ], 422);
        }

        $candidate = Candidate::create($validated);

        // Log activity: HRD adding new candidate
        LogActivityService::addToLog("Added new candidate: {$candidate->name} ({$candidate->email})", $request);

        return response()->json([
            'success' => true,
            'data' => $candidate,
            'message' => 'Kandidat berhasil ditambahkan'
        ], 201);
    }

    /**
     * Menampilkan detail kandidat
     */
    public function show($id)
    {
        $candidate = Candidate::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $candidate,
            'message' => 'Data kandidat berhasil ditemukan'
        ]);
    }

    /**
     * Mengupdate data kandidat
     */
    public function update(Request $request, Candidate $candidate)
    {
        // Cek akses berdasarkan departemen (untuk admin)
        $user = Auth::user();
        if ($user->role !== 'super_admin' && $candidate->department !== $user->department) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke kandidat ini'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|max:100',
            'email' => 'sometimes|email|unique:candidates,email,' . $candidate->id,
            'phone_number' => 'sometimes|max:20',
            'position' => 'sometimes|max:100',
            'department' => 'sometimes|max:100',
            'nik' => 'sometimes|max:16|unique:candidates,nik,' . $candidate->id,
            'birth_date' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female'
        ]);

        $candidate->update($validated);

        return response()->json([
            'success' => true,
            'data' => $candidate,
            'message' => 'Data kandidat berhasil diperbarui'
        ]);
    }

    /**
     * Menghapus kandidat
     */
    public function destroy( $id)
    {
        $candidate = Candidate::findOrFail($id);
       // cek apakah memimliki riwayat tes
        if ($candidate->tests()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kandidat ini tidak dapat dihapus karena memiliki riwayat tes'
            ], 422);
        }

        // Hapus kandidat
        $candidate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil dihapus'
        ]);
    }

    /**
     * Fungsi reusable untuk cek duplikat kandidat
     */
    public function checkDuplicate(Request $request)
    {
        $validator = $request->validate([
            'nik' => 'required|string|max:16'
        ]);
       if (!$validator) {
            return response()->json([
                'success' => false,
                'message' => 'NIK tidak valid'
            ], 422);
        }

        $result = $this->checkDuplicateCandidate($request->nik);

        return response()->json([
            'success' => true,
            'is_duplicate' => $result['is_duplicate'],
            'previous_test' => $result['previous_test']
        ]);
    }

    /**
     * Fungsi reusable untuk cek duplikat kandidat
     */
    private function checkDuplicateCandidate(string $nik): array
    {
        $oneYearAgo = Carbon::now()->subYear();

        $existingCandidate = Candidate::where('nik', $nik)
            ->whereHas('tests', function ($query) use ($oneYearAgo) {
                $query->where('created_at', '>=', $oneYearAgo);
            })
            ->with(['tests' => function ($query) {
                $query->orderBy('created_at', 'desc')
                    ->with('test')
                    ->first();
            }])
            ->first();

        if ($existingCandidate && $existingCandidate->tests->isNotEmpty()) {
            $latestTest = $existingCandidate->tests->first();

            return [
                'is_duplicate' => true,
                'previous_test' => [
                    'test_name' => $latestTest->test->name ?? 'Unknown Test',
                    'test_date' => $latestTest->created_at->format('Y-m-d H:i:s'),
                    'status' => $latestTest->is_completed ? 'Completed' : 'In Progress',
                    'candidate_name' => $existingCandidate->name,
                    'candidate_email' => $existingCandidate->email
                ]
            ];
        }

        return [
            'is_duplicate' => false,
            'previous_test' => null
        ];
    }
}