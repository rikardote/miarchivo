#!/usr/bin/env bash
# ==============================================================================
# Script de Restauración de Base de Datos - Sistema Archivo ISSSTE BC
# Uso: ./docker/restore.sh storage/backups/archivo_backup_YYYYMMDD_HHMMSS.sql.gz
# ==============================================================================
set -euo pipefail

if [ $# -lt 1 ]; then
    echo "Error: Debe especificar la ruta del archivo de respaldo a restaurar."
    echo "Uso: $0 <archivo_backup.sql.gz>"
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "Error: No se encontró el archivo '$BACKUP_FILE'."
    exit 1
fi

echo "==> AVISO: Esta acción sobrescribirá la base de datos 'archivo'."
echo "==> Restaurando desde: $BACKUP_FILE..."

gunzip -c "$BACKUP_FILE" | docker compose exec -T db mysql -u root -proot archivo

echo "==> Restauración completada exitosamente."
