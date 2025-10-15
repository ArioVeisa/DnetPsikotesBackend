<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestSection;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class TestSectionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        // Clear existing records
        TestSection::truncate();
        // Foreign key checks enabled

        $tests = Test::all();

        foreach ($tests as $test) {
            $sections = [];

            // Different sections based on position level
            if (in_array($test->target_position, ['Manager', 'Supervisor'])) {
                // Leadership positions get all three test types
                $sections = [
                    [
                        'test_id' => $test->id,
                        'section_type' => 'DISC',
                        'duration_minutes' => 30,
                        'question_count' => 20,
                        'sequence' => 1
                    ],
                    [
                        'test_id' => $test->id,
                        'section_type' => 'CAAS',
                        'duration_minutes' => 45,
                        'question_count' => 25,
                        'sequence' => 2
                    ],
                    [
                        'test_id' => $test->id,
                        'section_type' => 'teliti',
                        'duration_minutes' => 30,
                        'question_count' => 20,
                        'sequence' => 3
                    ]
                ];
            } else {
                // Staff positions get CAAS and teliti only
                $sections = [
                    [
                        'test_id' => $test->id,
                        'section_type' => 'CAAS',
                        'duration_minutes' => 40,
                        'question_count' => 20,
                        'sequence' => 1
                    ],
                    [
                        'test_id' => $test->id,
                        'section_type' => 'teliti',
                        'duration_minutes' => 25,
                        'question_count' => 15,
                        'sequence' => 2
                    ]
                ];
            }

            foreach ($sections as $section) {
                TestSection::create($section);
            }
        }
    }
}
