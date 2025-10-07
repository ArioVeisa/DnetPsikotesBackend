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
        Schema::create('disc_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_test_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->integer('most_d')->default(0);
            $table->integer('most_i')->default(0);
            $table->integer('most_s')->default(0);
            $table->integer('most_c')->default(0);
            $table->integer('least_d')->default(0);
            $table->integer('least_i')->default(0);
            $table->integer('least_s')->default(0);
            $table->integer('least_c')->default(0);
            $table->integer('diff_d')->default(0);
            $table->integer('diff_i')->default(0);
            $table->integer('diff_s')->default(0);
            $table->integer('diff_c')->default(0);

            $table->float('std1_d')->nullable();
            $table->float('std1_i')->nullable();
            $table->float('std1_s')->nullable();
            $table->float('std1_c')->nullable();

            $table->float('std2_d')->nullable();
            $table->float('std2_i')->nullable();
            $table->float('std2_s')->nullable();
            $table->float('std2_c')->nullable();

            $table->float('std3_d')->nullable();
            $table->float('std3_i')->nullable();
            $table->float('std3_s')->nullable();
            $table->float('std3_c')->nullable();


            $table->string('dominant_type', 20)->nullable();
            $table->string('dominant_type_2', 20)->nullable();
            $table->string('dominant_type_3', 20)->nullable();

            $table->text('interpretation')->nullable();
            $table->text('interpretation_2')->nullable();
            $table->text('interpretation_3')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_results');
    }
};
