# Setup Docker dengan PostgreSQL untuk DNet Psikotes

## Prerequisites
- Docker dan Docker Compose terinstall
- Port 8000 dan 5432 tidak digunakan aplikasi lain

## Quick Start

### 1. Jalankan aplikasi dengan script otomatis
```bash
chmod +x docker-start.sh
./docker-start.sh
```

### 2. Manual setup
```bash
# Build dan start container
docker-compose up --build -d

# Generate app key
docker-compose exec app php artisan key:generate

# Install dependencies
docker-compose exec app composer install

# Jalankan migration
docker-compose exec app php artisan migrate --force

# Seed database
docker-compose exec app php artisan db:seed --force
```

## Akses Aplikasi

- **Web Application**: http://localhost:8000
- **PostgreSQL Database**: localhost:5432
  - Database: laravel
  - Username: laravel
  - Password: secret

## Command yang Berguna

```bash
# Lihat logs aplikasi
docker-compose logs -f

# Lihat logs database
docker-compose logs -f db

# Masuk ke container aplikasi
docker-compose exec app bash

# Masuk ke database PostgreSQL
docker-compose exec db psql -U laravel -d laravel

# Stop aplikasi
docker-compose down

# Restart aplikasi
docker-compose restart

# Rebuild container
docker-compose up --build -d

# Jalankan Artisan command
docker-compose exec app php artisan [command]
```

## Troubleshooting

### Database connection error
```bash
# Cek status container
docker-compose ps

# Restart database
docker-compose restart db

# Cek logs database
docker-compose logs db
```

### Permission error
```bash
# Set permission untuk storage
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Port sudah digunakan
```bash
# Stop aplikasi yang menggunakan port 8000
sudo lsof -ti:8000 | xargs kill -9

# Atau ubah port di docker-compose.yml
```

## Konfigurasi Database

Konfigurasi PostgreSQL sudah diatur di `docker-compose.yml`:
- Host: db (internal Docker network)
- Port: 5432
- Database: laravel
- Username: laravel
- Password: secret

Data akan persisten di volume Docker `dbdata`.

