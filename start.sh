#!/bin/bash
set -e

# Use Railway's PORT or default to 80
export PORT="${PORT:-80}"

# Ensure we are in the application directory
cd /var/www/html

# If no .env file exists, fall back to the example shipped with the app
if [ ! -f .env ]; then
	cp .env.example .env 2>/dev/null || echo "APP_KEY=" > .env
fi

# Generate an APP_KEY if one is not provided via environment variables
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "null" ]; then
	echo "[start.sh] APP_KEY not provided. Generating a new key..."
	php artisan key:generate --force
	export APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f2-)
fi

# Refresh cached configuration to pick up the latest environment values
php artisan config:clear
php artisan config:cache

# Ensure Laravel storage permissions are correct
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Update Apache configuration with the actual PORT value
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

# Test if PHP is working
echo "[start.sh] Testing PHP..."
php -v

# Test if the health endpoint works
echo "[start.sh] Testing health endpoint..."
php /var/www/html/public/health.php

# Start Apache
echo "[start.sh] Starting Apache on port $PORT..."
exec apache2-foreground
