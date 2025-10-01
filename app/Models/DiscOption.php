<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_id',
        'option_text',
        'dimension_least',
        'dimension_most',
    ];

    public function question()
    {
        return $this->belongsTo(DiscQuestion::class, 'question_id');
    }
}
