<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateTest;
use App\Models\TestDistributionCandidate;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    /**
     * Menambahkan kandidat ke test distribution
     */
    public function addToTestDistribution(Request $request)
    {
        $validated = $request->validate([
            'test_distribution_id' => 'required|exists:test_distributions,id',
            'nik' => 'required|max:16',
            'name' => 'required|max:100',
            'email' => 'required|email',
            'phone_number' => 'required|max:20',
            'position' => 'required|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female',
            'department' => 'required|max:100'
        ]);

        // Validasi duplikat email/NIK dalam distribution yang sama
        $existingEmail = TestDistributionCandidate::where('test_distribution_id', $validated['test_distribution_id'])
            ->where('email', $validated['email'])
            ->first();
        if ($existingEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah digunakan untuk sesi ini',
                'errors' => [ 'email' => ['Email sudah digunakan untuk sesi ini'] ]
            ], 422);
        }
        $existingNik = TestDistributionCandidate::where('test_distribution_id', $validated['test_distribution_id'])
            ->where('nik', $validated['nik'])
            ->first();
        if ($existingNik) {
            return response()->json([
                'success' => false,
                'message' => 'NIK sudah digunakan untuk sesi ini',
                'errors' => [ 'nik' => ['NIK sudah digunakan untuk sesi ini'] ]
            ], 422);
        }

        // Buat kandidat khusus distribution
        $testCandidate = TestDistributionCandidate::create([
            'test_distribution_id' => $validated['test_distribution_id'],
            'nik' => $validated['nik'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'position' => $validated['position'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'department' => $validated['department'],
            'status' => TestDistributionCandidate::STATUS_PENDING,
        ]);

        // Log activity
        $distribution = \App\Models\TestDistribution::findOrFail($validated['test_distribution_id']);
        LogActivityService::addToLog("Added candidate {$testCandidate->name} to distribution: {$distribution->name}", $request);

        return response()->json([
            'success' => true,
            'data' => $testCandidate,
            'message' => 'Kandidat berhasil ditambahkan ke test distribution'
        ], 201);
    }

    /**
     * Mengambil kandidat untuk test distribution
     */
    public function getTestDistributionCandidates(Request $request)
    {
        $distributionId = $request->query('test_distribution_id');
        
        if (!$distributionId) {
            return response()->json([
                'success' => false,
                'message' => 'Test distribution ID is required'
            ], 400);
        }

        try {
            $testCandidates = TestDistributionCandidate::where('test_distribution_id', $distributionId)
                                         ->with('testDistribution')
                                         ->get();

            return response()->json([
                'success' => true,
                'data' => $testCandidates,
                'message' => 'Test distribution candidates retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving candidates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus kandidat dari test distribution
     */
    public function removeFromTestDistribution(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer'
        ]);

        $testCandidateId = $validated['id'];
        
        // Hapus test candidate
        $testCandidate = TestDistributionCandidate::findOrFail($testCandidateId);
        $candidateName = $testCandidate->name;
        
        // Hapus related answers jika ada
        $testCandidate->candidateAnswers()->delete();
        
        // Hapus test candidate
        $testCandidate->delete();

        LogActivityService::addToLog("Removed candidate {$candidateName} from test distribution", $request);

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil dihapus dari test distribution'
        ]);
    }

    /**
     * Download template Excel untuk import candidates
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $headers = [
                'A1' => 'NIK',
                'B1' => 'Nama',
                'C1' => 'Email',
                'D1' => 'No Telepon',
                'E1' => 'Posisi',
                'F1' => 'Tanggal Lahir',
                'G1' => 'Jenis Kelamin',
                'H1' => 'Departemen'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Set sample data dengan NIK unik dan format tanggal yang fleksibel
            $sampleData = [
                ['9876543210987654', 'Ahmad Rizki', 'ahmad.rizki@example.com', '081234567890', 'Software Developer', '15-01-1990', 'male', 'IT'],
                ['8765432109876543', 'Siti Nurhaliza', 'siti.nurhaliza@example.com', '081234567891', 'UI/UX Designer', '20-05-1992', 'female', 'Design'],
                ['7654321098765432', 'Budi Santoso', 'budi.santoso@example.com', '081234567892', 'Project Manager', '10-12-1988', 'male', 'Management']
            ];
            
            $row = 2;
            foreach ($sampleData as $data) {
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(20);
            
            // Create response
            $writer = new Xlsx($spreadsheet);
            $fileName = 'template-candidates.xlsx';
            
            // Save to temporary file first
            $tempFile = tempnam(sys_get_temp_dir(), 'template_');
            $writer->save($tempFile);
            
            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import candidates dari Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
            'test_package_id' => 'required|integer|exists:tests,id'
        ]);

        try {
            $file = $request->file('file');
            $testPackageId = $request->test_package_id;
            
            // Import hanya validasi data, tidak membuat test distribution
            // Test distribution akan dibuat saat "Send All"
            
            // Load Excel file
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            // Skip header row
            $data = array_slice($rows, 1);
            
            $importedCount = 0;
            $errors = [];
            
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because we skip header and array is 0-indexed
                
                try {
                    // Validate required fields
                    if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || 
                        empty($row[4]) || empty($row[5]) || empty($row[6]) || empty($row[7])) {
                        $errors[] = "Baris {$rowNumber}: Semua kolom harus diisi";
                        continue;
                    }
                    
                    // Validate NIK format (minimal 8 digit, maksimal 20 digit)
                    $nik = trim($row[0]);
                    if (!preg_match('/^\d{8,20}$/', $nik)) {
                        $errors[] = "Baris {$rowNumber}: NIK harus 8-20 digit angka";
                        continue;
                    }
                    
                    // Validate email
                    $email = trim($row[2]);
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Baris {$rowNumber}: Format email tidak valid";
                        continue;
                    }
                    
                    // Validate gender
                    $gender = strtolower(trim($row[6]));
                    if (!in_array($gender, ['male', 'female'])) {
                        $errors[] = "Baris {$rowNumber}: Jenis kelamin harus 'male' atau 'female'";
                        continue;
                    }
                    
                    // Validate and normalize date format
                    $birthDate = trim($row[5]);
                    $normalizedDate = null;
                    
                    // Try different date formats
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
                        // Already in YYYY-MM-DD format
                        $normalizedDate = $birthDate;
                    } elseif (preg_match('/^\d{2}-\d{2}-\d{4}$/', $birthDate)) {
                        // DD-MM-YYYY format, convert to YYYY-MM-DD
                        $parts = explode('-', $birthDate);
                        $normalizedDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $birthDate)) {
                        // DD/MM/YYYY format, convert to YYYY-MM-DD
                        $parts = explode('/', $birthDate);
                        $normalizedDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    } else {
                        $errors[] = "Baris {$rowNumber}: Format tanggal lahir tidak valid. Gunakan DD-MM-YYYY, DD/MM/YYYY, atau YYYY-MM-DD";
                        continue;
                    }
                    
                    // Validate that the date is valid
                    if (!strtotime($normalizedDate)) {
                        $errors[] = "Baris {$rowNumber}: Tanggal lahir tidak valid";
                        continue;
                    }
                    
                    // Validasi data berhasil, tambahkan ke array untuk dikembalikan ke frontend
                    $candidateData = [
                        'nik' => $nik,
                        'name' => trim($row[1]),
                        'email' => $email,
                        'phone_number' => trim($row[3]),
                        'position' => trim($row[4]),
                        'birth_date' => $normalizedDate,
                        'gender' => $gender,
                        'department' => trim($row[7])
                    ];
                    
                    $importedCandidates[] = $candidateData;
                    $importedCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }
            
            // Log activity
            LogActivityService::addToLog("Imported {$importedCount} candidates from Excel file", $request);
            
            return response()->json([
                'success' => true,
                'imported_count' => $importedCount,
                'errors' => $errors,
                'message' => "Berhasil mengimpor {$importedCount} kandidat",
                'candidates' => $importedCandidates,
                'test_package_id' => $testPackageId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor file: ' . $e->getMessage()
            ], 500);
        }
    }
}