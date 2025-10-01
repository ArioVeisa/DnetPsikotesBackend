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
        Schema::create('disc_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->string('media_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('category_id')->constrained('disc_categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_questions');
    }
};
