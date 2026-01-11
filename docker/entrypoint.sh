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

# Ensure permissions are correct at runtime
echo "Fixing permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Dump autoloader to ensure it's fresh
echo "Dumping autoloader..."
composer dump-autoload --optimize

# Register packages
echo "Discovering packages..."
php artisan package:discover --ansi

# Cache configuration, events, routes, and views for production
echo "Caching configuration..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx on port $PORT..."
nginx -g "daemon off;"
