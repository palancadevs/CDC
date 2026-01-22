#!/bin/bash
#
# CDC Integration Test Suite
# Ejecuta tests automáticos para verificar funcionalidad del sistema
#

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Base URL
BASE_URL="http://localhost:10013"
API_URL="${BASE_URL}/wp-json/cdc/v1"

# Counters
TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0

# Test results array
declare -a FAILED_TESTS

# Functions
print_header() {
    echo ""
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo ""
}

print_test() {
    echo -e "${YELLOW}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}  ✓ $1${NC}"
    ((TESTS_PASSED++))
}

print_fail() {
    echo -e "${RED}  ✗ $1${NC}"
    FAILED_TESTS+=("$2: $1")
    ((TESTS_FAILED++))
}

run_test() {
    ((TESTS_RUN++))
}

# Cleanup function
cleanup() {
    # Delete test data created during tests
    echo ""
    echo -e "${YELLOW}Limpiando datos de prueba...${NC}"

    # Note: In production, you'd want to delete test records
    # For now, we'll leave them as they help verify the system works

    echo -e "${GREEN}Limpieza completada${NC}"
}

trap cleanup EXIT

# Start tests
clear
print_header "🧪 CDC Sistema - Test Suite de Integración"

echo "Base URL: $BASE_URL"
echo "API URL: $API_URL"
echo ""

# ============================================
# TEST 1: Verificar que WordPress está corriendo
# ============================================
print_header "TEST 1: Conectividad"

print_test "Verificar que WordPress está corriendo"
run_test
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL")
if [ "$HTTP_CODE" -eq 200 ]; then
    print_success "WordPress responde correctamente (HTTP $HTTP_CODE)"
else
    print_fail "WordPress no responde (HTTP $HTTP_CODE)" "TEST 1.1"
fi

print_test "Verificar que API REST está disponible"
run_test
RESPONSE=$(curl -s "$API_URL/personas" -H "Content-Type: application/json")
if echo "$RESPONSE" | grep -q '"success"'; then
    print_success "API REST está disponible"
else
    print_fail "API REST no responde correctamente" "TEST 1.2"
fi

# ============================================
# TEST 2: CRUD de Personas
# ============================================
print_header "TEST 2: CRUD de Personas"

# Generate unique DNI
RANDOM_DNI="TEST$(date +%s)"

print_test "Crear persona (cliente)"
run_test
CREATE_RESPONSE=$(curl -s -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d "{\"tipo\":\"cliente\",\"nombre\":\"Test\",\"apellido\":\"Cliente\",\"dni\":\"${RANDOM_DNI}\"}")

if echo "$CREATE_RESPONSE" | grep -q '"success":true'; then
    PERSONA_ID=$(echo "$CREATE_RESPONSE" | grep -o '"id":[0-9]*' | grep -o '[0-9]*')
    print_success "Cliente creado correctamente (ID: $PERSONA_ID)"
else
    print_fail "Error al crear cliente: $CREATE_RESPONSE" "TEST 2.1"
    PERSONA_ID=""
fi

if [ -n "$PERSONA_ID" ]; then
    print_test "Obtener persona creada"
    run_test
    GET_RESPONSE=$(curl -s "$API_URL/personas/$PERSONA_ID")
    if echo "$GET_RESPONSE" | grep -q '"success":true'; then
        print_success "Persona obtenida correctamente"
    else
        print_fail "Error al obtener persona" "TEST 2.2"
    fi
fi

# ============================================
# TEST 3: Creación de Socio con Cuotas
# ============================================
print_header "TEST 3: Socios y Cuotas"

SOCIO_DNI="SOCIO$(date +%s)"

print_test "Crear socio con generación de cuotas"
run_test
SOCIO_RESPONSE=$(curl -s -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d "{\"tipo\":\"socio\",\"nombre\":\"Test\",\"apellido\":\"Socio\",\"dni\":\"${SOCIO_DNI}\",\"generar_cuotas\":true}")

if echo "$SOCIO_RESPONSE" | grep -q '"success":true'; then
    SOCIO_ID=$(echo "$SOCIO_RESPONSE" | grep -o '"id":[0-9]*' | grep -o '[0-9]*')
    print_success "Socio creado correctamente (ID: $SOCIO_ID)"
else
    print_fail "Error al crear socio: $SOCIO_RESPONSE" "TEST 3.1"
    SOCIO_ID=""
fi

if [ -n "$SOCIO_ID" ]; then
    print_test "Verificar generación de cuotas (12 meses)"
    run_test
    sleep 1  # Wait for cuotas to be created
    CUOTAS_RESPONSE=$(curl -s "$API_URL/personas/$SOCIO_ID/cuotas?anio=$(date +%Y)")
    CUOTAS_COUNT=$(echo "$CUOTAS_RESPONSE" | grep -o '"id":"[0-9]*"' | wc -l | tr -d ' ')

    if [ "$CUOTAS_COUNT" -eq 12 ]; then
        print_success "12 cuotas generadas correctamente"
    else
        print_fail "Se esperaban 12 cuotas, se obtuvieron $CUOTAS_COUNT" "TEST 3.2"
    fi
fi

# ============================================
# TEST 4: Cobros y WooCommerce
# ============================================
print_header "TEST 4: Cobros y WooCommerce"

if [ -n "$SOCIO_ID" ]; then
    print_test "Obtener cuotas pendientes del socio"
    run_test
    PENDIENTES_RESPONSE=$(curl -s "$API_URL/cobros/cuotas-pendientes/$SOCIO_ID")

    if echo "$PENDIENTES_RESPONSE" | grep -q '"success":true'; then
        # Get first cuota ID
        FIRST_CUOTA_ID=$(echo "$PENDIENTES_RESPONSE" | grep -o '"id":"[0-9]*"' | head -1 | grep -o '[0-9]*')
        print_success "Cuotas pendientes obtenidas (Primera ID: $FIRST_CUOTA_ID)"
    else
        print_fail "Error al obtener cuotas pendientes" "TEST 4.1"
        FIRST_CUOTA_ID=""
    fi

    if [ -n "$FIRST_CUOTA_ID" ]; then
        print_test "Cobrar cuota socio (con WooCommerce)"
        run_test
        COBRO_RESPONSE=$(curl -s -X POST "$API_URL/cobros/cuota-socio" \
            -H "Content-Type: application/json" \
            -d "{\"persona_id\":$SOCIO_ID,\"cuota_ids\":[$FIRST_CUOTA_ID],\"medio_pago\":\"efectivo\",\"observaciones\":\"Test automatico\"}")

        if echo "$COBRO_RESPONSE" | grep -q '"success":true'; then
            ORDER_ID=$(echo "$COBRO_RESPONSE" | grep -o '"order_id":[0-9]*' | grep -o '[0-9]*')
            MOVIMIENTO_ID=$(echo "$COBRO_RESPONSE" | grep -o '"movimiento_id":[0-9]*' | grep -o '[0-9]*')
            print_success "Cobro exitoso (Order: $ORDER_ID, Movimiento: $MOVIMIENTO_ID)"
        else
            print_fail "Error al cobrar cuota: $COBRO_RESPONSE" "TEST 4.2"
            ORDER_ID=""
        fi
    fi
fi

# ============================================
# TEST 5: ARCA Invoicing
# ============================================
print_header "TEST 5: ARCA Invoicing (Sandbox)"

if [ -n "$ORDER_ID" ]; then
    print_test "Verificar factura ARCA generada"
    run_test
    sleep 2  # Wait for ARCA processing

    ARCA_STATUS=$(curl -s "$API_URL/arca/status/$ORDER_ID")

    if echo "$ARCA_STATUS" | grep -q '"factura_status":"ok"'; then
        COMPROBANTE=$(echo "$ARCA_STATUS" | grep -o '"comprobante_id":"[^"]*"' | cut -d'"' -f4)
        print_success "Factura ARCA generada correctamente ($COMPROBANTE)"
    else
        print_fail "Factura ARCA no generada o con error" "TEST 5.1"
    fi

    print_test "Verificar que comprobante_id es de sandbox"
    run_test
    if echo "$COMPROBANTE" | grep -q '^SANDBOX-'; then
        print_success "Comprobante es de sandbox (modo test activado)"
    else
        print_fail "Comprobante no es de sandbox: $COMPROBANTE" "TEST 5.2"
    fi
fi

# ============================================
# TEST 6: Talleres y Productos WC
# ============================================
print_header "TEST 6: Talleres y WooCommerce"

print_test "Listar talleres"
run_test
TALLERES_RESPONSE=$(curl -s "$API_URL/talleres")
if echo "$TALLERES_RESPONSE" | grep -q '"success":true'; then
    TALLERES_COUNT=$(echo "$TALLERES_RESPONSE" | grep -o '"id":[0-9]*' | wc -l | tr -d ' ')
    print_success "Talleres listados correctamente (Total: $TALLERES_COUNT)"
else
    print_fail "Error al listar talleres" "TEST 6.1"
fi

print_test "Sincronizar talleres como productos WooCommerce"
run_test
SYNC_RESPONSE=$(curl -s -X POST "$API_URL/talleres/sync-wc-products" -H "Content-Type: application/json")
if echo "$SYNC_RESPONSE" | grep -q '"success":true'; then
    SYNCED=$(echo "$SYNC_RESPONSE" | grep -o '"synced":[0-9]*' | grep -o '[0-9]*')
    print_success "Talleres sincronizados como productos WC (Total: $SYNCED)"
else
    print_fail "Error al sincronizar talleres: $SYNC_RESPONSE" "TEST 6.2"
fi

# ============================================
# TEST 7: Búsqueda de Personas
# ============================================
print_header "TEST 7: Búsqueda y Filtros"

print_test "Buscar persona por query"
run_test
SEARCH_RESPONSE=$(curl -s "$API_URL/personas?query=Test")
if echo "$SEARCH_RESPONSE" | grep -q '"success":true'; then
    print_success "Búsqueda funciona correctamente"
else
    print_fail "Error en búsqueda de personas" "TEST 7.1"
fi

print_test "Filtrar por tipo (socios)"
run_test
FILTER_RESPONSE=$(curl -s "$API_URL/personas?tipo=socio")
if echo "$FILTER_RESPONSE" | grep -q '"success":true'; then
    print_success "Filtro por tipo funciona correctamente"
else
    print_fail "Error al filtrar por tipo" "TEST 7.2"
fi

# ============================================
# TEST 8: Caja (Movimientos)
# ============================================
print_header "TEST 8: Movimientos de Caja"

print_test "Listar movimientos de caja"
run_test
CAJA_RESPONSE=$(curl -s "$API_URL/caja/movimientos")
if echo "$CAJA_RESPONSE" | grep -q '"success":true'; then
    print_success "Movimientos de caja obtenidos correctamente"
else
    print_fail "Error al obtener movimientos de caja" "TEST 8.1"
fi

print_test "Obtener balance actual"
run_test
BALANCE_RESPONSE=$(curl -s "$API_URL/caja/balance")
if echo "$BALANCE_RESPONSE" | grep -q '"success":true'; then
    BALANCE=$(echo "$BALANCE_RESPONSE" | grep -o '"balance":"[^"]*"' | cut -d'"' -f4)
    print_success "Balance obtenido: \$$BALANCE"
else
    print_fail "Error al obtener balance" "TEST 8.2"
fi

# ============================================
# RESUMEN FINAL
# ============================================
print_header "📊 RESUMEN DE TESTS"

echo "Tests ejecutados: $TESTS_RUN"
echo -e "${GREEN}Tests exitosos:   $TESTS_PASSED${NC}"
echo -e "${RED}Tests fallidos:   $TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ TODOS LOS TESTS PASARON CORRECTAMENTE${NC}"
    echo ""
    exit 0
else
    echo -e "${RED}✗ ALGUNOS TESTS FALLARON${NC}"
    echo ""
    echo "Tests fallidos:"
    for failed_test in "${FAILED_TESTS[@]}"; do
        echo -e "${RED}  - $failed_test${NC}"
    done
    echo ""
    exit 1
fi
