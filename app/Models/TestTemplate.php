<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'position',
        'include_disc',
        'include_caas',
        'include_teliti',
        'disc_time',
        'caas_time',
        'teliti_time',
        'disc_questions_count',
        'caas_questions_count',
        'teliti_questions_count',
        'sequence',
        'is_active'
    ];

    protected $casts = [
        'include_disc' => 'boolean',
        'include_caas' => 'boolean',
        'include_teliti' => 'boolean',
        'is_active' => 'boolean',
        'sequence' => 'array'
    ];

    // Scope untuk template aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk posisi tertentu
    public function scopeForPosition($query, $position)
    {
        return $query->where('position', $position);
    }
}