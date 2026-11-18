# Frontend assets: Node LTS + Vite/Tailwind/Tiptap.
FROM node:22-alpine AS frontend-assets
WORKDIR /app
COPY . .
RUN if [ -f package-lock.json ]; then npm ci --no-audit --no-fund; else npm install --no-audit --no-fund; fi
RUN npm run build

# PHP-FPM runtime.
FROM php:8.4-fpm-bookworm AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip zip libzip-dev libicu-dev libpng-dev libjpeg62-turbo-dev \
    libfreetype6-dev libwebp-dev libonig-dev libxml2-dev libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_sqlite bcmath exif gd intl opcache zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN if [ -f composer.lock ]; then \
      composer install --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader; \
    else \
      composer update --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader; \
    fi
COPY --from=frontend-assets /app/public/build ./public/build
# Create Laravel writable directories BEFORE running Artisan package discovery.
# Use explicit paths because Docker's /bin/sh is not guaranteed to support Bash brace expansion.
RUN mkdir -p \
      storage/framework/cache/data \
      storage/framework/sessions \
      storage/framework/views \
      storage/logs \
      storage/app/public \
      bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache \
    && composer dump-autoload --optimize --no-scripts \
    && php artisan package:discover --ansi

RUN printf '%s\n' 'upload_max_filesize=6M' 'post_max_size=8M' 'memory_limit=256M' 'max_execution_time=60' > /usr/local/etc/php/conf.d/news-portal.ini
COPY docker/entrypoint.sh /usr/local/bin/news-portal-entrypoint
RUN chmod +x /usr/local/bin/news-portal-entrypoint

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=192.168.109.128" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

EXPOSE 9000
ENTRYPOINT ["news-portal-entrypoint"]
CMD ["php-fpm"]
