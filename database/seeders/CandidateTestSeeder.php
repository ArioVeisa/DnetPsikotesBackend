<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CandidateTest;
use App\Models\Candidate;
use App\Models\Test;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CandidateTestSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan sementara foreign key agar truncate aman
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CandidateTest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $candidates = Candidate::all();
        $tests = Test::all();

        // Jika belum ada kandidat atau test, buat dummy minimal
        if ($candidates->isEmpty()) {
            $candidates = collect([
                Candidate::create(['name' => 'Budi Santoso', 'email' => 'budi@example.com', 'position' => 'Manager']),
                Candidate::create(['name' => 'Andi Wijaya', 'email' => 'andi@example.com', 'position' => 'Staff']),
                Candidate::create(['name' => 'Siti Lestari', 'email' => 'siti@example.com', 'position' => 'Supervisor']),
            ]);
        }

        if ($tests->isEmpty()) {
            $tests = collect([
                Test::create(['name' => 'Tes Ketelitian', 'target_position' => 'Staff', 'icon_path' => 'square-code', 'started_date' => now(), 'access_type' => 'public']),
                Test::create(['name' => 'Tes Adaptasi Karir', 'target_position' => 'Supervisor', 'icon_path' => 'target', 'started_date' => now(), 'access_type' => 'private']),
                Test::create(['name' => 'Tes Kepribadian DISC', 'target_position' => 'Manager', 'icon_path' => 'user', 'started_date' => now(), 'access_type' => 'public']),
            ]);
        }

        // Tambahkan data kandidat test
        CandidateTest::insert([
            [
                'candidate_id' => $candidates[0]->id,
                'test_id' => $tests[0]->id,
                'unique_token' => Str::uuid(),
                'started_at' => now()->subDays(2),
                'completed_at' => now()->subDays(1),
                'score' => 85,
                'status' => CandidateTest::STATUS_COMPLETED,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'candidate_id' => $candidates[1]->id,
                'test_id' => $tests[1]->id,
                'unique_token' => Str::uuid(),
                'started_at' => now()->subDays(3),
                'completed_at' => now()->subHours(12),
                'score' => 92,
                'status' => CandidateTest::STATUS_COMPLETED,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'candidate_id' => $candidates[2]->id,
                'test_id' => $tests[0]->id,
                'unique_token' => Str::uuid(),
                'started_at' => now()->subHours(2),
                'completed_at' => null,
                'score' => null,
                'status' => CandidateTest::STATUS_IN_PROGRESS,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'candidate_id' => $candidates[0]->id,
                'test_id' => $tests[2]->id,
                'unique_token' => Str::uuid(),
                'started_at' => null,
                'completed_at' => null,
                'score' => null,
                'status' => CandidateTest::STATUS_NOT_STARTED,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'candidate_id' => $candidates[1]->id,
                'test_id' => $tests[1]->id,
                'unique_token' => Str::uuid(),
                'started_at' => null,
                'completed_at' => null,
                'score' => null,
                'status' => CandidateTest::STATUS_NOT_STARTED,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
