<?php

namespace App\Imports;

use App\Models\CaasQuestion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CaasQuestionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $question = CaasQuestion::create([
            'question_text' => $row['question_text'],
            'category_id' => $row['category_id'],
            'is_active' => isset($row['is_active'])
                ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN)
                : true,
        ]);

        $defaultOptions = [
            ['option_text' => 'Paling kuat', 'score' => 5],
            ['option_text' => 'Sangat kuat', 'score' => 4],
            ['option_text' => 'Kuat', 'score' => 3],
            ['option_text' => 'Cukup kuat', 'score' => 2],
            ['option_text' => 'Tidak kuat', 'score' => 1],
        ];

        foreach ($defaultOptions as $opt) {
            $question->options()->create($opt);
        }

        return $question;
    }
}
