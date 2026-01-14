# Documentación de Pantallas - CDC Gestión

Este documento detalla cada pantalla del sistema, vinculando mockups con user stories y especificaciones técnicas.

---

## 1. Inicio (Dashboard)

**Mockup:** [mockups/1. Home.png](./mockups/1.%20Home.png)

**User Story:** US1.1 - Dashboard Inicio

**Ruta sugerida:** `/wp-admin/admin.php?page=cdc-inicio`

### Elementos de UI

#### Header
- **Título:** "Casa de la Cultura — Sistema de Administración"
- **Usuario/Rol:** Mostrar usuario actual y rol (Recepción/Tesorería/Admin)

#### Sidebar (Consulta rápida)
- ☐ Talleres
- ☐ Eventos
- ☐ Alquiler de salas
- ☐ Salas
- ☐ (Caja - opcional según mockup)

#### Contenido Principal

**Buscador rápido**
- Input: "Buscar socio/cliente por Nombre, Apellido o DNI..."
- Botón: "Buscar"
- Búsqueda por: Nombre, Apellido, DNI
- Resultado: Navega a ficha de persona

**3 Botones de Acción Primaria** (cards grandes)
1. **Cobrar** (icono: billete + moneda)
2. **Registrar gasto** (icono: billete + flecha salida)
3. **Personas** (icono: grupo de personas)

**Últimos movimientos (hoy)**
- Tabla con columnas:
  - Hora (formato: HH:MM)
  - Tipo (Ingreso/Egreso)
  - Concepto (Cuota socio, Gasto insumo, Taller, Evento, etc.)
  - Monto (con formato de moneda)

### Lógica de Negocio
- Solo mostrar movimientos del día actual
- Ordenar por hora descendente (más reciente primero)
- Link a ficha de movimiento al hacer click en fila

### Endpoints/Servicios
- `GET /wp-json/cdc/v1/personas?query={busqueda}` - Búsqueda de personas
- `GET /wp-json/cdc/v1/caja/movimientos-hoy` - Últimos movimientos del día

---

## 2. Cobrar (Selector de Tipo de Cobro)

**Mockup:** [mockups/2. Cobrar.png](./mockups/2.%20Cobrar.png)

**User Story:** US4.1 - Pantalla "Cobrar" (Selector)

**Ruta sugerida:** `/wp-admin/admin.php?page=cdc-cobrar`

### Elementos de UI

#### Paso 1) ¿Qué va cobrar?
**Selector de tipo** (5 opciones en tiles/cards):
- Cuota socio (icono: personas)
- Cuota taller (icono: calendario/clase)
- Evento/entrada (icono: ticket/evento)
- Alquiler de sala (icono: edificio)
- Otro ingreso (icono: monedas)

#### Paso 2) ¿A quién?
- **Input:** "Buscar socio/cliente por Nombre o DNI..."
- **Botón:** "Buscar"
- Mostrar resultados en dropdown/lista
- Seleccionar persona → mostrar datos básicos

#### Paso 3) Datos del cobro
**Según el tipo seleccionado, mostrar formulario específico:**

**Monto**
- Input numérico: $10,000 (editable según permisos)
- Nota: "(Editado, monto sugerido)"

**Medio de pago**
- Radio buttons: Efectivo / Transferencia / Mercado Pago

**Cobro con Mercado Pago**
- Radio: Escaneo pago / Link pago
- Botón: "Confirmar cobro" (primario)
- Opción: "Mercado Pago" (botón secundario)

**Observación** (opcional)
- Textarea para notas

**Acciones**
- Botón primario: "Registrar gasto" (o "Confirmar cobro" según contexto)
- Botón secundario: "Cancelar"

### Lógica de Negocio
- Cada tipo de cobro tiene validaciones específicas
- Monto autocompletado según entidad relacionada (cuota socio, precio taller, etc.)
- Solo Tesorería/Admin puede editar montos preestablecidos
- Modal Mercado Pago se abre al seleccionar ese medio de pago

### Flujo de Navegación
1. Seleccionar tipo → activa Paso 2
2. Buscar persona → activa Paso 3
3. Completar datos → Confirmar
4. Ejecutar patrón de cobro consistente (5 pasos - ver PRD.MD sección 2.3)

### Endpoints/Servicios
- `POST /wp-json/cdc/v1/cobros/cuota-socio`
- `POST /wp-json/cdc/v1/cobros/cuota-taller`
- `POST /wp-json/cdc/v1/cobros/entrada-evento`
- `POST /wp-json/cdc/v1/cobros/alquiler-sala`
- `POST /wp-json/cdc/v1/cobros/otro`

---

## 3. Personas (Listado)

**Mockup:** [mockups/3. Personas.png](./mockups/3.%20Personas.png)

**User Story:** US2.1 - Listado Personas

**Ruta sugerida:** `/wp-admin/admin.php?page=cdc-personas`

### Elementos de UI

#### Header de Página
- **Título:** "Personas"
- **Acciones:**
  - Botón: "Nuevo socio"
  - Botón: "Nuevo cliente"

#### Filtros y Búsqueda
- **Filtros:** Todos / Socios / Clientes (tabs o segmented control)
- **Buscador:** "Buscar por Nombre, Apellido o DNI..."
- **Botón:** "Buscar"

#### Tabla de Resultados
**Columnas:**
- Nombre (apellido, nombre)
- Tipo (Socio / Cliente)
- DNI
- Teléfono
- Estado (Activo / Inactivo - con badge de color)
- Acción: "Ver ficha >" (link)

**Footer de Tabla:**
- Paginación: "1-5 de 20" con controles ◀ 1 2 3 ▶
- Botón: "Exportar" (opcional)

### Lógica de Negocio
- Paginación: 20 items por página (configurable)
- Búsqueda en tiempo real (con debounce de 300ms)
- Filtros combinables con búsqueda
- Estado visual:
  - Activo: badge verde
  - Inactivo: badge gris

### Endpoints/Servicios
- `GET /wp-json/cdc/v1/personas?tipo={socio|cliente}&estado={activo|inactivo}&query={busqueda}&page={num}`

---

## 4. Ficha de Socio/Cliente

**Mockup:** [mockups/4. ficha.png](./mockups/4.%20ficha.png)

**User Story:** US2.4 - Ficha de Socio

**Ruta sugerida:** `/wp-admin/admin.php?page=cdc-persona&id={persona_id}`

### Elementos de UI

#### Breadcrumb
- Personas > Socios > Ficha

#### Header
- **Nombre:** Juan Pérez
- **Tipo:** N° Socio 0523 + badge "ACTIVO"
- **Botón:** "Editar" (top-right)

#### Acciones Rápidas (botones horizontales)
- Cobrar cuota socio
- Cobrar cuota taller
- Inscribir a taller
- Manual de pago (opcional según mockup)

#### Tabs de Contenido
1. **Información** (por defecto)
   - Cuota socio: [badge estado]
   - Talleres: [estado/cantidad]
   - Notas: [texto/enlace]

2. **Cuota socio**
   - Grilla de 12 meses (Enero - Diciembre)
   - Cada mes muestra:
     - Estado: Pagada (✓ verde) / Pendiente (⚠ amarillo) / Vencida (✗ rojo)
     - Monto
     - Fecha de pago (si aplica)
     - Medio de pago (si aplica)
   - Indicador: "Al día" / "Debe X meses"

3. **Talleres**
   - Lista de talleres inscriptos
   - Estado de cuotas por taller
   - Asistencia (si aplica)

4. **Historial**
   - Tabla de todos los movimientos relacionados
   - Columnas: Fecha, Tipo, Concepto, Monto, Comprobante

5. **Notas**
   - Observaciones generales del socio/cliente
   - Editor de texto simple

#### Panel de Información (visible en tab Información)
- DNI: 12.345.678
- Teléfono: 299 123 4567
- Email: juan@mail.com
- Domicilio: Av. Roca 123
- Fecha alta: 10/01/2020
- Tipo/Categoría: Individual / Estudiante

### Lógica de Negocio
- Calcular estado "Al día" / "Debe X meses" automáticamente
- Al día: todas las cuotas del mes actual y anteriores están pagadas
- Debe X meses: cantidad de meses con cuotas pendientes/vencidas
- Acciones rápidas redirigen a flujo de cobro con persona preseleccionada

### Endpoints/Servicios
- `GET /wp-json/cdc/v1/personas/{id}` - Datos de la persona
- `GET /wp-json/cdc/v1/personas/{id}/cuotas` - Grilla de cuotas
- `GET /wp-json/cdc/v1/personas/{id}/talleres` - Talleres inscriptos
- `GET /wp-json/cdc/v1/personas/{id}/historial` - Movimientos de caja relacionados
- `PUT /wp-json/cdc/v1/personas/{id}` - Actualizar datos

---

## 5. Nuevo Socio

**Mockup:** [mockups/5. nuevo socio.png](./mockups/5.%20nuevo%20socio.png)

**User Story:** US2.2 - Alta de Socio

**Ruta sugerida:** `/wp-admin/admin.php?page=cdc-persona-nuevo&tipo=socio`

### Elementos de UI

#### Breadcrumb
- Personas > Socios > Nuevo

#### Header
- **Título:** "Nuevo socio"
- **Botón:** "Editar" (disabled hasta guardar)

#### Formulario

**Datos personales**
- **Nombre*** (obligatorio)
- **Apellido*** (obligatorio)
- **DNI*** (obligatorio)
- **Fecha de alta** (default: hoy, editable)
- **Estado:** Activo / Inactivo (radio buttons)

**Contacto**
- **Teléfono**
- **Email**
- **Domicilio**

**Categoría**
- **Tipo/Categoría** (dropdown: Individual, Familiar, etc.)
- **Subcategoría** (dropdown: Estudiante, Jubilado, General, etc.)

**Observaciones** (opcional)
- Textarea

#### Acción Especial
- ☐ Checkbox: "Generar cuota de YY (12 meses)"
  - Texto explicativo: "Genera automáticamente la planilla de cuotas del año actual (12 meses)"

#### Acciones
- **Botón primario:** "Guardar socio"
- **Botón secundario:** "Cancelar"

### Lógica de Negocio
- Validación de campos obligatorios antes de guardar
- DNI único (no permitir duplicados)
- Si checkbox "Generar cuota..." está marcado:
  - Crear 12 registros en `cdc_cuota_socio` (1 por mes del año actual)
  - Estado inicial: "Pendiente"
  - Monto según categoría/subcategoría
- Redirigir a Ficha de Socio después de guardar

### Endpoints/Servicios
- `POST /wp-json/cdc/v1/personas` - Crear socio
  - Body: datos del formulario + `generar_cuotas: true/false`

---

## 6. Registrar Gasto

**Mockup:** [mockups/6. Registrar gasto.png](./mockups/6.%20Registrar%20gasto.png)

**User Story:** US3.1 - Registrar Gasto (Egreso)

**Ruta sugerida:** `/wp-admin/admin.php?page=cdc-registrar-gasto`

### Elementos de UI

#### Breadcrumb
- Inicio > Registrar gasto

#### Header
- **Título:** "Registrar Gasto"

#### Formulario

**Fecha/Hora**
- **Fecha** (date picker, default: hoy) + **Hora** (time picker, default: ahora)

**Detalle / Descripción***
- Textarea (obligatorio)
- Placeholder: "Ej: Compra de insumos para..."

**Categoría**
- Dropdown: Insumos / Honorarios / Servicios / Mantenimiento / Otros
- Texto: "Medio de pago" (label)

**Comprobante**
- Dropdown para medio de pago: Efectivo / Transferencia / Tarjeta / Otro
- **Adjunto:** Input file "Subir archivo" (opcional)
  - Mostrar preview o nombre del archivo subido

**Observaciones** (opcional)
- Textarea

#### Acciones
- **Botón primario:** "Guardar gasto"
- **Botón secundario:** "Cancelar"

### Lógica de Negocio
- Al guardar crear registro en `cdc_movimiento_caja`:
  - tipo: `egreso`
  - monto: valor ingresado
  - fecha_hora: combinación de fecha + hora
  - concepto_tipo: `Gasto` (o categoría específica)
  - responsable_user_id: usuario actual
  - comprobante adjunto: guardar en media library de WP y vincular
- **Permisos:** Solo Recepción (si habilitado) o Tesorería/Admin
- Redirigir a listado de Caja después de guardar

### Endpoints/Servicios
- `POST /wp-json/cdc/v1/gastos`
  - Body: fecha, hora, monto, descripcion, categoria, medio_pago, adjunto_id, observaciones
- `POST /wp-json/cdc/v1/upload` - Subir comprobante (media library WP)

---

## 7. Talleres

**Mockup:** [mockups/7. Talleres.png](./mockups/7.%20Talleres.png)

**User Story:** US6.1 - Talleres (ABM + Inscripciones)

**Ruta sugerida:** `/wp-admin/admin.php?page=cdc-talleres`

### Elementos de UI

#### Header
- **Título:** "Talleres"
- **Acciones:**
  - Búsqueda: "Buscar por nombre de taller..."
  - Filtros: Sala / Activos (dropdowns)
  - Botón: "Nuevo taller" (primario)

#### Tabla de Talleres
**Columnas:**
- Taller (nombre)
- Sala (número/nombre)
- Tallerista (nombre)
- Días y horarios (ej: "Lun y Jue 18:30-20:30")
- Precio (formato moneda)
- Estado (badge: Activo, Inactivo, Completo, etc.)
- Acción: "Ver ficha >" (link)

**Indicadores visuales:**
- Badge de estado con colores:
  - Activo: verde
  - Completo: naranja
  - Inactivo: gris
  - Finalizado: rojo

**Footer:**
- Paginación: "1 de 44 - Página: ◀ 1 2 ... Siguiente ▶"
- Info: "Mostrando: 1-10 de 44"

### Lógica de Negocio
- Talleres pueden tener:
  - Cupo máximo (opcional)
  - Estado automático "Completo" cuando se alcanza cupo
- Ordenar por estado (Activo primero) y luego nombre
- Click en fila lleva a Ficha de Taller

### Ficha de Taller (pantalla adicional, no en mockup)
- Datos del taller
- Lista de inscriptos
- Planilla de asistencia
- Gestión de cuotas por inscripto

### Endpoints/Servicios
- `GET /wp-json/cdc/v1/talleres?query={busqueda}&sala={id}&estado={estado}`
- `POST /wp-json/cdc/v1/talleres` - Crear taller
- `GET /wp-json/cdc/v1/talleres/{id}` - Ficha de taller
- `POST /wp-json/cdc/v1/talleres/{id}/inscribir` - Inscribir persona

---

## 8. Alquiler de Salas

**Mockup:** [mockups/8. Alquiler de salas.png](./mockups/8.%20Alquiler%20de%20salas.png)

**User Story:** US6.4 - Alquiler de salas (agenda + reserva + ficha)

**Ruta sugerida:** `/wp-admin/admin.php?page=cdc-alquiler-salas`

### Elementos de UI

#### Header
- **Título:** "Alquiler de salas"
- **Acciones:**
  - Filtro: Sala (dropdown: Sala 1, Sala 2, etc.)
  - Filtro: Noviembre (mes/año picker)
  - Selector de vista: Grilla / Lista / Agenda (opcional)
  - Botón: "Nueva reserva" (primario)

#### Búsqueda
- Input: "Buscar por solicitante o motivo..."

#### Tabla de Alquileres
**Columnas:**
- **Alquiler de salas** (fecha completa: 24/12/2024 Sala 1)
- **Horario** (ej: "18:00 - 21:00 Alicia Gómez")
- **Solicitante** (nombre)
- **Motivo** (descripción breve)
- **Importe** (monto total)
- **Estado** (badge con color)
  - Pendiente: amarillo
  - Reservado: azul
  - Finalizado: verde
  - Cancelado: rojo
- **Acción:** "Ver ficha >" (link)

**Footer:**
- Paginación: "Mostrando 1-10 de 23 - ◀ 1 2 3 ▶"
- Botón: "Cancelar" (en contexto, probablemente filtro)

### Lógica de Negocio
- Reservas ordenadas por fecha descendente (más recientes primero)
- Filtrar por sala y rango de fechas
- Estados:
  - **Pendiente:** Reserva creada, sin señar
  - **Reservado:** Seña cobrada
  - **Finalizado:** Saldo cobrado, alquiler completado
  - **Cancelado:** Reserva cancelada (con posible devolución de seña)

### Ficha de Alquiler (pantalla adicional, no en mockup)
- Datos de la reserva: sala, fecha, horario, solicitante, contacto, motivo
- Precio acordado: total, seña, saldo
- Acciones:
  - "Cobrar seña" (si pendiente)
  - "Cobrar saldo" (si reservado)
  - "Cancelar reserva" (si admin/tesorería)
- Historial de pagos

### Endpoints/Servicios
- `GET /wp-json/cdc/v1/alquileres?sala={id}&mes={YYYY-MM}&query={busqueda}`
- `POST /wp-json/cdc/v1/alquileres` - Crear reserva
- `GET /wp-json/cdc/v1/alquileres/{id}` - Ficha de alquiler
- `POST /wp-json/cdc/v1/cobros/alquiler-sala` - Cobrar seña/saldo

---

## Pantallas Pendientes (sin mockup)

Las siguientes pantallas están especificadas en el PRD pero no tienen mockup visual:

### 9. Caja - Listado de Movimientos
- **User Story:** US3.2
- **Referencia:** Similar a tabla de Personas
- Filtros: rango de fechas, tipo (ingreso/egreso), medio de pago
- Totales: ingresos, egresos, neto
- Link a ficha de movimiento

### 10. Ficha de Movimiento
- **User Story:** US3.3
- Detalle completo del movimiento
- Datos: fecha/hora, tipo, monto, medio, concepto, responsable, observaciones
- Comprobante adjunto (visualización)
- Acciones: Anular (solo admin/tesorería), Reintentar factura (si aplica)

### 11. Modal Mercado Pago
- **User Story:** US5.1
- Modo asistido para vincular pagos
- Botón: "Generar cobro" → crea preferencia MP
- Lista: "Pagos recientes" (últimos N con estado)
- Acción: "Vincular y confirmar" → ejecuta flujo de pago

### 12. Eventos - Listado
- **User Story:** US6.2
- Similar a Talleres
- Acción: "Cobrar entrada" → navega a flujo de cobro

### 13. Salas - ABM
- **User Story:** US6.3
- CRUD simple de salas
- Campos: nombre, capacidad, descripción, estado

---

## Notas de Implementación

### Consistencia Visual
- **Colores de estados:**
  - Activo/Pagada/Finalizado: Verde (#28a745)
  - Pendiente/Reservado: Amarillo/Ámbar (#ffc107)
  - Inactivo: Gris (#6c757d)
  - Cancelado/Error: Rojo (#dc3545)

- **Tipografía:**
  - Header principal: 24px, bold
  - Subtítulos: 18px, semibold
  - Texto normal: 14px, regular
  - Labels: 12px, uppercase, semibold

- **Botones:**
  - Primario: Azul (#007bff), texto blanco
  - Secundario: Gris claro (#e9ecef), texto oscuro
  - Destructivo: Rojo (#dc3545), texto blanco

### Sidebar de Navegación
Presente en todas las pantallas:
- Inicio (home icon)
- Talleres
- Eventos
- Alquiler de salas
- Salas
- Caja (opcional, según mockup)

### Breadcrumbs
Siempre mostrar ruta de navegación:
- Formato: Módulo > Submódulo > Pantalla Actual
- Link en cada nivel excepto el actual
- Separador: ">"

### Mensajes de Confirmación
Usar toasts/notificaciones para feedback:
- Éxito: verde, "✓ Operación completada"
- Error: rojo, "✗ Error: [descripción]"
- Advertencia: amarillo, "⚠ Atención: [mensaje]"
- Info: azul, "ℹ [mensaje informativo]"
