<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'candidate_id',
        'test_id',
        'question_id',
        'question_type',
        'entity_type',
        'entity_id',
        'activity',
        'ip_address',
        'user_agent',
        'status',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model User.
     * Satu log aktivitas dimiliki oleh satu user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Candidate.
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Test.
     */
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}