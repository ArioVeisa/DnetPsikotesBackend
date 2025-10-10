<?php

namespace App\Imports;

use App\Models\DiscQuestion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DiscQuestionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $question = DiscQuestion::create([
            'question_text' => $row['question_text'],
            'category_id' => $row['category_id'],
            'is_active' => isset($row['is_active'])
                ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN)
                : true,
        ]);

        for ($i = 1; $i <= 4; $i++) {
            if (empty($row["option{$i}_text"])) {
                continue;
            }

            $question->options()->create([
                'option_text' => $row["option{$i}_text"],
                'dimension_most' => $row["option{$i}_most"] ?? '*',
                'dimension_least' => $row["option{$i}_least"] ?? '*',
            ]);
        }

        return $question;
    }
}
