#!/bin/bash
set -e

# Use Railway's PORT or default to 80
export PORT="${PORT:-80}"

# Set ServerName globally to suppress Apache warning
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Update Apache configuration with the actual PORT value
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

# Start Apache
exec apache2-foreground
