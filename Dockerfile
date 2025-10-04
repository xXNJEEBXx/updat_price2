# Stage 1: Install PHP dependencies with Composer
FROM composer/composer:2-php82 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Stage 2: Build frontend assets
FROM node:18 as frontend
WORKDIR /app
COPY package.json ./
RUN npm install
COPY . .
RUN npm run build

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

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy application code and built assets
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor/ ./vendor/
COPY --chown=www-data:www-data --from=frontend /app/public/build ./public/build

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80 and start Apache
EXPOSE 80
CMD ["apache2-foreground"]

