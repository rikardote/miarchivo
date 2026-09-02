#!/usr/bin/env bash
# ==============================================================================
# Script de Respaldo Automatizado de Base de Datos - Sistema Archivo ISSSTE BC
# ==============================================================================
set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-storage/backups}"
mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FILENAME="$BACKUP_DIR/archivo_backup_${TIMESTAMP}.sql.gz"

echo "==> Generando respaldo de base de datos 'archivo'..."
docker compose exec -T db mysqldump -u root -proot --single-transaction --quick archivo | gzip > "$FILENAME"

echo "==> Respaldo creado exitosamente: $FILENAME"
echo "==> Tamaño: $(du -h "$FILENAME" | cut -f1)"

# Mantener los últimos 14 respaldos diarios y rotar los más antiguos
find "$BACKUP_DIR" -name "archivo_backup_*.sql.gz" -type f -mtime +14 -delete || true
echo "==> Rotación completada (retenidos últimos 14 días)."
