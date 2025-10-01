<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestQuestion;
use App\Models\Test;
use App\Models\TestSection;
use App\Models\CaasQuestion;
use App\Models\DiscQuestion;
use App\Models\telitiQuestion;
use Illuminate\Support\Facades\DB;

class TestQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TestQuestion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tests = Test::all();
        
        foreach ($tests as $test) {
            $sections = $test->sections;
            
            foreach ($sections as $section) {
                $questions = [];
                
                switch ($section->section_type) {
                    case 'DISC':
                        // Get random DISC questions
                        $discQuestions = DiscQuestion::inRandomOrder()
                            ->limit($section->question_count)
                            ->get();
                        
                        foreach ($discQuestions as $question) {
                            $questions[] = [
                                'test_id' => $test->id,
                                'section_id' => $section->id,
                                'question_id' => $question->id,
                                'question_type' => 'disc'
                            ];
                        }
                        break;
                        
                    case 'CAAS':
                        // Get random CAAS questions
                        $caasQuestions = CaasQuestion::inRandomOrder()
                            ->limit($section->question_count)
                            ->get();
                        
                        foreach ($caasQuestions as $question) {
                            $questions[] = [
                                'test_id' => $test->id,
                                'section_id' => $section->id,
                                'question_id' => $question->id,
                                'question_type' => 'caas'
                            ];
                        }
                        break;
                        
                    case 'teliti':
                        // Get random teliti questions
                        $telitiQuestions = telitiQuestion::inRandomOrder()
                            ->limit($section->question_count)
                            ->get();
                        
                        foreach ($telitiQuestions as $question) {
                            $questions[] = [
                                'test_id' => $test->id,
                                'section_id' => $section->id,
                                'question_id' => $question->id,
                                'question_type' => 'teliti'
                            ];
                        }
                        break;
                }
                
                // Insert questions for this section
                foreach ($questions as $question) {
                    TestQuestion::create($question);
                }
            }
        }
    }
}
