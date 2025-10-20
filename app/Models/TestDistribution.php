<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestDistribution extends Model
{
    use HasFactory;

    protected $table = 'test_distributions';

    protected $fillable = [
        'name',
        'template_test_id',
        'target_position',
        'icon_path',
        'started_date',
        'ended_date',
        'access_type',
        'status',
    ];

    protected $casts = [
        'started_date' => 'date',
        'ended_date' => 'date',
    ];

    // Status constants
    const STATUS_SCHEDULED = 'Scheduled';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_CANCELLED = 'Cancelled';

    // Relationship to template test package
    public function templateTest()
    {
        return $this->belongsTo(Test::class, 'template_test_id');
    }

    // Relationship to test instances (if we still use them)
    public function testInstances()
    {
        return $this->hasMany(Test::class, 'parent_test_id');
    }

    // Relationship to candidates
    public function candidates()
    {
        return $this->hasMany(TestDistributionCandidate::class, 'test_distribution_id');
    }

    // Relationship to candidate tests
    public function candidateTests()
    {
        return $this->hasMany(CandidateTest::class, 'test_distribution_id');
    }
}

