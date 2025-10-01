<?php

namespace App\Imports;

use App\Models\DiscQuestion;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DiscQuestionImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        Log::info('Imported row:', $row);
        $question = DiscQuestion::create([
            'question_text' => $row['question_text'],
            'category_id' => $row['category_id'],
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
        ]);

        for ($i = 1; $i <= 4; $i++) {
            $question->options()->create([
                'option_text' => $row["option_{$i}"],
                'dimension' => $row["dimension_{$i}"] ?? null,
            ]);
        }
    }
}
