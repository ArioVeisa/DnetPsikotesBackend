<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class telitiQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'media_path',
        'category_id',
        'category',
        'is_active',
        'correct_option_id'
    ];

    public function options()
    {
        return $this->hasMany(telitiOption::class, 'question_id');
    }

    public function correctOption()
    {
        return $this->belongsTo(telitiOption::class, 'correct_option_id');
    }

    public function category()
    {
        return $this->belongsTo(telitiCategory::class);
    }
}
