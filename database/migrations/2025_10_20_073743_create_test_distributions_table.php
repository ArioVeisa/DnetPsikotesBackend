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
        // Create test_distributions table using raw SQL for better control
        DB::statement("
            CREATE TABLE test_distributions (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                template_test_id BIGINT NOT NULL,
                target_position VARCHAR(255),
                icon_path VARCHAR(255),
                started_date DATE NOT NULL,
                ended_date DATE,
                access_type VARCHAR(50) DEFAULT 'Public',
                status VARCHAR(50) DEFAULT 'Scheduled',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (template_test_id) REFERENCES tests(id) ON DELETE CASCADE
            )
        ");
        
        // Create indexes
        DB::statement("CREATE INDEX idx_test_distributions_template_test_id ON test_distributions (template_test_id)");
        DB::statement("CREATE INDEX idx_test_distributions_status ON test_distributions (status)");
        DB::statement("CREATE INDEX idx_test_distributions_created_at ON test_distributions (created_at)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_distributions');
    }
};