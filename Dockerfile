# Stage 1: Build frontend assets using Node
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Main application stage using PHP-FPM
FROM php:8.2-fpm

# Install system dependencies, Nginx, and Supervisor
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    unzip \
    git \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel & Excel/PDF packages
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        bcmath \
        mbstring \
        gd \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Copy Composer from official image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Set work directory
WORKDIR /var/www/html

# Copy application source files
COPY . .

# Copy compiled frontend assets from Stage 1
COPY --from=node-builder /app/public/build ./public/build

# Run Composer installation for production
RUN composer update ladumor/laravel-pwa --no-interaction --ignore-platform-reqs --no-scripts 2>/dev/null || true \
    && composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --ignore-platform-reqs

# Copy PHP OPcache and FPM pool configuration
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zzz-app.conf

# Copy Nginx virtual host configuration
COPY docker/nginx/default.conf /etc/nginx/sites-enabled/default

# Copy Supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Copy entrypoint script and make it executable
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create folder for Supervisor logs
RUN mkdir -p /var/log/supervisor

# Expose HTTP port
EXPOSE 80

# Configure entrypoint and start command
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
