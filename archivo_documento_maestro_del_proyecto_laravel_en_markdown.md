# Archivo — Documento Maestro del Proyecto Laravel

---

# Descripción General

## Nombre del Proyecto

**Archivo**

## Framework

- Laravel
- MySQL / PostgreSQL

## Objetivo

Crear un sistema de control y rastreo de expedientes físicos del personal.

El sistema permitirá conocer en todo momento:

- Dónde está el expediente
- Quién lo tiene
- Cuándo salió
- Cuándo regresó
- Historial de movimientos
- Responsable actual

---

# Problema Actual

Actualmente los expedientes físicos son controlados manualmente en papel.

Esto genera:

- Riesgo de pérdida
- Expedientes extraviados
- Falta de trazabilidad
- Dificultad para localizar carpetas
- Ausencia de auditoría
- Dependencia de memoria humana

---

# Concepto del Sistema

El sistema funciona como un modelo tipo:

- Videoclub
- Biblioteca
- Sistema de préstamo controlado

Donde:

- El expediente es un activo físico
- Siempre tiene responsable
- Siempre tiene ubicación
- Tiene historial permanente

---

# Alcance Inicial

## Primera Etapa

Sistema enfocado exclusivamente en:

- Rastreo físico
- Control de préstamo
- Ubicación
- Auditoría
- Alertas

## No Incluido Inicialmente

- PDFs
- Escaneo documental
- OCR
- Digitalización
- Gestión documental

---

# Modelo Real del Negocio

## Empleado

Cada empleado puede tener varios expedientes físicos.

## Expediente

Cada expediente es una carpeta física.

Cuando se llena, se crea otra carpeta.

### Ejemplo

RFC: ABC123456XYZ

- Expediente Volumen 1
- Expediente Volumen 2
- Expediente Volumen 3

---

# Roles del Sistema

## 1. Administrador

Puede:

- Aprobar solicitudes
- Entregar expediente
- Confirmar devolución
- Administrar expedientes
- Cambiar ubicación
- Consultar historial

## 2. Usuario Avanzado

Puede:

- Buscar expedientes
- Solicitar préstamos
- Ver historial
- Consultar disponibilidad
- Ver ubicación
- Ver tiempos de préstamo

No puede:

- Modificar expediente
- Aprobar préstamos
- Editar historial
- Cambiar ubicación

## 3. Superusuario

Puede:

- Ver todo
- Administrar sedes
- Configuración global
- Acceso total

---

# Flujo Oficial del Sistema

## Flujo Principal

1. Usuario busca expediente.
2. Usuario solicita expediente.
3. Solicitud queda pendiente.
4. Administrador recibe solicitud.
5. Administrador localiza expediente.
6. Admin aparta expediente.
7. Admin entrega expediente.
8. Sistema registra responsable.
9. Usuario devuelve expediente.
10. Admin confirma devolución.
11. Expediente regresa al archivo.
12. Sistema registra historial.

---

# Estados del Expediente

- Disponible
- Solicitado
- Reservado
- Prestado
- Devuelto
- Archivado
- En almacén
- Extraviado

---

# Política de Préstamos

## Modelo Flexible

- Sin límite de préstamos por usuario
- Puede tener múltiples expedientes
- Control por alertas y auditoría

---

# Ubicación Física

## Modelo Real

Los expedientes están organizados:

- Por orden alfabético
- Nuevos ingresos diarios
- Sin posición fija

## Campos de Ubicación

- Tipo de ubicación
- Archivero
- Gaveta
- Rango alfabético
- Observación

### Ejemplo

- Archivo Local
- Archivero B
- Gaveta 4
- Letras M–P

---

# Escalabilidad

## Sedes

Preparado para:

- RH Mexicali
- RH Tijuana
- RH Central
- Otras sedes

## Áreas

Preparado para:

- Recursos Humanos
- Jurídico
- Finanzas
- Compras
- Dirección

---

# Búsquedas Permitidas

- RFC
- Nombre
- Departamento
- Número de expediente
- Volumen
- Ubicación
- Responsable actual
- Fecha de préstamo
- Estado
- Historial laboral

## No Permitidas

- Texto libre
- Observaciones

---

# Seguridad

- Login Laravel
- Roles y permisos
- Auditoría completa
- Historial permanente
- Firma digital por contraseña
- Logs de eventos
- Respaldos diarios

---

# Base de Datos Recomendada

## employees

Representa empleados.

### Campos

- id
- rfc
- first_name
- last_name
- department_id
- branch_id
- employment_status
- created_at

---

## expedients

Representa carpetas físicas.

### Campos

- id
- employee_id
- expediente_code
- volume_number
- current_status
- current_location_id
- current_holder_id
- opened_at
- closed_at
- qr_code
- barcode
- is_active

---

## loan_requests

Solicitudes.

### Campos

- id
- expedient_id
- requester_id
- approved_by
- status
- requested_at
- approved_at
- reserved_at
- delivered_at
- returned_at
- due_date
- observations

---

## expediente_movements

Historial.

### Campos

- id
- expedient_id
- user_id
- movement_type
- location_id
- created_at
- notes

---

## archive_locations

Ubicaciones.

### Campos

- id
- branch_id
- location_type
- archive_name
- cabinet
- drawer
- alpha_range
- notes

---

## departments

Áreas.

---

## branches

Sedes.

---

# Relaciones Laravel

## Employee

Tiene muchos expedientes.

```php
Employee hasMany Expedient
```

## Expedient

Pertenece a empleado.

```php
Expedient belongsTo Employee
```

## Expedient

Tiene muchos movimientos.

```php
Expedient hasMany Movements
```

## LoanRequest

Relaciona préstamo.

```php
LoanRequest belongsTo User
LoanRequest belongsTo Expedient
```

---

# Librerías Laravel Recomendadas

## Roles

- spatie/laravel-permission

## Auditoría

- spatie/laravel-activitylog

## QR

- simplesoftwareio/simple-qrcode

## Código Barras

- milon/barcode

## Respaldos

- spatie/laravel-backup

---

# Dashboard

## Widgets

- Solicitudes pendientes
- Expedientes prestados
- Expedientes vencidos
- Expedientes sin regresar
- Últimos movimientos
- Expedientes por sede
- Expedientes por estado

---

# Arquitectura Laravel Recomendada

## Estructura

- Controllers
- Services
- Policies
- Repositories
- Notifications
- Events
- Jobs
- Scheduler

---

# Reglas Críticas

1. Un expediente siempre tiene responsable.
2. El historial nunca se elimina.
3. Todo movimiento genera bitácora.
4. Un expediente solo puede estar en un lugar.
5. Toda devolución debe validarse.
6. Todo préstamo tiene responsable.
7. Todo movimiento tiene fecha.
8. Todo expediente tiene ubicación.
9. Toda acción queda auditada.
10. El sistema debe soportar crecimiento.

---

# Roadmap

## Fase 1

- Login
- Roles
- Usuarios
- Expedientes
- Ubicación
- Solicitudes
- Préstamos
- Historial

## Fase 2

- Dashboard
- Alertas
- Notificaciones
- Filtros avanzados

## Fase 3

- QR
- Código barras
- Escaneo

## Fase 4

- Multi-sede
- API
- OCR futuro
- Integración documental

---

# Conclusión

Archivo será un sistema de control archivístico físico altamente trazable, escalable y auditable.

El objetivo principal es eliminar pérdida de expedientes y garantizar control absoluto del préstamo y ubicación.

