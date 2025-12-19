# syntax=docker/dockerfile:1

ARG APP_ENV=production

############# STAGE 1: Composer #############
FROM php:8.2-cli AS vendor
WORKDIR /app

# System deps + PHP extensions required by composer.json
RUN apt-get update && apt-get install -y \
    git unzip zip \
    libzip-dev \
  && docker-php-ext-install zip bcmath \
  && rm -rf /var/lib/apt/lists/*

# Copy composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

ARG APP_ENV

RUN if [ "$APP_ENV" = "local" ]; then \
        composer install --prefer-dist --no-interaction; \
    else \
        composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; \
    fi

############# STAGE 2: Node Build (Vite) #############
FROM node:20 AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY vite.config.* ./
COPY postcss.config.* ./
COPY tailwind.config.* ./

RUN npm run build

############# STAGE 3: Runtime Image #############
FROM php:8.2-fpm
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql gd zip bcmath \
  && docker-php-ext-enable opcache \
  && rm -rf /var/lib/apt/lists/*

# App source
COPY . .

# Vendor & assets
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Env
COPY .env.docker .env

# App key
RUN php artisan key:generate --force

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm"]
