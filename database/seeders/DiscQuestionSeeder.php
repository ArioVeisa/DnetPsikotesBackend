<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiscQuestion;
use App\Models\DiscCategory;
use Illuminate\Support\Facades\DB;

class DiscQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DiscQuestion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = DiscCategory::all();
        
        $questions = [
            // Dominance (D)
            [
                'question_text' => 'Saya cenderung mengambil keputusan dengan cepat dan tegas.',
                'category_id' => $categories->where('name', 'Dominance (D)')->first()->id,
                'is_active' => true
            ],
            [
                'question_text' => 'Saya suka memimpin dan mengarahkan tim.',
                'category_id' => $categories->where('name', 'Dominance (D)')->first()->id,
                'is_active' => true
            ],
            
            // Influence (I)
            [
                'question_text' => 'Saya mudah bergaul dan berkomunikasi dengan orang lain.',
                'category_id' => $categories->where('name', 'Influence (I)')->first()->id,
                'is_active' => true
            ],
            [
                'question_text' => 'Saya suka bekerja dalam tim dan berkolaborasi.',
                'category_id' => $categories->where('name', 'Influence (I)')->first()->id,
                'is_active' => true
            ],
            
            // Steadiness (S)
            [
                'question_text' => 'Saya lebih suka lingkungan kerja yang stabil dan terstruktur.',
                'category_id' => $categories->where('name', 'Steadiness (S)')->first()->id,
                'is_active' => true
            ],
            [
                'question_text' => 'Saya cenderung menghindari konflik dan mencari harmoni.',
                'category_id' => $categories->where('name', 'Steadiness (S)')->first()->id,
                'is_active' => true
            ],
            
            // Compliance (C)
            [
                'question_text' => 'Saya sangat memperhatikan detail dan akurasi dalam pekerjaan.',
                'category_id' => $categories->where('name', 'Compliance (C)')->first()->id,
                'is_active' => true
            ],
            [
                'question_text' => 'Saya suka menganalisis data sebelum mengambil keputusan.',
                'category_id' => $categories->where('name', 'Compliance (C)')->first()->id,
                'is_active' => true
            ],
            
            // Leadership Style
            [
                'question_text' => 'Dalam memimpin tim, saya lebih suka memberikan arahan yang jelas.',
                'category_id' => $categories->where('name', 'Leadership Style')->first()->id,
                'is_active' => true
            ],
            
            // Communication Style
            [
                'question_text' => 'Saya lebih suka berkomunikasi secara langsung dan to the point.',
                'category_id' => $categories->where('name', 'Communication Style')->first()->id,
                'is_active' => true
            ],
            
            // Work Environment
            [
                'question_text' => 'Saya bekerja paling baik dalam lingkungan yang kompetitif.',
                'category_id' => $categories->where('name', 'Work Environment')->first()->id,
                'is_active' => true
            ],
            
            // Decision Making
            [
                'question_text' => 'Saya cenderung membuat keputusan berdasarkan fakta dan data.',
                'category_id' => $categories->where('name', 'Decision Making')->first()->id,
                'is_active' => true
            ],
        ];

        foreach ($questions as $question) {
            DiscQuestion::create($question);
        }
    }
}
