# Archivo — Fase 1 Task List

## Resumen de Avance Actual (Generado)
✅ **Base de Datos y Modelos:** Tablas, relaciones, enumeradores (Enums) y logs de actividad completados.
✅ **Servicios del Sistema:** Sincronización de empleados (API), lógica transaccional de expedientes y préstamos.
✅ **UI Base:** MaryUI con tema oscuro, layout responsivo y navegación lateral.
✅ **Módulo de Expedientes:** CRUD completo (Listado, Creación, Detalle y Edición de ubicación física).
✅ **Módulo de Préstamos:** Listado de solicitudes, pantalla para solicitar y consola de gestión (Aprobar/Entregar/Devolver).
✅ **Módulo de Empleados:** Directorio sincronizado y vista de perfil con sus expedientes asociados.
✅ **Seguridad Crítica:** Trait `ConfirmsSudo` implementado para verificación de contraseña en entrega/devolución física.

**📌 Siguiente parada (Pendiente):**
- Componentes CRUD para Ubicaciones Físicas y Usuarios.
- Conectar el Dashboard con métricas reales.
- Reglas de autorización (Policies) para restringir vistas según rol.

---

## Credenciales de Acceso (Entorno Local)
Puedes probar el sistema con cualquiera de estos usuarios. La contraseña para todos y para las verificaciones **SUDO** es `password`.

| Rol | Correo / Usuario | Contraseña | Nivel de Acceso |
|---|---|---|---|
| **Superusuario** | `admin@archivo.local` | `password` | Acceso Total (Aprobación, Entrega y SUDO) |
| **Admin RH** | `rh@archivo.local` | `password` | Gestión regular |
| **Usuario Básico** | `usuario@archivo.local` | `password` | Solo lectura y solicitudes de expedientes |

---

## 1. Paquetes e Instalación
- [x] Instalar Laravel Breeze
- [x] Instalar Livewire 4
- [x] Instalar MaryUI
- [x] Instalar spatie/laravel-permission
- [x] Instalar spatie/laravel-activitylog
- [x] npm install + build

## 2. Configuración Docker y .env
- [x] Añadir extra_hosts a docker-compose.yml
- [x] Añadir EMPLOYEES_API_URL y LOAN_DEFAULT_DUE_DAYS al .env

## 3. Migraciones
- [x] create_branches_table
- [x] create_departments_table
- [x] create_employees_table
- [x] create_archive_locations_table
- [x] create_expedients_table
- [x] create_loan_requests_table
- [x] create_expedient_movements_table
- [x] Ejecutar migraciones

## 4. Enums
- [x] ExpedientStatus
- [x] LoanStatus
- [x] MovementType

## 5. Modelos
- [x] Branch
- [x] Department
- [x] Employee
- [x] ArchiveLocation
- [x] Expedient
- [x] LoanRequest
- [x] ExpedientMovement

## 6. Seeders
- [x] RolePermissionSeeder
- [x] BranchSeeder
- [x] DepartmentSeeder
- [x] UserSeeder (superuser de prueba)
- [x] Ejecutar seeders

## 7. Servicios
- [x] EmployeeApiService
- [x] ExpedientService
- [x] LoanService

## 8. Comando Artisan
- [x] SyncEmployees command

## 9. Layout y UI
- [x] Integrar MaryUI layout + sidebar
- [x] Theme toggle claro/oscuro
- [x] Configurar daisyUI themes

## 10. Componentes Livewire
- [x] Dashboard
- [x] Expedients.Index
- [x] Expedients.Show
- [x] Expedients.Create
- [x] Expedients.Edit
- [x] Loans.Index
- [x] Loans.Request
- [x] Loans.Manage
- [x] Employees.Index
- [x] Employees.Show
- [x] Locations.Index
- [x] Users.Index

## 11. Confirmación Sudo
- [x] Trait ConfirmsSudo

## 12. Policies
- [x] ExpedientPolicy
- [x] LoanRequestPolicy

## 13. Events
- [x] LoanRequested + listener
- [x] LoanApproved + listener
- [x] LoanDelivered + listener
- [x] LoanReturned + listener

## 14. Verificación
- [x] Migraciones + seeders sin errores
- [ ] Login funcional
- [ ] Flujo de préstamo completo
- [x] Dashboard con datos
