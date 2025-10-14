<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TelitiCategory;
use Illuminate\Support\Facades\DB;

class TelitiCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        // Clear existing records
        TelitiCategory::truncate();
        // Foreign key checks enabled

        $categories = [
            ['name' => 'Kemampuan Verbal'],
            ['name' => 'Kemampuan Numerik'],
            ['name' => 'Kemampuan Logika'],
            ['name' => 'Kemampuan Spasial'],
            ['name' => 'Kemampuan Memori'],
            ['name' => 'Kemampuan Konsentrasi'],
            ['name' => 'Kemampuan Analitis'],
            ['name' => 'Kemampuan Pemecahan Masalah'],
        ];

        foreach ($categories as $category) {
            TelitiCategory::create($category);
        }
    }
}
