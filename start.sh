#!/bin/bash
set -e

# Use Railway's PORT or default to 80, and sanitize it to a numeric value
PORT_RAW="${PORT:-80}"

# Extract numeric port: handle values like "3000" or "0.0.0.0:3000" or "tcp://0.0.0.0:3000"
if [[ "$PORT_RAW" =~ ^[0-9]+$ ]]; then
	PORT_NUM="$PORT_RAW"
elif [[ "$PORT_RAW" =~ :([0-9]+)$ ]]; then
	PORT_NUM="${BASH_REMATCH[1]}"
else
	echo "[start.sh] WARN: Invalid PORT '$PORT_RAW'. Falling back to 8080."
	PORT_NUM=8080
fi

# Validate range 1-65535
if ! [[ "$PORT_NUM" =~ ^[0-9]+$ ]] || [ "$PORT_NUM" -lt 1 ] || [ "$PORT_NUM" -gt 65535 ]; then
	echo "[start.sh] WARN: PORT out of range ('$PORT_NUM'). Falling back to 8080."
	PORT_NUM=8080
fi

export PORT="$PORT_NUM"

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

# Update Apache configuration with the actual PORT value safely
echo "[start.sh] Configuring Apache to listen on port $PORT ..."
# Replace any numeric Listen line with our PORT (keeps 443 for SSL intact)
sed -ri "s/^Listen[[:space:]]+[0-9]+$/Listen ${PORT}/" /etc/apache2/ports.conf || true
# If no matching Listen line found for our port, append it
if ! grep -qE "^Listen[[:space:]]+${PORT}$" /etc/apache2/ports.conf; then
	echo "Listen ${PORT}" >> /etc/apache2/ports.conf
fi

# Update VirtualHost port
sed -ri "s#<VirtualHost \*:[0-9]+>#<VirtualHost *:${PORT}>#" /etc/apache2/sites-available/000-default.conf || true

# Test if PHP is working
echo "[start.sh] Testing PHP..."
php -v

# Test if the health endpoint works
echo "[start.sh] Testing health endpoint..."
php /var/www/html/public/health.php

# Start Apache
echo "[start.sh] Starting Apache on port $PORT..."
exec apache2-foreground
