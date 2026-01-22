# 🧪 CDC Sistema - Suite de Tests Automatizados

## Descripción

Suite completa de tests automatizados para verificar la funcionalidad del sistema CDC. Los tests cubren endpoints REST, validaciones, integración WooCommerce y facturación ARCA.

## Ejecutar Tests

### Ejecución Rápida

```bash
./run-tests.sh
```

Este script ejecuta todas las suites de tests y genera un reporte completo.

### Ejecución Individual

```bash
# Solo tests de integración
bash tests/integration/test-suite.sh

# Solo tests de validación
bash tests/integration/test-validations.sh
```

## Cobertura de Tests

### Suite 1: Tests de Integración (16 tests)

**TEST 1: Conectividad**
- ✅ WordPress responde correctamente
- ✅ API REST está disponible

**TEST 2: CRUD de Personas**
- ✅ Crear persona (cliente)
- ✅ Obtener persona creada

**TEST 3: Socios y Cuotas**
- ✅ Crear socio con generación de cuotas
- ✅ Verificar generación de 12 cuotas

**TEST 4: Cobros y WooCommerce**
- ✅ Obtener cuotas pendientes del socio
- ✅ Cobrar cuota socio (con WooCommerce)

**TEST 5: ARCA Invoicing (Sandbox)**
- ✅ Verificar factura ARCA generada
- ✅ Verificar que comprobante_id es de sandbox

**TEST 6: Talleres y WooCommerce**
- ✅ Listar talleres
- ✅ Sincronizar talleres como productos WooCommerce

**TEST 7: Búsqueda y Filtros**
- ✅ Buscar persona por query
- ✅ Filtrar por tipo (socios)

**TEST 8: Movimientos de Caja**
- ✅ Listar movimientos de caja
- ✅ Obtener balance actual

### Suite 2: Tests de Validación (9 tests)

**TEST 1: Validaciones de Persona**
- ✅ Crear persona sin nombre (debe fallar)
- ✅ Crear persona sin DNI (debe fallar)
- ✅ Crear persona con DNI duplicado (debe fallar)

**TEST 2: Validaciones de Cobros**
- ✅ Cobrar sin persona_id (debe fallar)
- ✅ Cobrar sin medio_pago (debe fallar)
- ✅ Cobrar con medio_pago inválido (debe fallar)

**TEST 3: Casos Edge - Cuotas**
- ✅ Intentar cobrar cuota ya pagada (debe fallar)

**TEST 4: Manejo de Errores**
- ✅ Endpoint no existente (404)
- ✅ Persona no existente (404)

## Resultados

```
╔═══════════════════════════════════════════════════════════╗
║                    REPORTE FINAL                          ║
╚═══════════════════════════════════════════════════════════╝

Suites ejecutadas: 2
Suites exitosas:   2
Suites fallidas:   0

Resultados por suite:
  1. Tests de Integración:  ✓ PASSED
  2. Tests de Validación:   ✓ PASSED
```

**Total: 25/25 tests PASSED (100%)**

## Logs

Los logs de cada ejecución se guardan automáticamente en:
```
tests/logs/test-run-YYYYMMDD-HHMMSS.log
```

## Estructura de Archivos

```
tests/
├── integration/
│   ├── test-suite.sh          # Suite principal de integración
│   └── test-validations.sh    # Tests de validaciones
└── logs/
    └── test-run-*.log         # Logs de ejecuciones

run-tests.sh                   # Script maestro que ejecuta todas las suites
```

## Configuración

Los tests utilizan estas configuraciones:

- **Base URL**: `http://localhost:10013`
- **API URL**: `http://localhost:10013/wp-json/cdc/v1`
- **Modo ARCA**: Sandbox (sin credenciales reales)

## Qué Verifican los Tests

### Flujos Completos

1. **Creación de Socio**:
   - Crea persona tipo "socio"
   - Genera 12 cuotas automáticamente
   - Verifica que las cuotas existen en BD

2. **Cobro de Cuota**:
   - Marca cuota como pagada
   - Crea MovimientoCaja
   - Crea orden WooCommerce
   - Genera factura ARCA (sandbox)
   - Propaga comprobante_id

3. **Sincronización WC**:
   - Sincroniza talleres como productos WC
   - Verifica que los productos se crean correctamente

### Validaciones

- Campos requeridos
- Unicidad de DNI
- Validación de medio_pago
- Prevención de duplicados (cobro de cuota ya pagada)
- Manejo de errores 404

## Agregar Nuevos Tests

### 1. Tests de Integración

Edita `tests/integration/test-suite.sh` y agrega un nuevo bloque:

```bash
# ============================================
# TEST N: Descripción
# ============================================
print_header "TEST N: Descripción"

print_test "Verificar algo"
run_test
RESPONSE=$(curl -s "$API_URL/endpoint")
if echo "$RESPONSE" | grep -q '"success":true'; then
    print_success "Test pasó"
else
    print_fail "Test falló"
fi
```

### 2. Tests de Validación

Edita `tests/integration/test-validations.sh` siguiendo el mismo patrón.

## Troubleshooting

### Error: "WordPress no responde"

Verifica que el sitio esté corriendo:
```bash
curl http://localhost:10013
```

### Error: "API REST no responde"

Verifica que el plugin CDC API esté activado:
```bash
curl http://localhost:10013/wp-json/cdc/v1/personas
```

### Tests fallan por datos previos

Los tests usan DNIs únicos basados en timestamp para evitar colisiones:
- `TEST123456789` → `TEST1737564789`
- `SOCIO123` → `SOCIO1737564789`

### Ver output detallado

```bash
bash tests/integration/test-suite.sh 2>&1 | tee test-output.log
```

## CI/CD Integration

Para integrar estos tests en un pipeline CI/CD:

```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Start WordPress
        run: |
          # Setup WordPress, MySQL, etc.

      - name: Run Tests
        run: ./run-tests.sh

      - name: Upload test logs
        if: always()
        uses: actions/upload-artifact@v2
        with:
          name: test-logs
          path: tests/logs/
```

## Mejoras Futuras

- [ ] Tests de performance (medir tiempos de respuesta)
- [ ] Tests de carga (múltiples requests concurrentes)
- [ ] Tests de seguridad (SQL injection, XSS)
- [ ] Coverage report (% de código cubierto)
- [ ] Integration con PHPUnit para tests unitarios
- [ ] Tests de Mercado Pago webhooks
- [ ] Tests de ARCA en producción (con credenciales reales)
- [ ] Tests de UI con Selenium/Cypress

## Notas Importantes

- Los tests crean datos de prueba pero NO los limpian automáticamente
- Los DNIs de prueba siguen el patrón `TEST*` o `SOCIO*` + timestamp
- El modo sandbox de ARCA genera comprobantes ficticios: `SANDBOX-YYYYMMDD-####`
- Los tests NO requieren PHP en el PATH (usan curl para HTTP)

## Contacto

Si encuentras bugs o quieres agregar tests, crea un issue en el repositorio.
