# syntax=docker/dockerfile:1.7

# Keep readable release tags while immutable digests make every build auditable.
ARG PHP_IMAGE=php:8.4.25-fpm-alpine3.24@sha256:60ad95af451cc1b0e435ece74816798143658c1b615c1b7ec09f01fed73fca08
ARG COMPOSER_IMAGE=composer:2.10.3@sha256:4d045ea9f71d5d111a95e608400da61d187e487adf9eaf2dfe068998a8d4f584
ARG NODE_IMAGE=node:24.20.0-alpine3.24@sha256:e67514e5d0f6c46656005e1b693b2ec9d52e80b641307de684d4a015ba7a4eaf

# =============================================================================
# STAGE 1: Install deterministic production PHP dependencies
# =============================================================================
FROM ${COMPOSER_IMAGE} AS vendor

# Isolate dependency resolution from the application runtime filesystem.
WORKDIR /app

# Copy manifests first so application edits do not invalidate the vendor cache.
COPY composer.json composer.lock ./

# Exclude development packages and lifecycle scripts from the production graph.
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --prefer-dist \
        --classmap-authoritative

# =============================================================================
# STAGE 2: Compile deterministic frontend assets
# =============================================================================
FROM ${NODE_IMAGE} AS frontend

# Build assets outside the runtime so Node.js never enters the final image.
WORKDIR /app

# Install exactly the dependency graph recorded by npm's lock file.
COPY package.json package-lock.json ./

RUN --mount=type=cache,target=/root/.npm \
    npm ci --ignore-scripts --no-audit --no-fund

COPY resources ./resources
COPY vite.config.js ./
# Flux participates in Tailwind discovery, so the frontend build needs vendor.
COPY --from=vendor /app/vendor ./vendor

# Emit production assets only; node_modules remains in this disposable stage.
RUN npm run build

# =============================================================================
# STAGE 3: Assemble the rootless production runtime
# =============================================================================
FROM ${PHP_IMAGE} AS runtime

# Install only runtime libraries and compile the PHP extensions used by Laravel.
# Build dependencies are removed in the same layer to reduce the attack surface.
RUN apk add --no-cache \
        icu-libs \
        libpq \
        libzip \
        nginx=1.30.4-r1 \
        oniguruma \
        supervisor=4.3.0-r1 \
    && apk add --no-cache --virtual .build-dependencies \
        $PHPIZE_DEPS \
        icu-dev \
        libpq-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        zip \
    && apk del .build-dependencies \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# The Docker context excludes secrets, tests, documentation, and local artifacts.
COPY --chown=www-data:www-data . .
# Copy only immutable build outputs from the disposable dependency stages.
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build
# Keep service configuration versioned alongside the application it serves.
# Replace both Alpine's global defaults and its default virtual host explicitly.
COPY docker/production/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/production/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/production/php-fpm/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/production/php-fpm/production.ini /usr/local/etc/php/conf.d/zz-production.ini
COPY docker/production/php-fpm/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/production/supervisor/supervisord.conf /etc/supervisord.conf
COPY --chmod=755 docker/production/entrypoint.sh /usr/local/bin/entrypoint

# Prepare Laravel's writable paths and dedicated Nginx temp paths before dropping
# privileges. Nginx keeps request buffers outside the shared system /tmp so
# sustained traffic cannot collide with unrelated process files.
RUN --mount=from=vendor,source=/usr/bin/composer,target=/usr/local/bin/composer,ro \
    mkdir -p \
        bootstrap/cache \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        /run/php-fpm \
        /var/lib/nginx/logs \
        /var/lib/nginx/tmp/client_body \
        /var/lib/nginx/tmp/fastcgi \
        /var/lib/nginx/tmp/proxy \
        /var/lib/nginx/tmp/scgi \
        /var/lib/nginx/tmp/uwsgi \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data \
        /run/php-fpm \
        /var/lib/nginx \
        bootstrap/cache \
        public/storage \
        storage \
    && php artisan package:discover --ansi \
    && composer check-platform-reqs --no-dev

# Nginx, PHP-FPM, Supervisor, and the application run without root privileges.
USER www-data

# Use an unprivileged port; TLS termination belongs to the deployment edge.
EXPOSE 8080

# Exercise the full Nginx-to-PHP-to-Laravel path rather than only a process PID.
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD wget --quiet --output-document=- http://127.0.0.1:8080/up >/dev/null || exit 1

# Optimize Laravel after deployment variables are available, then let Supervisor
# remain PID 1 and propagate termination signals to both long-running services.
ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
