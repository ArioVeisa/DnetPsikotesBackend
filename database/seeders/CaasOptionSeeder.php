<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaasOption;
use App\Models\CaasQuestion;
use Illuminate\Support\Facades\DB;

class CaasOptionSeeder extends Seeder
{
    public function run(): void
    {
        CaasOption::truncate();

        $questions = CaasQuestion::all();

        // Use a consistent Likert-style scoring for all CAAS questions
        $likertOptions = [
            ['option_text' => 'Paling kuat', 'score' => 5],
            ['option_text' => 'Sangat kuat', 'score' => 4],
            ['option_text' => 'Kuat', 'score' => 3],
            ['option_text' => 'Cukup kuat', 'score' => 2],
            ['option_text' => 'Tidak kuat', 'score' => 1],
        ];

        foreach ($questions as $question) {
            foreach ($likertOptions as $option) {
                CaasOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'score' => $option['score']
                ]);
            }
        }
    }
}
