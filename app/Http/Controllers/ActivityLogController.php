<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Mulai query builder
        $query = ActivityLog::with(['user:id,name', 'candidate:id,name', 'test:id,name']);

        // Filter berdasarkan user/kandidat [cite: 251]
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan candidate
        if ($request->has('candidate_id')) {
            $query->where('candidate_id', $request->candidate_id);
        }

        // Filter berdasarkan test
        if ($request->has('test_id')) {
            $query->where('test_id', $request->test_id);
        }

        // Filter berdasarkan question type
        if ($request->has('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        // Filter berdasarkan entity type
        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan search pada jenis aktivitas [cite: 250]
        if ($request->has('search')) {
            $query->where('activity', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan rentang tanggal [cite: 248]
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        // Ambil data yang sudah difilter, urutkan dari yang terbaru
        $logs = $query->latest()->get();

        return response()->json($logs);
    }

    public function export(Request $request)
    {
        // Ambil nama file dengan timestamp
        $fileName = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';

        // Logika filter (sama seperti di method index)
        $query = ActivityLog::with(['user:id,name', 'candidate:id,name', 'test:id,name']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('candidate_id')) {
            $query->where('candidate_id', $request->candidate_id);
        }
        if ($request->has('test_id')) {
            $query->where('test_id', $request->test_id);
        }
        if ($request->has('question_type')) {
            $query->where('question_type', $request->question_type);
        }
        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('search')) {
            $query->where('activity', 'like', '%' . $request->search . '%');
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        $logs = $query->latest()->get();

        // Siapkan header untuk download file
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // Buat streamed response untuk efisiensi memori
        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            // Tulis header kolom
            fputcsv($file, ['ID', 'User', 'Candidate', 'Test', 'Activity', 'Question Type', 'Entity Type', 'Status', 'IP Address', 'Timestamp']);

            // Tulis data baris
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'N/A',
                    $log->candidate ? $log->candidate->name : 'N/A',
                    $log->test ? $log->test->name : 'N/A',
                    $log->activity,
                    $log->question_type ?? 'N/A',
                    $log->entity_type ?? 'N/A',
                    $log->status,
                    $log->ip_address,
                    $log->created_at->toDateTimeString(),
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}