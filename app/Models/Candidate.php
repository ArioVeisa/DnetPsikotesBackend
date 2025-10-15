<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'name',
        'phone_number',
        'position',
        'email',
        'birth_date',
        'gender',
        'department',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'gender' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function candidateTests()
    {
        return $this->hasMany(CandidateTest::class);
    }

    public function tests()
    {
        return $this->hasMany(CandidateTest::class);
    }

    public function checkDuplicateCandidate($nik) {
    return $this->where('nik', $nik)
                ->where('created_at', '>=', now()->subYear())
                ->first();
    }
}