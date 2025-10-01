<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\telitiQuestion;
use App\Models\telitiCategory;
use Illuminate\Support\Facades\DB;

class telitiQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        telitiQuestion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = telitiCategory::all();
        
        $questions = [
            // Kemampuan Verbal
            [
                'question_text' => 'Pilih kata yang paling tepat untuk melengkapi kalimat: "Dia adalah seorang yang sangat _____ dalam pekerjaannya."',
                'category_id' => $categories->where('name', 'Kemampuan Verbal')->first()->id,
                'is_active' => true
            ],
            [
                'question_text' => 'Manakah yang merupakan sinonim dari kata "Berdasarkan"?',
                'category_id' => $categories->where('name', 'Kemampuan Verbal')->first()->id,
                'is_active' => true
            ],
            
            // Kemampuan Numerik
            [
                'question_text' => 'Berapakah hasil dari 15% dari 200?',
                'category_id' => $categories->where('name', 'Kemampuan Numerik')->first()->id,
                'is_active' => true
            ],
            [
                'question_text' => 'Jika harga barang naik 20% dari Rp 50.000, berapakah harga baru?',
                'category_id' => $categories->where('name', 'Kemampuan Numerik')->first()->id,
                'is_active' => true
            ],
            
            // Kemampuan Logika
            [
                'question_text' => 'Jika semua A adalah B, dan semua B adalah C, maka:',
                'category_id' => $categories->where('name', 'Kemampuan Logika')->first()->id,
                'is_active' => true
            ],
            [
                'question_text' => 'Dalam suatu deret: 1, 4, 9, 16, 25, ... berapakah angka berikutnya?',
                'category_id' => $categories->where('name', 'Kemampuan Logika')->first()->id,
                'is_active' => true
            ],
            
            // Kemampuan Spasial
            [
                'question_text' => 'Jika sebuah persegi diputar 180 derajat, bagaimana posisinya?',
                'category_id' => $categories->where('name', 'Kemampuan Spasial')->first()->id,
                'is_active' => true
            ],
            
            // Kemampuan Memori
            [
                'question_text' => 'Hafalkan urutan warna berikut: Merah, Biru, Kuning, Hijau, Ungu. Warna apa yang berada di posisi ketiga?',
                'category_id' => $categories->where('name', 'Kemampuan Memori')->first()->id,
                'is_active' => true
            ],
            
            // Kemampuan Konsentrasi
            [
                'question_text' => 'Dalam teks berikut, hitung berapa kali huruf "A" muncul: "Anak-anak bermain di taman yang asri."',
                'category_id' => $categories->where('name', 'Kemampuan Konsentrasi')->first()->id,
                'is_active' => true
            ],
            
            // Kemampuan Analitis
            [
                'question_text' => 'Jika 3x + 5 = 20, berapakah nilai x?',
                'category_id' => $categories->where('name', 'Kemampuan Analitis')->first()->id,
                'is_active' => true
            ],
            
            // Kemampuan Pemecahan Masalah
            [
                'question_text' => 'Sebuah mobil menempuh jarak 240 km dalam waktu 4 jam. Berapakah kecepatan rata-rata mobil tersebut?',
                'category_id' => $categories->where('name', 'Kemampuan Pemecahan Masalah')->first()->id,
                'is_active' => true
            ],
        ];

        foreach ($questions as $question) {
            telitiQuestion::create($question);
        }
    }
}
