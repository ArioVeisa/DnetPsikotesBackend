<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_test_id',
        'section_id',
        'question_id',
        'most_option_id',
        'least_option_id',
        'selected_option_id',
        'is_correct',
        'score',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];
    public function section()
    {
        return $this->belongsTo(TestSection::class, 'section_id');
    }

    public function candidateTest()
    {
        return $this->belongsTo(CandidateTest::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function questions()
    {
        return $this->belongsToMany(TelitiQuestion::class, 'test_section_questions', 'section_id', 'question_id');
    }

    public function getTotalQuestionsAttribute()
    {
        return $this->questions()->count();
    }

    public function mostOption()
    {
        return $this->belongsTo(DiscOption::class, 'most_option_id');
    }

    public function leastOption()
    {
        return $this->belongsTo(DiscOption::class, 'least_option_id');
    }
}
