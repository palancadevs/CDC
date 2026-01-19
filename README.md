# CDC Gestión - Sistema de Gestión para Casa de la Cultura

Sistema completo de gestión administrativa para Casa de la Cultura, construido sobre WordPress + WooCommerce.

## 📋 Descripción del Proyecto

CDC Gestión es un **sistema frontend personalizado** (NO un sitio web tradicional) que maneja:
- 👥 Socios y clientes
- 📚 Talleres y eventos
- 🏛️ Alquiler de salas
- 💰 Sistema de caja (ingresos/egresos)
- 🧾 Cobros y recibos
- 📄 Facturación automática via ARCA

**IMPORTANTE**: La página pública de WordPress (http://localhost:10013/) ES la aplicación de gestión.

## 🚀 Quick Start

### Prerequisitos
- Local by Flywheel instalado
- MySQL running
- PHP 7.4+

### Instalación

1. **Clonar repositorio en Local Sites**
```bash
cd ~/Local\ Sites/
git clone https://github.com/palancadevs/CDC.git cdc-gestion
```

2. **Importar sitio en Local by Flywheel**
   - Abrir Local
   - Click "+" → Add Existing Site
   - Seleccionar carpeta `cdc-gestion`

3. **Activar plugin y tema**
   - Ir a http://localhost:10013/wp-admin
   - Plugins → Activar "CDC API"
   - Appearance → Themes → Activar "CDC Sistema"

4. **Verificar instalación**
   - Ir a http://localhost:10013/diagnostico/
   - Verificar que todas las tablas estén creadas
   - Verificar que API REST esté disponible

## 🧪 Testing del Sistema

### Tests Automatizados

El sistema incluye una suite completa de tests interactivos:

**Acceso**: http://localhost:10013/tests-v2/

**Cobertura de tests** (13 tests):
- ✅ API REST disponible y funcionando
- ✅ Endpoints de personas (GET, POST, búsqueda)
- ✅ Endpoints de caja (movimientos, balance)
- ✅ Endpoints de talleres (GET, POST)
- ✅ Creación de socios y clientes
- ✅ Sistema de recibos
- ✅ Registro de gastos
- ✅ Cálculo automático de balance

**Cómo ejecutar**:
1. Abrir http://localhost:10013/tests-v2/
2. Click en "▶️ Ejecutar Todos los Tests"
3. Ver resultados en tiempo real
4. Verificar que todos los tests pasen (13/13)

### Tests desde Terminal

También puedes ejecutar tests via curl:

```bash
# Test básico de API
curl -s "http://localhost:10013/wp-json/cdc/v1/" | python3 -m json.tool

# Listar personas
curl -s "http://localhost:10013/wp-json/cdc/v1/personas" | python3 -m json.tool

# Balance de caja
curl -s "http://localhost:10013/wp-json/cdc/v1/caja/balance" | python3 -m json.tool
```

**Resultado esperado**: Todos los endpoints deben responder con `{"success": true, "data": ...}`

## 🔧 Herramientas de Diagnóstico

### Panel de Diagnóstico

**URL**: http://localhost:10013/diagnostico/

Muestra:
- ✅ Estado de tablas de base de datos (13 tablas custom)
- ✅ Estado del plugin CDC API
- ✅ Estado del tema CDC Sistema
- ✅ Endpoints REST API disponibles
- ✅ Configuración de WordPress
- ✅ Páginas del sistema creadas

### Verificación de Páginas

**URL**: http://localhost:10013/verificar-paginas.php

Verifica que todas las páginas del sistema estén creadas:
- Personas
- Cobrar
- Registrar Gasto
- Talleres
- Eventos
- Salas
- Diagnóstico
- Tests

### Instalador de Tablas

**URL**: http://localhost:10013/instalar-tablas/

Permite reinstalar las tablas de base de datos si es necesario.

## 📊 Estado Actual del Proyecto

### ✅ Completado (Phase 1)

**Backend (Plugin CDC API)**:
- ✅ 13 tablas custom de base de datos
- ✅ 9 modelos de datos (Persona, Socio, Cliente, Recibo, MovimientoCaja, etc.)
- ✅ 6 servicios de negocio (PersonaService, CajaService, ReciboService, etc.)
- ✅ 6 controladores REST API
- ✅ Endpoints completos para personas, caja, recibos, talleres, eventos, salas

**Frontend (Tema CDC Sistema)**:
- ✅ Layout base con header y sidebar
- ✅ Sistema de navegación
- ✅ Dashboard/Inicio
- ✅ Páginas: Personas, Cobrar, Registrar Gasto, Talleres, Eventos, Salas
- ✅ Sistema de notificaciones toast
- ✅ Integración con API REST
- ✅ Sin autenticación (Phase 1 - sistema abierto para testing)

**Testing y Herramientas**:
- ✅ Suite completa de tests (13+ tests automatizados)
- ✅ Panel de diagnóstico
- ✅ Verificadores de páginas y templates
- ✅ Backup de base de datos (app/sql/local.sql)

### 🚧 En Desarrollo

- ⏳ Integración con WooCommerce para órdenes
- ⏳ Integración con ARCA para facturación
- ⏳ Sistema de roles y permisos (Phase 2)
- ⏳ Integración con Mercado Pago
- ⏳ Reportes y estadísticas

## 📁 Estructura del Proyecto

```
cdc-gestion/
├── README.md                          # Este archivo
├── CLAUDE.md                          # Guía técnica para Claude Code
├── PROJECT-STRUCTURE.md               # Estructura detallada
├── TESTING-GUIDE.md                   # Guía de testing (actualizada)
├── docs/                              # Documentación completa
│   ├── README.md                      # Índice de documentación
│   ├── PRD.MD                         # Product Requirements Document
│   ├── SCREENS.md                     # Especificación de pantallas
│   └── mockups/                       # Mockups visuales
├── app/public/                        # WordPress root
│   ├── wp-content/
│   │   ├── plugins/
│   │   │   └── cdc-api/               # ✅ Plugin backend
│   │   └── themes/
│   │       └── cdc-sistema/           # ✅ Tema frontend
│   ├── verificar-paginas.php          # Herramienta de verificación
│   ├── verificar-templates.php        # Verificador de templates
│   └── crear-paginas-faltantes.php    # Creador de páginas
└── app/sql/                           # Backups de base de datos
    └── local.sql                      # Snapshot actual
```

## 🔗 URLs Importantes

| Página | URL | Descripción |
|--------|-----|-------------|
| **Dashboard** | http://localhost:10013/ | Página principal del sistema |
| **Tests Automatizados** | http://localhost:10013/tests-v2/ | Suite de tests interactivos |
| **Diagnóstico** | http://localhost:10013/diagnostico/ | Panel de diagnóstico |
| **Personas** | http://localhost:10013/personas/ | Gestión de socios/clientes |
| **Cobrar** | http://localhost:10013/cobrar/ | Sistema de cobros |
| **Caja** | http://localhost:10013/caja/ | Movimientos de caja |
| **Talleres** | http://localhost:10013/talleres/ | Gestión de talleres |
| **API REST** | http://localhost:10013/wp-json/cdc/v1/ | Documentación de API |
| **WP Admin** | http://localhost:10013/wp-admin/ | Panel de WordPress |

## 🌐 API REST

Base URL: `/wp-json/cdc/v1/`

### Endpoints Principales

**Personas**
- `GET /personas` - Listar personas
- `POST /personas` - Crear socio/cliente
- `GET /personas/{id}` - Obtener ficha
- `GET /personas/search?query=...` - Buscar

**Caja**
- `GET /caja/movimientos/today` - Movimientos de hoy
- `GET /caja/balance` - Balance actual
- `POST /caja/gastos` - Registrar gasto
- `POST /caja/apertura` - Abrir caja
- `POST /caja/cierre` - Cerrar caja

**Recibos**
- `POST /recibos` - Crear recibo
- `GET /recibos/recent` - Recibos recientes
- `GET /recibos/{id}` - Detalle de recibo

**Talleres**
- `GET /talleres` - Listar talleres
- `POST /talleres` - Crear taller
- `GET /talleres/{id}` - Ficha de taller

Ver documentación completa: http://localhost:10013/wp-json/cdc/v1/

## 💾 Base de Datos

**Database**: `local`
**User**: `root`
**Password**: `root`
**Prefix**: `wp_`

### Tablas Custom CDC (13 tablas)

- `wp_cdc_personas` - Socios y clientes
- `wp_cdc_recibos` - Recibos de cobro
- `wp_cdc_movimientos_caja` - Movimientos de caja
- `wp_cdc_gastos` - Registro de gastos
- `wp_cdc_talleres` - Catálogo de talleres
- `wp_cdc_eventos` - Catálogo de eventos
- `wp_cdc_salas` - Catálogo de salas
- `wp_cdc_reservas_salas` - Reservas de salas
- ... (y más)

Ver esquema completo en: `app/public/wp-content/plugins/cdc-api/includes/database/schema.php`

## 🧑‍💻 Desarrollo

### Convenciones de Código

**PHP**:
- PSR-12 para estilo
- Namespaces: No usados (WordPress style)
- Clases: `class-nombre-clase.php`
- Prefijo: `CDC_` para todas las clases

**JavaScript**:
- Variables: camelCase
- Objetos globales: `CDCAPI`, `CDC`
- Usar jQuery (incluido en WordPress)

**CSS**:
- Clases: kebab-case con prefijo `cdc-`
- IDs: camelCase con prefijo `cdc`

### Workflow de Desarrollo

1. Crear rama feature desde `development`
2. Desarrollar funcionalidad
3. Ejecutar tests (http://localhost:10013/tests-v2/)
4. Commit con mensaje descriptivo
5. Push a feature branch
6. Merge a `development`

### Branches

- `main` - Producción (protegido)
- `development` - Desarrollo principal
- `feature/*` - Features en desarrollo

## 🐛 Troubleshooting

### Las páginas no existen
**Solución**: Ve a http://localhost:10013/verificar-paginas.php y sigue las instrucciones

### API devuelve error 404
**Solución**:
1. Verifica que el plugin CDC API esté activado
2. Ve a Settings → Permalinks y click "Save Changes"
3. Verifica en http://localhost:10013/wp-json/cdc/v1/

### Tests fallan
**Solución**:
1. Verifica que todas las tablas existan: http://localhost:10013/diagnostico/
2. Reinstala tablas: http://localhost:10013/instalar-tablas/
3. Revisa logs en `logs/php/error.log`

### Balance de caja incorrecto
**Solución**: Verifica que todos los movimientos tengan `saldo_anterior` y `saldo_nuevo` correctos en la tabla `wp_cdc_movimientos_caja`

## 📚 Documentación Adicional

- **PRD completo**: [docs/PRD.MD](docs/PRD.MD)
- **Especificación de pantallas**: [docs/SCREENS.md](docs/SCREENS.md)
- **Guía de testing**: [TESTING-GUIDE.md](TESTING-GUIDE.md)
- **Estructura del proyecto**: [PROJECT-STRUCTURE.md](PROJECT-STRUCTURE.md)
- **Guía para Claude Code**: [CLAUDE.md](CLAUDE.md)

## 📞 Contacto

Proyecto desarrollado para **Casa de la Cultura**

---

**Última actualización**: 2026-01-19
**Versión**: 0.1.0 (Phase 1 - Testing & Base)
**Estado**: ✅ Sistema base funcional con tests completos
