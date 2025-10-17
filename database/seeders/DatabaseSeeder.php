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
        $this->call([
            // 1️⃣ User dan Candidate
            UserSeeder::class,
            CandidateSeeder::class,

            // 2️⃣ Category Seeders
            CaasCategorySeeder::class,
            DiscCategorySeeder::class,
            TelitiCategorySeeder::class,

            // 3️⃣ Question Seeders
            CaasQuestionSeeder::class,
            DiscQuestionSeeder::class,
            TelitiQuestionSeeder::class,

            // 4️⃣ Option Seeders
            CaasOptionSeeder::class,
            TelitiOptionSeeder::class,

            // 5️⃣ Test dan TestSection
            TestSeeder::class,
            TestSectionSeeder::class,

            // 6️⃣ CandidateTestSeeder HARUS sebelum Result
            CandidateTestSeeder::class,

            // 7️⃣ Result Seeders (bergantung pada candidate_tests)
            CaasResultSeeder::class,
            DiscResultSeeder::class,
            TelitiResultSeeder::class,

            // 8️⃣ TestQuestion terakhir (butuh semua data sebelumnya)
            TestQuestionSeeder::class,
        ]);
    }
}
