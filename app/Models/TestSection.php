<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestSection extends Model
{
    use HasFactory;
    protected $table = 'test_sections';
    protected $fillable = [
        'test_id',
        'section_type',
        'duration_minutes',
        'question_count',
        'sequence'
    ];
    public function test()
    {
        return $this->belongsTo(Test::class);
    }
    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class, 'section_id');
    }
}
