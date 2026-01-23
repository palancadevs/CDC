# Integración WooCommerce + ARCA

## Resumen

Sistema completo de facturación electrónica integrado con WooCommerce. Cada cobro (cuota socio, cuota taller, alquiler sala) crea automáticamente:
1. MovimientoCaja (registro contable)
2. Orden WooCommerce (orden de venta)
3. Factura ARCA (comprobante electrónico)

## Modo Sandbox/Test

El sistema funciona en modo SANDBOX cuando no hay credenciales ARCA configuradas:
- ✅ Genera comprobante_id ficticios (formato: `SANDBOX-YYYYMMDD-####`)
- ✅ Registra toda la información en logs
- ✅ No requiere conexión a ARCA
- ✅ Permite probar el flujo completo sin API real

## Arquitectura

### Flujo Completo de Cobro

```
1. Usuario cobra cuota socio
   ↓
2. POST /wp-json/cdc/v1/cobros/cuota-socio
   ↓
3. Sistema marca cuota(s) como pagada(s)
   ↓
4. Sistema crea MovimientoCaja (ingreso)
   ↓
5. Sistema crea producto WC "Cuota Socio CDC" (si no existe)
   ↓
6. Sistema crea orden WooCommerce
   - customer_id: 0 (guest)
   - items: producto + cantidad + precio
   - meta_data:
     * _cdc_movimiento_id
     * _cdc_persona_id
     * _cdc_tipo_cobro
     * _cdc_cuota_ids
   ↓
7. Sistema marca orden como PAID (payment_complete)
   ↓
8. WooCommerce trigger: order_status_processing
   ↓
9. ARCA Service detecta orden pagada
   ↓
10. ARCA Service genera factura
    - SANDBOX: comprobante_id ficticio
    - PRODUCCIÓN: llamada a ARCA API
   ↓
11. Sistema guarda comprobante_id:
    - orden WC (_cdc_comprobante_id)
    - movimiento_caja.comprobante_id
    - cuota_socio.comprobante_id
```

## Archivos Creados/Modificados

### Nuevos Archivos

1. **class-woocommerce-service.php** (231 líneas)
   - `get_or_create_category()` - Categoría "Servicios CDC"
   - `get_or_create_cuota_socio_product()` - Producto genérico cuota socio
   - `sync_taller_product($taller_id, $taller_data)` - Sincroniza taller como producto
   - `sync_sala_product($sala_id, $sala_data)` - Sincroniza sala como producto
   - `create_order($args)` - Crea orden WC con metadata CDC
   - `get_order_by_movimiento($movimiento_id)` - Busca orden por movimiento

2. **class-arca-service.php** (327 líneas)
   - `process_order_invoice($order_id)` - Procesa factura al detectar orden pagada
   - `send_invoice_to_arca($invoice_data)` - Envía factura a ARCA (con modo sandbox)
   - `prepare_invoice_data($order, $persona, $tipo_cobro)` - Prepara datos para ARCA
   - `mark_invoice_failed($order_id, $movimiento_id, $error)` - Marca factura como fallida
   - `update_related_entities_with_comprobante($order, $comprobante_id)` - Actualiza entidades
   - `retry_invoice($order_id)` - Reintenta factura fallida

3. **class-arca-controller.php** (146 líneas)
   - `POST /wp-json/cdc/v1/arca/retry/{order_id}` - Reintentar factura
   - `GET /wp-json/cdc/v1/arca/failed-orders` - Listar órdenes con error
   - `GET /wp-json/cdc/v1/arca/status/{order_id}` - Ver estado de factura

4. **add-comprobante-id-fields.sql**
   - Migraciones para agregar campos comprobante_id

### Archivos Modificados

1. **class-cobros-controller.php**
   - `cobrar_cuota_socio()` - Agregada creación de orden WC
   - `cobrar_cuota_taller()` - Agregada creación de orden WC

2. **class-talleres-controller.php**
   - Agregado endpoint `POST /talleres/sync-wc-products`
   - Sincronización manual de todos los talleres

3. **cdc-api.php**
   - Agregados includes de WC service y ARCA service
   - Inicialización de ARCA hooks en `init()`
   - Sincronización automática en `activate()`
   - Registro de ARCA controller

4. **schema.php**
   - Agregado campo `comprobante_id` en `cdc_movimientos_caja`
   - Agregado campo `comprobante_id` en `cdc_reservas_salas`

## Configuración

### 1. Ejecutar Migraciones SQL

Abrir phpMyAdmin o ejecutar en MySQL:

```bash
# Conectar a la base de datos local
mysql -u root -proot local < app/sql/add-comprobante-id-fields.sql
```

O ejecutar manualmente:

```sql
-- Agregar comprobante_id a movimientos_caja
ALTER TABLE wp_cdc_movimientos_caja
ADD COLUMN comprobante_id VARCHAR(100) DEFAULT NULL,
ADD INDEX idx_comprobante_id (comprobante_id);

-- Agregar comprobante_id a reservas_salas
ALTER TABLE wp_cdc_reservas_salas
ADD COLUMN comprobante_id VARCHAR(100) DEFAULT NULL,
ADD INDEX idx_comprobante_id (comprobante_id);
```

### 2. Reactivar Plugin

En **WP Admin > Plugins**:
1. Desactivar "CDC API"
2. Activar "CDC API"

Esto ejecutará:
- Actualización de tablas (dbDelta)
- Creación de producto "Cuota Socio CDC"
- Sincronización de todos los talleres como productos WC
- Sincronización de todas las salas como productos WC

Verificar en logs (`logs/php/error.log`):
```
CDC: Producto "Cuota Socio" creado/sincronizado - ID: 123
CDC: 5 talleres sincronizados como productos WooCommerce
CDC: 3 salas sincronizadas como productos WooCommerce
CDC ARCA: Modo SANDBOX activado (sin credenciales configuradas)
```

### 3. Verificar Productos WooCommerce

En **WP Admin > Productos**:
- Filtrar por categoría "Servicios CDC"
- Deberías ver:
  - Cuota Socio CDC
  - Cuota Taller: [nombre taller] (por cada taller)
  - Alquiler Sala: [nombre sala] (por cada sala)
- Todos con visibilidad "Oculto" (no aparecen en tienda)

## Pruebas

### Test 1: Cobrar Cuota Socio

```bash
curl -X POST http://localhost:10013/wp-json/cdc/v1/cobros/cuota-socio \
  -H "Content-Type: application/json" \
  -d '{
    "persona_id": 1,
    "cuota_ids": [1, 2],
    "medio_pago": "efectivo",
    "observaciones": "Pago de enero y febrero 2026"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Cuota(s) cobrada(s) correctamente",
  "data": {
    "movimiento_id": 123,
    "order_id": 456,
    "monto_total": 1000.00,
    "cuotas_pagadas": [
      {"id": 1, "mes": 1, "anio": 2026, "monto": "500.00"},
      {"id": 2, "mes": 2, "anio": 2026, "monto": "500.00"}
    ],
    "saldo_nuevo": 5000.00
  }
}
```

**Verificar:**
1. En `wp_cdc_movimientos_caja`:
   ```sql
   SELECT * FROM wp_cdc_movimientos_caja WHERE id = 123;
   -- Verificar: comprobante_id = 'SANDBOX-20260122-XXXX'
   ```

2. En WooCommerce Orders:
   ```sql
   SELECT * FROM wp_posts WHERE ID = 456 AND post_type = 'shop_order';
   SELECT * FROM wp_postmeta WHERE post_id = 456;
   -- Verificar metadata: _cdc_movimiento_id, _cdc_comprobante_id
   ```

3. En `wp_cdc_cuota_socio`:
   ```sql
   SELECT * FROM wp_cdc_cuota_socio WHERE id IN (1, 2);
   -- Verificar: pagada = 1, comprobante_id = 'SANDBOX-20260122-XXXX'
   ```

4. En logs (`logs/php/error.log`):
   ```
   CDC ARCA SANDBOX: Generando comprobante ficticio - SANDBOX-20260122-0001
   CDC ARCA: Invoice created successfully - Order: 456, Comprobante: SANDBOX-20260122-0001
   ```

### Test 2: Cobrar Cuota Taller

```bash
curl -X POST http://localhost:10013/wp-json/cdc/v1/cobros/cuota-taller \
  -H "Content-Type: application/json" \
  -d '{
    "persona_id": 1,
    "cuota_ids": [5],
    "medio_pago": "transferencia",
    "observaciones": "Cuota taller de teatro enero 2026"
  }'
```

**Verificar:**
- Producto WC del taller existe
- Orden WC creada con producto del taller
- Comprobante_id generado y propagado

### Test 3: Ver Órdenes con Errores de Facturación

```bash
curl http://localhost:10013/wp-json/cdc/v1/arca/failed-orders
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": [],
  "total": 0
}
```

(Vacío porque en sandbox no hay errores)

### Test 4: Ver Estado de Factura

```bash
curl http://localhost:10013/wp-json/cdc/v1/arca/status/456
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "order_id": 456,
    "order_status": "processing",
    "factura_status": "ok",
    "comprobante_id": "SANDBOX-20260122-0001",
    "error": null,
    "fecha": "2026-01-22 14:30:15",
    "movimiento_id": "123",
    "tipo_cobro": "cuota_socio"
  }
}
```

### Test 5: Reintentar Factura (simular error)

Para probar retry, primero simular error manualmente:

```sql
-- Marcar orden como error
UPDATE wp_postmeta SET meta_value = 'error'
WHERE post_id = 456 AND meta_key = '_cdc_factura_status';

-- Agregar mensaje de error
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
VALUES (456, '_cdc_factura_error', 'Error simulado de conexión ARCA');
```

Luego reintentar:

```bash
curl -X POST http://localhost:10013/wp-json/cdc/v1/arca/retry/456
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Factura generada correctamente"
}
```

## Configurar Modo Producción

Cuando tengas credenciales ARCA reales:

```sql
-- Insertar credenciales en wp_options
INSERT INTO wp_options (option_name, option_value) VALUES
('cdc_arca_api_endpoint', 'https://api.arca.gob.ar/v1'),
('cdc_arca_api_key', 'TU_API_KEY_REAL'),
('cdc_arca_api_secret', 'TU_API_SECRET_REAL');
```

El sistema automáticamente:
- Detectará que hay credenciales configuradas
- Desactivará modo SANDBOX
- Realizará llamadas reales a ARCA API

## Monitoreo y Troubleshooting

### Ver Logs

```bash
# Ver logs en tiempo real
tail -f logs/php/error.log

# Filtrar solo logs de ARCA
grep "CDC ARCA" logs/php/error.log

# Filtrar solo logs de sandbox
grep "SANDBOX" logs/php/error.log
```

### Queries Útiles

```sql
-- Ver órdenes WC con metadata CDC
SELECT p.ID, p.post_status, p.post_date,
       m1.meta_value AS movimiento_id,
       m2.meta_value AS comprobante_id,
       m3.meta_value AS factura_status
FROM wp_posts p
LEFT JOIN wp_postmeta m1 ON p.ID = m1.post_id AND m1.meta_key = '_cdc_movimiento_id'
LEFT JOIN wp_postmeta m2 ON p.ID = m2.post_id AND m2.meta_key = '_cdc_comprobante_id'
LEFT JOIN wp_postmeta m3 ON p.ID = m3.post_id AND m3.meta_key = '_cdc_factura_status'
WHERE p.post_type = 'shop_order'
ORDER BY p.post_date DESC
LIMIT 10;

-- Ver movimientos con comprobantes
SELECT id, tipo, concepto, monto, comprobante_id, fecha_movimiento
FROM wp_cdc_movimientos_caja
WHERE comprobante_id IS NOT NULL
ORDER BY fecha_movimiento DESC
LIMIT 10;

-- Ver cuotas socio pagadas con comprobante
SELECT cs.*, p.nombre, p.apellido
FROM wp_cdc_cuota_socio cs
JOIN wp_cdc_personas p ON cs.persona_id = p.id
WHERE cs.pagada = 1 AND cs.comprobante_id IS NOT NULL
ORDER BY cs.fecha_pago DESC
LIMIT 10;
```

### Problemas Comunes

#### Producto no encontrado
**Error**: `Error al obtener producto de cuota socio`

**Solución**: Reactivar plugin o ejecutar manualmente:
```bash
curl -X POST http://localhost:10013/wp-json/cdc/v1/talleres/sync-wc-products
```

#### Orden WC no se crea
**Error**: `Error al crear orden WooCommerce`

**Verificar**:
- WooCommerce está activado
- Producto existe y está publicado
- Logs de WooCommerce en `wc-logs/`

#### Factura no se genera
**Error**: Orden creada pero sin comprobante_id

**Verificar**:
1. Hook de WooCommerce se ejecutó:
   ```
   grep "woocommerce_order_status" logs/php/error.log
   ```

2. ARCA service inicializado:
   ```
   grep "CDC ARCA: Modo SANDBOX" logs/php/error.log
   ```

3. Orden tiene metadata CDC:
   ```sql
   SELECT * FROM wp_postmeta
   WHERE post_id = [ORDER_ID]
   AND meta_key LIKE '_cdc%';
   ```

## Endpoints REST Disponibles

### Cobros (con WC integration)
- `POST /cdc/v1/cobros/cuota-socio` - Crear orden WC automáticamente
- `POST /cdc/v1/cobros/cuota-taller` - Crear orden WC automáticamente
- `POST /cdc/v1/cobros/otro-ingreso` - Sin orden WC (próximamente)

### Talleres
- `POST /cdc/v1/talleres/sync-wc-products` - Sincronizar todos los talleres como productos

### ARCA
- `POST /cdc/v1/arca/retry/{order_id}` - Reintentar factura fallida
- `GET /cdc/v1/arca/failed-orders` - Listar órdenes con error de facturación
- `GET /cdc/v1/arca/status/{order_id}` - Ver estado de facturación de una orden

## Próximos Pasos

1. **Página de Administración ARCA**
   - Formulario para configurar credenciales
   - Dashboard con órdenes fallidas
   - Botones "Reintentar factura"

2. **Integración Completa de Alquileres**
   - Crear órdenes WC al confirmar reserva de sala
   - Actualizar comprobante_id en reservas_salas

3. **Webhooks de Mercado Pago**
   - Crear orden WC cuando MP notifica pago
   - Link automático pago MP → orden WC → factura ARCA

4. **Reportes**
   - Total facturado por período
   - Órdenes sin facturar
   - Errores de facturación por tipo

## Notas de Implementación

- **Idempotencia**: Verificar que orden no tenga comprobante_id antes de facturar
- **Transacciones**: Todo el flujo de cobro está en una transacción SQL
- **Reversiones**: No se implementó aún la anulación de facturas (reverso en ARCA)
- **Guest Checkout**: Todas las órdenes WC se crean como "guest" (customer_id=0)
- **Productos ocultos**: Todos los productos CDC tienen visibilidad "hidden"
- **Precio variable**: Producto "Cuota Socio" tiene precio $0, se setea por orden
