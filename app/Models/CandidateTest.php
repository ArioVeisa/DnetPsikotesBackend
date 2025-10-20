<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\TestCompletionEmailService;

class CandidateTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'test_id',
        'test_distribution_id',
        'unique_token',
        'started_at',
        'completed_at',
        'score',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Get the candidate associated with this test
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the test associated with this candidate test
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the test distribution associated with this candidate test
     */
    public function testDistribution()
    {
        return $this->belongsTo(TestDistribution::class);
    }

    /**
     * Get the DISC results for this candidate test
     */
    public function discResults()
    {
        return $this->hasMany(DiscResult::class);
    }

    /**
     * Get the CAAS results for this candidate test
     */
    public function caasResults()
    {
        return $this->hasMany(CaasResult::class);
    }

    /**
     * Get the Teliti results for this candidate test
     */
    public function telitiResults()
    {
        return $this->hasMany(telitiResult::class);
    }

    /**
     * Check if the test is expired
     */
    public function isExpired(): bool
    {
        return $this->status !== self::STATUS_COMPLETED && 
               $this->created_at->addDays(7)->isPast();
    }

    /**
     * Mark test as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark test as completed with score
     */
    public function markAsCompleted(int $score): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'score' => $score,
        ]);

        // Send email notification after test completion
        $this->sendCompletionEmailNotification();
    }

    /**
     * Send email notification when test is completed
     */
    private function sendCompletionEmailNotification(): void
    {
        try {
            $emailService = app(TestCompletionEmailService::class);
            $emailService->sendCompletionNotification($this);
        } catch (\Exception $e) {
            // Log error but don't fail the test completion
            \Log::error('Failed to send completion email notification', [
                'candidate_test_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate a new unique token for this test
     */
    public function regenerateToken(): string
    {
        $newToken = \Illuminate\Support\Str::uuid();
        $this->update(['unique_token' => $newToken]);
        return $newToken;
    }
}