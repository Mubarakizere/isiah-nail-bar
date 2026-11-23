#!/bin/bash
echo "Running custom deployment script..."

cd /var/www/html/backend

# Cache configuration and API routes. The Next.js frontend owns all page rendering.
php artisan config:cache
php artisan route:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

echo "Deployment script completed."
