# ======================
# STAGE 1: FRONTEND BUILD
# ======================
FROM node:20 AS frontend

WORKDIR /app

# Copy dependency dulu (biar cache optimal)
COPY package*.json ./

RUN npm install

# Copy semua file
COPY . .

# Build Vite
RUN npm run build


# ======================
# STAGE 2: LARAVEL APP
# ======================
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    curl \
    zip \
    unzip \
    git \
    supervisor \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy semua project
COPY . .

# Copy hasil build frontend dari stage 1
COPY --from=frontend /app/public/build ./public/build

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permission
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy config nginx & supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]