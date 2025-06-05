#!/bin/bash

# Custom post-deployment script for Laravel

# Copy the .env.example to .env
cp /var/www/html/.env.example /var/www/html/.env

# Generate the Laravel application key
php artisan key:generate

php artisan migrate

# Configurar cron para publicar eventos
echo "* * * * * cd /var/www/html && php artisan commercial:publish-events >> /var/log/cron.log 2>&1" > /etc/cron.d/publish-events
chmod 0644 /etc/cron.d/publish-events
crontab /etc/cron.d/publish-events

# Iniciar cron
service cron start

# Start Apache (this keeps the container running)
apache2-foreground
