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
        Schema::create('caas_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_test_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->integer('concern')->default(0);
            $table->integer('control')->default(0);
            $table->integer('curiosity')->default(0);
            $table->integer('confidence')->default(0);
            $table->integer('total')->default(0);
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caas_results');
    }
};
