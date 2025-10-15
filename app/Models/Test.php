<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;
    protected $table = 'tests';
    protected $fillable = [
        'name',
        'target_position',
        'icon_path',
        'started_date',
        'access_type'
    ];

    public function caasQuestions()
    {
        return $this->belongsToMany(CaasQuestion::class, 'test_questions', 'test_id', 'question_id')
            ->withPivot('question_type')
            ->wherePivot('question_type', 'caas');
    }

    public function discQuestions()
    {
        return $this->belongsToMany(DiscQuestion::class, 'test_questions', 'test_id', 'question_id')
            ->withPivot('question_type')
            ->wherePivot('question_type', 'disc');
    }

    public function telitiQuestions()
    {
        return $this->belongsToMany(TelitiQuestion::class, 'test_questions', 'test_id', 'question_id')
            ->withPivot('question_type')
            ->wherePivot('question_type', 'teliti');
    }

    public function sections()
    {
        return $this->hasMany(TestSection::class);
    }

    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function candidateTests()
    {
        return $this->hasMany(CandidateTest::class);
    }
}