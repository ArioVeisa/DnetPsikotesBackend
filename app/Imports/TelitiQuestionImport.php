<?php

namespace App\Imports;

use App\Models\telitiQuestion;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class telitiQuestionImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::info('Imported row:', $row);
        $question = telitiQuestion::create([
            'question_text' => $row['question_text'],
            'category_id' => $row['category_id'],
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
        ]);
        // Log::info('Parsed Row:', $row);
        $correctOptionId = null;

        for ($i = 1; $i <= 2; $i++) {
            $option = $question->options()->create([
                'option_text' => $row["option_{$i}"],
            ]);

            if (!empty($row["is_correct_{$i}"]) && $row["is_correct_{$i}"] == 1) {
                $correctOptionId = $option->id;
            }
        }

        $question->update([
            'correct_option_id' => $correctOptionId,
        ]);
    }
}
