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
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CaasOption::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $questions = CaasQuestion::all();
        
        foreach ($questions as $question) {
            $options = [];
            
            switch ($question->question_text) {
                case 'Jika A = 5, B = 3, dan C = A + B, maka berapakah nilai C?':
                    $options = [
                        ['option_text' => '8', 'score' => 10],
                        ['option_text' => '15', 'score' => 0],
                        ['option_text' => '2', 'score' => 0],
                        ['option_text' => '53', 'score' => 0],
                    ];
                    break;
                    
                case 'Dalam suatu deret: 2, 4, 8, 16, ... berapakah angka berikutnya?':
                    $options = [
                        ['option_text' => '32', 'score' => 10],
                        ['option_text' => '24', 'score' => 0],
                        ['option_text' => '20', 'score' => 0],
                        ['option_text' => '18', 'score' => 0],
                    ];
                    break;
                    
                case 'Sinonim dari kata "Cerdas" adalah:':
                    $options = [
                        ['option_text' => 'Pintar', 'score' => 10],
                        ['option_text' => 'Bodoh', 'score' => 0],
                        ['option_text' => 'Lambat', 'score' => 0],
                        ['option_text' => 'Malas', 'score' => 0],
                    ];
                    break;
                    
                case 'Antonim dari kata "Mudah" adalah:':
                    $options = [
                        ['option_text' => 'Sulit', 'score' => 10],
                        ['option_text' => 'Gampang', 'score' => 0],
                        ['option_text' => 'Simpel', 'score' => 0],
                        ['option_text' => 'Ringan', 'score' => 0],
                    ];
                    break;
                    
                case 'Berapakah hasil dari 25 × 4?':
                    $options = [
                        ['option_text' => '100', 'score' => 10],
                        ['option_text' => '80', 'score' => 0],
                        ['option_text' => '120', 'score' => 0],
                        ['option_text' => '90', 'score' => 0],
                    ];
                    break;
                    
                case 'Jika 1/4 dari suatu angka adalah 10, berapakah angka tersebut?':
                    $options = [
                        ['option_text' => '40', 'score' => 10],
                        ['option_text' => '25', 'score' => 0],
                        ['option_text' => '14', 'score' => 0],
                        ['option_text' => '2.5', 'score' => 0],
                    ];
                    break;
                    
                case 'Jika sebuah kubus diputar 90 derajat ke kanan, bagaimana posisinya?':
                    $options = [
                        ['option_text' => 'Berubah orientasi', 'score' => 10],
                        ['option_text' => 'Tetap sama', 'score' => 0],
                        ['option_text' => 'Menjadi balok', 'score' => 0],
                        ['option_text' => 'Hilang', 'score' => 0],
                    ];
                    break;
                    
                case 'Semua burung bisa terbang. Penguin adalah burung. Kesimpulannya:':
                    $options = [
                        ['option_text' => 'Penguin bisa terbang (logika salah)', 'score' => 10],
                        ['option_text' => 'Penguin tidak bisa terbang', 'score' => 0],
                        ['option_text' => 'Tidak ada kesimpulan', 'score' => 0],
                        ['option_text' => 'Semua burung adalah penguin', 'score' => 0],
                    ];
                    break;
                    
                case 'Hafalkan urutan angka berikut: 7, 2, 9, 1, 5. Berapakah angka ketiga?':
                    $options = [
                        ['option_text' => '9', 'score' => 10],
                        ['option_text' => '2', 'score' => 0],
                        ['option_text' => '1', 'score' => 0],
                        ['option_text' => '5', 'score' => 0],
                    ];
                    break;
                    
                case 'Dalam teks berikut, hitung berapa kali kata "dan" muncul: "Saya dan teman saya pergi ke pasar dan membeli sayuran dan buah-buahan."':
                    $options = [
                        ['option_text' => '3 kali', 'score' => 10],
                        ['option_text' => '2 kali', 'score' => 0],
                        ['option_text' => '4 kali', 'score' => 0],
                        ['option_text' => '1 kali', 'score' => 0],
                    ];
                    break;
                    
                case 'Jika Anda memiliki 12 apel dan ingin membaginya sama rata kepada 4 orang, berapa apel yang didapat setiap orang?':
                    $options = [
                        ['option_text' => '3 apel', 'score' => 10],
                        ['option_text' => '4 apel', 'score' => 0],
                        ['option_text' => '2 apel', 'score' => 0],
                        ['option_text' => '6 apel', 'score' => 0],
                    ];
                    break;
                    
                default:
                    // Default options for any other questions
                    $options = [
                        ['option_text' => 'Opsi A', 'score' => 10],
                        ['option_text' => 'Opsi B', 'score' => 0],
                        ['option_text' => 'Opsi C', 'score' => 0],
                        ['option_text' => 'Opsi D', 'score' => 0],
                    ];
                    break;
            }
            
            foreach ($options as $option) {
                CaasOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'score' => $option['score']
                ]);
            }
        }
    }
}
