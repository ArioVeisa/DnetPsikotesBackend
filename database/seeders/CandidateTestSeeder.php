<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CandidateTest;
use App\Models\Candidate;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class CandidateTestSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CandidateTest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $candidates = Candidate::all();
        $tests = Test::all();

        if ($candidates->count() > 0 && $tests->count() > 0) {
            // Buat beberapa tes yang sudah completed
            CandidateTest::create([
                'candidate_id' => $candidates[0]->id,
                'test_id' => $tests[0]->id,
                'unique_token' => \Illuminate\Support\Str::uuid(),
                'started_at' => now()->subDays(2),
                'completed_at' => now()->subDays(1),
                'score' => 85,
                'status' => CandidateTest::STATUS_COMPLETED,
            ]);

            CandidateTest::create([
                'candidate_id' => $candidates[1]->id ?? $candidates[0]->id,
                'test_id' => $tests[1]->id ?? $tests[0]->id,
                'unique_token' => \Illuminate\Support\Str::uuid(),
                'started_at' => now()->subDays(3),
                'completed_at' => now()->subHours(12),
                'score' => 92,
                'status' => CandidateTest::STATUS_COMPLETED,
            ]);

            // Buat beberapa tes yang in progress
            CandidateTest::create([
                'candidate_id' => $candidates[2]->id ?? $candidates[0]->id,
                'test_id' => $tests[0]->id,
                'unique_token' => \Illuminate\Support\Str::uuid(),
                'started_at' => now()->subHours(2),
                'completed_at' => null,
                'score' => null,
                'status' => CandidateTest::STATUS_IN_PROGRESS,
            ]);

            // Buat beberapa tes yang pending
            CandidateTest::create([
                'candidate_id' => $candidates[0]->id,
                'test_id' => $tests[2]->id ?? $tests[0]->id,
                'unique_token' => \Illuminate\Support\Str::uuid(),
                'started_at' => null,
                'completed_at' => null,
                'score' => null,
                'status' => CandidateTest::STATUS_NOT_STARTED,
            ]);

            CandidateTest::create([
                'candidate_id' => $candidates[1]->id ?? $candidates[0]->id,
                'test_id' => $tests[1]->id ?? $tests[0]->id,
                'unique_token' => \Illuminate\Support\Str::uuid(),
                'started_at' => null,
                'completed_at' => null,
                'score' => null,
                'status' => CandidateTest::STATUS_NOT_STARTED,
            ]);
        }
    }
}
