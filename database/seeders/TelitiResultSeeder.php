<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TelitiResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('teliti_results')->insert([
            [
                'candidate_test_id' => 1,
                'score'             => 18,
                'total_questions'   => 20,
                'category'          => 'Excellent',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'candidate_test_id' => 2,
                'score'             => 14,
                'total_questions'   => 20,
                'category'          => 'Good',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'candidate_test_id' => 3,
                'score'             => 9,
                'total_questions'   => 20,
                'category'          => 'Average',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'candidate_test_id' => 4,
                'score'             => 5,
                'total_questions'   => 20,
                'category'          => 'Poor',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
