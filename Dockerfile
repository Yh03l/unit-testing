# Use the official PHP image with Apache as the base image
FROM php:8.2.4-apache

# Set working directory
WORKDIR /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install system dependencies
RUN apt-get update -y && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    unzip zip \
    libpq-dev \
    libffi-dev \
    cron \
    supervisor \
    libicu-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install mbstring exif pcntl bcmath gd zip pdo_pgsql sockets intl

# Install FFI extension
RUN docker-php-ext-install ffi

# Install and configure Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9202" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Configure GD library
RUN docker-php-ext-configure gd --enable-gd \
    && docker-php-ext-install -j$(nproc) gd

# Copy Composer from the official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents to the working directory
COPY . /var/www/html

# Copy the Apache vhost file into the container to configure Apache
COPY vhost/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy and setup the events processing script
COPY scripts/process-events.sh /usr/local/bin/process-events.sh
RUN chmod +x /usr/local/bin/process-events.sh

# Create log directory for Supervisor
RUN mkdir -p /var/log/supervisor && \
    chown -R www-data:www-data /var/log/supervisor

# Set proper permissions for the web directory
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Set proper permissions for the storage directory
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 755 /var/www/html/storage

# Install project dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Enable the vhost configuration
RUN a2ensite 000-default.conf

# Copy the custom post-deployment script
COPY post_deploy.sh /usr/local/bin/post_deploy.sh
RUN chmod +x /usr/local/bin/post_deploy.sh

# Expose port 80 and start Apache server
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/post_deploy.sh"]
