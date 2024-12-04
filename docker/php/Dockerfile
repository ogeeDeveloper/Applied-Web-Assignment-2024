FROM php:8.2-fpm
 
# Install system dependencies

RUN apt-get update && apt-get install -y \

    git \

    curl \

    libpng-dev \

    libonig-dev \

    libxml2-dev \

    zip \

    unzip \

    default-mysql-client \

    dos2unix
 
# Install PHP extensions

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
 
# Install Composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
 
# Set working directory

WORKDIR /var/www
 
# Copy composer files first

COPY composer.json composer.lock ./
 
# Install dependencies

RUN composer install --no-scripts --no-autoloader --no-dev
 
# Copy the rest of the application

COPY . .
 
# Generate autoloader

RUN composer dump-autoload --optimize
 
# Fix line endings and set permissions

RUN dos2unix /var/www/docker/php/docker-entrypoint.sh && \

    chmod +x /var/www/docker/php/docker-entrypoint.sh && \

    chown -R www-data:www-data /var/www && \

    chmod -R 755 /var/www/storage
 
CMD ["/bin/sh", "/var/www/docker/php/docker-entrypoint.sh"]
 

