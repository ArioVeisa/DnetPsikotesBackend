<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('disc_results')->insert([
            [
                'candidate_test_id' => 1,
                'section_id'        => 1,

                'most_d' => 12,
                'most_i' => 8,
                'most_s' => 15,
                'most_c' => 10,
                'least_d' => 5,
                'least_i' => 6,
                'least_s' => 7,
                'least_c' => 4,
                'diff_d' => 7,
                'diff_i' => 2,
                'diff_s' => 8,
                'diff_c' => 6,

                'std1_d' => 7.5,
                'std1_i' => 6.2,
                'std1_s' => 8.1,
                'std1_c' => 7.0,
                'std2_d' => 8.0,
                'std2_i' => 6.8,
                'std2_s' => 8.3,
                'std2_c' => 7.2,
                'std3_d' => 7.8,
                'std3_i' => 6.5,
                'std3_s' => 8.4,
                'std3_c' => 7.1,

                'dominant_type'   => 'D',
                'dominant_type_2' => 'S',
                'dominant_type_3' => 'C',

                'interpretation'   => 'Dominant (D): orang ini cenderung tegas dan berorientasi pada hasil.',
                'interpretation_2' => 'Steady (S): mampu bekerja dengan stabil dan konsisten.',
                'interpretation_3' => 'Conscientious (C): berhati-hati dan memperhatikan detail.',

                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'candidate_test_id' => 2,
                'section_id'        => 1,

                'most_d' => 6,
                'most_i' => 14,
                'most_s' => 10,
                'most_c' => 8,
                'least_d' => 5,
                'least_i' => 4,
                'least_s' => 7,
                'least_c' => 6,
                'diff_d' => 1,
                'diff_i' => 10,
                'diff_s' => 3,
                'diff_c' => 2,

                'std1_d' => 6.2,
                'std1_i' => 8.0,
                'std1_s' => 7.3,
                'std1_c' => 6.9,
                'std2_d' => 6.4,
                'std2_i' => 8.5,
                'std2_s' => 7.5,
                'std2_c' => 7.0,
                'std3_d' => 6.3,
                'std3_i' => 8.3,
                'std3_s' => 7.4,
                'std3_c' => 6.8,

                'dominant_type'   => 'I',
                'dominant_type_2' => 'S',
                'dominant_type_3' => 'D',

                'interpretation'   => 'Influence (I): orang ini komunikatif dan memotivasi orang lain.',
                'interpretation_2' => 'Steady (S): menunjukkan kesabaran dan keandalan.',
                'interpretation_3' => 'Dominant (D): mampu mengambil keputusan cepat.',

                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'candidate_test_id' => 3,
                'section_id'        => 2,

                'most_d' => 8,
                'most_i' => 6,
                'most_s' => 13,
                'most_c' => 11,
                'least_d' => 6,
                'least_i' => 5,
                'least_s' => 9,
                'least_c' => 7,
                'diff_d' => 2,
                'diff_i' => 1,
                'diff_s' => 4,
                'diff_c' => 4,

                'std1_d' => 7.0,
                'std1_i' => 6.0,
                'std1_s' => 7.5,
                'std1_c' => 7.8,
                'std2_d' => 7.2,
                'std2_i' => 6.3,
                'std2_s' => 7.6,
                'std2_c' => 7.9,
                'std3_d' => 7.1,
                'std3_i' => 6.1,
                'std3_s' => 7.7,
                'std3_c' => 7.8,

                'dominant_type'   => 'S',
                'dominant_type_2' => 'C',
                'dominant_type_3' => 'D',

                'interpretation'   => 'Steady (S): memiliki kestabilan dan kesabaran tinggi.',
                'interpretation_2' => 'Conscientious (C): fokus pada kualitas dan ketelitian.',
                'interpretation_3' => 'Dominant (D): dapat memimpin jika diperlukan.',

                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
