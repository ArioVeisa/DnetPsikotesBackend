<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaasQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_text',
        'category_id',
        'media_path',
        'category',
        'is_active'
    ];

    public function options()
    {
        return $this->hasMany(CaasOption::class, 'question_id');
    }

    public function category()
    {
        return $this->belongsTo(CaasCategory::class);
    }
}
