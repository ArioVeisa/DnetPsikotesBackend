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
        // Add test_distribution_id column to test_distribution_candidates table
        DB::statement("ALTER TABLE test_distribution_candidates ADD COLUMN test_distribution_id BIGINT");
        DB::statement("ALTER TABLE test_distribution_candidates ADD CONSTRAINT test_distribution_candidates_test_distribution_id_foreign FOREIGN KEY (test_distribution_id) REFERENCES test_distributions(id) ON DELETE CASCADE");
        DB::statement("CREATE INDEX idx_test_distribution_candidates_test_distribution_id ON test_distribution_candidates (test_distribution_id)");
        
        // Remove old test_id foreign key constraint and column
        DB::statement("ALTER TABLE test_distribution_candidates DROP CONSTRAINT IF EXISTS test_distribution_candidates_test_id_foreign");
        DB::statement("ALTER TABLE test_distribution_candidates DROP COLUMN IF EXISTS test_id");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back test_id column
        DB::statement("ALTER TABLE test_distribution_candidates ADD COLUMN test_id BIGINT NOT NULL");
        DB::statement("ALTER TABLE test_distribution_candidates ADD CONSTRAINT test_distribution_candidates_test_id_foreign FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE");
        
        // Remove test_distribution_id
        DB::statement("ALTER TABLE test_distribution_candidates DROP CONSTRAINT IF EXISTS test_distribution_candidates_test_distribution_id_foreign");
        DB::statement("ALTER TABLE test_distribution_candidates DROP COLUMN IF EXISTS test_distribution_id");
    }
};