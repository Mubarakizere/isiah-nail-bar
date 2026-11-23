# Base image with Nginx + PHP-FPM
FROM richarvey/nginx-php-fpm:latest

# Laravel backend lives in its own directory in the monorepo.
WORKDIR /var/www/html/backend

# Copy backend application code. Frontend builds from frontend/Dockerfile.
COPY backend/ /var/www/html/backend/
RUN rm -f /var/www/html/backend/public/storage /var/www/html/backend/public/storage-old-link \
    && mkdir -p /var/www/html/backend/storage/app/public \
    && ln -s /var/www/html/backend/storage/app/public /var/www/html/backend/public/storage

# Image config
ENV SKIP_COMPOSER 0
ENV WEBROOT /var/www/html/backend/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV=production
ENV LOG_CHANNEL=stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy deploy script
COPY backend/scripts/00-laravel-deploy.sh /scripts/00-laravel-deploy.sh
RUN chmod +x /scripts/00-laravel-deploy.sh

# Start script provided by the image
CMD ["/start.sh"]
