# syntax=docker/dockerfile:1
# Lab 14 — Imagen de producción de Bon Appétit (back-end Laravel + front-end web).
# Construcción en múltiples etapas: las herramientas de build (Node, Composer) se
# quedan en etapas intermedias y la imagen final solo lleva PHP + la app.

############################################
# Etapa 1: build optimizado del front-end (Vite + Tailwind)
############################################
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

############################################
# Etapa base: PHP 8.4 (Alpine) + extensiones necesarias
############################################
FROM php:8.4-cli-alpine AS php-base
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql intl bcmath opcache \
 && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# php.ini de producción: display_errors=Off, así ningún error muestra rutas ni trazas al visitante.

############################################
# Etapa 2: dependencias de PHP SIN paquetes de desarrollo
############################################
FROM php-base AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

############################################
# Etapa final: runtime mínimo, usuario sin privilegios
############################################
FROM php-base AS runtime
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_CLI_SERVER_WORKERS=4 \
    PORT=8080
WORKDIR /var/www/html

COPY --from=vendor --chown=www-data:www-data /app /var/www/html
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod 755 /usr/local/bin/entrypoint.sh \
 && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

# A partir de acá nada corre como root (ni el build restante ni el contenedor).
USER www-data
RUN php artisan package:discover --ansi

EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
  CMD wget -qO- "http://127.0.0.1:${PORT}/up" >/dev/null || exit 1
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
