# PROTOCOLO Y MANUAL DE EXCEPCIONES OPERATIVAS
## Sistema Integral de Gestión de Archivo — ISSSTE Baja California

**Órgano Emisor:** Subdelegación de Administración | Departamento de Recursos Humanos  
**Ámbito de Aplicación:** Sala de Archivo y Ventanilla de Préstamos  
**Propósito:** Establecer los procedimientos de actuación inmediata ante anomalías físicas o de datos sin depender de memoria verbal ni improvisación.

---

## 1. PRINCIPIO GENERAL DE ACTUACIÓN

Cada situación no ordinaria que se presente en la sala de archivo físico o durante el censo se rige bajo la secuencia obligatoria:

```text
1. DETECCIÓN → 2. ACCIÓN PERMITIDA → 3. RESPONSABLE → 4. REGISTRO DIGITAL → 5. RESOLUCIÓN
```

> **Regla de Oro:**  
> Ninguna carpeta física se altera, reubica, desecha o retiene sin que quede el asiento correspondiente en el sistema institucional.

---

## 2. CATÁLOGO INSTITUCIONAL DE EXCEPCIONES OPERATIVAS

---

### CASO 1: Expediente no localizado en gaveta física al extraer para préstamo
- **Detección:** El operador consulta la *Picking List* o solicitud autorizada, acude al archivero/cajón indicado y la carpeta no se encuentra físicamente.
- **Acción Permitida:**  
  1. No marcarlo de inmediato como "Extraviado".
  2. Verificar si está en la charola de "Devueltos pendientes de re-archivar" o en tránsito en ventanilla de RH.
  3. Si no se localiza en 15 minutos, notificar al Administrador de RH.
- **Responsable:** Operador de Planta Baja y Administrador de RH.
- **Registro:** En la Mesa de Control se registra la nota en la solicitud y se reporta en bitácora.
- **Resolución:** Si tras búsqueda exhaustiva no aparece, el Administrador emite reporte de investigación preliminar.

---

### CASO 2: Expediente físico encontrado en otra gaveta o cajón (Desubicado)
- **Detección:** Durante el censo o una búsqueda, se extrae una carpeta cuyo código o carátula no corresponde al archivero en el que estaba guardada.
- **Acción Permitida:** Utilizar el módulo **Escáner Inteligente** (`/expedients/scanner`) para asignarle su ubicación real.
- **Responsable:** Operador de Archivo.
- **Registro:** El sistema registra automáticamente el tipo de movimiento `relocated` en `expedient_movements`.
- **Resolución:** La carpeta se regresa a su gaveta legítima o se valida si el nuevo cajón será su posición definitiva.

---

### CASO 3: Expediente perteneciente a otra Sede (ej. Mexicali vs Tijuana vs Ensenada)
- **Detección:** Se localiza una carpeta con folio o adscripción de una representación distinta.
- **Acción Permitida:** No reincorporar al cajón local. Depositar en charola de "Transferencias Inter-sedes".
- **Responsable:** Administrador de RH.
- **Registro:** Se levanta nota en el sistema mediante el módulo de auditoría (`misplaced` inter-sede).
- **Resolución:** Notificar a la sede emisora para envío por valija oficial o regularización de adscripción en plantilla.

---

### CASO 4: Código de barras o QR ilegible o manchado
- **Detección:** La pistola óptica o cámara no logra decodificar el código de barras por desgaste, manchas o rotura.
- **Acción Permitida:**  
  1. Ingresar el folio alfanumérico manualmente en el buscador (ej. `EXP-00452-V1`).
  2. Proceder a la **Reimpresión Oficial de Etiqueta**.
- **Responsable:** Operador de Archivo / Administrador de RH.
- **Registro:** Se registra en la bitácora interna de auditoría como reimpresión.
- **Resolución:** Se pega la nueva etiqueta exactamente sobre la dañada. **Nunca se genera un expediente o folio nuevo**.

---

### CASO 5: Etiqueta física dañada o despegada
- **Detección:** La etiqueta autoadhesiva se encuentra desprendida o rota en los bordes.
- **Acción Permitida:** Reimpresión inmediata desde la ficha del expediente (`/expedients/{id}/print`).
- **Responsable:** Operador de Archivo.
- **Registro:** Reimpresión de etiqueta sin alterar el código Code128 institucional existente.
- **Resolución:** Se asegura el nuevo adhesivo en el lomo o ceja de la carpeta.

---

### CASO 6: Etiqueta física pegada en carpeta incorrecta
- **Detección:** El código de barras corresponde a un empleado "García", pero los documentos internos pertenecen a "López".
- **Acción Permitida:** Detener la circulación de la carpeta de inmediato. Desprender o cancelar físicamente la etiqueta.
- **Responsable:** Administrador de RH.
- **Registro:** Registro de auditoría por corrección de identidad física.
- **Resolución:** Reinspeccionar ambas carpetas físicas, reimprimir la etiqueta legítima para cada una y verificar contenidos.

---

### CASO 7: Expediente duplicado (Dos carpetas físicas para el mismo Tomo)
- **Detección:** Existen dos carpetas físicas rotuladas como "Volumen 1" para el mismo empleado.
- **Acción Permitida:** No crear un segundo registro del mismo volumen (el sistema bloquea `employee_id + volume_number`).
- **Responsable:** Administrador de RH.
- **Registro:** Revisión cronológica de fojas.
- **Resolución:** Si la documentación excede la capacidad de una carpeta, se fusionan o se reclasifica formalmente la segunda carpeta como **Volumen 2** (`EXP-XXXXX-V2`) generando su etiqueta correspondiente.

---

### CASO 8: Empleado en plantilla sin expediente físico en gaveta
- **Detección:** En el módulo de *Alta Continua*, el empleado aparece en la cola de pendientes pero no se localiza su carpeta en el lote.
- **Acción Permitida:** Presionar el botón **"Aplazar (*Skip*)"**. Bajo ninguna circunstancia inventar un expediente vacío.
- **Responsable:** Operador de Archivo.
- **Registro:** El empleado pasa a la lista de *Aplazados* del censo.
- **Resolución:** Se indaga si el empleado es de nuevo ingreso sin apertura de carpeta o si su expediente está en almacén de concentración.

---

### CASO 9: Carpeta física en mano cuyo empleado no aparece en la plantilla digital
- **Detección:** Se tiene una carpeta física pero el RFC no arroja resultados en el sistema.
- **Acción Permitida:**  
  1. Ejecutar el comando de sincronización de personal (`php artisan employees:sync`).
  2. Si persiste ausente (ej. baja muy antigua), registrar al empleado manualmente desde el módulo de empleados (`/employees`).
- **Responsable:** Administrador de RH.
- **Registro:** Alta administrativa del empleado previo a la creación del expediente.
- **Resolución:** Crear el expediente y adherir la etiqueta correspondiente.

---

### CASO 10: Expediente devuelto físicamente pero pendiente de re-archivar
- **Detección:** El solicitante entregó la carpeta en ventanilla, pero el operador aún no la coloca en la gaveta física.
- **Acción Permitida:** El expediente debe permanecer en la charola rotulada como **"DEVUELTOS POR RE-ARCHIVAR"**.
- **Responsable:** Operador de Planta Baja.
- **Registro:** Su estado en el sistema es `returned` (Devuelto).
- **Resolución:** El operador lo regresa al cajón y confirma el botón de re-archivado en la consola de Despacho, pasando a `available`.

---

### CASO 11: Expediente Faltante en Auditoría Física (`missing`)
- **Detección:** En el módulo de Auditoría de Control (`/expedients/audit`), una carpeta no fue leída durante el escaneo del cajón.
- **Acción Permitida:** **NO cambiar su estado a `lost` (Extraviado)**. Se apertura período de verificación de 48 horas.
- **Responsable:** Supervisor de Auditoría / Administrador de RH.
- **Registro:** Queda asentado en el reporte de la sesión de auditoría como `Faltante en investigación`.
- **Resolución:**  
  - Si aparece en otra oficina o préstamo activo, se regulariza su estatus.  
  - Si transcurrido el plazo y la investigación no se localiza, el Administrador de RH emite el acta de extravío formal (`lost`).

---

### CASO 12: Expediente extraviado formalmente que es posteriormente localizado
- **Detección:** Una carpeta que estaba en estado `lost` aparece en una oficina o archivero secundario.
- **Acción Permitida:** Reintegración formal al archivo.
- **Responsable:** Administrador de RH.
- **Registro:** Se cambia el estado a `available` mediante movimiento de tipo `found` (Encontrado) con notas explicativas en bitácora.
- **Resolución:** Se valida la integridad física de sus fojas y se reincorpora a su gaveta asignada.

---

### CASO 13: Discrepancia en Préstamo Físico (Carpeta entregada no coincide con el folio)
- **Detección:** El solicitante acude a devolver una carpeta cuyo código de barras no concuerda con la solicitud registrada a su nombre.
- **Acción Permitida:** No recibir a ciegas. Escanear el código para conocer quién tenía realmente asignada esa carpeta.
- **Responsable:** Operador de Despacho.
- **Registro:** Se asienta en las notas de recepción del préstamo real y se alerta sobre el intercambio de carpetas.
- **Resolución:** Se contacta a ambos involucrados para regularizar la custodia en el sistema.

---

### CASO 14: Error de captura o asignación de cajón durante el Censo
- **Detección:** El operador seleccionó accidentalmente el Cajón 2 en lugar del Cajón 1 al generar etiquetas masivas.
- **Acción Permitida:** Utilizar la corrección masiva en el módulo de Auditoría o reubicar mediante el Escáner Inteligente.
- **Responsable:** Administrador de RH.
- **Registro:** Se actualizan las ubicaciones en bloque registrando la corrección operativa.
- **Resolución:** El inventario físico y digital quedan alineados sin necesidad de reimprimir etiquetas (el código del expediente no cambia).

---

### CASO 15: Falla de red local o servidor durante la jornada de captura
- **Detección:** El navegador muestra error de conexión o tiempo de espera agotado.
- **Acción Permitida:**  
  1. Detener de inmediato la colocación de nuevas etiquetas en carpetas físicas.
  2. No intentar adivinar los códigos ni rotular a mano.
  3. Esperar el restablecimiento del servicio.
- **Responsable:** Operador de Archivo y Soporte Técnico.
- **Registro:** Al reconectar, verificar el último folio generado antes de reanudar el censo.
- **Resolución:** Continuar desde el último empleado guardado en base de datos.

---

### CASO 16: Falla mecánica o atasco de la impresora térmica
- **Detección:** La etiqueta sale cortada, con tinta desvanecida o atascada en el rodillo.
- **Acción Permitida:** Limpiar o destrabar el cabezal térmico.
- **Responsable:** Operador de Archivo.
- **Registro:** Reimpresión de la misma etiqueta sin generar folios alternos.
- **Resolución:** Adherir la etiqueta limpia y legible.

---

### CASO 17: Daño físico o mutilación de documentos en la devolución
- **Detección:** Al recibir una carpeta devuelta, el operador detecta carátula rota, fojas desprendidas o faltantes respecto a la nota de entrega original.
- **Acción Permitida:** No confirmar la devolución sin asentar la nota de incidencia.
- **Responsable:** Operador de Despacho y Encargado de RH.
- **Registro:** Captura detallada en el campo `return_notes` del préstamo (ej. *"Se recibe con carátula desprendida y faltante de constancia fojas 14-16"*).
- **Resolución:** Turnar copia de la incidencia al área jurídica o administrativa según el reglamento interno.

---

*Manual de Excepciones Operativas — Sistema Archivo ISSSTE Baja California (2026)*
