# MANUAL METODOLÓGICO DE IMPLEMENTACIÓN Y OPERACIÓN
## Sistema Integral de Gestión de Archivo — ISSSTE Baja California

**Órgano Emisor:** Subdelegación de Administración | Departamento de Recursos Humanos  
**Ámbito:** Representación Estatal Baja California  
**Versión:** 1.0 (2026)  
**Estado:** Oficial — Guía de Implementación

---

## 1. INTRODUCCIÓN Y ENFOQUE ESTRATÉGICO

### 1.1 El Dilema Clave: ¿Ajustar el Archivo al Sistema o el Sistema al Archivo?
Uno de los errores más comunes en la digitalización de archivos físicos es intentar ordenar y reclasificar todas las carpetas en papel antes de registrarlas en el sistema. En la práctica institucional, ese enfoque colapsa la operación diaria, genera extravíos temporales y agota el esfuerzo del personal sin mostrar avances tangibles a corto plazo.

> ### 🏛️ PRINCIPIO RECTOR INSTITUCIONAL
> *"No muevas el archivo para encajarlo en el software; modela el software para que refleje la realidad física de tus gavetas, y utiliza el software para gobernar el orden."*

El sistema ha sido diseñado con una arquitectura flexible por gavetas y cajones (rangos alfabéticos y secciones especiales como Directivos). Por lo tanto, la metodología recomendada es la **Adopción Progresiva (Bottom-Up)**, donde cada cajón censado y etiquetado queda inmediatamente blindado y controlado.

| Enfoque Operativo | Impacto y Consecuencias Reales | Veredicto |
| :--- | :--- | :---: |
| **Ajustar el Archivo al Sistema**<br>*(Mover y reclasificar todo en papel antes de capturar)* | **Riesgo crítico.** Provoca pérdida temporal de carpetas activas, estrés severo en el personal y meses de trabajo a ciegas sin resultados visibles para las autoridades. | ❌ **No Recomendado** |
| **Ajustar el Sistema al Archivo**<br>*(Metodología Bottom-Up por cajón cerrado)* | **Riesgo mínimo.** El sistema asimila la distribución actual de los archiveros (rangos alfabéticos y directivos), asignando códigos de barras inmediatos y blindando la custodia desde el día uno. | ✅ **Recomendado** |

---

## 2. PREPARATIVOS PREVIOS (CHECKLIST OPERATIVO)

Antes de iniciar formalmente las jornadas de captura en la sala de archivo, el área debe verificar los siguientes cuatro elementos:

1. **Estación de Trabajo:**  
   Computadora de escritorio o laptop conectada a la red local institucional con navegador moderno (Google Chrome o Microsoft Edge).
2. **Impresora Térmica de Etiquetas:**  
   Calibrada con rollos de etiquetas autoadhesivas estándar de archivo, verificando que la impresión de códigos de barras sea nítida y legible.
3. **Lectores Ópticos:**  
   Lector USB tipo pistola para la mesa de trabajo o teléfono móvil inteligente con la cámara habilitada (el sistema integra escáner autónomo sin requerir internet).
4. **Catálogo Inicial de Archiveros:**  
   Registrar en el menú `Sistema → Archiveros` (`/locations`) las gavetas y cajones exactamente como se encuentran rotulados hoy en día (ejemplo: Gaveta `G-01`, Cajones `1` a `4` con sus rangos `A - C`, `D - G`, etc.).

---

## 3. METODOLOGÍA EN TRES FASES DE ADOPCIÓN

* **Fase 1: Censo y Etiquetado Masivo ("Cajón por Cajón")**  
  Digitalización estricta gaveta por gaveta con el módulo de *Alta Continua*. Cada cajón concluido queda 100% blindado sin alterar su orden tradicional.
* **Fase 2: Circuito Estricto de Préstamos (Tolerancia Cero)**  
  A partir del inicio del proyecto, ningún expediente sale de Recursos Humanos sin registro digital previo, aprovechando los préstamos para etiquetar carpetas rezagadas.
* **Fase 3: Auditorías de Control y Reubicación Dinámica**  
  Mantenimiento preventivo periódico con escáner inteligente para verificar que el contenido físico de los cajones coincida con la base de datos.

---

## 4. FASE 1: PROCEDIMIENTO DE ALTA CONTINUA

Este módulo fue creado para capturar a gran velocidad sin necesidad de navegar entre pantallas ni recargar formularios:

1. **Selección de Sesión:**  
   En el menú lateral, ingresar a `Expedientes → Alta Continua (WIP)` (`/expedients/continuous-create`). Seleccionar el archivero (ej. `G-01`) y el cajón correspondiente (ej. `Cajón 1 — [ Rango: A - C ]`). El sistema mostrará la cantidad de empleados pendientes y censados.
2. **Extracción Física:**  
   El operador toma la primera carpeta física del cajón. En pantalla aparecerá automáticamente el primer empleado pendiente en orden alfabético estricto.
3. **Cotejo y Creación:**  
   Se confirma que los datos en pantalla (RFC, número de empleado, puesto) coincidan con la carpeta en mano y se presiona **"Crear Expediente y Etiqueta"**.
4. **Impresión y Pegado:**  
   Se presiona el botón **"Imprimir Etiqueta"**. La impresora emite la etiqueta con código de barras institucional. Se adhiere al lomo o ceja de la carpeta y se regresa al cajón.
5. **Avance Inmediato:**  
   Se presiona **"Confirmar y Siguiente"** y el sistema presenta automáticamente el siguiente expediente en orden.

> ### ⚠️ PROTOCOLO PARA EXCEPCIONES DURANTE EL CENSO
> * **Si el empleado en pantalla no tiene carpeta en ese cajón:** Presionar el botón **"Aplazar (carpeta no está en este lote)"**. El empleado pasa a la bandeja inferior de espera y el flujo continúa sin trabarse.
> * **Si aparece una carpeta que pertenece a otra letra:** Se aparta físicamente en una bandeja de "Pendientes de Reubicación" para ingresarla cuando se trabaje su cajón correspondiente.
> * **Doble verificación de seguridad:** Si un empleado ya tenía expediente previo, el sistema bloquea automáticamente la creación para evitar duplicidades accidentales.

---

## 5. FASE 2: CIRCUITO DE PRÉSTAMOS Y CUSTODIA

| Etapa | Responsable | Acción Operativa |
| :--- | :--- | :--- |
| **1. Solicitud** | Usuario Solicitante / RH | Se registra la petición en el sistema (`/loans/request`) especificando el motivo oficial del trámite. |
| **2. Aprobación** | Encargado de Archivo (RH) | Valida la solicitud en la Mesa de Control (`/loans/manage`). Al autorizar, se genera la orden de extracción para el operador. |
| **3. Extracción** | Operador (Planta Baja) | Localiza la carpeta en la gaveta y escanea el código de barras en la pantalla de Despacho (`/loans/dispatch`). El estado cambia a *"Extraído"*. |
| **4. Entrega** | Encargado de Archivo | Registra la nota de inspección física (número de fojas y estado de carátula) y entrega la carpeta física al solicitante. |
| **5. Devolución** | Operador / Encargado | Al recibir la carpeta devuelta, se escanea su código y se registra el estado físico de recepción. Se coloca en su gaveta y se confirma el re-archivado. |

---

## 6. FASE 3: AUDITORÍAS Y REUBICACIÓN DINÁMICA

Conforme los expedientes circulan por trámites administrativos, no es necesario volver a reorganizar el archivo a mano:

* **Reubicación Dinámica:**  
  Si un expediente devuelto ya no cabe en su cajón original, el operador accede a `Expedientes → Escáner Inteligente`, escanea el código y le asigna una nueva gaveta. La base de datos y la bitácora histórica quedan actualizadas de inmediato.
* **Auditoría Preventiva:**  
  En `Expedientes → Auditoría`, el supervisor puede seleccionar un cajón y pistolear consecutivamente todos los expedientes presentes. El sistema marcará en verde los correctos, alertará si falta alguno prestado y detectará carpetas mal ubicadas.

---

## 7. PLAN DE TRABAJO RECOMENDADO (PRIMERAS DOS SEMANAS)

| Período | Objetivo Principal | Meta Tangible |
| :--- | :--- | :--- |
| **Semana 1 (Días 1-2)** | Configuración del catálogo de ubicaciones físicas y calibración de impresoras térmicas. | Catálogo completo de archiveros cargado en el sistema. |
| **Semana 1 (Días 3-5)** | Prueba piloto de Alta Continua con la Gaveta G-01 y capacitación del personal de ventanilla. | Primer cajón 100% etiquetado y personal familiarizado con el escáner. |
| **Semana 2 en adelante** | Cierre de préstamos manuales e inicio del censo progresivo diario. | Cero salidas informales sin folio digital y avance de 1 a 2 cajones censados por día. |

---
*ISSSTE Baja California — Plataforma Integral de Gestión y Control Documental de Archivo (2026).*
