<?php

namespace App\Imports;

use App\Models\TelitiQuestion;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TelitiQuestionImport implements ToModel, WithHeadingRow
{
    private function resolveIsCorrectFlag(array $row, int $index): bool
    {
        $candidateKeys = [
            "is_correct_{$index}",
            "is_correct{$index}",
            "is_correct_t_{$index}", // toleransi nama kolom pada template lama
            "is_correc_t_{$index}",  // toleransi salah ketik umum (spasi terpotong)
        ];

        foreach ($candidateKeys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                $value = $row[$key];
                // dukung 1/0, true/false, TRUE/FALSE, "true"/"false"
                if (is_numeric($value)) {
                    return ((int) $value) === 1;
                }

                if (is_bool($value)) {
                    return $value;
                }

                $normalized = strtolower(trim((string) $value));
                return in_array($normalized, ['1', 'true', 'yes', 'y'], true);
            }
        }

        return false;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::info('Imported row:', $row);

        // Dukungan format baru: question_a + question_b + is_true
        $hasAB = !empty($row['question_a']) || !empty($row['question_b']);

        if ($hasAB) {
            $a = isset($row['question_a']) ? trim((string) $row['question_a']) : '';
            $b = isset($row['question_b']) ? trim((string) $row['question_b']) : '';

            // validasi: A dan B wajib terisi
            if ($a === '' || $b === '') {
                Log::warning('Skip row: question_a or question_b empty', $row);
                return null; // lewati baris invalid
            }

            $questionText = $a . ' | ' . $b;
            $question = TelitiQuestion::create([
                'question_text' => $questionText,
                'category_id' => $row['category_id'],
                'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            ]);

            // Buat opsi fixed True/False
            $optTrue = $question->options()->create(['option_text' => 'True']);
            $optFalse = $question->options()->create(['option_text' => 'False']);

            // Tentukan jawaban dari kolom is_true / answer / correct
            $flagKeys = ['is_true', 'answer', 'correct'];
            $isTrue = null;
            foreach ($flagKeys as $k) {
                if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                    $val = $row[$k];
                    if (is_numeric($val)) {
                        $isTrue = ((int) $val) === 1;
                    } else {
                        $norm = strtolower(trim((string) $val));
                        if (in_array($norm, ['1', 'true', 'yes', 'y'], true)) $isTrue = true;
                        if (in_array($norm, ['0', 'false', 'no', 'n'], true)) $isTrue = false;
                    }
                    break;
                }
            }

            // Jika tidak ada flag, fallback ke logika A|B sama → True
            if ($isTrue === null) {
                $isTrue = $a === $b;
            }

            $question->update([
                'correct_option_id' => $isTrue ? $optTrue->id : $optFalse->id,
            ]);
            return $question;
        }

        // Format lama: question_text + option_1/2 + is_correct_1/2
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

            if ($this->resolveIsCorrectFlag($row, $i)) {
                $correctOptionId = $option->id;
            }
        }

        // 🔹 Untuk Fast Accuracy (format lama): jika tidak ada correct option, gunakan logika A|B
        if ($correctOptionId === null && strpos($question->question_text, '|') !== false) {
            $parts = explode('|', $question->question_text);
            if (count($parts) === 2) {
                $itemA = trim($parts[0]);
                $itemB = trim($parts[1]);
                $isSame = $itemA === $itemB;
                
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
        return $question;
    }
}
