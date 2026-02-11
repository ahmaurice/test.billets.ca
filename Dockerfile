FROM php:8.2-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    bash \
    mysql-client \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-dev || true

# Copy application files
COPY . .

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Make console executable
RUN chmod +x /app/bin/console || true

# Default command
CMD ["php", "bin/console", "list"]
