<?php

namespace App\Imports;

use App\Models\CaasQuestion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CaasQuestionImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $question = CaasQuestion::create([
            'question_text' => $row['question_text'],
            'category_id' => $row['category_id'],
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
        ]);
        for ($i = 1; $i <= 5; $i++) {
            $question->options()->create([
                'option_text' => $row["option_{$i}"],
                'score' => isset($row["score_{$i}"]) ? (int)$row["score_{$i}"] : 0,
            ]);
        }
    }
}
