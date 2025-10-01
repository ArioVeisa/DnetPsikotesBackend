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
        Schema::create('disc_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('disc_questions')->onDelete('cascade');
            $table->string('option_text');
            $table->enum('dimension_most', ['D', 'I', 'S', 'C', '*']);
            $table->enum('dimension_least', ['D', 'I', 'S', 'C', '*']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_options');
    }
};
