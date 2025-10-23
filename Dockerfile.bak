FROM php:8.3-fpm

# tools & libs untuk ekstensi (gd, sodium, zip, intl, postgresql)
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    libzip-dev zlib1g-dev libonig-dev libsodium-dev pkg-config build-essential \
    libxml2-dev libpq-dev postgresql-client && \
    docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip bcmath sodium && \
    pecl install xdebug-3.4.4 || true && docker-php-ext-enable xdebug || true && \
    rm -rf /var/lib/apt/lists/*
    
# copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# jangan change owner supaya gampang (opsional: add user)


