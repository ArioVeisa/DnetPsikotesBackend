<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class telitiResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_test_id',
        'section_id',
        'score',
        'total_questions',
        'category',
    ];
}
