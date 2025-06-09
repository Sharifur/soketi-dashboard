FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libsqlite3-dev \
    sqlite3 \
    zip \
    unzip \
    nginx \
    supervisor

# Create directory for PHP logs
RUN mkdir -p /var/log/php && chmod 777 /var/log/php

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip intl
RUN pecl install redis && docker-php-ext-enable redis

# Copy custom PHP configuration
COPY docker-php.ini /usr/local/etc/php/conf.d/docker-php-custom.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/soketi-admin

# Copy app files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Create SQLite file if missing
RUN mkdir -p database && touch database/database.sqlite

# Permissions
RUN chown -R www-data:www-data /var/www/soketi-admin \
    && chmod -R 775 storage bootstrap/cache database

# Expose port
EXPOSE 8000

# Start Laravel dev server
CMD php artisan serve --host=0.0.0.0 --port=8000
