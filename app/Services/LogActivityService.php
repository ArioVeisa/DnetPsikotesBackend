<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LogActivityService
{
    
    public static function addToLog(
        string $activity, 
        Request $request, 
        string $status = 'success',
        array $additionalData = []
    ) {
        $log = new ActivityLog();
        $log->activity = $activity;
        $log->user_id = Auth::check() ? Auth::id() : null;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('user-agent');
        $log->status = $status;
        
        // Set additional data if provided
        if (isset($additionalData['candidate_id'])) {
            $log->candidate_id = $additionalData['candidate_id'];
        }
        if (isset($additionalData['test_id'])) {
            $log->test_id = $additionalData['test_id'];
        }
        if (isset($additionalData['question_id'])) {
            $log->question_id = $additionalData['question_id'];
        }
        if (isset($additionalData['question_type'])) {
            $log->question_type = $additionalData['question_type'];
        }
        if (isset($additionalData['entity_type'])) {
            $log->entity_type = $additionalData['entity_type'];
        }
        if (isset($additionalData['entity_id'])) {
            $log->entity_id = $additionalData['entity_id'];
        }
        
        $log->save();
    }
}