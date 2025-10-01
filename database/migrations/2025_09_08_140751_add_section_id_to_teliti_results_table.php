<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('teliti_results', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->after('candidate_test_id');
        });
    }

    public function down()
    {
        Schema::table('teliti_results', function (Blueprint $table) {
            $table->dropColumn('section_id');
        });
    }
};
