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
        // Clear existing records
        DiscCategory::truncate();
        // Foreign key checks enabled

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
