#!/bin/bash

# Script untuk menjalankan aplikasi Laravel dengan Docker dan PostgreSQL

echo "🚀 Memulai aplikasi Laravel dengan Docker dan PostgreSQL..."

# Stop container yang mungkin sedang berjalan
echo "🛑 Menghentikan container yang sedang berjalan..."
docker-compose down

# Build dan start container
echo "🔨 Building dan starting container..."
docker-compose up --build -d

# Tunggu database siap
echo "⏳ Menunggu database PostgreSQL siap..."
sleep 10

# Generate app key jika belum ada
echo "🔑 Generate application key..."
docker-compose exec app php artisan key:generate

# Install dependencies
echo "📦 Install dependencies..."
docker-compose exec app composer install

# Jalankan migration
echo "🗄️ Menjalankan database migration..."
docker-compose exec app php artisan migrate --force

# Seed database jika ada
echo "🌱 Seeding database..."
docker-compose exec app php artisan db:seed --force

# Set permission untuk storage
echo "🔐 Setting permission untuk storage..."
docker-compose exec app chmod -R 775 storage bootstrap/cache

echo "✅ Aplikasi berhasil dijalankan!"
echo "🌐 Akses aplikasi di: http://localhost:8000"
echo "🗄️ PostgreSQL tersedia di: localhost:5432"
echo ""
echo "📋 Command yang berguna:"
echo "  - Lihat logs: docker-compose logs -f"
echo "  - Masuk ke container: docker-compose exec app bash"
echo "  - Stop aplikasi: docker-compose down"
echo "  - Restart aplikasi: docker-compose restart"

