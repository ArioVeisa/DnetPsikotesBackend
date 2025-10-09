<?php

namespace App\Imports;

use App\Models\TelitiQuestion;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TelitiQuestionImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::info('Imported row:', $row);
        $question = TelitiQuestion::create([
            'question_text' => $row['question_text'],
            'category_id' => $row['category_id'],
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
        ]);
        
        $correctOptionId = null;

        for ($i = 1; $i <= 2; $i++) {
            $option = $question->options()->create([
                'option_text' => $row["option_{$i}"],
            ]);

            if (!empty($row["is_correct_{$i}"]) && $row["is_correct_{$i}"] == 1) {
                $correctOptionId = $option->id;
            }
        }

        // 🔹 Untuk Fast Accuracy: jika tidak ada correct option yang diset dari CSV,
        // tentukan berdasarkan logika "nama | nama" (True jika sama, False jika berbeda)
        if ($correctOptionId === null && strpos($question->question_text, '|') !== false) {
            $parts = explode('|', $question->question_text);
            if (count($parts) === 2) {
                $itemA = trim($parts[0]);
                $itemB = trim($parts[1]);
                $isSame = $itemA === $itemB;
                
                // Cari option yang sesuai dengan logika
                $options = $question->options;
                foreach ($options as $option) {
                    $optionText = strtolower(trim($option->option_text));
                    if (($isSame && $optionText === 'true') || (!$isSame && $optionText === 'false')) {
                        $correctOptionId = $option->id;
                        break;
                    }
                }
            }
        }

        $question->update([
            'correct_option_id' => $correctOptionId,
        ]);
    }
}
