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
        return match ($this->question_type) {
            'caas' => CaasQuestion::find($this->question_id),
            'teliti' => telitiQuestion::find($this->question_id),
            'disc' => DiscQuestion::find($this->question_id),
            default => null
        };
    }
}
