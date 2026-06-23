FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libfreetype6-dev \
    zlib-dev \
    libzip-dev \
    sqlite \
    git \
    composer \
    nodejs \
    npm \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    zip \
    pdo \
    pdo_sqlite \
    fileinfo

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application files
COPY . .

# Copy NPM dependencies and build assets
COPY package.json package-lock.json ./
RUN npm ci && npm run build

# Create necessary directories
RUN mkdir -p storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /app

# Generate application key
RUN php artisan key:generate --force || true

# Expose port
EXPOSE 8000

# Run Laravel development server
CMD php artisan serve --host=0.0.0.0 --port=8000
