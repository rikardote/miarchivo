# Archivo --- Plan Adicional de Consolidación, Mejoras y Preparación Operativa

## 1. Propósito

Este documento complementa el **Documento Maestro del Proyecto Archivo**
y no sustituye su alcance ni su arquitectura.

Su objetivo es identificar mejoras, ajustes, aclaraciones y controles
que conviene incorporar durante la **Fase 4: Puesta en Marcha y Censo
Piloto**, así como algunas mejoras futuras.

### Principio rector

> **No rediseñar lo que ya funciona. Consolidar, documentar y blindar el
> sistema antes de ampliar su alcance.**

El sistema ya cuenta con los módulos operativos principales:
expedientes, préstamos en dos etapas, Alta Continua, escaneo, auditoría
física, impresión de etiquetas, ubicaciones, seguridad, reportes e
historial.

Por ello, las recomendaciones de este plan se clasifican en:

-   **MODIFICAR:** cambiar una implementación o definición existente.
-   **AGREGAR:** incorporar una capacidad nueva.
-   **MEJORAR:** conservar la funcionalidad, pero hacerla más robusta.
-   **DOCUMENTAR:** cuando la funcionalidad ya existe pero debe quedar
    formalmente especificada.
-   **VALIDAR:** comprobar durante el piloto antes de realizar cambios.
-   **NO HACER AHORA:** evitar ampliar innecesariamente el alcance.

------------------------------------------------------------------------

# 2. Prioridad general

  -----------------------------------------------------------------------
  Prioridad                           Objetivo
  ----------------------------------- -----------------------------------
  P0 --- Crítico                      Evitar pérdida de trazabilidad,
                                      errores de custodia o
                                      inconsistencias de datos

  P1 --- Alta                         Mejorar operación diaria y manejo
                                      de excepciones

  P2 --- Media                        Mejorar reportes, experiencia y
                                      administración

  P3 --- Futuro                       Funciones que pueden esperar a una
                                      etapa posterior
  -----------------------------------------------------------------------

------------------------------------------------------------------------

# 3. P0 --- Blindaje antes del censo piloto

## 3.1 Crear un Manual de Excepciones Operativas

### Acción

**AGREGAR / DOCUMENTAR**

Crear un catálogo formal de situaciones excepcionales.

### Casos mínimos

1.  Expediente no localizado.
2.  Expediente encontrado en otra gaveta.
3.  Expediente encontrado en otra sede.
4.  Código de barras ilegible.
5.  QR ilegible.
6.  Etiqueta dañada.
7.  Etiqueta incorrecta.
8.  Expediente duplicado.
9.  Empleado sin expediente.
10. Expediente sin empleado.
11. Expediente físico que no aparece en plantilla.
12. Expediente devuelto pero todavía no rearchivado.
13. Expediente extraviado posteriormente localizado.
14. Expediente prestado que no corresponde al préstamo registrado.
15. Error de ubicación durante el censo.
16. Fallo de red o servidor durante una jornada de captura.
17. Error de impresión de etiqueta.

### Resultado esperado

Cada excepción debe tener:

``` text
Detección
   ↓
Acción permitida
   ↓
Responsable
   ↓
Registro
   ↓
Resolución
```

No debe depender de instrucciones verbales o de memoria del operador.

------------------------------------------------------------------------

# 4. P0 --- Definir claramente la semántica de custodia

## 4.1 `current_holder_id`

### Situación

El modelo actual contiene:

``` text
expedients.current_holder_id
```

y lo define como custodio temporal actual.

### Acción

**DOCUMENTAR**

Establecer formalmente:

> `current_holder_id` representa exclusivamente al usuario que posee o
> tiene bajo custodia física el expediente fuera de su ubicación
> ordinaria.

No debe utilizarse para representar:

-   quien aprobó;
-   quien solicitó originalmente;
-   quien escaneó;
-   quien entregó;
-   quien recibió.

Esos actores ya se registran mediante las entidades correspondientes y/o
la bitácora.

### Beneficio

Evita ambigüedad cuando una operación involucra:

``` text
Solicitante
Administrador
Operador
Custodio físico
```

------------------------------------------------------------------------

# 5. P0 --- Blindar la transición de estados

Los estados actuales son:

``` text
available
requested
reserved
loaned
returned
archived
in_storage
lost
```

### Acción

**DOCUMENTAR + VALIDAR**

Definir una máquina de estados explícita.

### Flujo normal

``` text
AVAILABLE
    ↓
REQUESTED
    ↓
RESERVED
    ↓
LOANED
    ↓
RETURNED
    ↓
AVAILABLE
```

### Flujos excepcionales

``` text
AVAILABLE
    ↓
LOST
    ↓
FOUND
    ↓
AVAILABLE
```

y:

``` text
AVAILABLE
    ↓
IN_STORAGE
```

### Regla

No permitir transiciones arbitrarias desde la interfaz.

Ejemplo:

``` text
LOANED → AVAILABLE
```

debe ser imposible sin pasar por el proceso de devolución/rearchivo
correspondiente.

------------------------------------------------------------------------

# 6. P0 --- Clarificar `archived`

### Situación

El término `archived` puede confundirse con "el expediente ya fue
rearchivado".

Sin embargo, el flujo operativo utiliza:

``` text
RETURNED → AVAILABLE
```

para la devolución física.

### Acción

**DOCUMENTAR o MODIFICAR**

Definir `archived` exclusivamente como:

> Expediente histórico, inactivo o cerrado que ya no forma parte de la
> circulación ordinaria.

No utilizar `archived` como sinónimo de:

> "ya fue colocado nuevamente en su cajón".

Si el código todavía no está consolidado, evaluar un nombre más
explícito como:

``` text
inactive
historical
closed
```

No cambiar el enum durante el piloto si el sistema ya depende de él;
primero validar el impacto.

------------------------------------------------------------------------

# 7. P0 --- Auditoría física: conservar evidencia

El sistema ya identifica:

-   Correctos.
-   Faltantes.
-   Infiltrados.

### Acción

**MEJORAR / DOCUMENTAR**

La auditoría debe conservar una evidencia histórica de cada sesión.

Como mínimo:

``` text
Auditoría
- fecha/hora
- usuario
- sede
- archivero
- gaveta/cajón
- expedientes esperados
- expedientes escaneados
- correctos
- faltantes
- infiltrados
- resultado
```

### Importante

El resultado mostrado en pantalla no debe ser la única evidencia.

Debe ser posible consultar posteriormente:

> ¿Qué ocurrió cuando se auditó esta gaveta?

------------------------------------------------------------------------

# 8. P0 --- No declarar automáticamente un expediente como extraviado

La detección de:

``` text
FALTANTE
```

no necesariamente significa:

``` text
EXTRAVIADO
```

### Acción

**MODIFICAR REGLA OPERATIVA**

Separar:

``` text
Faltante en auditoría
```

de:

``` text
Extraviado formalmente
```

Propuesta:

``` text
AUDITORÍA
   ↓
FALTANTE
   ↓
Investigación
   ├── Encontrado → FOUND / AVAILABLE
   ├── Prestado correctamente → actualizar situación
   ├── Mal ubicado → RELOCATED
   └── No localizado después del procedimiento → LOST
```

### Beneficio

Evita marcar expedientes como extraviados por un simple error de
colocación.

------------------------------------------------------------------------

# 9. P1 --- Mejorar la bitácora de movimientos

Actualmente `expedient_movements` conserva:

``` text
expedient_id
user_id
movement_type
location_id
notes
created_at
```

### Acción

**VALIDAR / MEJORAR**

Para movimientos de ubicación, evaluar:

``` text
from_location_id
to_location_id
```

### Ventaja

En lugar de reconstruir el movimiento mediante notas:

``` text
"Se movió de G-02 cajón 3 a G-04 cajón 1"
```

se tendría información estructurada.

### Recomendación

No cambiar inmediatamente si el sistema actual funciona correctamente.

Primero determinar si:

-   `location_id` ya permite reconstruir suficientemente el historial;
-   los reportes actuales necesitan origen/destino;
-   la modificación requiere migración.

Si no aporta valor durante el piloto, dejarlo como mejora P2.

------------------------------------------------------------------------

# 10. P1 --- Etiquetas: protocolo de reimpresión

El sistema ya cuenta con impresión térmica Code128 + QR.

### Acción

**AGREGAR / DOCUMENTAR**

Definir qué ocurre cuando:

-   se rompe una etiqueta;
-   se despega;
-   se vuelve ilegible;
-   la impresora falla;
-   se imprime una etiqueta incorrecta.

### Regla recomendada

La reimpresión:

-   no debe generar un nuevo expediente;
-   no debe generar un nuevo folio;
-   debe conservar el mismo `expedient_code`;
-   debe quedar registrada como evento de auditoría.

Ejemplo:

``` text
EXP-00123-V1
    ↓
Etiqueta original
    ↓
Dañada
    ↓
Reimpresión
    ↓
Mismo expediente
```

Opcionalmente registrar:

``` text
label_reprinted
```

como actividad del sistema.

------------------------------------------------------------------------

# 11. P1 --- Control de duplicados

El modelo ya tiene:

``` text
['employee_id', 'volume_number']
```

como restricción de unicidad.

### Acción

**MEJORAR**

Durante Alta Continua y Alta Manual, mostrar una advertencia explícita
si:

``` text
Empleado + Volumen
```

ya existe.

También validar:

``` text
expedient_code
barcode
qr_code
```

antes de confirmar la creación.

### Objetivo

Impedir que un error operativo produzca dos identidades físicas para la
misma carpeta.

------------------------------------------------------------------------

# 12. P1 --- Alta Continua: convertir el WIP documental en proceso controlado

La ruta aparece actualmente como:

``` text
Alta Continua ... (WIP)
```

aunque el roadmap la marca como completada.

### Acción

**MODIFICAR DOCUMENTACIÓN**

Si el módulo ya está operativo, eliminar la etiqueta:

``` text
WIP
```

y sustituirla por algo como:

``` text
Operativo — en validación de campo
```

### Además

Registrar métricas de la sesión:

``` text
empleados revisados
expedientes creados
aplazados
errores
reimpresiones
tiempo de sesión
```

Esto permitirá evaluar el rendimiento real del proceso.

------------------------------------------------------------------------

# 13. P1 --- Aplazamientos del censo

El sistema ya contempla:

``` text
Aplazar / Skip
```

### Acción

**MEJORAR**

El aplazamiento no debe desaparecer.

Debe conservar:

``` text
Empleado
Ubicación
Usuario
Fecha
Motivo opcional
Estado
```

### Estados sugeridos

``` text
PENDIENTE
APLAZADO
COMPLETADO
INCIDENCIA
```

Esto permite responder:

> ¿Qué empleados siguen pendientes de censar?

------------------------------------------------------------------------

# 14. P1 --- Control de integridad durante el censo

Durante el piloto validar sistemáticamente:

``` text
Empleado
      ↓
Expediente
      ↓
Volumen
      ↓
Código
      ↓
Ubicación
      ↓
Etiqueta física
```

### Casos a detectar

-   carpeta física sin registro;
-   registro sin carpeta física;
-   empleado duplicado;
-   volumen duplicado;
-   ubicación incorrecta;
-   código ilegible;
-   expediente perteneciente a otra ubicación.

### Resultado

Generar una cola de incidencias en lugar de obligar al operador a
resolver todo en el momento.

------------------------------------------------------------------------

# 15. P1 --- "Localizar expediente" como operación principal

El sistema ya tiene búsqueda general y búsqueda directa por código.

### Acción

**MEJORAR UX**

La ficha del expediente debe responder inmediatamente:

``` text
¿De quién es?
¿Dónde está?
¿Está disponible?
¿Quién lo tiene?
¿Cuándo salió?
¿Cuándo debe regresar?
¿Cuál fue su último movimiento?
```

### Información recomendada

``` text
EXP-00123-V1

Empleado
RFC
Volumen

Estado actual
Ubicación actual
Custodio actual

Último movimiento
Última auditoría
Préstamo activo
Fecha de vencimiento
```

No requiere necesariamente un módulo nuevo; puede ser una mejora de la
ficha existente.

------------------------------------------------------------------------

# 16. P1 --- Vista de custodia por usuario

### Acción

**MEJORAR**

La Bandeja Personal ya existe.

Debe permitir responder:

``` text
¿Qué expedientes tengo?
¿Cuántos?
¿Cuáles están vencidos?
¿Cuáles vencen pronto?
```

Para administradores:

``` text
¿Qué expedientes están fuera de archivo?
¿Quién los tiene?
¿Cuánto tiempo llevan fuera?
```

Esto convierte la trazabilidad en una herramienta de control operativo.

------------------------------------------------------------------------

# 17. P1 --- Revisión de préstamos vencidos

El Dashboard ya muestra préstamos vencidos.

### Acción

**MEJORAR**

Agregar una vista operativa:

``` text
Préstamos vencidos

Usuario
Expediente
Fecha de salida
Fecha límite
Días de atraso
```

Y permitir filtrar por:

-   usuario;
-   departamento;
-   sede;
-   antigüedad del atraso.

------------------------------------------------------------------------

# 18. P1 --- Contingencia offline

El documento define soporte de operación offline para lectores y
escáneres.

### Acción

**VALIDAR EN CAMPO**

No asumir que "el escáner funciona sin internet" equivale a que:

> todo el sistema funciona sin conexión.

Debe documentarse exactamente qué operaciones funcionan cuando:

-   se pierde Internet;
-   se pierde la red local;
-   el servidor no responde;
-   la impresora continúa funcionando;
-   el navegador pierde conexión.

### Recomendación

Crear una prueba de contingencia:

``` text
Prueba 1 — Sin Internet
Prueba 2 — Sin red local
Prueba 3 — Servidor apagado
Prueba 4 — Reconexión
Prueba 5 — Doble captura posterior
```

El objetivo es evitar duplicados o movimientos incompletos.

------------------------------------------------------------------------

# 19. P1 --- Concurrencia

El documento ya contempla guardas transaccionales.

### Acción

**VALIDAR**

Durante pruebas reales simular:

``` text
Usuario A solicita expediente
Usuario B solicita el mismo expediente
```

simultáneamente.

También:

``` text
Operador A intenta entregar
Operador B intenta entregar
```

y:

``` text
Dos escáneres registran movimientos simultáneamente.
```

El resultado debe ser consistente.

------------------------------------------------------------------------

# 20. P2 --- Catálogo de ubicaciones

El modelo actual soporta:

-   sede;
-   archivero;
-   gaveta;
-   cajón;
-   rango alfabético.

### Acción

**MEJORAR**

Validar reglas de integridad:

``` text
Sede
 ↓
Archivero
 ↓
Gaveta
 ↓
Cajón
```

Evitar ubicaciones incompletas cuando una operación requiere ubicación
física precisa.

También validar que una ubicación inactiva no pueda seleccionarse como
nuevo destino.

------------------------------------------------------------------------

# 21. P2 --- Capacidad física de cajones

No aparece como requisito principal actual.

### Acción

**NO IMPLEMENTAR TODAVÍA**, salvo que el piloto demuestre necesidad.

Si posteriormente se requiere, evaluar:

``` text
capacity
current_count
capacity_percentage
```

Esto podría ayudar a Alta Continua y al modelo dinámico.

Pero no conviene introducirlo antes de conocer la realidad física de los
muebles.

------------------------------------------------------------------------

# 22. P2 --- Reportes de operación

El sistema ya cuenta con inventario y CSV.

### Acción

**MEJORAR**

Agregar, cuando sea necesario:

### Inventario

``` text
Total expedientes
Por sede
Por gaveta
Por cajón
Por estado
```

### Préstamos

``` text
Solicitados
Aprobados
Prestados
Devueltos
Vencidos
Rechazados
```

### Censo

``` text
Censados
Pendientes
Aplazados
Incidencias
```

### Auditoría

``` text
Correctos
Faltantes
Infiltrados
Extraviados
```

------------------------------------------------------------------------

# 23. P2 --- Indicadores del piloto

Agregar temporalmente al Dashboard o a un reporte:

``` text
% de expedientes censados
% de ubicaciones auditadas
expedientes por hora
etiquetas impresas
reimpresiones
aplazamientos
incidencias
expedientes mal ubicados
```

Esto permitirá medir si el sistema realmente mejora el trabajo físico.

------------------------------------------------------------------------

# 24. P2 --- Registro de operaciones administrativas críticas

Ya existe ActivityLog.

### Acción

**VALIDAR**

Asegurar que queden registradas, como mínimo:

``` text
Creación de expediente
Edición crítica
Cambio de ubicación
Reimpresión de etiqueta
Aprobación
Rechazo
Entrega
Devolución
Declaración de extravío
Recuperación
Cambio de usuario/rol
```

No duplicar innecesariamente información entre `activity_log` y
`expedient_movements`.

### Regla

``` text
expedient_movements
= historial operativo del expediente

activity_log
= auditoría general del sistema
```

Mantener esa separación.

------------------------------------------------------------------------

# 25. P2 --- Respaldos y recuperación

El documento actual menciona Docker, base de datos y trazabilidad, pero
el plan operativo debe definir explícitamente:

``` text
Backup
↓
Periodicidad
↓
Retención
↓
Ubicación
↓
Prueba de restauración
```

### Acción

**AGREGAR AL PLAN DE OPERACIÓN**

No basta con tener backups.

Debe probarse que un backup realmente puede restaurarse.

### Prueba mínima

``` text
Backup
↓
Restauración en entorno separado
↓
Validación de datos
↓
Validación de movimientos
↓
Validación de usuarios
```

------------------------------------------------------------------------

# 26. P2 --- Seguridad de datos personales

El sistema maneja información laboral y RFC.

### Acción

**VALIDAR / DOCUMENTAR**

Definir:

-   quién puede consultar expedientes;
-   quién puede exportar;
-   quién puede modificar;
-   quién puede ver historial;
-   quién puede administrar usuarios;
-   quién puede declarar extravío.

Especial atención a:

``` text
CSV
reportes
logs
backups
```

No deben convertirse accidentalmente en vías de acceso no controladas.

------------------------------------------------------------------------

# 27. P2 --- Política de eliminación

El sistema utiliza `softDeletes` en empleados y expedientes.

### Acción

**DOCUMENTAR**

Definir:

> Un expediente no se elimina físicamente como consecuencia de una
> operación ordinaria.

El borrado lógico debe utilizarse solamente para casos administrativos
definidos.

El historial de movimientos debe permanecer.

------------------------------------------------------------------------

# 28. P3 --- No ampliar ahora hacia OCR

El documento ya establece:

``` text
OCR y Digitalización Completa
```

como fase futura.

### Acción

**NO HACER AHORA**

No mezclar:

``` text
archivo físico
```

con:

``` text
gestión documental digital
```

hasta estabilizar:

-   inventario;
-   ubicación;
-   préstamo;
-   auditoría;
-   censo.

El OCR puede tratarse posteriormente como otro proyecto o fase.

------------------------------------------------------------------------

# 29. P3 --- Firma electrónica avanzada

También está correctamente fuera del alcance actual.

### Acción

**NO HACER AHORA**

No incorporar FIEL/e.firma hasta que exista una necesidad jurídica y
administrativa concreta.

------------------------------------------------------------------------

# 30. Correcciones documentales recomendadas

## 30.1 Framework

El documento indica:

``` text
Laravel 12 (Framework v13)
```

### Acción

**CORREGIR**

Si realmente el proyecto está en Laravel 13, escribir:

``` text
Laravel 13 / PHP 8.4
```

Si realmente se está usando Laravel 12, conservar Laravel 12.

No mezclar "Laravel 12" con "Framework v13".

------------------------------------------------------------------------

## 30.2 Estado de Alta Continua

Actualmente aparece:

``` text
WIP
```

pero el roadmap indica:

``` text
done
```

### Acción

**CORREGIR**

Usar:

``` text
Operativo — validación en campo
```

si ya funciona pero todavía está en prueba.

------------------------------------------------------------------------

## 30.3 Numeración de fases

Actualmente la sección indica Fase 4 y posteriormente Fase 5, aunque el
Gantt denomina OCR dentro de la Fase 4.

### Acción

**CORREGIR**

Elegir una sola estructura.

Recomendación:

``` text
Fase 1 — Arquitectura
Fase 2 — Operación
Fase 3 — Escaneo y Censo
Fase 4 — Puesta en Marcha
Fase 5 — Digitalización/OCR futura
```

Esto hace que el texto y el Gantt sean consistentes.

------------------------------------------------------------------------

# 31. Matriz final de acciones

  Elemento                        Acción                   Prioridad
  ------------------------------- ------------------------ -----------
  Manual de excepciones           Agregar                  P0
  Semántica `current_holder_id`   Documentar               P0
  Máquina de estados              Documentar/validar       P0
  `archived`                      Aclarar                  P0
  Auditorías persistentes         Validar/mejorar          P0
  Faltante ≠ Extraviado           Modificar regla          P0
  Bitácora origen/destino         Evaluar                  P1
  Reimpresión de etiquetas        Agregar                  P1
  Duplicados                      Mejorar validaciones     P1
  Alta Continua WIP               Corregir documentación   P1
  Aplazamientos                   Mejorar trazabilidad     P1
  Incidencias de censo            Agregar/validar          P1
  Localizar expediente            Mejorar UX               P1
  Custodia por usuario            Mejorar                  P1
  Vencimientos                    Mejorar                  P1
  Contingencia offline            Probar                   P1
  Concurrencia                    Probar                   P1
  Ubicaciones                     Validar integridad       P2
  Capacidad de cajones            Posponer                 P2
  Reportes                        Mejorar                  P2
  Métricas de piloto              Agregar                  P2
  ActivityLog                     Validar cobertura        P2
  Backups                         Formalizar/pruebas       P2
  Datos personales                Documentar               P2
  Eliminación                     Documentar               P2
  OCR                             Posponer                 P3
  FIEL/e.firma                    Posponer                 P3

------------------------------------------------------------------------

# 32. Orden recomendado de ejecución

No implementar todo simultáneamente.

## Etapa A --- Antes de iniciar el piloto

``` text
1. Corregir documentación.
2. Definir estados.
3. Definir current_holder_id.
4. Definir procedimiento de faltantes/extraviados.
5. Validar auditoría.
6. Validar respaldos.
7. Preparar manual de excepciones.
```

## Etapa B --- Durante el piloto

``` text
1. Medir Alta Continua.
2. Registrar aplazamientos.
3. Registrar incidencias.
4. Probar escaneo.
5. Probar préstamos.
6. Probar devolución.
7. Probar auditoría.
8. Probar concurrencia.
9. Probar contingencia.
```

## Etapa C --- Después del piloto

Analizar los problemas reales y decidir cuáles requieren:

``` text
cambio de BD
cambio de lógica
nuevo módulo
mejora UX
solo documentación
```

## Etapa D --- Consolidación

Implementar únicamente los cambios que el piloto justifique.

------------------------------------------------------------------------

# 33. Principio de control del proyecto

A partir de esta etapa se recomienda mantener tres documentos separados:

### Documento Maestro

Define:

> **Qué es el sistema y qué incluye.**

### Manual de Implementación y Operación

Define:

> **Cómo se utiliza y cómo se ejecutan los procesos.**

### Plan Adicional de Consolidación

Define:

> **Qué se debe validar, corregir o mejorar durante la puesta en
> marcha.**

Esto evita convertir el Documento Maestro en un documento interminable y
permite mantener separado el diseño estable de las decisiones que surjan
durante el piloto.

------------------------------------------------------------------------

# 34. Resultado esperado

Al finalizar la Fase 4 el objetivo no debe ser simplemente:

> "El sistema funciona."

Debe ser:

> **"El sistema funciona de manera controlada frente a las situaciones
> normales y excepcionales que ocurren en el archivo físico."**

La prioridad debe ser obtener certeza sobre:

``` text
¿Dónde está?
¿Quién lo tiene?
¿Qué ocurrió con él?
¿Quién hizo el movimiento?
¿Puede demostrarse?
¿Puede recuperarse la información?
¿Qué ocurre si algo sale mal?
```

Una vez que esas respuestas sean confiables, el sistema estará listo
para crecer hacia etapas posteriores.

------------------------------------------------------------------------

# 35. Regla final

**No agregar funcionalidades por anticipación.**

Durante el piloto, cada problema debe clasificarse:

``` text
¿Es un error?
      ↓
Corregir.

¿Es una limitación?
      ↓
Evaluar mejora.

¿Es una nueva necesidad?
      ↓
Agregar al backlog.

¿Es solamente falta de capacitación?
      ↓
Documentar/capacitar.

¿Es una situación excepcional?
      ↓
Agregar al Manual de Excepciones.
```

El objetivo de esta fase es **consolidar el sistema que ya existe**, no
reiniciar el diseño.
