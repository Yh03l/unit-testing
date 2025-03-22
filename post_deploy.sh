#!/bin/bash

# Custom post-deployment script for Laravel

# Copy the .env.example to .env
cp /var/www/html/.env.example /var/www/html/.env

# Generate the Laravel application key
php artisan key:generate

php artisan migrate


# Start Apache (this keeps the container running)
apache2-foreground
