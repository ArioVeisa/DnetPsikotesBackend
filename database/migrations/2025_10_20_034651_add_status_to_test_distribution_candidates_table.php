<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status column to test_distribution_candidates table
        DB::statement("ALTER TABLE test_distribution_candidates ADD COLUMN status VARCHAR(255) DEFAULT 'pending'");
        DB::statement("ALTER TABLE test_distribution_candidates ADD CONSTRAINT test_distribution_candidates_status_check CHECK (status IN ('pending', 'invited', 'completed', 'cancelled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE test_distribution_candidates DROP CONSTRAINT IF EXISTS test_distribution_candidates_status_check");
        DB::statement("ALTER TABLE test_distribution_candidates DROP COLUMN IF EXISTS status");
    }
};
