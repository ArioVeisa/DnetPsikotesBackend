#!/bin/bash

echo "🧪 Testing Docker setup dengan PostgreSQL..."

# Cek apakah Docker berjalan
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker tidak berjalan. Silakan start Docker terlebih dahulu."
    exit 1
fi

# Cek apakah docker-compose tersedia
if ! command -v docker-compose &> /dev/null; then
    echo "❌ docker-compose tidak ditemukan. Silakan install docker-compose."
    exit 1
fi

echo "✅ Docker dan docker-compose tersedia"

# Test koneksi database
echo ""
echo "🔍 Testing koneksi PostgreSQL..."
docker-compose exec app php test-db-connection.php

echo ""
echo "🧪 Testing Laravel database connection..."
docker-compose exec app php artisan tinker --execute="DB::connection()->getPdo(); echo 'Laravel database connection: OK';"

echo ""
echo "📊 Testing migrations..."
docker-compose exec app php artisan migrate:status

echo ""
echo "✅ Semua test selesai!"

