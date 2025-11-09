<?php

namespace App\Mail;

use App\Models\Candidate;
use App\Models\CandidateTest;
use App\Models\Test;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $candidate;
    public $candidateTest;
    public $test;
    public $customMessage;

    public function __construct(Candidate $candidate, CandidateTest $candidateTest, Test $test, $customMessage = null)
    {
        $this->candidate = $candidate;
        $this->candidateTest = $candidateTest;
        $this->test = $test;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        // FE base url diambil dari .env
        $frontendUrl = env('FRONTEND_URL', 'https://gertude-uncategorised-laurene.ngrok-free.dev');
        
        // Get test distribution untuk mendapatkan start_date dan end_date
        $testDistribution = $this->candidateTest->testDistribution;
        $startDate = $testDistribution ? $testDistribution->started_date : null;
        $endDate = $testDistribution ? $testDistribution->ended_date : null;
        
        // Format durasi test (dalam bahasa Indonesia)
        $durationHours = floor($this->test->duration_minutes / 60);
        $durationMinutes = $this->test->duration_minutes % 60;
        $durationText = '';
        if ($durationHours > 0) {
            $durationText .= $durationHours . ' jam';
        }
        if ($durationMinutes > 0) {
            if ($durationText) $durationText .= ' ';
            $durationText .= $durationMinutes . ' menit';
        }

        return $this->subject('Undangan Tes: ' . $this->test->name)
            ->view('emails.test_invitation')
            ->with([
                'candidate'        => $this->candidate,
                'testLink'         => $frontendUrl . '/test/' . $this->candidateTest->unique_token,
                'testName'         => $this->test->name,
                'testDuration'     => $durationText,
                'testDurationMinutes' => $this->test->duration_minutes,
                'testInstructions' => $this->test->instructions,
                'customMessage'    => $this->customMessage,
                'startDate'        => $startDate,
                'endDate'          => $endDate,
                'frontendUrl'      => $frontendUrl,
            ]);
    }
}
