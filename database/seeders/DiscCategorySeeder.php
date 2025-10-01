<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiscCategory;
use Illuminate\Support\Facades\DB;

class DiscCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DiscCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            ['name' => 'Dominance (D)'],
            ['name' => 'Influence (I)'],
            ['name' => 'Steadiness (S)'],
            ['name' => 'Compliance (C)'],
            ['name' => 'Leadership Style'],
            ['name' => 'Communication Style'],
            ['name' => 'Work Environment'],
            ['name' => 'Decision Making'],
        ];

        foreach ($categories as $category) {
            DiscCategory::create($category);
        }
    }
}
