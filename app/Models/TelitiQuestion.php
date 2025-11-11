<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelitiQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'media_path',
        'media_path_2',
        'category_id',
        'category',
        'is_active',
        'correct_option_id'
    ];

    public function options()
    {
        return $this->hasMany(TelitiOption::class, 'question_id');
    }

    public function correctOption()
    {
        return $this->belongsTo(TelitiOption::class, 'correct_option_id');
    }

    public function category()
    {
        return $this->belongsTo(TelitiCategory::class);
    }
}
