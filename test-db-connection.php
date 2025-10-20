<?php
/**
 * Script untuk test koneksi PostgreSQL dalam Docker menggunakan PDO
 */

echo "🔍 Testing koneksi PostgreSQL dengan PDO...\n\n";

// Konfigurasi database dari docker-compose.yml
$host = 'db';
$port = '5432';
$database = 'laravel';
$username = 'laravel';
$password = 'secret';

echo "📋 Konfigurasi Database:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";

try {
    // Test koneksi dengan PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$database";
    echo "🔗 Mencoba koneksi dengan: $dsn\n";
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Koneksi berhasil!\n\n";
    
    // Test query sederhana
    echo "🧪 Testing query...\n";
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "📊 PostgreSQL Version: " . $version . "\n";
    
    // Test query database info
    echo "\n📋 Database Information:\n";
    $stmt = $pdo->query("SELECT current_database(), current_user, inet_server_addr(), inet_server_port()");
    $info = $stmt->fetch(PDO::FETCH_NUM);
    
    echo "Database: " . $info[0] . "\n";
    echo "User: " . $info[1] . "\n";
    echo "Server IP: " . ($info[2] ?: 'localhost') . "\n";
    echo "Server Port: " . $info[3] . "\n";
    
    // Test list tables
    echo "\n📝 Daftar Tables:\n";
    $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if ($tables) {
        foreach ($tables as $table) {
            echo "- " . $table . "\n";
        }
    } else {
        echo "Tidak ada tables ditemukan.\n";
    }
    
    echo "\n✅ Test koneksi berhasil!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "1. Pastikan Docker container 'db' sudah running\n";
    echo "2. Cek dengan: docker-compose ps\n";
    echo "3. Restart database: docker-compose restart db\n";
    echo "4. Cek logs: docker-compose logs db\n";
    exit(1);
}
