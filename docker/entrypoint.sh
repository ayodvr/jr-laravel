#!/bin/bash
set -e

git config --global --add safe.directory /var/www

# Default to port 80 if PORT is not set
PORT=${PORT:-80}

# Update Nginx port
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/conf.d/default.conf

# Log Nginx config for debugging
echo "Verifying Nginx configuration..."
nginx -t

# Clear existing cache to prevent stale config issues
echo "Clearing caches..."
rm -f bootstrap/cache/*.php

# Register packages and clear caches
echo "Discovering packages..."
php artisan package:discover --ansi

echo "Clearing config/view/route caches..."
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx on port $PORT..."
nginx -g "daemon off;"
