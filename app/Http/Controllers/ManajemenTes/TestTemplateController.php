<?php

namespace App\Http\Controllers\ManajemenTes;

use App\Http\Controllers\Controller;
use App\Models\TestTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestTemplateController extends Controller
{
    // FR-010: Mendapatkan semua template tes
    public function index(Request $request)
    {
        try {
            $query = TestTemplate::query();
            
            // Filter berdasarkan posisi
            if ($request->has('position')) {
                $query->where('position', $request->position);
            }
            
            // Filter hanya yang aktif
            if ($request->has('active_only') && $request->active_only) {
                $query->active();
            }
            
            $templates = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data template tes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // FR-010: Mendapatkan template berdasarkan ID
    public function show($id)
    {
        try {
            $template = TestTemplate::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template tes tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // FR-010: Membuat template tes baru
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'position' => 'required|string|in:Manager,Staff,Fresh Graduate',
                'include_disc' => 'boolean',
                'include_caas' => 'boolean',
                'include_teliti' => 'boolean',
                'disc_time' => 'nullable|integer|min:1',
                'caas_time' => 'nullable|integer|min:1',
                'teliti_time' => 'nullable|integer|min:1',
                'disc_questions_count' => 'nullable|integer|min:1',
                'caas_questions_count' => 'nullable|integer|min:1',
                'teliti_questions_count' => 'nullable|integer|min:1',
                'sequence' => 'nullable|array',
                'sequence.*' => 'string|in:disc,caas,teliti',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validasi kombinasi tes
            if (!$request->include_disc && !$request->include_caas && !$request->include_teliti) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal satu jenis tes harus dipilih'
                ], 422);
            }

            // Validasi sequence jika ada
            if ($request->has('sequence')) {
                $validSequence = true;
                foreach ($request->sequence as $testType) {
                    if ($testType === 'disc' && !$request->include_disc) {
                        $validSequence = false;
                    }
                    if ($testType === 'caas' && !$request->include_caas) {
                        $validSequence = false;
                    }
                    if ($testType === 'teliti' && !$request->include_teliti) {
                        $validSequence = false;
                    }
                }
                
                if (!$validSequence) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sequence mengandung jenis tes yang tidak dipilih'
                    ], 422);
                }
            }

            $template = TestTemplate::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Template tes berhasil dibuat',
                'data' => $template
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat template tes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // FR-010: Mengupdate template tes
    public function update(Request $request, $id)
    {
        try {
            $template = TestTemplate::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'position' => 'sometimes|required|string|in:Manager,Staff,Fresh Graduate',
                'include_disc' => 'boolean',
                'include_caas' => 'boolean',
                'include_teliti' => 'boolean',
                'disc_time' => 'nullable|integer|min:1',
                'caas_time' => 'nullable|integer|min:1',
                'teliti_time' => 'nullable|integer|min:1',
                'disc_questions_count' => 'nullable|integer|min:1',
                'caas_questions_count' => 'nullable|integer|min:1',
                'teliti_questions_count' => 'nullable|integer|min:1',
                'sequence' => 'nullable|array',
                'sequence.*' => 'string|in:disc,caas,teliti',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validasi kombinasi tes
            $includeDisc = $request->has('include_disc') ? $request->include_disc : $template->include_disc;
            $includeCaas = $request->has('include_caas') ? $request->include_caas : $template->include_caas;
            $includeteliti = $request->has('include_teliti') ? $request->include_teliti : $template->include_teliti;
            
            if (!$includeDisc && !$includeCaas && !$includeteliti) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal satu jenis tes harus dipilih'
                ], 422);
            }

            $template->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Template tes berhasil diupdate',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate template tes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // FR-010: Menghapus template tes (soft delete)
    public function destroy($id)
    {
        try {
            $template = TestTemplate::findOrFail($id);
            $template->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Template tes berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus template tes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // FR-011: Mendapatkan soal acak berdasarkan jenis tes dan jumlah
    public function getRandomQuestions(Request $request, $testType)
    {
        try {
            $validator = Validator::make($request->all(), [
                'count' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $count = $request->count;
            $questions = [];
            
            switch ($testType) {
                case 'disc':
                    $questions = \App\Models\DiscQuestion::with('options')
                        ->where('is_active', true)
                        ->inRandomOrder()
                        ->limit($count)
                        ->get();
                    break;
                    
                case 'caas':
                    $questions = \App\Models\CaaaQuestion::with('options')
                        ->where('is_active', true)
                        ->inRandomOrder()
                        ->limit($count)
                        ->get();
                    break;
                    
                case 'teliti':
                    $questions = \App\Models\TelitQuestion::with('options')
                        ->where('is_active', true)
                        ->inRandomOrder()
                        ->limit($count)
                        ->get();
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Jenis tes tidak valid'
                    ], 422);
            }
            
            return response()->json([
                'success' => true,
                'data' => $questions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil soal acak',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}