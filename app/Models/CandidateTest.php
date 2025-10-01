<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'test_id',
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