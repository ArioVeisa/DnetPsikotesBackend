<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Add fields to track related entities for better filtering and reporting
            $table->foreignId('candidate_id')->nullable()->constrained()->onDelete('set null')->after('user_id');
            $table->foreignId('test_id')->nullable()->constrained()->onDelete('set null')->after('candidate_id');
            $table->foreignId('question_id')->nullable()->after('test_id');
            $table->string('question_type')->nullable()->after('question_id'); // 'caas', 'disc', 'teliti'
            $table->string('entity_type')->nullable()->after('question_type'); // 'candidate', 'test', 'question', 'result', etc.
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type'); // ID of the related entity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['candidate_id']);
            $table->dropForeign(['test_id']);
            $table->dropColumn([
                'candidate_id',
                'test_id', 
                'question_id',
                'question_type',
                'entity_type',
                'entity_id'
            ]);
        });
    }
};