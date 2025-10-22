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
        // Create table using raw SQL for better control
        DB::statement("
            CREATE TABLE test_distribution_candidates (
                id BIGSERIAL PRIMARY KEY,
                test_id BIGINT NOT NULL,
                name VARCHAR(255) NOT NULL,
                nik VARCHAR(255) NOT NULL UNIQUE,
                phone_number VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                position VARCHAR(255) NOT NULL,
                birth_date DATE NOT NULL,
                gender VARCHAR(255) NOT NULL CHECK (gender IN ('male', 'female')),
                department VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
            )
        ");
        
        // Create index
        DB::statement("CREATE INDEX idx_test_distribution_candidates_test_id_created_at ON test_distribution_candidates (test_id, created_at)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_distribution_candidates');
    }
};
