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
        // Drop existing constraint first
        DB::statement("ALTER TABLE test_questions DROP CONSTRAINT IF EXISTS test_questions_question_type_check");
        
        // Update existing data to uppercase
        DB::table('test_questions')->where('question_type', 'disc')->update(['question_type' => 'DISC']);
        DB::table('test_questions')->where('question_type', 'caas')->update(['question_type' => 'CAAS']);
        // teliti remains lowercase as per existing schema
        
        // Add new constraint with correct enum values
        DB::statement("ALTER TABLE test_questions ADD CONSTRAINT test_questions_question_type_check CHECK (question_type::text = ANY (ARRAY['DISC'::character varying, 'CAAS'::character varying, 'teliti'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to lowercase
        DB::table('test_questions')->where('question_type', 'DISC')->update(['question_type' => 'disc']);
        DB::table('test_questions')->where('question_type', 'CAAS')->update(['question_type' => 'caas']);
        
        // Drop and recreate the enum type for PostgreSQL
        DB::statement("ALTER TABLE test_questions DROP CONSTRAINT IF EXISTS test_questions_question_type_check");
        DB::statement("ALTER TABLE test_questions ADD CONSTRAINT test_questions_question_type_check CHECK (question_type::text = ANY (ARRAY['caas'::character varying, 'disc'::character varying, 'teliti'::character varying]::text[]))");
    }
};
