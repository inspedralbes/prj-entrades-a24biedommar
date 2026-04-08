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

# Script d'entrada (fitxer apart per evitar problemes CRLF amb heredoc a Windows)
COPY docker/php/docker-entrypoint-laravel.sh /usr/local/bin/docker-entrypoint-laravel.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint-laravel.sh && chmod +x /usr/local/bin/docker-entrypoint-laravel.sh

ENTRYPOINT ["/bin/sh", "/usr/local/bin/docker-entrypoint-laravel.sh"]
