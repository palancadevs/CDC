# CDC Gestión - Documentación del Proyecto

## Índice de Documentación

### 📋 Requisitos y Especificaciones
- **[PRD.MD](./PRD.MD)** - Product Requirements Document completo con:
  - Backlog tipo Jira (Épicas y User Stories)
  - Especificación técnica detallada
  - Modelo de datos
  - Patrones de implementación

### 🎨 Diseño de Interfaz
- **[SCREENS.md](./SCREENS.md)** - Documentación de pantallas con mockups y referencias a user stories
- **[mockups/](./mockups/)** - Carpeta con todos los mockups de las pantallas

### 🏗️ Arquitectura
- **[../CLAUDE.md](../CLAUDE.md)** - Guía técnica para desarrollo con Claude Code

---

## Navegación Rápida por Módulos

### Módulo: Inicio / Dashboard
- **User Story**: US1.1 (ver [PRD.MD](./PRD.MD#us11--dashboard-inicio))
- **Mockup**: [1. Home.png](./mockups/1.%20Home.png)
- **Pantalla**: [Inicio](./SCREENS.md#1-inicio-dashboard)

### Módulo: Cobros
- **User Stories**: US4.1-4.6, US5.1-5.2 (ver [PRD.MD](./PRD.MD))
- **Mockup**: [2. Cobrar.png](./mockups/2.%20Cobrar.png)
- **Pantalla**: [Cobrar](./SCREENS.md#2-cobrar-selector-de-tipo-de-cobro)

### Módulo: Personas (Socios/Clientes)
- **User Stories**: US2.1-2.4 (ver [PRD.MD](./PRD.MD))
- **Mockups**:
  - [3. Personas.png](./mockups/3.%20Personas.png) - Listado
  - [4. ficha.png](./mockups/4.%20ficha.png) - Ficha de socio/cliente
  - [5. nuevo socio.png](./mockups/5.%20nuevo%20socio.png) - Alta de socio
- **Pantallas**:
  - [Personas - Listado](./SCREENS.md#3-personas-listado)
  - [Ficha Socio/Cliente](./SCREENS.md#4-ficha-de-sociocliente)
  - [Nuevo Socio](./SCREENS.md#5-nuevo-socio)

### Módulo: Caja
- **User Stories**: US3.1-3.3 (ver [PRD.MD](./PRD.MD))
- **Mockup**: [6. Registrar gasto.png](./mockups/6.%20Registrar%20gasto.png)
- **Pantalla**: [Registrar Gasto](./SCREENS.md#6-registrar-gasto)

### Módulo: Talleres
- **User Story**: US6.1 (ver [PRD.MD](./PRD.MD))
- **Mockup**: [7. Talleres.png](./mockups/7.%20Talleres.png)
- **Pantalla**: [Talleres](./SCREENS.md#7-talleres)

### Módulo: Alquiler de Salas
- **User Story**: US6.4 (ver [PRD.MD](./PRD.MD))
- **Mockup**: [8. Alquiler de salas.png](./mockups/8.%20Alquiler%20de%20salas.png)
- **Pantalla**: [Alquiler de Salas](./SCREENS.md#8-alquiler-de-salas)

---

## Arquitectura del Sistema

**IMPORTANTE:** Este es un sistema **frontend completo**, NO un plugin de wp-admin.

- **Frontend:** Aplicación web completa en el sitio raíz (`http://localhost:10013/`)
- **Backend:** WordPress + WooCommerce como REST API
- **Autenticación:** Login custom (DNI como usuario)
- **Acceso:** Sistema privado para uso interno exclusivamente

### Estructura Técnica
```
/app/public/
├── wp-content/
│   ├── themes/
│   │   └── cdc-sistema/        ← Tema custom (frontend completo)
│   │       ├── assets/         ← CSS, JS, imágenes
│   │       ├── templates/      ← Vistas del sistema
│   │       ├── includes/       ← Lógica PHP
│   │       └── functions.php   ← Setup del tema
│   └── plugins/
│       └── cdc-api/            ← REST API custom (backend)
```

## Flujo de Desarrollo Recomendado

### Fase 1: Setup Base Frontend (Épica E0)
1. Crear tema custom `cdc-sistema`
2. Implementar login custom (DNI + contraseña)
3. Crear layout base con sidebar de navegación
4. Implementar sistema de roles y permisos
5. Dashboard/Inicio con acciones rápidas

### Fase 2: Módulo Personas (Épica E2)
1. Crear tabla `cdc_persona`
2. Implementar Listado de Personas (US2.1)
3. Implementar Alta de Socio (US2.2)
4. Implementar Alta de Cliente (US2.3)
5. Implementar Ficha de Socio (US2.4)

### Fase 3: Módulo Caja (Épica E3)
1. Crear tabla `cdc_movimiento_caja`
2. Implementar Registrar Gasto (US3.1)
3. Implementar Listado de Caja (US3.2)
4. Implementar Ficha de Movimiento (US3.3)

### Fase 4: Módulo Cobros Base (Épica E4 - Parte 1)
1. Crear tablas de cuotas y alquileres
2. Implementar pantalla selector "Cobrar" (US4.1)
3. Implementar Cobrar Cuota Socio (US4.2)
4. Implementar Cobrar Otro Ingreso (US4.6)

### Fase 5: Integración WooCommerce + ARCA (Épica E4 - Parte 2)
1. Configurar creación automática de órdenes
2. Implementar hook de facturación ARCA (US4.7)
3. Probar flujo completo de cobro → orden → factura

### Fase 6: Mercado Pago (Épica E5)
1. Implementar modal asistido (US5.1)
2. Implementar webhook con idempotencia (US5.2)

### Fase 7: Módulos Adicionales (Épica E6)
1. Talleres (US6.1)
2. Eventos (US6.2)
3. Salas (US6.3)
4. Alquiler de salas (US6.4)

### Fase 8: Dashboard e Integraciones Finales (Épica E1)
1. Implementar Dashboard/Inicio (US1.1)
2. Completar cobros restantes (talleres, eventos, alquiler)
3. Testing integral y ajustes finales

---

## Convenciones del Proyecto

### Nombres de Archivos y Clases
- **Servicios API**: `{Modulo}Service.php` (ej: `PersonasService.php`) - En plugin cdc-api
- **Modelos**: `{Entidad}.php` (ej: `Persona.php`, `MovimientoCaja.php`) - En plugin cdc-api
- **Templates Frontend**: `template-{modulo}.php` (ej: `template-personas.php`) - En tema
- **Partials**: `partials/{componente}.php` (ej: `partials/sidebar.php`) - En tema

### Prefijos de Base de Datos
- Todas las tablas custom: `cdc_*`
- Ejemplo: `cdc_persona`, `cdc_movimiento_caja`, `cdc_cuota_socio`

### Nomenclatura en Español
- Variables, campos de BD, comentarios: **Español**
- Código y funciones PHP/WP: **Inglés** (cuando sea estándar de WordPress)
- UI y mensajes al usuario: **Español**

---

## Recursos Externos

- [WordPress Codex](https://codex.wordpress.org/)
- [WooCommerce Developer Docs](https://woocommerce.github.io/code-reference/)
- [Mercado Pago Developers](https://www.mercadopago.com.ar/developers/)
- [ARCA - Facturación Electrónica](https://www.afip.gob.ar/)
