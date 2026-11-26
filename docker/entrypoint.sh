#!/bin/sh
set -eu

faltantes=""
for var in APP_KEY APP_URL DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    eval "valor=\${$var:-}"
    if [ -z "$valor" ]; then
        faltantes="$faltantes $var"
    fi
done
if [ -n "$faltantes" ]; then
    echo "[entrypoint] ERROR: faltan variables de entorno obligatorias:$faltantes" >&2
    exit 1
fi

if [ "$APP_ENV" = "production" ] && [ "$APP_DEBUG" = "true" ]; then
    echo "[entrypoint] ERROR: APP_DEBUG=true no está permitido con APP_ENV=production" >&2
    exit 1
fi

intentos=0
until php artisan migrate --force --no-interaction; do
    intentos=$((intentos + 1))
    if [ "$intentos" -ge 5 ]; then
        echo "[entrypoint] ERROR: no se pudieron ejecutar las migraciones" >&2
        exit 1
    fi
    echo "[entrypoint] Base de datos no disponible, reintentando en 5s ($intentos/5)..."
    sleep 5
done

if [ "${SEED_ON_BOOT:-false}" = "true" ]; then
    for seeder in RoleSeeder UserSeeder CategoriaSeeder ProductoSeeder CatalogosSeeder MesaSeeder; do
        php artisan db:seed --class="Database\\Seeders\\$seeder" --force --no-interaction
    done
fi

php artisan config:cache --no-interaction

echo "[entrypoint] Iniciando servidor en el puerto $PORT"
cd /var/www/html/public
exec php -S "0.0.0.0:$PORT" ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
