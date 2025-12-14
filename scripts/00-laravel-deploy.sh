#!/bin/bash
echo "Running custom deployment script..."

# Cache configuration, routes, and views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

echo "Deployment script completed."
