<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\telitiCategory;
use Illuminate\Support\Facades\DB;

class telitiCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        telitiCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
            telitiCategory::create($category);
        }
    }
}
