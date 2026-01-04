# Stage 1: Install PHP dependencies with Composer
FROM php:8.2 as vendor
RUN apt-get update && apt-get install -y unzip git libzip-dev && docker-php-ext-install zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts

# Stage 2: Build frontend assets
FROM node:18 as frontend
WORKDIR /app
COPY package.json ./
RUN npm install
COPY . .
RUN npm run production

# Stage 3: Create the production image
FROM php:8.2-apache
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip exif pcntl bcmath gd

# Copy Apache virtual host configuration
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Fix MPM conflict: Remove all MPM modules first, then enable only prefork
RUN a2dismod mpm_worker 2>/dev/null || true && \
    a2dismod mpm_event 2>/dev/null || true && \
    a2dismod mpm_prefork 2>/dev/null || true && \
    a2enmod mpm_prefork

# Enable Apache rewrite module and set ServerName globally
RUN a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy application code and built assets
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor/ ./vendor/
COPY --chown=www-data:www-data --from=frontend /app/public/js ./public/js
COPY --chown=www-data:www-data --from=frontend /app/public/css ./public/css

# Create .env from .env.example if not exists
RUN if [ ! -f .env ]; then cp .env.example .env 2>/dev/null || echo "APP_NAME=Laravel" > .env; fi

# Ensure storage directories exist
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Warm up Laravel's package manifest
RUN php artisan package:discover --ansi || true

# Make start.sh executable
RUN chmod +x /var/www/html/start.sh

# Use start.sh as the entrypoint
CMD ["/bin/bash", "/var/www/html/start.sh"]

