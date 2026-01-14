# Estructura del Proyecto CDC Gestión

## Árbol de Directorios

```
cdc-gestion/
├── CLAUDE.md                          # Guía técnica para Claude Code
├── PROJECT-STRUCTURE.md               # Este archivo
│
├── docs/                              # 📚 Documentación del proyecto
│   ├── README.md                      # Índice principal de documentación
│   ├── PRD.MD                         # Product Requirements Document
│   ├── SCREENS.md                     # Especificación detallada de pantallas
│   └── mockups/                       # Mockups visuales de las pantallas
│       ├── 1. Home.png
│       ├── 2. Cobrar.png
│       ├── 3. Personas.png
│       ├── 4. ficha.png
│       ├── 5. nuevo socio.png
│       ├── 6. Registrar gasto.png
│       ├── 7. Talleres.png
│       └── 8. Alquiler de salas.png
│
├── app/
│   └── public/                        # WordPress root
│       ├── wp-config.php              # WordPress configuration
│       ├── wp-content/
│       │   ├── plugins/
│       │   │   ├── woocommerce/       # WooCommerce 10.4.3 (instalado)
│       │   │   └── cdc-admin/         # ⚠️ Plugin custom (A CREAR)
│       │   │       ├── cdc-admin.php  # Main plugin file
│       │   │       ├── includes/
│       │   │       │   ├── admin-pages/
│       │   │       │   │   ├── inicio-page.php
│       │   │       │   │   ├── personas-page.php
│       │   │       │   │   ├── ficha-socio-page.php
│       │   │       │   │   ├── cobrar-page.php
│       │   │       │   │   ├── caja-page.php
│       │   │       │   │   ├── talleres-page.php
│       │   │       │   │   ├── salas-page.php
│       │   │       │   │   └── alquiler-salas-page.php
│       │   │       │   ├── services/
│       │   │       │   │   ├── PersonasService.php
│       │   │       │   │   ├── CajaService.php
│       │   │       │   │   ├── CobrosService.php
│       │   │       │   │   ├── FacturacionService.php
│       │   │       │   │   └── MercadoPagoService.php
│       │   │       │   ├── models/
│       │   │       │   │   ├── Persona.php
│       │   │       │   │   ├── CuotaSocio.php
│       │   │       │   │   ├── MovimientoCaja.php
│       │   │       │   │   ├── Taller.php
│       │   │       │   │   ├── AlquilerSala.php
│       │   │       │   │   └── Sala.php
│       │   │       │   ├── rest-api/
│       │   │       │   │   ├── PersonasController.php
│       │   │       │   │   ├── CajaController.php
│       │   │       │   │   ├── CobrosController.php
│       │   │       │   │   ├── TalleresController.php
│       │   │       │   │   └── MercadoPagoWebhook.php
│       │   │       │   ├── database/
│       │   │       │   │   └── schema.php         # Definición de tablas custom
│       │   │       │   └── helpers/
│       │   │       │       ├── permissions.php
│       │   │       │       ├── formatters.php
│       │   │       │       └── validators.php
│       │   │       ├── assets/
│       │   │       │   ├── css/
│       │   │       │   │   ├── admin-style.css
│       │   │       │   │   └── components.css
│       │   │       │   └── js/
│       │   │       │       ├── admin-scripts.js
│       │   │       │       ├── personas.js
│       │   │       │       ├── cobros.js
│       │   │       │       └── mercadopago-modal.js
│       │   │       └── templates/
│       │   │           ├── inicio/
│       │   │           ├── personas/
│       │   │           ├── cobros/
│       │   │           ├── caja/
│       │   │           └── talleres/
│       │   └── themes/
│       │       ├── twentytwentyfive/           # Theme activo por defecto
│       │       ├── twentytwentyfour/
│       │       └── twentytwentythree/
│       ├── wp-admin/
│       └── wp-includes/
│
├── conf/                              # Configuración del servidor (Local by Flywheel)
│   ├── mysql/
│   ├── nginx/
│   └── php/
│
└── logs/                              # Logs del servidor local
```

## Base de Datos MySQL

```
Database: local

Tablas WordPress (estándar):
├── wp_posts
├── wp_users
├── wp_options
├── wp_postmeta
└── ... (otras tablas WP estándar)

Tablas WooCommerce (estándar):
├── wp_wc_orders
├── wp_wc_order_items
├── wp_wc_product_meta_lookup
└── ... (otras tablas WC)

Tablas Custom CDC (A CREAR):
├── wp_cdc_persona                     # Socios y clientes
├── wp_cdc_cuota_socio                 # Cuotas mensuales de socios
├── wp_cdc_movimiento_caja             # Movimientos de caja (ingresos/egresos)
├── wp_cdc_alquiler_sala               # Reservas de alquiler de salas
├── wp_cdc_sala                        # Catálogo de salas
├── wp_cdc_taller                      # Catálogo de talleres
├── wp_cdc_tallerista                  # Profesores/instructores
├── wp_cdc_inscripcion_taller          # Inscripciones a talleres
├── wp_cdc_cuota_taller                # Cuotas de talleres
├── wp_cdc_asistencia_taller           # Registro de asistencias
├── wp_cdc_evento                      # Catálogo de eventos
├── wp_cdc_entrada_evento              # Entradas/inscripciones a eventos
└── wp_cdc_mp_events                   # Log de eventos de Mercado Pago (idempotencia)
```

## REST API Endpoints

```
Base URL: /wp-json/cdc/v1/

Personas:
├── GET    /personas                   # Listar personas (con filtros)
├── POST   /personas                   # Crear socio/cliente
├── GET    /personas/{id}              # Obtener ficha de persona
├── PUT    /personas/{id}              # Actualizar persona
├── GET    /personas/{id}/cuotas       # Grilla de cuotas (12 meses)
├── GET    /personas/{id}/talleres     # Talleres inscriptos
└── GET    /personas/{id}/historial    # Historial de movimientos

Caja:
├── GET    /caja/movimientos           # Listar movimientos (con filtros)
├── GET    /caja/movimientos-hoy       # Movimientos del día
├── GET    /caja/movimiento/{id}       # Detalle de movimiento
├── POST   /gastos                     # Registrar egreso
├── POST   /caja/anular/{id}           # Anular movimiento
└── GET    /caja/balance               # Balance (ingresos, egresos, neto)

Cobros:
├── POST   /cobros/cuota-socio         # Cobrar cuota de socio
├── POST   /cobros/cuota-taller        # Cobrar cuota de taller
├── POST   /cobros/entrada-evento      # Cobrar entrada a evento
├── POST   /cobros/alquiler-sala       # Cobrar alquiler (seña/saldo)
└── POST   /cobros/otro                # Otro ingreso

Talleres:
├── GET    /talleres                   # Listar talleres
├── POST   /talleres                   # Crear taller
├── GET    /talleres/{id}              # Ficha de taller
├── PUT    /talleres/{id}              # Actualizar taller
├── POST   /talleres/{id}/inscribir    # Inscribir persona
└── GET    /talleres/{id}/asistencia   # Planilla de asistencia

Salas:
├── GET    /salas                      # Listar salas
├── POST   /salas                      # Crear sala
├── GET    /alquileres                 # Listar alquileres/reservas
├── POST   /alquileres                 # Crear reserva
└── GET    /alquileres/{id}            # Ficha de alquiler

Eventos:
├── GET    /eventos                    # Listar eventos
├── POST   /eventos                    # Crear evento
└── GET    /eventos/{id}               # Ficha de evento

Mercado Pago:
├── POST   /mp/webhook                 # Webhook de notificaciones
├── POST   /mp/crear-cobro             # Generar preferencia de pago
└── GET    /mp/pagos-recientes         # Listar pagos recientes (modo asistido)

Facturación:
├── POST   /facturacion/reintentar     # Reintentar facturación ARCA
└── GET    /facturacion/estado/{id}    # Estado de facturación
```

## Flujos de Navegación

### Flujo Principal: Cobro de Cuota Socio

```
1. Dashboard (Inicio)
   └─> Click "Cobrar"
       └─> Pantalla: Cobrar (selector)
           └─> Seleccionar "Cuota socio"
               └─> Buscar socio por DNI/Nombre
                   └─> Seleccionar socio
                       └─> Formulario: mes, monto, medio de pago
                           └─> Confirmar cobro
                               └─> Backend ejecuta 5 pasos:
                                   1. Actualiza cuota en cdc_cuota_socio (pagada=true)
                                   2. Crea registro en cdc_movimiento_caja (tipo=ingreso)
                                   3. Crea/actualiza orden WooCommerce
                                   4. Marca orden como pagada (trigger ARCA)
                                   5. Guarda comprobanteId en todas las entidades
                               └─> Mensaje éxito + opción ver comprobante
```

### Flujo: Alta de Socio

```
1. Dashboard (Inicio)
   └─> Click "Personas"
       └─> Pantalla: Personas (listado)
           └─> Click "Nuevo socio"
               └─> Formulario: datos personales + categoría
                   └─> ☑ Checkbox: "Generar cuota del año (12 meses)"
                       └─> Guardar socio
                           └─> Backend:
                               1. Crea registro en cdc_persona
                               2. Si checkbox marcado: crea 12 registros en cdc_cuota_socio
                           └─> Redirige a: Ficha de Socio
```

### Flujo: Registrar Gasto

```
1. Dashboard (Inicio)
   └─> Click "Registrar gasto"
       └─> Formulario: fecha, monto, descripción, categoría, comprobante
           └─> Confirmar
               └─> Backend:
                   1. Crea registro en cdc_movimiento_caja (tipo=egreso)
                   2. Adjunta comprobante a media library
               └─> Redirige a: Listado Caja
```

## Roles y Permisos

```
Admin (Administrador):
├── ✓ Acceso completo al sistema
├── ✓ Gestionar usuarios y roles
├── ✓ Anular movimientos de caja
├── ✓ Editar catálogos (talleres, salas, tarifas)
├── ✓ Reintentar facturación ARCA
└── ✓ Ver todos los reportes

Tesorería:
├── ✓ Ver y editar catálogos
├── ✓ Anular movimientos de caja
├── ✓ Reintentar facturación ARCA
├── ✓ Editar montos preestablecidos en cobros
├── ✓ Ver todos los reportes
└── ✗ Gestionar usuarios

Recepción:
├── ✓ Alta/edición de personas (socios/clientes)
├── ✓ Registrar cobros (todos los tipos)
├── ✓ Registrar gastos (según configuración)
├── ✓ Buscar personas
├── ✓ Ver fichas de personas
├── ✗ Editar montos preestablecidos
├── ✗ Anular movimientos
└── ✗ Gestionar catálogos
```

## Orden de Desarrollo Recomendado

```
Fase 1: Base (1-2 días)
├── 1.1 Crear plugin cdc-admin
├── 1.2 Crear estructura de carpetas
├── 1.3 Crear menú lateral de navegación
├── 1.4 Implementar roles y permisos
└── 1.5 Crear archivo de schema de BD

Fase 2: Módulo Personas (3-4 días)
├── 2.1 Crear tabla cdc_persona
├── 2.2 Crear tabla cdc_cuota_socio
├── 2.3 Implementar listado de personas
├── 2.4 Implementar alta de socio
├── 2.5 Implementar alta de cliente
└── 2.6 Implementar ficha de socio (con tabs)

Fase 3: Módulo Caja (2-3 días)
├── 3.1 Crear tabla cdc_movimiento_caja
├── 3.2 Implementar registrar gasto
├── 3.3 Implementar listado de caja
└── 3.4 Implementar ficha de movimiento

Fase 4: Cobros Base (4-5 días)
├── 4.1 Implementar pantalla selector "Cobrar"
├── 4.2 Implementar cobrar cuota socio (sin facturación)
├── 4.3 Implementar cobrar otro ingreso
└── 4.4 Probar flujo completo de cobro manual

Fase 5: WooCommerce + ARCA (3-4 días)
├── 5.1 Integrar creación de órdenes WooCommerce
├── 5.2 Implementar hook de facturación ARCA
├── 5.3 Implementar lógica de reintento de factura
└── 5.4 Probar flujo completo: cobro → orden → factura

Fase 6: Mercado Pago (3-4 días)
├── 6.1 Implementar webhook con idempotencia
├── 6.2 Implementar modal de cobro asistido
├── 6.3 Implementar listado de pagos recientes
└── 6.4 Probar flujos de pago MP (integrado + asistido)

Fase 7: Talleres y Salas (4-5 días)
├── 7.1 Crear tablas: cdc_taller, cdc_sala, cdc_alquiler_sala
├── 7.2 Implementar ABM de talleres
├── 7.3 Implementar ABM de salas
├── 7.4 Implementar alquiler de salas
├── 7.5 Implementar cobrar cuota taller
└── 7.6 Implementar cobrar alquiler sala

Fase 8: Dashboard y Ajustes Finales (2-3 días)
├── 8.1 Implementar dashboard con buscador
├── 8.2 Implementar últimos movimientos
├── 8.3 Completar cobros restantes (eventos)
├── 8.4 Testing integral
└── 8.5 Ajustes de UI/UX

Total estimado: 22-30 días de desarrollo
```

## Convenciones de Código

### PHP
- **PSR-12** para estilo de código
- **Namespaces**: `CDC\Admin\{Module}`
- **Clases**: PascalCase (ej: `PersonasService`, `CajaController`)
- **Métodos**: camelCase (ej: `crearSocio()`, `cobrarCuota()`)
- **Constantes**: UPPER_SNAKE_CASE (ej: `CDC_VERSION`, `CDC_PLUGIN_DIR`)

### Base de Datos
- **Tablas**: `wp_cdc_{entidad}` (snake_case)
- **Columnas**: snake_case (ej: `fecha_alta`, `monto`, `comprobante_id`)
- **IDs**: siempre `id` como primary key autoincremental

### JavaScript
- **Variables**: camelCase
- **Funciones**: camelCase
- **Constantes**: UPPER_SNAKE_CASE
- **Usar** jQuery cuando sea necesario (ya incluido en WP)

### CSS
- **Clases**: kebab-case con prefijo `cdc-` (ej: `.cdc-button-primary`, `.cdc-card`)
- **IDs**: camelCase con prefijo `cdc` (ej: `#cdcPersonasList`)

## Recursos Útiles

- [WordPress Developer Resources](https://developer.wordpress.org/)
- [WooCommerce Developer Docs](https://woocommerce.github.io/code-reference/)
- [WP REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Mercado Pago API](https://www.mercadopago.com.ar/developers/es/reference)
- [AFIP - ARCA Facturación Electrónica](https://www.afip.gob.ar/fe/)
