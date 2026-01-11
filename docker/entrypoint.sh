#!/bin/bash
git config --global --add safe.directory /var/www

# Install dependencies if vendor is missing (for first run)
if [ ! -f "vendor/autoload.php" ]; then
    composer install
fi

# Run migrations
php artisan migrate --force

# Start php-fpm
php-fpm
