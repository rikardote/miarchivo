#!/bin/bash
set -e

# Configurar zona horaria del sistema
export TZ=America/Tijuana
ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Asegurar directorios de almacenamiento y permisos
mkdir -p /var/www/storage/framework/{sessions,views,cache} /var/www/storage/app/{public,tmp} /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Iniciar el proceso principal
if [ "$#" -gt 0 ]; then
    echo "Ejecutando comando: $@"
    exec "$@"
else
    echo "Iniciando PHP-FPM..."
    exec php-fpm
fi
