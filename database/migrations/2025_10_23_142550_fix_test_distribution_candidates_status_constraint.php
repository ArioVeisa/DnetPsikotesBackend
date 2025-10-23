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
        // Drop existing constraint
        DB::statement("ALTER TABLE test_distribution_candidates DROP CONSTRAINT IF EXISTS test_distribution_candidates_status_check");
        
        // Add new constraint with 'in_progress' status
        DB::statement("ALTER TABLE test_distribution_candidates ADD CONSTRAINT test_distribution_candidates_status_check CHECK (status IN ('pending', 'invited', 'in_progress', 'completed', 'cancelled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new constraint
        DB::statement("ALTER TABLE test_distribution_candidates DROP CONSTRAINT IF EXISTS test_distribution_candidates_status_check");
        
        // Restore original constraint
        DB::statement("ALTER TABLE test_distribution_candidates ADD CONSTRAINT test_distribution_candidates_status_check CHECK (status IN ('pending', 'invited', 'completed', 'cancelled'))");
    }
};