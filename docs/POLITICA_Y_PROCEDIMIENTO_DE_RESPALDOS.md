# POLÍTICA INSTITUCIONAL Y PROCEDIMIENTO DE RESPALDOS
## Sistema Integral de Gestión de Archivo — ISSSTE Baja California

**Órgano Emisor:** Subdelegación de Administración | Departamento de Recursos Humanos / Informática  
**Objetivo:** Garantizar la integridad, confidencialidad y capacidad de recuperación inmediata del censo e inventario de expedientes ante contingencias.

---

## 1. POLÍTICA DE RESPALDO

1. **Frecuencia Obligatoria:**
   - Durante jornadas de censo masivo: **Respaldo diario automático** al concluir el turno operativo (16:00 hrs) y **respaldo previo** a cualquier mantenimiento.
2. **Retención de Respaldos:**
   - Respaldos diarios: Retención mínima de **14 días**.
   - Respaldos semanales: Retención de **1 mes**.
   - Respaldos mensuales de cierre de auditoría: Retención de **1 año**.
3. **Ubicación de Almacenamiento:**
   - Local en servidor: `storage/backups/`.
   - Copia secundaria externa: Servidor de almacenamiento institucional fuera de la máquina host.
4. **Verificación de Integridad:**
   - Se prohíbe asumir que un archivo de respaldo es funcional sin realizar periódicamente simulacros de restauración en ambiente de prueba.

---

## 2. PROCEDIMIENTO TÉCNICO DE RESPALDO

### 2.1 Generación Manual o Automatizada
El sistema cuenta con el script ejecutable:
```bash
./docker/backup.sh
```

**Comportamiento del script:**
1. Ejecuta `mysqldump` con bandera `--single-transaction` para no bloquear consultas en producción.
2. Comprime en formato `gzip` reduciendo el almacenamiento hasta en un 85%.
3. Genera el archivo rotulado con marca de tiempo: `storage/backups/archivo_backup_YYYYMMDD_HHMMSS.sql.gz`.
4. Aplica rotación automática eliminando respaldos locales con más de 14 días de antigüedad.

### 2.2 Programación Automática en Crontab del Servidor
Para programar la ejecución diaria a las 16:00 hrs:
```cron
0 16 * * * /bin/bash /var/www/archivo/docker/backup.sh >> /var/log/archivo_backup.log 2>&1
```

---

## 3. PROCEDIMIENTO DE RESTAURACIÓN ANTE DESASTRES

En caso de contingencia o prueba de validación:

### Paso 1: Localizar el archivo de respaldo más reciente
```bash
ls -lh storage/backups/
```

### Paso 2: Ejecutar el script de restauración
```bash
./docker/restore.sh storage/backups/archivo_backup_YYYYMMDD_HHMMSS.sql.gz
```

### Paso 3: Validación post-restauración
Verificar que la información cargó de forma íntegra ejecutando:
```bash
docker compose exec -T app php artisan test --compact
```
O validando el conteo de expedientes desde tinker:
```bash
php artisan tinker --execute 'echo "Expedientes: " . \App\Models\Expedient::count() . PHP_EOL;'
```

---
*Política de Respaldos y Recuperación — ISSSTE Baja California (2026)*
