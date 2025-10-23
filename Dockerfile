FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \
    libsodium-dev \
    pkg-config \
    build-essential \
    libxml2-dev \
    libpq-dev \
    postgresql-client \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_pgsql \
        zip \
        bcmath \
        sodium \
        intl \
        opcache \
    && pecl install xdebug-3.4.4 || true \
    && docker-php-ext-enable xdebug || true \
    && rm -rf /var/lib/apt/lists/*

# Copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Expose port
EXPOSE 8000

# Start PHP-FPM
CMD ["php-fpm"]
