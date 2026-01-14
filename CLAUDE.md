# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is "CDC Gestión" - a **custom frontend application** built on WordPress + WooCommerce for Casa de la Cultura (Cultural Center). The system handles members (socios), clients, workshops (talleres), events, room rentals (alquiler de salas), cash flow, and automatic invoicing through ARCA.

**IMPORTANT:** This is a **frontend-only system** (NOT wp-admin). The entire public site is the management application.

**Technology Stack:**
- WordPress 6.8+ with WooCommerce 10.4.3
- Local by Flywheel development environment
- PHP 7.4+
- MySQL database
- Custom plugin architecture (to be developed)

**Key Business Domains:**
- **Personas**: Members (socios) and clients with different fee structures
- **Caja**: Cash flow management (ingresos/egresos) with full audit trail
- **Cobros**: Payment collection for membership fees, workshop fees, event tickets, and room rentals
- **Facturación**: Automatic invoice generation via ARCA integration
- **Talleres**: Workshop management with enrollments and attendance
- **Eventos**: Event management with ticket sales
- **Salas**: Room management with rental/booking system

## Development Environment

**Local Setup (Local by Flywheel):**
- Site root: `/Users/joanromero/Local Sites/cdc-gestion/`
- WordPress root: `app/public/`
- Database: MySQL with credentials in `wp-config.php` (DB: local, user: root, pass: root)
- Server config: `conf/` (nginx, php, mysql)
- Logs: `logs/`

**WordPress Configuration:**
- Table prefix: `wp_`
- Environment: `local`
- Debug mode: `false` by default

## Architecture & Data Model

### Core Architecture Pattern

The system follows a **consistent payment flow** for all transactions:
1. Update business entity (cuota/reserva/inscripción/etc.)
2. Create MovimientoCaja record (source of truth for accounting)
3. Create/update WooCommerce order
4. Mark order as paid (triggers ARCA invoice generation)
5. Store comprobanteId (invoice ID) in all related entities

### Custom Database Tables

The system uses custom tables (not WordPress custom post types) for financial data integrity:

**cdc_persona**
- Members (socios) and clients
- Fields: tipo (socio|cliente), nombre, apellido, dni, tel, email, domicilio, fecha_alta, estado, categoria, subcategoria

**cdc_cuota_socio**
- Monthly membership fees with 12-month grid per member
- Fields: socio_id, anio, mes, monto, pagada (bool), fecha_pago, medio_pago, comprobante_id

**cdc_movimiento_caja**
- **Source of truth** for all financial movements
- Fields: fecha_hora, tipo (ingreso|egreso), monto, medio_pago, responsable_user_id, concepto_tipo, concepto_id, observaciones, comprobante_id, estado (activo|anulado), motivo_anulacion
- **Never delete** financial records - use anulación/reverso pattern

**cdc_alquiler_sala**
- Room rental bookings
- Fields: sala_id, solicitante, fecha, horario_inicio, horario_fin, precio_acordado, sena_monto, saldo, estado

**Additional tables** (as needed):
- cdc_taller, cdc_inscripcion_taller, cdc_cuota_taller, cdc_asistencia_taller
- cdc_evento, cdc_entrada_evento
- cdc_sala, cdc_tallerista

### Role-Based Access Control

**Admin**: Full system access
**Tesorería (Treasury)**: Edit catalogs/rates, void transactions, retry invoicing
**Recepción (Reception)**: Create persons, register payments/expenses (per configuration)

All critical actions must log: user_id + timestamp

## Plugin Structure

**Primary custom plugin: `cdc-admin`** (to be created in `wp-content/plugins/cdc-admin/`)

```
cdc-admin/
├── cdc-admin.php (main plugin file)
├── includes/
│   ├── admin-pages/     # UI pages (Inicio, Personas, Ficha, Caja, Cobrar, etc.)
│   ├── services/        # Business logic
│   │   ├── PersonasService.php
│   │   ├── CajaService.php
│   │   ├── CobrosService.php
│   │   ├── FacturacionService.php
│   │   └── MercadoPagoService.php
│   ├── models/          # Database models
│   └── rest-api/        # WP REST API endpoints
├── assets/
│   ├── css/
│   └── js/
└── templates/
```

### REST API Endpoints

All under `/wp-json/cdc/v1/`:
- `POST /personas` - Create member/client
- `GET /personas?query=...` - Search persons
- `POST /gastos` - Register expense
- `GET /caja?from=...&to=...` - Get cash movements
- `POST /cobros/cuota-socio` - Collect membership fee
- `POST /cobros/cuota-taller` - Collect workshop fee
- `POST /cobros/entrada-evento` - Collect event ticket
- `POST /cobros/alquiler-sala` - Collect room rental (deposit/balance)
- `POST /cobros/otro` - Collect other income
- `POST /mp/webhook` - Mercado Pago webhook handler

## Payment Integration Patterns

### Manual Payments (Efectivo/Transferencia/Tarjeta)
1. UI confirms payment
2. Create/update WooCommerce order
3. Mark order as paid (processing or completed status)
4. Create MovimientoCaja record
5. Trigger ARCA invoicing (or mark as "pending/error" if fails)

### Mercado Pago Integration

**Mode A: Integrated (recommended)**
- Create MP payment with unique `external_reference`
- Webhook receives payment notification
- Validate and fetch payment details
- Check idempotency (prevent duplicates)
- Resolve order by external_reference
- Execute standard payment flow

**Mode B: Assisted (manual link)**
- Generate MP payment
- List recent payments in modal
- Reception staff manually links payment to order
- Execute standard payment flow

### Idempotency Requirements

**Critical**: All payment processing must be idempotent
- Store processed `mp_payment_id` (in table `cdc_mp_events` or order meta)
- Before creating MovimientoCaja, check if already exists
- For chargebacks/reversals: create reversal movement (egreso), **never delete**

## ARCA Invoice Generation

- Triggered by WooCommerce order status change (processing or completed)
- On success: store `comprobanteId` in:
  - `cdc_movimiento_caja.comprobante_id`
  - Related entity (cuota_socio, alquiler, etc.)
  - Optional: WooCommerce order meta
- On failure: set `factura_status = pending|error|ok`
- Provide "Retry Invoice" action for Tesorería/Admin roles

## UI Pages Overview

**Inicio (Dashboard)**
- Quick search (Name/Apellido/DNI)
- 3 primary actions: Cobrar / Registrar gasto / Personas
- Recent movements list (today)

**Personas (Members/Clients)**
- List with filters (Todos/Socios/Clientes, Activos/Inactivos)
- Search by name/DNI
- Actions: Ver ficha, Nuevo socio, Nuevo cliente

**Ficha de Socio/Cliente**
- Quick actions: Cobrar cuota socio, Cobrar cuota taller, Inscribir a taller, Historial
- Tabs: Información, Cuota socio (12-month grid), Talleres, Historial, Notas
- Status indicator: "Al día" / "Debe X meses"

**Cobrar (Payment Collection)**
- Type selector: Cuota socio / Cuota taller / Entrada evento / Alquiler sala / Otro ingreso
- Each type has specific form with validation
- Mercado Pago modal for assisted payments

**Caja (Cash Flow)**
- Movement list with filters (date range, type)
- Balance summary: ingresos, egresos, neto
- Link to movement detail (ficha)

**Registrar Gasto (Expense)**
- Form: fecha/hora, monto, descripción, categoría, medio_pago, adjunto, observaciones
- Creates MovimientoCaja type EGRESO

## Key Business Rules

1. **No data deletion for financial records** - use anulación (void) with reason
2. **Consistent payment flow** - all collections follow the same 5-step pattern
3. **Audit trail** - all critical actions log user + timestamp
4. **Idempotency** - payment webhooks must not create duplicates
5. **Invoice consistency** - comprobanteId stored in all related entities
6. **Role enforcement** - permissions checked on all critical operations
7. **12-month grid** - membership fees generated annually when member created

## Testing & Development

**Important considerations:**
- Test with ARCA in sandbox/test mode before production
- Validate idempotency with duplicate webhook events
- Test all payment flows (manual + MP integrated + MP assisted)
- Verify comprobanteId propagation across all entities
- Test role permissions thoroughly
- Validate that no financial records can be deleted (only voided)

## Common Development Commands

**WP-CLI (if available):**
```bash
wp plugin list
wp theme list
wp db export
wp cache flush
```

**Database access:**
```bash
mysql -u root -proot local
```

**Activate custom plugin (after creation):**
Navigate to WP Admin > Plugins > Activate `cdc-admin`

## Important Notes

- This is a **Local by Flywheel** environment - paths may differ from standard WordPress
- WooCommerce is already installed (v10.4.3) - leverage its order/payment infrastructure
- Financial data uses custom tables for consistency, not CPT (Custom Post Types)
- Spanish is the primary language - keep all user-facing text in Spanish
- See PRD.MD for complete functional requirements and user stories

## Documentation Structure

All project documentation is organized in the `/docs` folder:

**📋 [docs/README.md](docs/README.md)** - Main documentation index
- Quick navigation by module (Inicio, Cobros, Personas, Caja, Talleres, Salas)
- Links between PRD user stories and mockups
- Recommended development workflow by phases
- Project conventions and naming standards

**📄 [docs/PRD.MD](docs/PRD.MD)** - Product Requirements Document
- Complete backlog with Epics and User Stories (Jira-style)
- Technical specifications
- Data model definitions
- Implementation patterns

**🎨 [docs/SCREENS.md](docs/SCREENS.md)** - Detailed UI/UX specifications
- Every screen documented with:
  - Visual mockup reference
  - Related user stories
  - UI elements breakdown
  - Business logic
  - API endpoints
  - Navigation flows
- Design system (colors, typography, buttons)

**🖼️ [docs/mockups/](docs/mockups/)** - Visual mockups folder
- `1. Home.png` - Dashboard/Inicio
- `2. Cobrar.png` - Payment collection selector
- `3. Personas.png` - Members/clients list
- `4. ficha.png` - Member/client profile
- `5. nuevo socio.png` - New member form
- `6. Registrar gasto.png` - Expense registration
- `7. Talleres.png` - Workshops management
- `8. Alquiler de salas.png` - Room rentals list

### When Implementing a Feature

1. **Start with docs/README.md** to locate the module and user stories
2. **Read the User Story** in docs/PRD.MD for functional requirements
3. **Check the Mockup** in docs/mockups/ to see the visual design
4. **Review docs/SCREENS.md** for detailed UI specifications and endpoints
5. **Follow the architecture patterns** defined in this CLAUDE.md
6. **Implement following the 5-step payment flow** for any financial transaction

Example workflow for implementing "Cobrar Cuota Socio":
```
1. docs/README.md → Navigate to "Módulo: Cobros" → Find US4.2
2. docs/PRD.MD → Read US4.2 acceptance criteria
3. docs/mockups/2. Cobrar.png → View payment UI design
4. docs/SCREENS.md → Section #2 → Get form fields, validation, and endpoints
5. Implement following consistent payment flow pattern (5 steps)
```

## References

- **Main Docs Index**: [docs/README.md](docs/README.md)
- **PRD**: [docs/PRD.MD](docs/PRD.MD)
- **Screens Spec**: [docs/SCREENS.md](docs/SCREENS.md)
- **Mockups**: [docs/mockups/](docs/mockups/)
- **WooCommerce Docs**: https://woocommerce.com/documentation/
- **ARCA Integration**: Consult ARCA API documentation for Argentina electronic invoicing
- **Mercado Pago SDK**: https://www.mercadopago.com.ar/developers/
