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
        // Add test_distribution_id column to candidate_tests table
        DB::statement("ALTER TABLE candidate_tests ADD COLUMN test_distribution_id BIGINT");
        DB::statement("ALTER TABLE candidate_tests ADD CONSTRAINT candidate_tests_test_distribution_id_foreign FOREIGN KEY (test_distribution_id) REFERENCES test_distributions(id) ON DELETE CASCADE");
        DB::statement("CREATE INDEX idx_candidate_tests_test_distribution_id ON candidate_tests (test_distribution_id)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE candidate_tests DROP CONSTRAINT IF EXISTS candidate_tests_test_distribution_id_foreign");
        DB::statement("ALTER TABLE candidate_tests DROP COLUMN IF EXISTS test_distribution_id");
    }
};