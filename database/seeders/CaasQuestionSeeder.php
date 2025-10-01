<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaasQuestion;

class CaasQuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            ['category_id' => 1, 'question_text' => 'Berpikir seperti apa masa depan saya'],
            ['category_id' => 1, 'question_text' => 'Menyadari bahwa pilihan hari ini menentukan masa depan saya'],
            ['category_id' => 1, 'question_text' => 'Mempersiapkan masa depan'],
            ['category_id' => 1, 'question_text' => 'Menyadari akan pilihan-pilihan pendidikan dan pilihan karir yang harus saya buat'],
            ['category_id' => 1, 'question_text' => 'Merencanakan bagaimana cara mencapai tujuan saya'],
            ['category_id' => 1, 'question_text' => 'Memikirkan mengenai karir saya'],
            ['category_id' => 1, 'question_text' => 'Menjaga agar tetap optimis'],
            ['category_id' => 1, 'question_text' => 'Membuat keputusan sendiri'],
            ['category_id' => 1, 'question_text' => 'Bertanggung jawab atas tindakan saya'],
            ['category_id' => 1, 'question_text' => 'Tetap teguh dengan keyakinan saya'],
            ['category_id' => 1, 'question_text' => 'Mengandalkan kemampuan diri sendiri'],
            ['category_id' => 1, 'question_text' => 'Melakukan apa yang benar menurut saya'],
            ['category_id' => 1, 'question_text' => 'Mengeksplorasi lingkungan sekitar'],
            ['category_id' => 1, 'question_text' => 'Mencari peluang-peluang untuk berkembang'],
            ['category_id' => 1, 'question_text' => 'Mencari tahu alternatif sebelum menentukan pilihan'],
            ['category_id' => 1, 'question_text' => 'Mengamati cara-cara yang berbeda dalam melakukan sesuatu'],
            ['category_id' => 1, 'question_text' => 'Menyelidiki secara lebih dalam pertanyaan-pertanyaan yang saya miliki'],
            ['category_id' => 1, 'question_text' => 'Menjadi ingin tahu tentang peluang-peluang baru'],
            ['category_id' => 1, 'question_text' => 'Mengerjakan tugas secara efisien'],
            ['category_id' => 1, 'question_text' => 'Menjaga dalam melakukan sesuatu dengan baik'],
            ['category_id' => 1, 'question_text' => 'Mempelajari keterampilan-keterampilan baru'],
            ['category_id' => 1, 'question_text' => 'Bekerja dengan kemampuan saya'],
            ['category_id' => 1, 'question_text' => 'Mengatasi hambatan-hambatan'],
            ['category_id' => 1, 'question_text' => 'Menyelesaikan masalah-masalah'],
        ];

        $defaultOptions = [
            ['option_text' => 'Paling kuat', 'score' => 5],
            ['option_text' => 'Sangat kuat', 'score' => 4],
            ['option_text' => 'Kuat', 'score' => 3],
            ['option_text' => 'Cukup kuat', 'score' => 2],
            ['option_text' => 'Tidak kuat', 'score' => 1],
        ];

        foreach ($questions as $q) {
            $question = CaasQuestion::create([
                'question_text' => $q['question_text'],
                'category_id'=> $q['category_id'],
                'is_active'=> true,
            ]);

            foreach ($defaultOptions as $opt) {
                $question->options()->create($opt);
            }
        }
    }
}
