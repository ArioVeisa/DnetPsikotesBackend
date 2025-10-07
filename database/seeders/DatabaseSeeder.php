<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil semua seeder dalam urutan yang benar
        $this->call([
            // User dan Candidate seeder
            UserSeeder::class,
            CandidateSeeder::class,

            // Category seeders (harus dijalankan terlebih dahulu)
            CaasCategorySeeder::class,
            DiscCategorySeeder::class,
            TelitiCategorySeeder::class,

            // Question seeders
            CaasQuestionSeeder::class,
            DiscQuestionSeeder::class,
            TelitiQuestionSeeder::class,

            // Option seeders
            CaasOptionSeeder::class,
            TelitiOptionSeeder::class,

            // Test dan TestSection seeders
            TestSeeder::class,
            TestSectionSeeder::class,

            // TestQuestion seeder (harus dijalankan terakhir karena membutuhkan semua data sebelumnya)
            TestQuestionSeeder::class,
        ]);
    }
}
