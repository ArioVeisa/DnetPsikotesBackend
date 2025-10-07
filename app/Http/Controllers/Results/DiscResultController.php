<?php

namespace App\Http\Controllers\Results;

use App\Http\Controllers\Controller;
use App\Models\CandidateAnswer;
use App\Models\DiscResult;
use App\Services\LogActivityService;
use Illuminate\Http\Request;

class DiscResultController extends Controller
{
    public function index()
    {
        $result = DiscResult::with(['candidateTest', 'section'])->get();
        return response()->json([
            'data' => $result,
            'status' => 'success',
            'message' => 'Results retrieved successfully'
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $result = DiscResult::with(['candidateTest', 'section'])->where('candidate_test_id', $id)->first();
        if (!$result) {
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Result not found'
            ], 404);
        }

        // Log activity: HRD viewing test result details
        LogActivityService::addToLog("Viewed DISC test result details for candidate test ID: {$id}", $request);

        return response()->json([
            'data' => $result,
            'status' => 'success',
            'message' => 'Result retrieved successfully'
        ], 200);
    }

    public function calculateByIds($candidateTestId, $sectionId, $answers = null)
    {
        if ($answers === null) {
            $answers = CandidateAnswer::where('candidate_test_id', $candidateTestId)
                ->where('section_id', $sectionId)
                ->with(['mostOption', 'leastOption'])
                ->get()
                ->map(function ($a) {
                    return [
                        'most'  => $a->mostOption->dimension_most ?? null,
                        'least' => $a->leastOption->dimension_least ?? null,
                    ];
                });
        }

        // --- Graph1 dan Graph2 (Raw Count) ---
        $most  = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
        $least = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];

        foreach ($answers as $ans) {
            if (!empty($ans['most']) && isset($most[$ans['most']])) {
                $most[$ans['most']]++;
            }
            if (!empty($ans['least']) && isset($least[$ans['least']])) {
                $least[$ans['least']]++;
            }
        }

        // --- Graph3 (Difference) ---
        $diff = [];
        foreach ($most as $dim => $val) {
            $diff[$dim] = $val - $least[$dim];
        }

        // --- Konversi ke Standar (Norma) ---
        $graph1_std = $this->convertToStandard($most, 1);
        $graph2_std = $this->convertToStandard($least, 2);
        $graph3_std = $this->convertToStandard($diff, 3);

        // --- Ambil Dominant Type untuk masing-masing grafik ---
        $dominant_type_1 = $this->getDominantType($graph1_std);
        $dominant_type_2 = $this->getDominantType($graph2_std);
        $dominant_type_3 = $this->getDominantType($graph3_std);

        // --- Interpretasi masing-masing grafik ---
        $interpretation_1 = $this->getInterpretation($dominant_type_1);
        $interpretation_2 = $this->getInterpretation($dominant_type_2);
        $interpretation_3 = $this->getInterpretation($dominant_type_3);

        // --- Simpan ke Database ---
        DiscResult::updateOrCreate(
            ['candidate_test_id' => $candidateTestId, 'section_id' => $sectionId],
            [
                // Graph Raw
                'most_d' => $most['D'],
                'most_i' => $most['I'],
                'most_s' => $most['S'],
                'most_c' => $most['C'],

                'least_d' => $least['D'],
                'least_i' => $least['I'],
                'least_s' => $least['S'],
                'least_c' => $least['C'],

                'diff_d' => $diff['D'],
                'diff_i' => $diff['I'],
                'diff_s' => $diff['S'],
                'diff_c' => $diff['C'],

                // Graph Standard
                'std1_d' => $graph1_std['D'],
                'std1_i' => $graph1_std['I'],
                'std1_s' => $graph1_std['S'],
                'std1_c' => $graph1_std['C'],
                'std2_d' => $graph2_std['D'],
                'std2_i' => $graph2_std['I'],
                'std2_s' => $graph2_std['S'],
                'std2_c' => $graph2_std['C'],
                'std3_d' => $graph3_std['D'],
                'std3_i' => $graph3_std['I'],
                'std3_s' => $graph3_std['S'],
                'std3_c' => $graph3_std['C'],

                // Interpretasi & Dominan
                'dominant_type'   => $dominant_type_1,
                'interpretation'  => $interpretation_1,
                'dominant_type_2' => $dominant_type_2,
                'interpretation_2' => $interpretation_2,
                'dominant_type_3' => $dominant_type_3,
                'interpretation_3' => $interpretation_3,
            ]
        );

        // --- Kembalikan hasil ke response ---
        return [
            'graph1_raw' => $most,
            'graph2_raw' => $least,
            'graph3_raw' => $diff,
            'graph1_std' => $graph1_std,
            'graph2_std' => $graph2_std,
            'graph3_std' => $graph3_std,
            'dominant_type_1' => $dominant_type_1,
            'interpretation_1' => $interpretation_1,
            'dominant_type_2' => $dominant_type_2,
            'interpretation_2' => $interpretation_2,
            'dominant_type_3' => $dominant_type_3,
            'interpretation_3' => $interpretation_3,
        ];
    }


    private function getDominantType($graph_std)
    {
        $priorityOrder = ['D', 'I', 'S', 'C'];

        // Hanya nilai positif
        $positiveScores = array_filter($graph_std, fn($v) => $v >= 0);

        if (empty($positiveScores)) {
            $positiveScores = $graph_std;
        }

        // Urutkan dengan prioritas
        uksort($positiveScores, function ($a, $b) use ($positiveScores, $priorityOrder) {
            if ($positiveScores[$a] === $positiveScores[$b]) {
                return array_search($a, $priorityOrder) <=> array_search($b, $priorityOrder);
            }
            return $positiveScores[$b] <=> $positiveScores[$a];
        });

        $topKeys = array_slice(array_keys($positiveScores), 0, 3);

        return implode('-', $topKeys);
    }

    // Konversi nilai raw ke standar (VLOOKUP Excel)
    private function convertToStandard($rawScores, $graphType)
    {
        $norma = $this->getDiscNorma($graphType);
        $stdScores = [];

        foreach ($rawScores as $dim => $val) {
            if (isset($norma[$val])) {
                $stdScores[$dim] = $norma[$val][$dim];
            } else {
                $keys = array_keys($norma);
                $closest = $this->findClosest($val, $keys);
                $stdScores[$dim] = $norma[$closest][$dim];
            }
        }
        return $stdScores;
    }

    //  Cari nilai terdekat kalau tidak ada di norma
    private function findClosest($search, $arr)
    {
        return array_reduce($arr, function ($carry, $item) use ($search) {
            return (abs($item - $search) < abs($carry - $search)) ? $item : $carry;
        }, $arr[0]);
    }


    // Tabel Norma DISC (dari Excel Nilai Grafik)
    private function getDiscNorma($graphType)
    {
        if ($graphType === 3) {
            return [
                -22 => ['D' => -8, 'I' => -8, 'S' => -8, 'C' => -7.5],
                -21 => ['D' => -7.5, 'I' => -8, 'S' => -8, 'C' => -7.3],
                -20 => ['D' => -7, 'I' => -8, 'S' => -8, 'C' => -7.3],
                -19 => ['D' => -6.8, 'I' => -8, 'S' => -8, 'C' => -7],
                -18 => ['D' => -6.75, 'I' => -7, 'S' => -7.5, 'C' => -6.7],
                -17 => ['D' => -6.7, 'I' => -6.7, 'S' => -7.3, 'C' => -6.7],
                -16 => ['D' => -6.5, 'I' => -6.7, 'S' => -7.3, 'C' => -6.7],
                -15 => ['D' => -6.3, 'I' => -6.7, 'S' => -7, 'C' => -6.5],
                -14 => ['D' => -6.1, 'I' => -6.7, 'S' => -6.5, 'C' => -6.3],
                -13 => ['D' => -5.9, 'I' => -6.7, 'S' => -6.5, 'C' => -6],
                -12 => ['D' => -5.7, 'I' => -6.7, 'S' => -6.5, 'C' => -5.85],
                -11 => ['D' => -5.3, 'I' => -6.7, 'S' => -6.5, 'C' => -5.85],
                -10 => ['D' => -4.3, 'I' => -6.5, 'S' => -6, 'C' => -5.7],
                -9  => ['D' => -3.5, 'I' => -6, 'S' => -4.7, 'C' => -4.7],
                -8  => ['D' => -3.25, 'I' => -5.7, 'S' => -4.3, 'C' => -4.3],
                -7  => ['D' => -3, 'I' => -4.7, 'S' => -3.5, 'C' => -3.5],
                -6  => ['D' => -2.75, 'I' => -4.3, 'S' => -3, 'C' => -3],
                -5  => ['D' => -2.5, 'I' => -3.5, 'S' => -2, 'C' => -2.5],
                -4  => ['D' => -1.5, 'I' => -3, 'S' => -1.5, 'C' => -0.5],
                -3  => ['D' => -1, 'I' => -2, 'S' => -1, 'C' => 0],
                -2  => ['D' => -0.5, 'I' => -1.5, 'S' => -0.5, 'C' => 0.3],
                -1  => ['D' => -0.25, 'I' => 0, 'S' => 0, 'C' => 0.5],
                0  => ['D' => 0, 'I' => 0, 'S' => 0.5, 'C' => 1.5],
                1  => ['D' => 0.5, 'I' => 1, 'S' => 1.5, 'C' => 3],
                2  => ['D' => 0.7, 'I' => 1.5, 'S' => 2, 'C' => 4],
                3  => ['D' => 1, 'I' => 3, 'S' => 3, 'C' => 4.3],
                4  => ['D' => 1.3, 'I' => 4, 'S' => 3.5, 'C' => 5.5],
                5  => ['D' => 1.5, 'I' => 4.3, 'S' => 4, 'C' => 5.7],
                6  => ['D' => 2, 'I' => 5, 'S' => 5, 'C' => 6],
                7  => ['D' => 2.5, 'I' => 5.5, 'S' => 4.7, 'C' => 6.3],
                8  => ['D' => 3.5, 'I' => 6.5, 'S' => 5, 'C' => 6.5],
                9  => ['D' => 4, 'I' => 6.7, 'S' => 5.5, 'C' => 6.7],
                10  => ['D' => 4.7, 'I' => 7, 'S' => 6, 'C' => 7],
                11  => ['D' => 4.85, 'I' => 7.3, 'S' => 6.2, 'C' => 7.3],
                12  => ['D' => 5, 'I' => 7.3, 'S' => 6.3, 'C' => 7.3],
                13  => ['D' => 5.5, 'I' => 7.3, 'S' => 6.5, 'C' => 7.3],
                14  => ['D' => 6, 'I' => 7.3, 'S' => 6.7, 'C' => 7.3],
                15  => ['D' => 6.3, 'I' => 7.3, 'S' => 7, 'C' => 7.3],
                16  => ['D' => 6.5, 'I' => 7.3, 'S' => 7.3, 'C' => 7.3],
                17  => ['D' => 6.7, 'I' => 7.3, 'S' => 7.3, 'C' => 7.5],
                18  => ['D' => 7, 'I' => 7.5, 'S' => 7.3, 'C' => 8],
                19  => ['D' => 7.3, 'I' => 8, 'S' => 7.3, 'C' => 8],
                20  => ['D' => 7.3, 'I' => 8, 'S' => 7.5, 'C' => 8],
                21  => ['D' => 7.5, 'I' => 8, 'S' => 8, 'C' => 8],
                22  => ['D' => 8, 'I' => 8, 'S' => 8, 'C' => 8],
            ];
        }

        if ($graphType === 1) {
            return [
                0 => ['D' => -6, 'I' => -7, 'S' => -5.7, 'C' => -6],
                1 => ['D' => -5.3, 'I' => -4.6, 'S' => -4.3, 'C' => -4.7],
                2 => ['D' => -4, 'I' => -2.5, 'S' => -3.5, 'C' => -3.5],
                3 => ['D' => -2.5, 'I' => -1.3, 'S' => -1.5, 'C' => -1.5],
                4 => ['D' => -1.7, 'I' => 1, 'S' => -0.7, 'C' => 0.5],
                5 => ['D' => -1.3, 'I' => 3, 'S' => 0.5, 'C' => 2],
                6 => ['D' => 0, 'I' => 3.5, 'S' => 1, 'C' => 3],
                7 => ['D' => 0.5, 'I' => 5.3, 'S' => 2.5, 'C' => 5.3],
                8 => ['D' => 1, 'I' => 5.7, 'S' => 3, 'C' => 5.7],
                9 => ['D' => 2, 'I' => 6, 'S' => 4, 'C' => 6],
                10 => ['D' => 3, 'I' => 6.5, 'S' => 4.6, 'C' => 6.3],
                11 => ['D' => 3.5, 'I' => 7, 'S' => 5, 'C' => 6.5],
                12 => ['D' => 4, 'I' => 7, 'S' => 5.7, 'C' => 6.7],
                13 => ['D' => 4.7, 'I' => 7, 'S' => 6, 'C' => 7],
                14 => ['D' => 5.3, 'I' => 7, 'S' => 6.5, 'C' => 7.3],
                15 => ['D' => 6.5, 'I' => 7, 'S' => 6.5, 'C' => 7.3],
                16 => ['D' => 7, 'I' => 7.5, 'S' => 7, 'C' => 7.3],
                17 => ['D' => 7, 'I' => 7.5, 'S' => 7, 'C' => 7.5],
                18 => ['D' => 7, 'I' => 7.5, 'S' => 7, 'C' => 8],
                19 => ['D' => 7.5, 'I' => 7.5, 'S' => 7.5, 'C' => 8],
                20 => ['D' => 7.5, 'I' => 8, 'S' => 7.5, 'C' => 8],
            ];
        }

        if ($graphType === 2) {
            return [
                0 => ['D' => 7.5, 'I' => 7, 'S' => 7.5, 'C' => 7.5],
                1 => ['D' => 6.5, 'I' => 6, 'S' => 7, 'C' => 7],
                2 => ['D' => 4.3, 'I' => 4, 'S' => 6, 'C' => 5.6],
                3 => ['D' => 2.5, 'I' => 2.5, 'S' => 4, 'C' => 4],
                4 => ['D' => 1.5, 'I' => 0.5, 'S' => 2.5, 'C' => 2.5],
                5 => ['D' => 0.5, 'I' => 0, 'S' => 1.5, 'C' => 1.5],
                6 => ['D' => 0, 'I' => -2, 'S' => 0.5, 'C' => 0.5],
                7 => ['D' => -1.3, 'I' => -3.5, 'S' => -1.3, 'C' => 0],
                8 => ['D' => -1.5, 'I' => -4.3, 'S' => -2, 'C' => -1.3],
                9 => ['D' => -2.5, 'I' => -5.3, 'S' => -3, 'C' => -2.5],
                10 => ['D' => -3, 'I' => -6, 'S' => -4.3, 'C' => -3.5],
                11 => ['D' => -3.5, 'I' => -6.5, 'S' => -5.3, 'C' => -5.3],
                12 => ['D' => -4.3, 'I' => -7, 'S' => -6, 'C' => -5.7],
                13 => ['D' => -5.3, 'I' => -7.2, 'S' => -6.5, 'C' => -6],
                14 => ['D' => -5.7, 'I' => -7.2, 'S' => -6.7, 'C' => -6.5],
                15 => ['D' => -6, 'I' => -7.2, 'S' => -6.7, 'C' => -7],
                16 => ['D' => -6.5, 'I' => -7.3, 'S' => -7, 'C' => -7.3],
                17 => ['D' => 6.7, 'I' => -7.3, 'S' => -7.2, 'C' => -7.5],
                18 => ['D' => 7, 'I' => -7.3, 'S' => -7.3, 'C' => -7.7],
                19 => ['D' => -7.3, 'I' => -7.5, 'S' => -7.5, 'C' => -7.9],
                20 => ['D' => -7.5, 'I' => -8, 'S' => -8, 'C' => -8],

            ];
        }
    }


    private function getInterpretation($dominant_type)
    {
        $map = [
            'C-D'        => 'Logical Thinker, Pendiam, Anti Kritik, Perfeksionis, Cenderung Santai, Detail, Empati, Rapi, Organized, Kaku pada Metode & Prosedur',
            'D'          => 'Establisher, Individualis, Ego Tinggi, Kurang Sensitif, Kurang Pertimbangan, Efektif, High Motivation, Bersemangat Tinggi, Percaya Diri, Cenderung Nekat, Kreatif, Terlalu Dominan, Agresif, Terlalu Dinamis, Penuh Ambisi',
            'D/C-D'      => 'Designer, Sensitif, Kurang Cepat, Anti Tekanan, Terlalu Mandiri, Kurang Percaya Orang Lain, Anti Kritik, Dingin, Kreatif, Result Oriented, Suka Tantangan',
            'D/I-D'      => 'Negotiator, Suka Bergaul, Anti Rutin, Aktif, Terlalu Percaya Diri, Agresif, Optimis, Kurang Detail, Result Oriented',
            'D/I-D-C'    => 'Confident & Determined, Pandai Memilih Orang, Leader, Good Interpersonal Skill, Dominan, Agresif, Perfeksionis, Good Communication Skill, Aktif, Need Recognition n Reward, Kurang Peduli pada Aturan, Terburu-buru',
            'D/I-D-S'    => 'Reformer, Mudah Bergaul, Leader, Sadar Diri, Butuh Pujian, Cepat Percaya Orang, Mudah Simpati & Empati, Motivator, Optimis & Positif, Anti Aturan, Kurang Detail, Terlalu Selektif',
            'D/I-S-D'    => 'Motivator, Leader Kelompok Kecil, Supporter, Sosialisasi Baik, Butuh Ketegasan, Butuh Pujian, Kurang Detail, Agak Kaku, Result Oriented, Good Service, Kurang Managerial',
            'D/S-D-C'    => 'Inquirer, Full Self Control, Sabar, Penuh Pertimbangan, Selektif, Lambat Adaptasi, Inisiatif Kurang, Kaku, Good Interpersonal, Good Service',
            'D-I'        => 'Pengambil Keputusan, Pekerja Keras, Leader, Banyak Minat, Dingin, Kurang Pergaulan, Kontrol Emosi, Suka Tantangan, Cepat Bosan, Anti Aturan, Kurang Detail, Argumentatif',
            'D-I-S'      => 'Director, Pengelola, Enerjik, Agresif, Mudah Bosan, Kurang Focus, Cepat Beradaptasi, Anti Kritik, Arogan, Kurang Detail, Anti Aturan',
            'D-S'        => 'Self-Motivated, Objektif, Mandiri, Good Planner, Komitmen Target, Good Analytical Think, Good Interpersonal, Cepat Bosan, Monoton, Not Leader',
            'I/C-I-S'    => 'Mediator, Loyal, Tight Scheduled, Curious, Good Communication Skill, Too Detail, Sistematis, Kaku, Not Leader, Work/Play Conflict',
            'I/C-S-I'    => 'Practitioner, Perfeksionis, Quality Oriented, Sensitif, Good Interpersonal, Sistematis, Empati, Concern ke Fakta, Loyal, Introvert',
            'I-S-C'      => 'Responsive & Thoughtful, High Energy, Good Communication, To The Point, Sensitif, Banyak Bicara, Need Recognition, Butuh Sosialisasi, Leadership kurang, Kurang Fokus, Anti Deadline',
            'S'          => 'Specialist, Stabil, Konsisten, Terkendali, Sabar, Loyal, Sulit Adaptasi, Butuh Situasi Stabil, Process Oriented, Need for Peace, Anti Perubahan',
            'S/C-S'      => 'Perfectionist, Detail, Sistematis, Teliti, Prosedural, Sulit Adaptasi, Lambat Memutuskan, Empati, Terlalu Mendalam, Concern ke Fakta',
            'S-C'        => 'Peacemaker, Respectful & Accurate, Sulit Beradaptasi, Anti Kritik, Pendendam, Sukar Berubah, Empati, Introvert, Concern Data, Loyal',
            'D-C'        => 'Challenger, Tekun, Sensitif, Keputusan Kuat, Kreatif, Reaksi Cepat, Banyak Ide, Perfeksionis, Mandiri, Cermat',
            'D-I-C'      => 'Chancellor, Ramah, Menyukai Hubungan, Detil, Tepat, Sering Melalaikan Perencanaan, Mudah Beralih Proyek, Menilai Hati-hati',
            'D-S-I'      => 'Director, Objektif, Analitis, Ingin Terlibat, Memberi Bantuan, Termotivasi Target Pribadi, Stabil, Ulet, Mandiri, Cermat',
            'D-S-C'      => 'Director, Objektif, Analitis, Tenang, Stabil, Ulet, Mandiri, Cermat, Determinasi Kuat',
            'D-C-I'      => 'Challenger, Sensitif, Kreatif, Reaksi Cepat, Mencari Solusi, Banyak Ide, Perfeksionis',
            'D-C-S'      => 'Challenger, Sensitif, Kreatif, Reaksi Cepat, Banyak Ide, Perfeksionis',
            'I'          => 'Communicator, Antusias, Percaya, Optimis, Persuasif, Bicara Aktif, Impulsif, Emosional, Ramah, Inspirasional',
            'I-S'        => 'Advisor, Hangat, Simpati, Tenang, Pendengar Baik, Demonstratif, Tidak Memaksakan Ide, Kurang Tegas, Menerima Kritik, Toleran, Penjaga Damai',
            'I-C'        => 'Assessor, Ramah, Nyaman dengan Orang Asing, Perfeksionis Alamiah, Analitis, Hati-hati, Peduli, Perfeksionis',
            'I-C-D'      => 'Assessor, Analitis, Hati-hati, Perfeksionis Alamiah, Ramah, Cermat, Terprediksi, Berorientasi Kualitas',
            'I-C-S'      => 'Responsive & Thoughtful, High Energy, Komunikatif, Sensitif, Banyak Bicara, Butuh Sosialisasi, Kurang Fokus, Anti Kritik, Leadership Kurang',
            'S-D'        => 'Self-Motivated, Objektif, Mandiri, Planner, Komitmen Target, Menghindari Konflik, Stabil, Tekun',
            'S-I'        => 'Advisor, Hangat, Simpati, Tenang, Pendengar Baik, Demonstratif, Tidak Memaksakan Ide, Toleran, Penjaga Damai',
            'S-D-I'      => 'Director, Objektif, Analitis, Determinasi Kuat, Tenang, Stabil, Ulet, Mandiri, Cermat',
            'S-I-D'      => 'Advisor, Hangat, Simpati, Tenang, Demonstratif, Penjaga Damai',
            'S-I-C'      => 'Advocate, Stabil, Ramah, Detail, Teguh Pendirian, Mendukung Pihak Lemah, Moderat, Cermat, Andal',
            'S-C-D'      => 'Inquirer, Orientasi Detil, Teliti, Hati-hati, Penuh Pertimbangan, Lambat Adaptasi, Kaku, Keras Kepala',
            'S-C-I'      => 'Advocate, Stabil, Ramah, Teguh Pendirian, Mendukung Pihak Lemah, Sulit Membuat Keputusan, Moderat, Cermat',
            'C-I'        => 'Assessor, Analitis, Hati-hati, Perfeksionis, Peduli, Ramah, Cermat, Berorientasi Kualitas',
            'C-D-I'      => 'Challenger, Sensitif, Kukuh, Dingin, Membuat Keputusan Faktual, Pendiam, Tidak Mudah Percaya',
            'C-D-S'      => 'Contemplator, Detil, Perfeksionis, Logis, Analitis, Kompetitif, Fokus pada Mutu, Andal',
            'C-I-D'      => 'Assessor, Analitis, Hati-hati, Perfeksionis, Ramah, Cermat, Prediktif, Berorientasi Kualitas',
            'C-S-D'      => 'Precisionist, Sistematis, Prosedural, Teliti, Fokus Detil, Bijaksana, Diplomatis, Perfeksionis, Anti Perubahan Mendadak',
        ];
        return $map[$dominant_type] ?? 'Unmapped Type';
    }
}
