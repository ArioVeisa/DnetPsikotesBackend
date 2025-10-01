<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_text',
        'media_path',
        'category_id',
        'category',
        'is_active'
    ];
    public function options()
    {
        return $this->hasMany(DiscOption::class, 'question_id');
    }

    public function category()
    {
        return $this->belongsTo(DiscCategory::class);
    }
}
