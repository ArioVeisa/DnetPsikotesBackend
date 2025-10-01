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
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DiscOption::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $questions = DiscQuestion::all();
        
        foreach ($questions as $question) {
            $options = [];
            
            switch ($question->question_text) {
                case 'Saya cenderung mengambil keputusan dengan cepat dan tegas.':
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'C', 'dimension_most' => 'D'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'C', 'dimension_most' => 'D'],
                        ['option_text' => 'Netral', 'dimension_least' => 'S', 'dimension_most' => 'I'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'D', 'dimension_most' => 'C'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'D', 'dimension_most' => 'C'],
                    ];
                    break;
                    
                case 'Saya suka memimpin dan mengarahkan tim.':
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'S', 'dimension_most' => 'D'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'S', 'dimension_most' => 'D'],
                        ['option_text' => 'Netral', 'dimension_least' => 'C', 'dimension_most' => 'I'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'D', 'dimension_most' => 'S'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'D', 'dimension_most' => 'S'],
                    ];
                    break;
                    
                case 'Saya mudah bergaul dan berkomunikasi dengan orang lain.':
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'C', 'dimension_most' => 'I'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'C', 'dimension_most' => 'I'],
                        ['option_text' => 'Netral', 'dimension_least' => 'D', 'dimension_most' => 'S'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'I', 'dimension_most' => 'C'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'I', 'dimension_most' => 'C'],
                    ];
                    break;
                    
                case 'Saya suka bekerja dalam tim dan berkolaborasi.':
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'D', 'dimension_most' => 'I'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'D', 'dimension_most' => 'I'],
                        ['option_text' => 'Netral', 'dimension_least' => 'C', 'dimension_most' => 'S'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'I', 'dimension_most' => 'D'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'I', 'dimension_most' => 'D'],
                    ];
                    break;
                    
                case 'Saya lebih suka lingkungan kerja yang stabil dan terstruktur.':
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'D', 'dimension_most' => 'S'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'D', 'dimension_most' => 'S'],
                        ['option_text' => 'Netral', 'dimension_least' => 'I', 'dimension_most' => 'C'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'S', 'dimension_most' => 'D'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'S', 'dimension_most' => 'D'],
                    ];
                    break;
                    
                case 'Saya cenderung menghindari konflik dan mencari harmoni.':
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'D', 'dimension_most' => 'S'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'D', 'dimension_most' => 'S'],
                        ['option_text' => 'Netral', 'dimension_least' => 'I', 'dimension_most' => 'C'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'S', 'dimension_most' => 'D'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'S', 'dimension_most' => 'D'],
                    ];
                    break;
                    
                case 'Saya sangat memperhatikan detail dan akurasi dalam pekerjaan.':
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'I', 'dimension_most' => 'C'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'I', 'dimension_most' => 'C'],
                        ['option_text' => 'Netral', 'dimension_least' => 'D', 'dimension_most' => 'S'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'C', 'dimension_most' => 'I'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'C', 'dimension_most' => 'I'],
                    ];
                    break;
                    
                case 'Saya suka menganalisis data sebelum mengambil keputusan.':
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'D', 'dimension_most' => 'C'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'D', 'dimension_most' => 'C'],
                        ['option_text' => 'Netral', 'dimension_least' => 'I', 'dimension_most' => 'S'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'C', 'dimension_most' => 'D'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'C', 'dimension_most' => 'D'],
                    ];
                    break;
                    
                default:
                    // Default options for any other questions
                    $options = [
                        ['option_text' => 'Sangat Setuju', 'dimension_least' => 'C', 'dimension_most' => 'D'],
                        ['option_text' => 'Setuju', 'dimension_least' => 'C', 'dimension_most' => 'D'],
                        ['option_text' => 'Netral', 'dimension_least' => 'D', 'dimension_most' => 'I'],
                        ['option_text' => 'Tidak Setuju', 'dimension_least' => 'D', 'dimension_most' => 'C'],
                        ['option_text' => 'Sangat Tidak Setuju', 'dimension_least' => 'D', 'dimension_most' => 'C'],
                    ];
                    break;
            }
            
            foreach ($options as $option) {
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
