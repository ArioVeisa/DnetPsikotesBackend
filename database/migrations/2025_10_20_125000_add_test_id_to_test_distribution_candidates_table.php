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
        // Tambahkan kolom test_id jika belum ada (menghindari error pada tabel lama)
        if (!Schema::hasColumn('test_distribution_candidates', 'test_id')) {
            Schema::table('test_distribution_candidates', function (Blueprint $table) {
                // Izinkan NULL agar migrasi tidak gagal pada data lama
                $table->foreignId('test_id')
                    ->nullable()
                    ->constrained('tests')
                    ->nullOnDelete();

                $table->index(['test_id', 'created_at'], 'idx_test_distribution_candidates_test_id_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('test_distribution_candidates', 'test_id')) {
            Schema::table('test_distribution_candidates', function (Blueprint $table) {
                $table->dropForeign(['test_id']);
                $table->dropIndex('idx_test_distribution_candidates_test_id_created_at');
                $table->dropColumn('test_id');
            });
        }
    }
};


