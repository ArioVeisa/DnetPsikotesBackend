<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position'); // Manager, Staff, Fresh Graduate
            $table->boolean('include_disc')->default(false);
            $table->boolean('include_caas')->default(false);
            $table->boolean('include_teliti')->default(false);
            $table->integer('disc_time')->nullable(); // waktu dalam menit
            $table->integer('caas_time')->nullable();
            $table->integer('teliti_time')->nullable();
            $table->integer('disc_questions_count')->nullable();
            $table->integer('caas_questions_count')->nullable();
            $table->integer('teliti_questions_count')->nullable();
            $table->json('sequence')->nullable(); // urutan tes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_templates');
    }
};