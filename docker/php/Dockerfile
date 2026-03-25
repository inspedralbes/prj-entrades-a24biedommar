# 🐘 PHP 8.3-FPM Dockerfile for Laravel 13
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    libpq-dev \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    zlib-dev \
    autoconf \
    gcc \
    g++ \
    make \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install pdo pdo_pgsql mbstring gd zip bcmath opcache intl

# Install Redis extension (optional but useful for laravel+redis)
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup entrypoint to install deps if missing
COPY <<EOF /usr/local/bin/docker-entrypoint-laravel.sh
#!/bin/sh
set -e

if [ ! -f "composer.lock" ]; then
  echo "Composer dependencies missing. Installing..."
  composer install --no-interaction --no-scripts --no-autoloader
  composer dump-autoload --optimize
fi

# Run php-fpm (default) or serve (for easy dev)
# If using serve, we use port 8000
php artisan serve --host=0.0.0.0 --port=8000
EOF

RUN chmod +x /usr/local/bin/docker-entrypoint-laravel.sh

# Entrypoint script config
ENTRYPOINT ["docker-entrypoint-laravel.sh"]
