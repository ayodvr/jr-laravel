#!/bin/bash

# Install dependencies if vendor is missing (for first run)
if [ ! -d "vendor" ]; then
    composer install
fi

# Run migrations
php artisan migrate --force

# Start php-fpm
php-fpm
