# PRD + Backlog (Jira) + Especificación Técnica — Casa de la Cultura (Etapas 1 y 2)

> **Alcance de esta etapa**
> - ✅ Etapa 1: Sistema de gestión (personas, talleres, eventos, salas, alquileres, caja)
> - ✅ Etapa 2: Contabilización + emisión automática de facturas/comprobantes
> - ❌ Fuera: Bot WhatsApp, tótem, analítica avanzada, etc.

---

## 1) Backlog tipo Jira

### ÉPICA E0 — Base del sistema (WP/WooCommerce + Roles + UI Shell)

#### US0.1 — Setup WP + WooCommerce + Admin UI CDC
**Como** admin técnico, **quiero** un WordPress con WooCommerce configurado y un panel “CDC” con menú lateral (consulta rápida), **para** operar desde ventanilla.

**Criterios de aceptación**
- WP + WooCommerce instalados y operativos.
- Existe un menú lateral con accesos: Talleres, Eventos, Alquiler de salas, Salas, Caja (y Config si aplica).
- Layout base: header con “Casa de la Cultura – Sistema de Administración” + usuario/rol visible.

#### US0.2 — Roles y permisos
**Como** admin, **quiero** roles Admin / Tesorería / Recepción, **para** restringir acciones críticas.

**Criterios de aceptación**
- Recepción: alta/edición de personas + registrar cobros/gastos (según decisión).
- Tesorería/Admin: editar catálogos/tarifas, anular movimientos, reintentar facturación.
- Auditoría mínima: usuario + timestamp en acciones críticas.

---

### ÉPICA E1 — Inicio / Ventanilla

#### US1.1 — Dashboard Inicio
**Como** Recepción, **quiero** un Inicio con buscador rápido y accesos (Cobrar / Registrar gasto / Personas) + últimos movimientos, **para** operar rápido.

**Criterios de aceptación**
- Buscador por Nombre/Apellido/DNI (socios + clientes).
- 3 botones grandes visibles: Cobrar / Registrar gasto / Personas.
- “Últimos movimientos” del día con hora, tipo, concepto y monto.

---

### ÉPICA E2 — Personas (Socios / Clientes)

#### US2.1 — Listado Personas
**Como** Recepción, **quiero** listado con filtros y buscador, **para** encontrar gente en segundos.

**Criterios de aceptación**
- Filtros: Todos / Socios / Clientes (+ Activos/Inactivos opcional).
- Tabla: Nombre, Tipo, DNI, Teléfono, Estado, Acción “Ver ficha”.
- Botones: Nuevo socio / Nuevo cliente.

#### US2.2 — Alta de Socio
**Como** Recepción, **quiero** crear un socio con datos personales + categoría/subcategoría y opcional planilla anual, **para** cobrar cuotas y ver historial.

**Campos mínimos**
- Nombre, Apellido, DNI
- Fecha Alta (default hoy), Estado
- Tel, Email, Dirección
- Categoría/Tipo, Subcategoría (descuento)
- Observaciones

**Criterios de aceptación**
- Validación de campos obligatorios (Nombre, Apellido, DNI).
- Opción: “Generar planilla de cuotas del año (12 meses)”.
- Guardado crea Ficha de socio y (si aplica) genera cuotas (12 registros).

#### US2.3 — Alta de Cliente
**Como** Recepción, **quiero** crear un cliente sin lógica de cuota socio, **para** registrar operaciones.

**Criterios de aceptación**
- Campos similares (sin cuotas).
- Se puede asociar a cobros.

#### US2.4 — Ficha de Socio
**Como** Recepción/Tesorería, **quiero** ver ficha con acciones rápidas y pestañas, **para** operar sin navegar de más.

**Criterios de aceptación**
- Acciones: Cobrar cuota socio, Cobrar cuota taller, Inscribir a taller, Historial de pagos.
- Tabs: Información, Cuota socio (grilla 12 meses), Talleres, Historial, Notas.
- Indicador “Al día / Debe X meses”.

---

### ÉPICA E3 — Caja (Movimientos)

#### US3.1 — Registrar Gasto (Egreso)
**Como** Recepción, **quiero** registrar un gasto con detalle y adjunto, **para** llevar caja real.

**Campos**
- Fecha (default hoy), Hora (default ahora)
- Monto (obligatorio)
- Descripción/Detalle (obligatorio)
- Categoría (opcional)
- Medio de pago (obligatorio)
- Comprobante adjunto (opcional)
- Observaciones (opcional)

**Criterios de aceptación**
- Al guardar crea MovimientoCaja tipo EGRESO.
- Adjuntos quedan vinculados al movimiento.

#### US3.2 — Caja: listado + balance
**Como** Tesorería, **quiero** ver movimientos con filtros y balance, **para** controlar ingresos/egresos.

**Criterios de aceptación**
- Filtros por rango / mes.
- Totales: ingresos, egresos, neto.
- Link a ficha de movimiento.

#### US3.3 — Ficha de Movimiento
**Como** Tesorería, **quiero** ver detalle del movimiento, **para** auditar.

**Criterios de aceptación**
- Muestra tipo, monto, medio, responsable, conceptoTipo, conceptoId, observaciones, comprobanteId.
- Acciones restringidas por rol (anular / reintentar factura si aplica).

---

### ÉPICA E4 — Cobros + Facturación automática (ARCA)

#### US4.1 — Pantalla “Cobrar” (Selector)
**Como** Recepción, **quiero** elegir el tipo de cobro, **para** iniciar el flujo correcto.

**Opciones (MVP)**
- Cuota socio
- Cuota taller
- Entrada evento
- Alquiler sala
- Otro ingreso

**Criterios de aceptación**
- Card/tiles con cada tipo.
- Navega al formulario correspondiente.

#### US4.2 — Cobrar Cuota Socio
**Como** Recepción, **quiero** cobrar una cuota socio por período, **para** registrar y facturar.

**Criterios de aceptación**
- Selección de Socio + Mes/Año.
- Estado: Pendiente/Pagada. Si Pagada: bloquear o requerir rol.
- Monto autocompletado (editable según rol).
- Confirmación => 
  - Marca Cuota pagada (fecha/medio)
  - Crea MovimientoCaja (INGRESO)
  - Crea/actualiza Orden WooCommerce y la marca pagada (processing/completed según regla)
  - Dispara facturación ARCA y guarda comprobanteId (o deja “pendiente/error”)

#### US4.3 — Cobrar Cuota Taller
**Como** Recepción, **quiero** cobrar cuota de taller por período, **para** registrar y facturar.

**Criterios de aceptación**
- Selección de Taller + Persona + Período.
- Misma salida consistente: entidad actualizada + MovimientoCaja + Orden Woo + Comprobante.

#### US4.4 — Cobrar Entrada Evento
**Como** Recepción, **quiero** cobrar entrada/inscripción a evento, **para** registrar y facturar.

**Criterios de aceptación**
- Selección de Evento + Persona + Cantidad (si aplica).
- Misma salida consistente.

#### US4.5 — Cobrar Alquiler de Sala (seña/total/saldo)
**Como** Recepción, **quiero** cobrar alquiler (seña/total/saldo) asociado a una reserva, **para** registrar y facturar.

**Campos**
- Solicitante, sala, fecha
- Horario inicio/fin
- Motivo
- Tipo cobro (seña/total/saldo)
- Monto, medio, observación

**Criterios de aceptación**
- Actualiza Reserva/Alquiler (saldo/estado)
- Crea MovimientoCaja + Orden Woo + Comprobante

#### US4.6 — Cobrar “Otro ingreso”
**Como** Recepción, **quiero** registrar ingresos especiales con concepto libre, **para** no perder plata fuera del sistema.

**Criterios de aceptación**
- Concepto libre obligatorio, persona opcional.
- Misma salida consistente (MovimientoCaja + factura si corresponde).

#### US4.7 — Facturación automática (ARCA)
**Como** Tesorería, **quiero** que cuando la orden esté pagada se emita comprobante y quede asociado, **para** eliminar trabajo manual.

**Criterios de aceptación**
- Facturación se dispara con cambio de estado Woo (definido: processing o completed).
- Si falla: “Factura pendiente/error” + reintento disponible.
- comprobanteId guardado en MovimientoCaja y entidad relacionada.

---

### ÉPICA E5 — Mercado Pago (cobro asistido + conciliación)

#### US5.1 — Modal Mercado Pago (asistido)
**Como** Recepción, **quiero** generar cobro y vincular pago desde un modal, **para** conciliar sin errores.

**Criterios de aceptación**
- Botón “Generar cobro” crea preferencia/pago con `external_reference` único.
- Lista “Pagos recientes” (últimos N) con monto/fecha/estado.
- Acción “Vincular y confirmar”:
  - vincula pago→orden
  - marca orden pagada
  - crea MovimientoCaja
  - dispara facturación

#### US5.2 — Webhook MP + idempotencia
**Como** sistema, **quiero** procesar webhooks sin duplicar movimientos, **para** evitar inconsistencias.

**Criterios de aceptación**
- Idempotencia por `mp_payment_id`: mismo evento no duplica MovimientoCaja.
- Reversos/chargebacks => generan movimiento reverso (sin borrado).

---

### ÉPICA E6 — Módulos de gestión (Talleres / Eventos / Salas / Alquileres)

#### US6.1 — Talleres (ABM + Inscripciones)
**Como** Tesorería/Recepción, **quiero** crear/editar talleres y gestionar inscripciones, **para** controlar cuotas y asistencia.

**Criterios de aceptación**
- Taller: nombre, días/horarios, precio, sala, tallerista, estado.
- Inscripción crea registro y planilla de cuotas (si aplica).
- Ficha muestra inscritos + cuotas + asistencia.

#### US6.2 — Eventos (listado + operar cobro)
**Como** Recepción, **quiero** listar eventos y cobrar entradas, **para** registrar ingresos.

**Criterios de aceptación**
- Listado eventos con acción “Cobrar entrada” que deriva a flujo de cobro.

#### US6.3 — Salas (ABM)
**Como** Admin/Tesorería, **quiero** gestionar salas, **para** manejar alquileres/reservas.

**Criterios de aceptación**
- Alta/edición con estado (disponible/no disponible/mantenimiento si aplica).

#### US6.4 — Alquiler de salas (agenda + reserva + ficha)
**Como** Recepción, **quiero** registrar reservas y cobrar seña/saldo, **para** controlar ocupación y caja.

**Criterios de aceptación**
- Listado/agenda de reservas con estados.
- Desde ficha: acciones “Cobrar seña” / “Cobrar saldo”.

---

## 2) Especificación técnica (lista para desarrollo)

### 2.1 Arquitectura
- WordPress como backoffice y UI.
- WooCommerce como motor de órdenes/pagos.
- ARCA como facturación automática al confirmar pago (hook por estado Woo).
- Mercado Pago:
  - modo integrado recomendado: generar cobro con `external_reference` = id de operación/orden para conciliación.

### 2.2 Modelo de datos (recomendación)
> Recomendado: **tablas custom** para Movimientos/Cuotas por consistencia contable.
> Catálogos (Taller/Sala/Evento) pueden ser CPT o tablas según preferencia.

#### Tablas core

**cdc_persona**
- id (PK)
- tipo: `socio|cliente`
- nombre, apellido, dni
- tel, email, domicilio
- fecha_alta
- estado: `activo|inactivo`
- categoria, subcategoria
- observaciones

**cdc_cuota_socio**
- id (PK)
- socio_id (FK -> cdc_persona.id)
- anio, mes
- monto
- pagada (bool)
- fecha_pago
- medio_pago
- comprobante_id (nullable)
- observaciones

**cdc_movimiento_caja**
- id (PK)
- fecha_hora
- tipo: `ingreso|egreso`
- monto
- medio_pago
- responsable_user_id (WP user ID)
- concepto_tipo: `CuotaSocio|CuotaTaller|EntradaEvento|AlquilerSala|PagoTallerista|Otro`
- concepto_id (id entidad relacionada)
- observaciones
- comprobante_id (nullable)
- estado: `activo|anulado`
- motivo_anulacion (nullable)

**cdc_alquiler_sala**
- id (PK)
- sala_id
- solicitante, contacto_tel, contacto_email
- fecha, horario_inicio, horario_fin
- motivo
- precio_acordado
- sena_monto
- saldo
- estado
- observaciones

**(opcional según alcance inmediato)**
- cdc_taller, cdc_tallerista, cdc_inscripcion_taller, cdc_cuota_taller, cdc_asistencia_taller
- cdc_evento, cdc_entrada_evento (si se requiere trazabilidad de entradas)

### 2.3 Patrón de cobro (salida consistente)
Todo cobro debe terminar en:
1) Entidad de negocio actualizada (cuota/reserva/inscripción/etc.)
2) MovimientoCaja creado (source of truth contable)
3) Orden WooCommerce pagada → dispara ARCA → guarda comprobanteId

#### Medios manuales (efectivo/transferencia/tarjeta)
- UI confirma cobro
- crea/actualiza orden Woo
- marca como pagada (processing o completed)
- crea MovimientoCaja
- dispara ARCA (o “Factura pendiente/error” si falla)

#### Mercado Pago: 2 modos
**A) Integrado (ideal)**
- crear cobro MP con `external_reference` único
- webhook MP:
  - validar evento
  - fetch detalle del payment
  - idempotencia
  - resolver orden por external_reference
  - marcar orden pagada
  - crear MovimientoCaja
  - disparar ARCA

**B) Asistido (ventanilla)**
- modal:
  - generar cobro
  - listar pagos recientes
  - vincular pago seleccionado
  - confirmar => misma salida consistente

### 2.4 Idempotencia (obligatorio)
- Persistir `mp_payment_id` ya procesado (tabla cdc_mp_events o meta en order).
- Antes de crear MovimientoCaja, chequear si ya existe.
- Reversos/chargebacks: crear movimiento reverso (egreso) y actualizar estados; **no borrar**.

### 2.5 Facturación ARCA
- Definir estado Woo que dispara (processing o completed).
- Guardar comprobanteId en:
  - cdc_movimiento_caja.comprobante_id
  - entidad relacionada (cuota socio / alquiler / etc.)
  - (opcional) order meta en WooCommerce
- Si falla:
  - `factura_status = pending|error|ok`
  - acción “Reintentar factura” (Tesorería/Admin)

### 2.6 Plugin propio (estructura sugerida)
Crear plugin `cdc-admin` con:
- Admin pages (UI custom): Inicio, Personas, Ficha socio/cliente, Caja, Cobrar, Registrar gasto, Talleres, Eventos, Salas, Alquileres.
- Servicios:
  - PersonasService
  - CajaService
  - CobrosService
  - FacturacionService
  - MercadoPagoService
- REST API interna (WP REST):
  - `POST /cdc/v1/personas`
  - `GET /cdc/v1/personas?query=...`
  - `POST /cdc/v1/gastos`
  - `GET /cdc/v1/caja?from=...&to=...`
  - `POST /cdc/v1/cobros/cuota-socio`
  - `POST /cdc/v1/cobros/cuota-taller`
  - `POST /cdc/v1/cobros/entrada-evento`
  - `POST /cdc/v1/cobros/alquiler-sala`
  - `POST /cdc/v1/cobros/otro`
  - `POST /cdc/v1/mp/webhook`

### 2.7 UI / Páginas (mínimo viable)
- Login
- Inicio (buscador + 3 botones + últimos movimientos)
- Cobrar (selector)
- Cobrar Cuota Socio
- Cobrar Cuota Taller
- Cobrar Entrada Evento
- Cobrar Alquiler Sala
- Cobrar Otro Ingreso
- Registrar gasto
- Personas (listado)
- Alta Socio / Alta Cliente
- Ficha Socio/Cliente (tabs + acciones rápidas)
- Caja (listado + balance)
- Ficha Movimiento
- Talleres (ABM + ficha)
- Eventos (listado)
- Salas (ABM)
- Alquiler de salas (agenda/listado + ficha)

### 2.8 Definition of Done (global)
- Validaciones de campos obligatorios.
- Permisos por rol.
- Auditoría (user+timestamp).
- Idempotencia MP.
- No se borra dinero: anulación/reverso.
- Factura automática OK o estado pendiente/error con reintento.
- Logs mínimos y manejo de errores.

---

## 3) Parámetros a cerrar (sin frenar dev)
- ¿Cuota socio siempre con grilla 12 meses? (recomendado)
- ¿Recepción puede registrar gastos o solo Tesorería?
- ¿Se factura todo ingreso o solo ciertos conceptos?
- ¿Tipo de comprobante requerido en ARCA?
- ¿MP integrado desde el MVP o se empieza con modo asistido?

---