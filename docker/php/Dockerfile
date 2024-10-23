# Use the official PHP 8.3 FPM image as the base
FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory
WORKDIR /var/www

# Copy composer files first for better build caching
COPY composer.json composer.lock ./

# Update composer lock file to ensure dependencies match
RUN composer install --no-dev --optimize-autoloader

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application files
COPY public /var/www/public
COPY src /var/www/src
COPY config /var/www/config

# Fix permissions
# RUN chown -R www-data:www-data /var/www
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
