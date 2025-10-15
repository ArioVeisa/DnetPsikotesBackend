<?php

namespace Database\Seeders;

use App\Models\DiscQuestion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Easy-going, Agreeable", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Results are what matter", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Education, Culture", "dimension_most" => "*", "dimension_least" => "C"],
                    ["option_text" => "Trusting, Believing in other", "dimension_most" => "I", "dimension_least" => "I"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Do it right, Accuracy counts", "dimension_most" => "C", "dimension_least" => "C"],
                    ["option_text" => "Achievements, Awards", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Adventurous, Risk Taker", "dimension_most" => "*", "dimension_least" => "D"],
                    ["option_text" => "Make it enjoyable", "dimension_most" => "*", "dimension_least" => "I"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Safety, Security", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Tolerant, Respectful", "dimension_most" => "C", "dimension_least" => "C"],
                    ["option_text" => "Let's do it together", "dimension_most" => "*", "dimension_least" => "S"],
                    ["option_text" => "Social, Group gatherings", "dimension_most" => "I", "dimension_least" => "*"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Soft spoken, Reserved", "dimension_most" => "C", "dimension_least" => "*"],
                    ["option_text" => "Will do without, Self-controlled", "dimension_most" => "*", "dimension_least" => "C"],
                    ["option_text" => "Take charge, Direct approach", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Optimistic, Visionary", "dimension_most" => "D", "dimension_least" => "D"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Will buy on impulse", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Outgoing, Enthusiastic", "dimension_most" => "*", "dimension_least" => "I"],
                    ["option_text" => "Center of attention, Sociable", "dimension_most" => "*", "dimension_least" => "I"],
                    ["option_text" => "Will wait, No pressure", "dimension_most" => "S", "dimension_least" => "S"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Predictable, Consistent", "dimension_most" => "*", "dimension_least" => "S"],
                    ["option_text" => "Peacemaker, Bring harmony", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Will spend on what I want", "dimension_most" => "I", "dimension_least" => "*"],
                    ["option_text" => "Cautious, Careful", "dimension_most" => "C", "dimension_least" => "*"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Encourage others", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Friendly, Easy to be with", "dimension_most" => "S", "dimension_least" => "*"],
                    ["option_text" => "Not easily defeated", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Strive for perfection", "dimension_most" => "*", "dimension_least" => "C"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Unique, Bored by routine", "dimension_most" => "*", "dimension_least" => "I"],
                    ["option_text" => "Will do as told, Follows leader", "dimension_most" => "S", "dimension_least" => "*"],
                    ["option_text" => "Actively change things", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Excitable, Cheerful", "dimension_most" => "I", "dimension_least" => "I"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Want to establish goals", "dimension_most" => "D", "dimension_least" => "*"],
                    ["option_text" => "Want things exact", "dimension_most" => "C", "dimension_least" => "C"],
                    ["option_text" => "Want things orderly, Neat", "dimension_most" => "*", "dimension_least" => "C"],
                    ["option_text" => "Become frustrated", "dimension_most" => "C", "dimension_least" => "C"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Non-confrontational, Giving in", "dimension_most" => "*", "dimension_least" => "S"],
                    ["option_text" => "I will lead them", "dimension_most" => "D", "dimension_least" => "*"],
                    ["option_text" => "Keep my feeling inside", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Overloaded with details", "dimension_most" => "C", "dimension_least" => "*"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Tell my side of the story", "dimension_most" => "*", "dimension_least" => "I"],
                    ["option_text" => "Changes at the last minute", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "I will persuade them", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Stand up to opposition", "dimension_most" => "D", "dimension_least" => "D"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Demanding, Abrupt", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "I will get the facts", "dimension_most" => "C", "dimension_least" => "*"],
                    ["option_text" => "Lively, Talkative", "dimension_most" => "I", "dimension_least" => "*"],
                    ["option_text" => "Want advancement", "dimension_most" => "D", "dimension_least" => "D"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Thinks of others first", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Fast paced, Determined", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Satisfied with things, Content", "dimension_most" => "S", "dimension_least" => "*"],
                    ["option_text" => "Competitive, Like a challenge", "dimension_most" => "D", "dimension_least" => "D"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Try to maintain balance", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Openly display feelings", "dimension_most" => "I", "dimension_least" => "*"],
                    ["option_text" => "Optimistic, Positive", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Try to follow the rules", "dimension_most" => "*", "dimension_least" => "C"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Humble, Modest", "dimension_most" => "*", "dimension_least" => "C"],
                    ["option_text" => "Logical thinker, Systematic", "dimension_most" => "*", "dimension_least" => "C"],
                    ["option_text" => "Manage time efficiently", "dimension_most" => "C", "dimension_least" => "*"],
                    ["option_text" => "Cool, Reserved", "dimension_most" => "C", "dimension_least" => "C"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Please others, Agreeable", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Often rushed, Feel pressured", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Happy, Carefree", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Laugh out loud, Animated", "dimension_most" => "*", "dimension_least" => "I"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Social things are important", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Pleasing, Kind", "dimension_most" => "S", "dimension_least" => "*"],
                    ["option_text" => "Courageous, Bold", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Like to finish what I start", "dimension_most" => "S", "dimension_least" => "S"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Bold, Daring", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Quiet, Reserved", "dimension_most" => "C", "dimension_least" => "C"],
                    ["option_text" => "Resist sudden change", "dimension_most" => "S", "dimension_least" => "*"],
                    ["option_text" => "Spend quality time with others", "dimension_most" => "S", "dimension_least" => "S"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Want more authority", "dimension_most" => "*", "dimension_least" => "D"],
                    ["option_text" => "Tend to over promise", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Plan for future, Be prepared", "dimension_most" => "C", "dimension_least" => "*"],
                    ["option_text" => "Want new opportunities", "dimension_most" => "I", "dimension_least" => "*"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Withdraw under pressure", "dimension_most" => "*", "dimension_least" => "C"],
                    ["option_text" => "Travel to new adventures", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Avoid any conflict", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Not afraid to fight", "dimension_most" => "*", "dimension_least" => "D"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Receive rewards for goals met", "dimension_most" => "D", "dimension_least" => "D"],
                    ["option_text" => "Want clear directions", "dimension_most" => "*", "dimension_least" => "C"],
                    ["option_text" => "A good encourager", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Rules need to be challenged", "dimension_most" => "*", "dimension_least" => "D"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "Reliable, Dependable", "dimension_most" => "*", "dimension_least" => "S"],
                    ["option_text" => "A good listener", "dimension_most" => "S", "dimension_least" => "S"],
                    ["option_text" => "Rules make it fair", "dimension_most" => "C", "dimension_least" => "*"],
                    ["option_text" => "Creative, Unique", "dimension_most" => "I", "dimension_least" => "I"]
                ]
            ],
            [
                "question_text" => "Dari pernyataan berikut, mana yang PALING menggambarkan Anda dan mana yang PALING TIDAK menggambarkan Anda?",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    ["option_text" => "A good analyzer", "dimension_most" => "C", "dimension_least" => "C"],
                    ["option_text" => "Rules make it boring", "dimension_most" => "I", "dimension_least" => "I"],
                    ["option_text" => "Bottom line, Results oriented", "dimension_most" => "D", "dimension_least" => "*"],
                    ["option_text" => "A good delegator", "dimension_most" => "D", "dimension_least" => "D"]
                ]
            ],
            [
                "question_text" => "Pilih pernyataan yang paling mencerminkan diri Anda (MOST) dan yang paling tidak mencerminkan diri Anda (LEAST).",
                "category_id" => 1,
                "is_active" => true,
                "options" => [
                    [
                        "option_text" => "Rules need to be challenged",
                        "dimension_most" => "D",
                        "dimension_least" => "*"
                    ],
                    [
                        "option_text" => "Rules make it fair",
                        "dimension_most" => "C",
                        "dimension_least" => "*"
                    ],
                    [
                        "option_text" => "Rules make it boring",
                        "dimension_most" => "I",
                        "dimension_least" => "I"
                    ],
                    [
                        "option_text" => "Rules make it safe",
                        "dimension_most" => "S",
                        "dimension_least" => "S"
                    ]
                ]
            ]
        ];

        foreach ($questions as $q) {
            $question = DiscQuestion::create([
                'question_text' => $q['question_text'],
                'category_id' => $q['category_id'],
                'is_active' => $q['is_active'],
            ]);

            foreach ($q['options'] as $opt) {
                $question->options()->create([
                    'option_text' => $opt['option_text'],
                    'dimension_most' => $opt['dimension_most'],
                    'dimension_least' => $opt['dimension_least'],
                ]);
            }
        }
    }
}
