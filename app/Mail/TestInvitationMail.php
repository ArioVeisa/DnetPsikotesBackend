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

        return $this->subject('Undangan Tes: ' . $this->test->name)
            ->markdown('emails.test_invitation')
            ->with([
                'candidate'        => $this->candidate,
                'testLink'         => $frontendUrl . '/test/' . $this->candidateTest->unique_token,
                'testName'         => $this->test->name,
                'testDuration'     => $this->test->duration_minutes,
                'testInstructions' => $this->test->instructions,
                'customMessage'    => $this->customMessage,
                'expiryDays'       => 7,
            ]);
    }
}
