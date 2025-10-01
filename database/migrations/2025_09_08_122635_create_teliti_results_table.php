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
        Schema::create('teliti_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_test_id')->constrained()->onDelete('cascade');
            $table->integer('score');
            $table->integer('total_questions');
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teliti_results');
    }
};
