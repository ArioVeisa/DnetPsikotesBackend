#!/bin/bash

# Script untuk menjalankan server Laravel dengan SQLite
echo "🚀 Starting Laravel server with SQLite..."

# Set environment variables untuk SQLite
export DB_CONNECTION=sqlite
export DB_DATABASE=/home/xrey/Documents/code/DnetPsikotesBackend/database/database.sqlite

# Jalankan server
php artisan serve --host=127.0.0.1 --port=8000
