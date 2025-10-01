<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class telitiOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_id',
        'option_text'
    ];

    public function question()
    {
        return $this->belongsTo(telitiQuestion::class, 'question_id');
    }
}
