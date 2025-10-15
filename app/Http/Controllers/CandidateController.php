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
     * Menampilkan kandidat yang belum pernah mengikuti test atau yang baru ditambahkan
     */
    public function getAvailableCandidates(Request $request)
    {
        $testId = $request->query('test_id');
        $includeExisting = $request->query('include_existing', 'false');
        
        // Untuk test distribution baru, default tidak include existing candidates
        // Hanya return empty array kecuali explicitly diminta
        if ($includeExisting !== 'true') {
            return response()->json([
                'data' => [],
                'message' => 'No existing candidates loaded. Use "Add Candidate" to add new candidates.'
            ]);
        }
        
        // Jika include_existing=true, baru tampilkan kandidat yang available
        if ($testId) {
            // Filter kandidat yang belum pernah test dengan test tersebut
            $candidates = Candidate::whereDoesntHave('tests', function ($query) use ($testId) {
                $query->where('test_id', $testId)
                      ->where('status', '!=', CandidateTest::STATUS_COMPLETED);
            })->get();
        } else {
            // Filter kandidat yang belum pernah test sama sekali
            $candidates = Candidate::whereDoesntHave('tests', function ($query) {
                $query->where('status', '!=', CandidateTest::STATUS_COMPLETED);
            })->get();
        }

        return response()->json([
            'data' => $candidates,
            'message' => 'Available candidates retrieved successfully'
        ]);
    }

    /**
     * Load existing candidates yang belum pernah test dengan test package tertentu
     */
    public function loadExistingCandidates(Request $request)
    {
        $testId = $request->query('test_id');
        
        if ($testId) {
            // Filter kandidat yang belum pernah test dengan test tersebut
            $candidates = Candidate::whereDoesntHave('tests', function ($query) use ($testId) {
                $query->where('test_id', $testId)
                      ->where('status', '!=', CandidateTest::STATUS_COMPLETED);
            })->get();
        } else {
            // Filter kandidat yang belum pernah test sama sekali
            $candidates = Candidate::whereDoesntHave('tests', function ($query) {
                $query->where('status', '!=', CandidateTest::STATUS_COMPLETED);
            })->get();
        }

        return response()->json([
            'data' => $candidates,
            'message' => 'Existing available candidates loaded successfully'
        ]);
    }

    /**
     * Get candidates that are already added to a specific test distribution
     */
    public function getTestDistributionCandidates(Request $request)
    {
        $testId = $request->query('test_id');
        
        if (!$testId) {
            return response()->json([
                'data' => [],
                'message' => 'Test ID is required'
            ], 400);
        }

        // Get candidates from test_distribution_candidates table
        $candidates = \App\Models\TestDistributionCandidate::where('test_id', $testId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $candidates,
            'message' => 'Test distribution candidates loaded successfully'
        ]);
    }

    /**
     * Remove candidate from test distribution
     */
    public function removeFromTestDistribution(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:test_distribution_candidates,id',
        ]);

        try {
            $testDistributionCandidate = \App\Models\TestDistributionCandidate::findOrFail($request->id);
            $candidateName = $testDistributionCandidate->name;
            
            // Delete from test_distribution_candidates table
            $testDistributionCandidate->delete();

            // Log activity
            LogActivityService::addToLog("Removed candidate {$candidateName} from test distribution", $request);

            return response()->json([
                'success' => true,
                'message' => 'Candidate removed from test distribution successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing candidate from test distribution: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new candidate to test distribution (save to both tables)
     */
    public function addToTestDistribution(Request $request)
    {
        $request->validate([
            'test_id' => 'required|exists:tests,id',
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:255|unique:candidates,nik|unique:test_distribution_candidates,nik',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'position' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female',
            'department' => 'required|string|max:255',
        ]);

        try {
            // Save to global candidates table
            $globalCandidate = Candidate::create([
                'name' => $request->name,
                'nik' => $request->nik,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'position' => $request->position,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'department' => $request->department,
            ]);

            // Save to test distribution candidates table
            $testCandidate = new \App\Models\TestDistributionCandidate();
            $testCandidate->test_id = $request->test_id;
            $testCandidate->name = $request->name;
            $testCandidate->nik = $request->nik;
            $testCandidate->phone_number = $request->phone_number;
            $testCandidate->email = $request->email;
            $testCandidate->position = $request->position;
            $testCandidate->birth_date = $request->birth_date;
            $testCandidate->gender = $request->gender;
            $testCandidate->department = $request->department;
            $testCandidate->status = 'pending';
            $testCandidate->save();

            return response()->json([
                'success' => true,
                'data' => $testCandidate,
                'global_candidate' => $globalCandidate,
                'message' => 'Candidate added to test distribution successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding candidate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan kandidat baru dengan validasi duplikat NIK (FR-008)
     */
   public function store(Request $request)
    {
        try {
            // Validasi dengan pesan error yang spesifik
            $validated = $request->validate([
                'nik' => 'required|unique:candidates|max:16',
                'name' => 'required|max:100',
                'email' => 'required|email|unique:candidates',
                'phone_number' => 'required|max:20',
                'position' => 'required|max:100',
                'birth_date' => 'required|date',
                'gender' => 'required|in:male,female',
                'department' => 'required|max:100'
            ], [
                'nik.required' => 'NIK harus diisi',
                'nik.unique' => 'NIK sudah digunakan',
                'nik.max' => 'NIK maksimal 16 karakter',
                'name.required' => 'Nama lengkap harus diisi',
                'name.max' => 'Nama lengkap maksimal 100 karakter',
                'email.required' => 'Email harus diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'phone_number.required' => 'Nomor telepon harus diisi',
                'phone_number.max' => 'Nomor telepon maksimal 20 karakter',
                'position.required' => 'Posisi harus diisi',
                'position.max' => 'Posisi maksimal 100 karakter',
                'birth_date.required' => 'Tanggal lahir harus diisi',
                'birth_date.date' => 'Format tanggal lahir tidak valid',
                'gender.required' => 'Jenis kelamin harus diisi',
                'gender.in' => 'Jenis kelamin harus Male atau Female',
                'department.required' => 'Departemen harus diisi',
                'department.max' => 'Departemen maksimal 100 karakter',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors in the format expected by frontend
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        }

        // Validasi duplikat NIK dalam 1 tahun terakhir (FR-008)
         $duplicateCheck = Candidate::where('nik', $request->nik)
                              ->where('created_at', '>=', now()->subYear())
                              ->first();
    
        if ($duplicateCheck) {
            return response()->json([
                'success' => false,
                'message' => 'NIK sudah digunakan dalam 1 tahun terakhir',
                'field' => 'nik',
                'previous_test' => $duplicateCheck->created_at,
                'can_continue' => false
            ], 422);
        }

        try {
            // Mapping gender untuk memastikan kompatibilitas dengan database
            $validated['gender'] = $validated['gender'] === 'female' ? 'female' : 'male';
            
            $candidate = Candidate::create($validated);

            // Log activity: HRD adding new candidate
            LogActivityService::addToLog("Added new candidate: {$candidate->name} ({$candidate->email})", $request);

            return response()->json([
                'success' => true,
                'data' => $candidate,
                'message' => 'Kandidat berhasil ditambahkan'
            ], 201);
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Candidate creation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data. Silakan periksa data yang diisi.',
                'field' => 'general',
                'debug' => $e->getMessage()
            ], 500);
        }
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