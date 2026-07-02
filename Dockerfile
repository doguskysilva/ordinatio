FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_sqlite pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create database directory
RUN mkdir -p /var/database && chmod 777 /var/database

# Install dependencies
RUN composer install --no-interaction

# Create necessary directories
RUN mkdir -p storage/logs && chmod -R 775 storage bootstrap/cache

# Generate app key if not set
RUN if [ -z "$APP_KEY" ]; then php artisan key:generate; fi

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
