# --- Stage 1: GCS Fuse Builder ---
FROM golang:1.24-alpine AS gcsfuse-builder

RUN apk add --no-cache git fuse3
RUN git clone https://github.com/GoogleCloudPlatform/gcsfuse.git /gcsfuse
WORKDIR /gcsfuse
RUN mkdir -p /tmp/gcsfuse-build
RUN go run ./tools/build_gcsfuse . /tmp/gcsfuse-build "$(git rev-parse --short HEAD)"


# --- Stage 2: Composer/Laravel Builder ---
FROM composer:2.7 as builder

WORKDIR /app

# 1. Create a full, working Laravel project. This creates the correct /public directory.
RUN composer create-project --prefer-dist laravel/laravel .

# 2. Copy the host's /src directory.
# Because of src/.dockerignore, this will copy your custom app, resources, routes,
# composer.json etc., BUT IT WILL NOT COPY THE `public` DIRECTORY.
# The correct /public directory from step 1 is preserved. This is the fix.
COPY . .

# 3. We have a new composer.json, so we must regenerate the lock file.
RUN rm composer.lock
RUN composer install --optimize-autoloader --no-dev --no-scripts

# 4. As a best practice, remove stale cache files that might reference old packages.
RUN rm -f bootstrap/cache/*.php

# 5. Set final permissions on the fully-built and correct code.
RUN chown -R www-data:www-data /app


# --- Stage 3: Final 'app' Image ---
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install dependencies
RUN apk add --no-cache \
    fuse3 libzip-dev libjpeg-turbo-dev libpng-dev gmp-dev icu-dev

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) zip pdo_mysql gmp intl opcache

# Copy the gcsfuse binary from the builder
COPY --from=gcsfuse-builder /tmp/gcsfuse-build/bin/gcsfuse /usr/bin/gcsfuse

# Copy the clean, fully-built Laravel application
COPY --from=builder /app .

# Copy and set up the entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]