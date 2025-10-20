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
        // For PostgreSQL, we need to use raw SQL to modify enum columns
        DB::statement("ALTER TABLE candidates ALTER COLUMN gender TYPE VARCHAR(255)");
        
        // Check if constraint already exists before adding it
        $constraintExists = DB::select("
            SELECT 1 FROM information_schema.table_constraints 
            WHERE constraint_name = 'candidates_gender_check' 
            AND table_name = 'candidates'
        ");
        
        if (empty($constraintExists)) {
            DB::statement("ALTER TABLE candidates ADD CONSTRAINT candidates_gender_check CHECK (gender IN ('male', 'female'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->enum('gender', ['male', 'famale'])->change();
        });
    }
};