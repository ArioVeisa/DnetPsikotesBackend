<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaasResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('caas_results')->insert([
            [
                'candidate_test_id' => 1,
                'section_id'        => 1,
                'concern'           => 12,
                'control'           => 18,
                'curiosity'         => 15,
                'confidence'        => 20,
                'total'             => 65,
                'category'          => 'High',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'candidate_test_id' => 2,
                'section_id'        => 1,
                'concern'           => 8,
                'control'           => 9,
                'curiosity'         => 10,
                'confidence'        => 7,
                'total'             => 34,
                'category'          => 'Medium',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'candidate_test_id' => 3,
                'section_id'        => 2,
                'concern'           => 5,
                'control'           => 6,
                'curiosity'         => 4,
                'confidence'        => 5,
                'total'             => 20,
                'category'          => 'Low',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
