<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_test_id',
        'section_id',

        'most_d',
        'most_i',
        'most_s',
        'most_c',

        'least_d',
        'least_i',
        'least_s',
        'least_c',

        'diff_d',
        'diff_i',
        'diff_s',
        'diff_c',



        'std1_d',
        'std1_i',
        'std1_s',
        'std1_c',
        'std2_d',
        'std2_i',
        'std2_s',
        'std2_c',
        'std3_d',
        'std3_i',
        'std3_s',
        'std3_c',

        'dominant_type',
        'dominant_type_2',
        'dominant_type_3',

        'interpretation',
        'interpretation_2',
        'interpretation_3',
    ];

    public function candidateTest()
    {
        return $this->belongsTo(CandidateTest::class);
    }

    public function section()
    {
        return $this->belongsTo(TestSection::class);
    }
}
