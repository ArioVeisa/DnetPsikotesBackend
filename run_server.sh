#!/bin/bash

echo "🚀 Starting Laravel server with SQLite..."

# Kill any existing server
pkill -f "php artisan serve" 2>/dev/null

# Set environment variables
export DB_CONNECTION=sqlite
export DB_DATABASE=/home/xrey/Documents/code/DnetPsikotesBackend/database/database.sqlite

echo "Database: $DB_CONNECTION"
echo "Database file: $DB_DATABASE"

# Start server
php artisan serve --host=127.0.0.1 --port=8000
