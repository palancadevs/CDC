# CDC Gestión - Roadmap de Desarrollo

## 📊 Estado Actual (2026-01-19)

### ✅ Completado - Phase 1: Base + Testing

**Backend (Plugin CDC API)**
- ✅ 13 tablas custom de base de datos
- ✅ 9 modelos de datos (Persona, Socio, Cliente, Recibo, MovimientoCaja, Taller, Evento, Sala, ReservaSala)
- ✅ 6 servicios de negocio (PersonaService, CajaService, ReciboService, TallerService, EventoService, SalaService)
- ✅ 6 controladores REST API (Personas, Caja, Recibos, Talleres, Eventos, Salas)
- ✅ 14+ endpoints REST funcionales

**Frontend (Tema CDC Sistema)**
- ✅ Layout base con header y sidebar
- ✅ Sistema de navegación
- ✅ Dashboard/Inicio básico
- ✅ Páginas principales creadas (Personas, Cobrar, Caja, Talleres, Eventos, Salas)
- ✅ Sistema de notificaciones toast
- ✅ Integración con API REST (CDCAPI objeto global)
- ✅ Sin autenticación (sistema abierto para Phase 1)

**Testing y Herramientas**
- ✅ Suite de tests automatizados (13 tests)
- ✅ Panel de diagnóstico
- ✅ Herramientas de verificación
- ✅ Documentación completa

**Progreso estimado**: ~30% del proyecto total

---

## 🚀 Próximas Fases

### Phase 2: Funcionalidad Core (PRÓXIMO - 2-3 semanas)

**Prioridad ALTA - Funcionalidades esenciales del negocio**

#### 2.1 Módulo Personas (Completar) - 3-4 días
- [ ] **Página Personas**: Conectar con API real
  - [ ] Listar personas con filtros (tipo, estado, búsqueda)
  - [ ] Búsqueda por nombre/apellido/DNI en tiempo real
  - [ ] Paginación
  - [ ] Vista de tarjetas/tabla toggle
  
- [ ] **Formulario Nuevo Socio**: Funcional completo
  - [ ] Validación de campos (DNI único, email válido, etc.)
  - [ ] Checkbox "Generar cuotas del año" (crear 12 registros en cdc_cuota_socio)
  - [ ] Integración con API POST /personas
  - [ ] Redirección a ficha tras crear

- [ ] **Formulario Nuevo Cliente**: Similar a socio (sin cuotas)

- [ ] **Ficha de Socio/Cliente**: Página completa
  - [ ] Tab "Información": datos personales, editar inline
  - [ ] Tab "Cuota Socio": grilla de 12 meses con estado (pagada/pendiente)
  - [ ] Tab "Talleres": talleres inscriptos
  - [ ] Tab "Historial": movimientos de caja relacionados
  - [ ] Tab "Notas": sistema de notas interno
  - [ ] Indicador "Al día" / "Debe X meses"
  - [ ] Acciones rápidas (Cobrar cuota, Inscribir a taller)

**Estimado**: 3-4 días

---

#### 2.2 Módulo Caja (Completar) - 2-3 días

- [ ] **Tabla cdc_cuota_socio**: Crear esquema
  - [ ] 12 registros por socio por año
  - [ ] Campos: socio_id, anio, mes, monto, pagada, fecha_pago, medio_pago, comprobante_id

- [ ] **Página Caja**: Listado de movimientos
  - [ ] Filtros por fecha (desde/hasta)
  - [ ] Filtros por tipo (ingreso/egreso/todos)
  - [ ] Búsqueda por concepto
  - [ ] Balance summary (ingresos, egresos, neto)
  - [ ] Click en movimiento → ver detalle

- [ ] **Detalle de Movimiento**: Modal o página
  - [ ] Información completa del movimiento
  - [ ] Link a recibo relacionado (si existe)
  - [ ] Link a persona relacionada
  - [ ] Botón "Anular" (solo admins/tesorería)

- [ ] **Sistema de Anulación**:
  - [ ] Crear movimiento reverso (no borrar)
  - [ ] Campo motivo_anulacion
  - [ ] Actualizar balance

**Estimado**: 2-3 días

---

#### 2.3 Módulo Cobros (Core) - 4-5 días

- [ ] **Página Cobrar**: Selector funcional
  - [ ] 5 botones grandes (Cuota socio, Cuota taller, Entrada evento, Alquiler sala, Otro)
  - [ ] Al seleccionar → mostrar formulario específico

- [ ] **Cobrar Cuota Socio**: Flujo completo
  - [ ] Buscar socio (autocomplete)
  - [ ] Mostrar grilla de cuotas (seleccionar mes/es)
  - [ ] Calcular total automático
  - [ ] Selector medio de pago
  - [ ] Confirmación → Ejecutar 5 pasos:
    1. Actualizar cuota(s) en cdc_cuota_socio (pagada=true)
    2. Crear MovimientoCaja (tipo=ingreso)
    3. Crear orden WooCommerce (pendiente)
    4. Marcar orden como paid
    5. Guardar comprobanteId (cuando ARCA esté integrado)
  - [ ] Mostrar recibo generado
  - [ ] Opción imprimir/enviar por email

- [ ] **Cobrar Otro Ingreso**: Formulario simple
  - [ ] Concepto, monto, medio de pago
  - [ ] Crear recibo + movimiento caja

- [ ] **Sistema de Recibos**:
  - [ ] Numeración automática (formato: C20260119-001)
  - [ ] Almacenar items del recibo
  - [ ] Generar PDF básico (template simple)

**Estimado**: 4-5 días

---

#### 2.4 Módulo Talleres (Básico) - 2-3 días

- [ ] **Tabla cdc_inscripcion_taller**: Crear
- [ ] **Tabla cdc_cuota_taller**: Crear

- [ ] **Página Talleres**: Listado
  - [ ] Filtros por estado (activo/inactivo)
  - [ ] Card por taller (nombre, profesor, horario, cupo)
  - [ ] Click → Ver detalle

- [ ] **Formulario Nuevo Taller**: Funcional
  - [ ] Campos: nombre, descripción, tallerista, horarios, cupo, precio mensual
  - [ ] Crear taller via API

- [ ] **Detalle de Taller**:
  - [ ] Info del taller
  - [ ] Lista de inscriptos
  - [ ] Botón "Inscribir persona"

- [ ] **Inscribir Persona a Taller**:
  - [ ] Buscar persona
  - [ ] Crear inscripción
  - [ ] Generar cuotas mensuales (cantidad configurable)

- [ ] **Cobrar Cuota Taller**: Agregar a página Cobrar
  - [ ] Buscar persona inscripta
  - [ ] Seleccionar cuota(s) pendiente(s)
  - [ ] Flujo similar a cuota socio

**Estimado**: 2-3 días

---

### Phase 3: WooCommerce + Facturación - 3-4 días

**Prioridad ALTA - Requerimiento legal**

#### 3.1 Integración WooCommerce - 2 días

- [ ] **Crear producto virtual "Cobro CDC"**
  - [ ] Producto único para todos los cobros
  - [ ] Precio variable

- [ ] **Servicio WooCommerceService**:
  - [ ] Método createOrder(items, total, metadata)
  - [ ] Método markOrderAsPaid(order_id)
  - [ ] Almacenar metadata custom (tipo_cobro, persona_id, etc.)

- [ ] **Integrar en flujo de cobros**:
  - [ ] Crear orden al confirmar cobro
  - [ ] Marcar como paid inmediatamente
  - [ ] Link orden ↔ MovimientoCaja

**Estimado**: 2 días

---

#### 3.2 Integración ARCA (Facturación Electrónica) - 2 días

- [ ] **Investigar ARCA API**:
  - [ ] Credenciales y endpoint de prueba
  - [ ] Formato de factura requerido
  - [ ] Manejo de errores y reintentos

- [ ] **Servicio ARCAService**:
  - [ ] Método generarFactura(order_id)
  - [ ] Obtener comprobanteId
  - [ ] Manejo de errores (almacenar estado)

- [ ] **Hook WooCommerce**:
  - [ ] woocommerce_order_status_completed
  - [ ] Llamar ARCAService automáticamente
  - [ ] Almacenar comprobanteId en:
    - wp_cdc_movimientos_caja.comprobante_id
    - wp_cdc_cuota_socio.comprobante_id (si aplica)
    - wp_postmeta (order meta)

- [ ] **Sistema de Reintentos**:
  - [ ] Campo factura_status (pending/ok/error)
  - [ ] Botón "Reintentar Facturación" (Tesorería/Admin)
  - [ ] Log de intentos

**Estimado**: 2 días

---

### Phase 4: Mercado Pago - 3-4 días

**Prioridad MEDIA - Mejora UX**

#### 4.1 Webhook Mercado Pago - 2 días

- [ ] **Tabla cdc_mp_events**: Idempotencia
  - [ ] Campos: mp_payment_id, external_reference, status, processed_at

- [ ] **Endpoint POST /mp/webhook**:
  - [ ] Recibir notificación de pago
  - [ ] Validar firma (si disponible)
  - [ ] Verificar idempotencia (no procesar duplicados)
  - [ ] Obtener payment details de MP API
  - [ ] Resolver orden por external_reference
  - [ ] Ejecutar flujo de cobro estándar

- [ ] **Configurar webhook en Mercado Pago**:
  - [ ] URL del webhook
  - [ ] Eventos a escuchar (payment.created, payment.updated)

**Estimado**: 2 días

---

#### 4.2 Modal Cobro Asistido - 1-2 días

- [ ] **Modal en página Cobrar**:
  - [ ] Botón "Pagar con Mercado Pago"
  - [ ] Generar link de pago (preferencia MP)
  - [ ] Mostrar link + QR
  - [ ] Opción: "Pago ya realizado, vincular manualmente"

- [ ] **Modo Asistido**:
  - [ ] Endpoint GET /mp/pagos-recientes
  - [ ] Listar últimos 20 pagos de MP
  - [ ] Seleccionar pago → vincular con orden → procesar cobro

**Estimado**: 1-2 días

---

### Phase 5: Módulos Adicionales - 4-5 días

**Prioridad MEDIA**

#### 5.1 Alquiler de Salas - 2-3 días

- [ ] **Página Salas**: Listado
  - [ ] Cards con salas disponibles
  - [ ] Click → ver calendario de reservas

- [ ] **Formulario Nueva Reserva**:
  - [ ] Seleccionar sala
  - [ ] Fecha + horario (inicio/fin)
  - [ ] Solicitante (persona o externo)
  - [ ] Precio acordado
  - [ ] Monto de seña (opcional)

- [ ] **Cobrar Alquiler Sala**: En página Cobrar
  - [ ] Buscar reserva
  - [ ] Opciones: Cobrar seña / Cobrar saldo
  - [ ] Actualizar estado de reserva

**Estimado**: 2-3 días

---

#### 5.2 Eventos - 2 días

- [ ] **Tabla cdc_entrada_evento**: Crear

- [ ] **Página Eventos**: Listado
  - [ ] Filtros por fecha (próximos/pasados)
  - [ ] Card por evento

- [ ] **Formulario Nuevo Evento**:
  - [ ] Nombre, fecha, precio entrada, cupo

- [ ] **Cobrar Entrada Evento**: En página Cobrar
  - [ ] Seleccionar evento
  - [ ] Seleccionar persona (opcional)
  - [ ] Cantidad de entradas
  - [ ] Generar entradas numeradas

**Estimado**: 2 días

---

### Phase 6: Dashboard y Reportes - 2-3 días

**Prioridad MEDIA**

#### 6.1 Dashboard Mejorado - 1-2 días

- [ ] **Buscador Global**:
  - [ ] Búsqueda rápida (nombre/DNI)
  - [ ] Resultados con acciones (Ver ficha, Cobrar)

- [ ] **Acciones Rápidas**:
  - [ ] 3 botones grandes (Cobrar, Registrar gasto, Personas)
  - [ ] Shortcuts de teclado

- [ ] **Últimos Movimientos de Hoy**:
  - [ ] Listar últimos 10 movimientos
  - [ ] Resumen del día (ingresos/egresos)

**Estimado**: 1-2 días

---

#### 6.2 Reportes Básicos - 1-2 días

- [ ] **Reporte: Socios al día vs deudores**
  - [ ] Cantidad y porcentaje
  - [ ] Monto total adeudado

- [ ] **Reporte: Movimientos por período**
  - [ ] Filtros de fecha
  - [ ] Exportar a Excel/CSV

- [ ] **Reporte: Inscriptos por taller**
  - [ ] Cupo ocupado
  - [ ] Ingresos generados

**Estimado**: 1-2 días

---

### Phase 7: Autenticación y Roles - 3-4 días

**Prioridad MEDIA/BAJA - Por ahora sistema abierto**

#### 7.1 Sistema de Roles - 2 días

- [ ] **Definir roles WordPress custom**:
  - [ ] CDC Admin
  - [ ] CDC Tesorería
  - [ ] CDC Recepción

- [ ] **Capability mapping**:
  - [ ] cdc_edit_catalogs (Admin, Tesorería)
  - [ ] cdc_void_movements (Admin, Tesorería)
  - [ ] cdc_register_payments (Todos)
  - [ ] cdc_register_expenses (Admin, Tesorería, Recepción según config)

- [ ] **Guards en endpoints API**:
  - [ ] Verificar permissions en cada endpoint
  - [ ] Retornar 403 si no autorizado

**Estimado**: 2 días

---

#### 7.2 Login y Sesiones - 1-2 días

- [ ] **Página Login**: (ya existe pero deshabilitada)
  - [ ] Login con usuario/password de WordPress
  - [ ] Redirección a dashboard

- [ ] **Session Guard**: (ya existe pero deshabilitado)
  - [ ] Proteger todas las páginas excepto login
  - [ ] Verificar sesión activa

- [ ] **Header con usuario**:
  - [ ] Mostrar nombre del usuario logueado
  - [ ] Rol
  - [ ] Botón logout

**Estimado**: 1-2 días

---

### Phase 8: Mejoras UX y Polish - 2-3 días

**Prioridad BAJA - Nice to have**

- [ ] **Impresión de recibos**:
  - [ ] Template PDF profesional
  - [ ] Logo de CDC
  - [ ] Botón imprimir

- [ ] **Notificaciones por email**:
  - [ ] Enviar recibo por email al cobrar
  - [ ] Reminder de cuota vencida

- [ ] **Dashboard con gráficos**:
  - [ ] Chart.js para visualizaciones
  - [ ] Ingresos por mes (últimos 6 meses)
  - [ ] Distribución de ingresos por tipo

- [ ] **Exportar datos**:
  - [ ] Exportar personas a Excel
  - [ ] Exportar movimientos a Excel

- [ ] **Búsqueda avanzada**:
  - [ ] Filtros combinados en personas
  - [ ] Guardado de búsquedas frecuentes

**Estimado**: 2-3 días

---

## 📊 Resumen de Estimaciones

| Phase | Descripción | Días | Prioridad |
|-------|-------------|------|-----------|
| ✅ Phase 1 | Base + Testing | HECHO | - |
| 🚀 Phase 2 | Funcionalidad Core | 11-15 días | ALTA |
| 🚀 Phase 3 | WooCommerce + ARCA | 4 días | ALTA |
| 🔶 Phase 4 | Mercado Pago | 3-4 días | MEDIA |
| 🔶 Phase 5 | Módulos Adicionales | 4-5 días | MEDIA |
| 🔶 Phase 6 | Dashboard + Reportes | 2-3 días | MEDIA |
| 🔷 Phase 7 | Autenticación + Roles | 3-4 días | BAJA |
| 🔷 Phase 8 | UX Polish | 2-3 días | BAJA |

**Total estimado**: 29-38 días adicionales (~6-8 semanas)

---

## 🎯 Recomendación de Orden

### Mes 1 (Próximas 4 semanas)
1. **Phase 2**: Funcionalidad Core (2 semanas)
   - Personas completo
   - Caja completo
   - Cobros básicos
   - Talleres básico

2. **Phase 3**: WooCommerce + ARCA (1 semana)
   - Órdenes WooCommerce
   - Facturación ARCA

### Mes 2 (Siguientes 4 semanas)
3. **Phase 4**: Mercado Pago (1 semana)
4. **Phase 5**: Salas + Eventos (1 semana)
5. **Phase 6**: Dashboard + Reportes (0.5 semanas)
6. **Phase 7**: Autenticación (si necesario) (1 semana)
7. **Phase 8**: Polish y mejoras (0.5 semanas)

---

## ✅ Criterios de "Terminado"

Para considerar el MVP completo:

**Funcional**:
- [x] Gestión completa de personas (socios/clientes)
- [x] Sistema de caja funcional con balance correcto
- [x] Cobro de cuotas socios
- [x] Cobro de cuotas talleres
- [x] Gestión de talleres e inscripciones
- [x] Facturación automática via ARCA
- [x] Integración con WooCommerce

**Técnico**:
- [x] Todos los tests pasan (100%)
- [x] Sin errores en logs
- [x] Documentación completa
- [x] API REST funcional
- [x] Base de datos sin inconsistencias

**UX**:
- [x] Navegación fluida
- [x] Tiempos de respuesta < 1s
- [x] Mensajes de error claros
- [x] Feedback visual en todas las acciones

---

## 🔄 Workflow Sugerido

Por cada funcionalidad:
1. Leer User Story en docs/PRD.MD
2. Ver mockup correspondiente
3. Revisar specs en docs/SCREENS.md
4. Implementar backend (modelo → service → controller)
5. Implementar frontend (página → integración API)
6. Crear/actualizar tests
7. Documentar si es necesario
8. Commit + push
9. Testing manual
10. Marcar como completado

---

**Última actualización**: 2026-01-19
**Próximo milestone**: Phase 2 - Funcionalidad Core
