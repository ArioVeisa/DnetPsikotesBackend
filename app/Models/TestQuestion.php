<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestion extends Model
{
    use HasFactory;
    protected $fillable = ['test_id', 'question_id', 'question_type', 'section_id'];
    protected $appends = ['question_detail'];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
    public function section()
    {
        return $this->belongsTo(TestSection::class, 'section_id');
    }

    public function getQuestionDetailAttribute()
    {
        $question = match ($this->question_type) {
            'CAAS' => CaasQuestion::with('options')->find($this->question_id),
            'DISC' => DiscQuestion::with('options')->find($this->question_id),
            'teliti' => TelitiQuestion::with('options')->find($this->question_id),
            // Fallback untuk case lama (jika ada data lama)
            'caas' => CaasQuestion::with('options')->find($this->question_id),
            'disc' => DiscQuestion::with('options')->find($this->question_id),
            default => null
        };
        
        return $question;
    }
}
