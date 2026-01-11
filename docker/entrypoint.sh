#!/bin/bash
git config --global --add safe.directory /var/www

# Default to port 80 if PORT is not set
PORT=${PORT:-80}

# Update Nginx port
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/conf.d/default.conf

# Log Nginx config for debugging
echo "Verifying Nginx configuration..."
nginx -t

# Register packages and clear caches
php artisan package:discover --ansi
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Run migrations
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx on port $PORT..."
nginx -g "daemon off;"
