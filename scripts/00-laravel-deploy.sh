#!/usr/bin/env bash

echo "Installing Packages"
composer update --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts -o --working-dir=/var/www/html

echo "Clearing old caches..."
php artisan cache:clear

echo "Caching routes and configuration..."
php artisan config:cache
php artisan trail:generate
php artisan route:cache

echo "Running vite..."
npm install --prefer-offline --no-audit --progress=false
npm run build
