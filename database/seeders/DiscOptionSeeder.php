<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiscOption;
use App\Models\DiscQuestion;
use Illuminate\Support\Facades\DB;

class DiscOptionSeeder extends Seeder
{
    public function run(): void
    {
        DiscOption::truncate();

        $questions = DiscQuestion::all();

        // Provide a consistent 5-option set per question with mapped dimensions
        $genericOptions = [
            ['option_text' => 'Sangat Setuju', 'dimension_most' => 'D', 'dimension_least' => 'C'],
            ['option_text' => 'Setuju', 'dimension_most' => 'I', 'dimension_least' => 'C'],
            ['option_text' => 'Netral', 'dimension_most' => 'S', 'dimension_least' => 'D'],
            ['option_text' => 'Tidak Setuju', 'dimension_most' => 'C', 'dimension_least' => 'I'],
            ['option_text' => 'Sangat Tidak Setuju', 'dimension_most' => 'C', 'dimension_least' => 'D'],
        ];

        foreach ($questions as $question) {
            foreach ($genericOptions as $option) {
                DiscOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'dimension_least' => $option['dimension_least'],
                    'dimension_most' => $option['dimension_most']
                ]);
            }
        }
    }
}
