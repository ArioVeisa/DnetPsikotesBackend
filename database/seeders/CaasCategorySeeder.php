<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaasCategory;
use Illuminate\Support\Facades\DB;

class CaasCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        // Clear existing records
        CaasCategory::truncate();
        // Foreign key checks enabled

        $categories = [
            ['name' => 'Kemampuan Analitis'],
            ['name' => 'Kemampuan Verbal'],
            ['name' => 'Kemampuan Numerik'],
            ['name' => 'Kemampuan Spasial'],
            ['name' => 'Kemampuan Logika'],
            ['name' => 'Kemampuan Memori'],
            ['name' => 'Kemampuan Konsentrasi'],
            ['name' => 'Kemampuan Pemecahan Masalah'],
        ];

        foreach ($categories as $category) {
            CaasCategory::create($category);
        }
    }
}
