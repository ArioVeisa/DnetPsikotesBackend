<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TelitiOption;
use App\Models\TelitiQuestion;
use Illuminate\Support\Facades\DB;

class TelitiOptionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TelitiOption::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $questions = TelitiQuestion::all();

        foreach ($questions as $question) {
            $options = [];

            switch ($question->question_text) {
                case 'Pilih kata yang paling tepat untuk melengkapi kalimat: "Dia adalah seorang yang sangat _____ dalam pekerjaannya."':
                    $options = [
                        ['option_text' => 'teliti'],
                        ['option_text' => 'Malas'],
                        ['option_text' => 'Ceroboh'],
                        ['option_text' => 'Lambat'],
                    ];
                    break;

                case 'Manakah yang merupakan sinonim dari kata "Berdasarkan"?':
                    $options = [
                        ['option_text' => 'Menurut'],
                        ['option_text' => 'Melawan'],
                        ['option_text' => 'Menentang'],
                        ['option_text' => 'Menyangkal'],
                    ];
                    break;

                case 'Berapakah hasil dari 15% dari 200?':
                    $options = [
                        ['option_text' => '30'],
                        ['option_text' => '25'],
                        ['option_text' => '35'],
                        ['option_text' => '40'],
                    ];
                    break;

                case 'Jika harga barang naik 20% dari Rp 50.000, berapakah harga baru?':
                    $options = [
                        ['option_text' => 'Rp 60.000'],
                        ['option_text' => 'Rp 55.000'],
                        ['option_text' => 'Rp 65.000'],
                        ['option_text' => 'Rp 70.000'],
                    ];
                    break;

                case 'Jika semua A adalah B, dan semua B adalah C, maka:':
                    $options = [
                        ['option_text' => 'Semua A adalah C'],
                        ['option_text' => 'Semua C adalah A'],
                        ['option_text' => 'Tidak ada hubungan'],
                        ['option_text' => 'Beberapa A adalah C'],
                    ];
                    break;

                case 'Dalam suatu deret: 1, 4, 9, 16, 25, ... berapakah angka berikutnya?':
                    $options = [
                        ['option_text' => '36'],
                        ['option_text' => '30'],
                        ['option_text' => '32'],
                        ['option_text' => '40'],
                    ];
                    break;

                case 'Jika sebuah persegi diputar 180 derajat, bagaimana posisinya?':
                    $options = [
                        ['option_text' => 'Terbalik'],
                        ['option_text' => 'Tetap sama'],
                        ['option_text' => 'Menjadi segitiga'],
                        ['option_text' => 'Hilang'],
                    ];
                    break;

                case 'Hafalkan urutan warna berikut: Merah, Biru, Kuning, Hijau, Ungu. Warna apa yang berada di posisi ketiga?':
                    $options = [
                        ['option_text' => 'Kuning'],
                        ['option_text' => 'Biru'],
                        ['option_text' => 'Hijau'],
                        ['option_text' => 'Merah'],
                    ];
                    break;

                case 'Dalam teks berikut, hitung berapa kali huruf "A" muncul: "Anak-anak bermain di taman yang asri."':
                    $options = [
                        ['option_text' => '8 kali'],
                        ['option_text' => '6 kali'],
                        ['option_text' => '7 kali'],
                        ['option_text' => '9 kali'],
                    ];
                    break;

                case 'Jika 3x + 5 = 20, berapakah nilai x?':
                    $options = [
                        ['option_text' => '5'],
                        ['option_text' => '4'],
                        ['option_text' => '6'],
                        ['option_text' => '3'],
                    ];
                    break;

                case 'Sebuah mobil menempuh jarak 240 km dalam waktu 4 jam. Berapakah kecepatan rata-rata mobil tersebut?':
                    $options = [
                        ['option_text' => '60 km/jam'],
                        ['option_text' => '50 km/jam'],
                        ['option_text' => '70 km/jam'],
                        ['option_text' => '80 km/jam'],
                    ];
                    break;

                default:
                    // Default options for any other questions
                    $options = [
                        ['option_text' => 'Opsi A'],
                        ['option_text' => 'Opsi B'],
                        ['option_text' => 'Opsi C'],
                        ['option_text' => 'Opsi D'],
                    ];
                    break;
            }

            foreach ($options as $index => $option) {
                $optionRecord = TelitiOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text']
                ]);

                // Set the first option as correct for most questions
                if ($index === 0) {
                    $question->update(['correct_option_id' => $optionRecord->id]);
                }
            }
        }
    }
}
