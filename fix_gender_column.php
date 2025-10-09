<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Candidate;

echo "🔧 Memperbaiki kolom gender di database...\n";

try {
    // Cek koneksi database
    $connection = config('database.default');
    echo "Database connection: {$connection}\n";
    
    // Cek apakah tabel candidates ada
    if (!Schema::hasTable('candidates')) {
        echo "❌ Tabel candidates tidak ada. Membuat tabel...\n";
        
        Schema::create('candidates', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('nik')->unique();
            $table->string('phone_number');
            $table->string('email')->unique();
            $table->string('position');
            $table->date('birth_date');
            $table->string('gender'); // Gunakan string, bukan enum
            $table->string('department');
            $table->timestamps();
        });
        
        echo "✅ Tabel candidates berhasil dibuat\n";
    } else {
        echo "✅ Tabel candidates sudah ada\n";
        
        // Cek struktur kolom gender
        $columns = Schema::getColumnListing('candidates');
        echo "Kolom yang ada: " . implode(', ', $columns) . "\n";
        
        if (in_array('gender', $columns)) {
            echo "✅ Kolom gender sudah ada\n";
        } else {
            echo "❌ Kolom gender tidak ada. Menambahkan...\n";
            Schema::table('candidates', function ($table) {
                $table->string('gender')->after('birth_date');
            });
            echo "✅ Kolom gender berhasil ditambahkan\n";
        }
    }
    
    // Test membuat kandidat
    echo "\n🧪 Testing pembuatan kandidat...\n";
    
    $candidate = Candidate::create([
        'nik' => '1234567890123456',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone_number' => '081234567890',
        'position' => 'Staff',
        'birth_date' => '1990-01-01',
        'gender' => 'female', // Test dengan female
        'department' => 'HRD'
    ]);
    
    echo "✅ Kandidat berhasil dibuat: {$candidate->name} ({$candidate->gender})\n";
    
    // Clean up
    $candidate->delete();
    echo "✅ Test data berhasil dihapus\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎉 Proses selesai!\n";
