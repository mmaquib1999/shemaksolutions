# syntax=docker/dockerfile:1

# --- Composer dependencies ---
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

# --- Frontend build (Vite/NPM) ---
FROM node:20 AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY vite.config.* ./
COPY postcss.config.* ./
COPY tailwind.config.* ./

RUN npm run build

# --- Runtime PHP-FPM image ---
FROM php:8.2-fpm
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev zip \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql gd zip bcmath \
  && docker-php-ext-enable opcache \
  && rm -rf /var/lib/apt/lists/*

# Copy app
COPY . ./

# Copy vendor + built assets
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm"]
