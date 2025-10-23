<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelitiResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_test_id',
        'section_id',
        'score',
        'total_questions',
        'category',
    ];

    public function candidateTest()
    {
        return $this->belongsTo(CandidateTest::class);
    }

    public function section()
    {
        return $this->belongsTo(TestSection::class);
    }
}
