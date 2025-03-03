#!/bin/bash

# Exit on error
set -e

# Load environment variables
source .env

# Print deployment start
echo "Starting deployment..."

# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear caches
php artisan clear-compiled
php artisan cache:clear

# Migrate database
php artisan migrate --force

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Update permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Clear opcache
echo "<?php opcache_reset(); ?>" > /tmp/opcache-reset.php
curl -s http://localhost/opcache-reset.php
rm /tmp/opcache-reset.php

echo "Deployment completed successfully!" 