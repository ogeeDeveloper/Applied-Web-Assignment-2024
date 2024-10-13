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

# Install Composer globally inside the container
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory
WORKDIR /var/www/html

# Copy application code to the container
COPY src/ /var/www/html/

# Check if composer.json exists and is valid, then install dependencies
RUN if [ -f "composer.json" ] && composer validate --no-check-lock --strict; then composer install --no-dev --no-interaction --optimize-autoloader; else echo "No valid composer.json found, skipping composer install"; fi

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
