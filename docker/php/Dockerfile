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
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer.json only (not the lock file)
COPY composer.json .

# Install dependencies and generate lock file
RUN composer update --no-scripts --no-autoloader

# Copy existing application directory
COPY . .

# Generate autoload files
RUN composer dump-autoload --optimize

# Fix permissions
RUN chown -R www-data:www-data /var/www