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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 
            $table->string('nik')->unique();
            $table->string('phone_number');
            $table->string('email')->unique(); // Email sebaiknya unik
            $table->string('position'); // Sesuai BRD "Posisi yang dilamar"
            $table->date('birth_date');
            $table->enum('gender', ['male', 'famale']);
            $table->string('department'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
