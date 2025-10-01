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
        Schema::create('candidate_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_test_id')->constrained('candidate_tests')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('test_sections')->onDelete('cascade');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('most_option_id')->nullable(); // DISC
            $table->unsignedBigInteger('least_option_id')->nullable(); // DISC
            $table->unsignedBigInteger('selected_option_id')->nullable(); // teliti
            $table->boolean('is_correct')->nullable(); // teliti
            $table->integer('score')->nullable(); // CAAS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_answers');
    }
};