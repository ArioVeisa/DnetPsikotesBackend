<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaasOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_id',
        'option_text',
        'score'
    ];
    public function question()
    {
        return $this->belongsTo(CaasQuestion::class, 'question_id');
    }
}
