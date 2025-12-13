# syntax=docker/dockerfile:1

### ARG to toggle environment
ARG APP_ENV=production

############# STAGE 1: Composer #############
FROM composer:2 AS vendor
WORKDIR /app

COPY . .

RUN if [ "$APP_ENV" = "local" ] ; then \
        composer install --prefer-dist --no-interaction; \
    else \
        composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; \
    fi

############# STAGE 2: Node Build (Vite)
FROM node:20 AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY vite.config.* ./
COPY postcss.config.* ./
COPY tailwind.config.* ./

RUN npm run build

############# STAGE 3: Runtime Image
FROM php:8.2-fpm
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql gd zip bcmath \
  && docker-php-ext-enable opcache \
  && rm -rf /var/lib/apt/lists/*

# Copy app source
COPY . .

# Copy dependencies & assets
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Copy env file
RUN if [ -f .env.docker ]; then cp .env.docker .env; fi

# Generate Laravel app key
RUN php artisan key:generate --force

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm"]
