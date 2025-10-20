<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestDistributionCandidate extends Model
{
    use HasFactory;

    protected $table = 'test_distribution_candidates';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_INVITED = 'invited';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'test_distribution_id',
        'name',
        'nik',
        'phone_number',
        'email',
        'position',
        'birth_date',
        'gender',
        'department',
        'status'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function testDistribution()
    {
        return $this->belongsTo(TestDistribution::class);
    }

    public function candidateAnswers()
    {
        return $this->hasMany(CandidateAnswer::class, 'candidate_id', 'id');
    }
}
