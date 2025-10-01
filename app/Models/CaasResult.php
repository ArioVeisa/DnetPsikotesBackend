<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaasResult extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidate_test_id',
        'section_id',
        'concern',
        'control',
        'curiosity',
        'confidence',
        'total',
        'category',
    ];
}
