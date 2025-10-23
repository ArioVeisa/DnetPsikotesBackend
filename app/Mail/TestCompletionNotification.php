<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestCompletionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $candidateName;
    public $candidateEmail;
    public $candidatePosition;
    public $testName;
    public $targetPosition;
    public $score;
    public $completedAt;
    public $resultLink;

    /**
     * Create a new message instance.
     */
    public function __construct($candidateName, $candidateEmail, $candidatePosition, $testName, $targetPosition, $score, $completedAt, $resultLink)
    {
        $this->candidateName = $candidateName;
        $this->candidateEmail = $candidateEmail;
        $this->candidatePosition = $candidatePosition;
        $this->testName = $testName;
        $this->targetPosition = $targetPosition;
        $this->score = $score;
        $this->completedAt = $completedAt;
        $this->resultLink = $resultLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Completion Notification - ' . $this->candidateName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.test-completion-notification',
            with: [
                'candidateName' => $this->candidateName,
                'candidateEmail' => $this->candidateEmail,
                'candidatePosition' => $this->candidatePosition,
                'testName' => $this->testName,
                'targetPosition' => $this->targetPosition,
                'score' => $this->score,
                'completedAt' => $this->completedAt,
                'resultLink' => $this->resultLink,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}