#!/bin/bash

echo "🔄 Restart Docker dengan konfigurasi yang diperbaiki..."

# Stop dan remove container yang ada
echo "🛑 Menghentikan dan menghapus container yang ada..."
docker-compose down -v

# Remove image yang ada untuk rebuild
echo "🗑️ Menghapus image yang ada..."
docker-compose build --no-cache

# Start container dengan konfigurasi baru
echo "🚀 Menjalankan container dengan konfigurasi baru..."
docker-compose up -d

# Tunggu database siap
echo "⏳ Menunggu database PostgreSQL siap..."
sleep 15

# Generate app key
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

echo "✅ Aplikasi berhasil di-restart!"
echo "🌐 Akses aplikasi di: http://localhost:8000"
echo "🗄️ PostgreSQL tersedia di: localhost:5432"





