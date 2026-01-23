#!/bin/bash
#
# CDC Validation Tests
# Tests para validaciones, errores y casos edge
#

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

BASE_URL="http://localhost:10013"
API_URL="${BASE_URL}/wp-json/cdc/v1"
COOKIES_FILE="/tmp/cdc-test-cookies-validations.txt"

TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0

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
    ((TESTS_FAILED++))
}

run_test() {
    ((TESTS_RUN++))
}

clear
print_header "🔍 CDC Sistema - Tests de Validación"

# ============================================
# TEST 0: Autenticación (Login)
# ============================================
print_header "TEST 0: Autenticación"

print_test "Login como admin para ejecutar tests"
run_test
LOGIN_RESPONSE=$(curl -s -c "$COOKIES_FILE" -X POST "$API_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"dni":"12345678"}')

if echo "$LOGIN_RESPONSE" | grep -q '"success":true'; then
    # Extract nonce for subsequent requests
    NONCE=$(echo "$LOGIN_RESPONSE" | grep -o '"nonce":"[^"]*"' | cut -d'"' -f4)
    print_success "Autenticación exitosa para tests (Nonce: ${NONCE:0:10}...)"
else
    echo -e "${RED}❌ ERROR: No se pudo autenticar. Los tests fallarán.${NC}"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
fi

# ============================================
# TEST 1: Validaciones de Persona
# ============================================
print_header "TEST 1: Validaciones de Persona"

print_test "Crear persona sin nombre (debe fallar)"
run_test
RESPONSE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d '{"apellido":"Test","dni":"12345678"}')

if echo "$RESPONSE" | grep -q '"success":false\|requerido\|required'; then
    print_success "Validación de nombre requerido funciona"
else
    print_fail "No valida nombre requerido: $RESPONSE"
fi

print_test "Crear persona sin DNI (debe fallar)"
run_test
RESPONSE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d '{"nombre":"Test","apellido":"Apellido"}')

if echo "$RESPONSE" | grep -q '"success":false\|requerido\|required'; then
    print_success "Validación de DNI requerido funciona"
else
    print_fail "No valida DNI requerido: $RESPONSE"
fi

print_test "Crear persona con DNI duplicado (debe fallar)"
run_test
# Generate unique DNI for this test
DUP_DNI="DUP$(date +%s)"

# First create
curl -s -b "$COOKIES_FILE" -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d "{\"nombre\":\"Original\",\"apellido\":\"Test\",\"dni\":\"${DUP_DNI}\"}" > /dev/null

sleep 0.5

# Try duplicate
RESPONSE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d "{\"nombre\":\"Duplicado\",\"apellido\":\"Test\",\"dni\":\"${DUP_DNI}\"}")

if echo "$RESPONSE" | grep -q 'DNI ya está registrado\|already registered\|"code":'; then
    print_success "Validación de DNI único funciona"
else
    print_fail "No valida DNI duplicado: $RESPONSE"
fi

# ============================================
# TEST 2: Validaciones de Cobros
# ============================================
print_header "TEST 2: Validaciones de Cobros"

print_test "Cobrar sin persona_id (debe fallar)"
run_test
RESPONSE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/cobros/cuota-socio" \
    -H "Content-Type: application/json" \
    -d '{"cuota_ids":[1],"medio_pago":"efectivo"}')

if echo "$RESPONSE" | grep -q 'requerido\|required\|"code":"'; then
    print_success "Validación de persona_id requerido funciona"
else
    print_fail "No valida persona_id requerido: $RESPONSE"
fi

print_test "Cobrar sin medio_pago (debe fallar)"
run_test
RESPONSE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/cobros/cuota-socio" \
    -H "Content-Type: application/json" \
    -d '{"persona_id":1,"cuota_ids":[1]}')

if echo "$RESPONSE" | grep -q 'requerido\|required\|"code":"'; then
    print_success "Validación de medio_pago requerido funciona"
else
    print_fail "No valida medio_pago requerido: $RESPONSE"
fi

print_test "Cobrar con medio_pago inválido (debe fallar)"
run_test
RESPONSE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/cobros/cuota-socio" \
    -H "Content-Type: application/json" \
    -d '{"persona_id":1,"cuota_ids":[1],"medio_pago":"bitcoin"}')

if echo "$RESPONSE" | grep -q 'inválido\|invalid\|"code":"'; then
    print_success "Validación de medio_pago válido funciona"
else
    print_fail "No valida medio_pago inválido: $RESPONSE"
fi

# ============================================
# TEST 3: Casos Edge - Cuotas
# ============================================
print_header "TEST 3: Casos Edge - Cuotas"

print_test "Intentar cobrar cuota ya pagada (debe fallar)"
run_test
# Create test socio
SOCIO_DNI="EDGETEST$(date +%s)"
CREATE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d "{\"tipo\":\"socio\",\"nombre\":\"Edge\",\"apellido\":\"Test\",\"dni\":\"${SOCIO_DNI}\",\"generar_cuotas\":true}")

SOCIO_ID=$(echo "$CREATE" | grep -o '"id":[0-9]*' | grep -o '[0-9]*')

if [ -n "$SOCIO_ID" ]; then
    sleep 1
    # Get first cuota
    CUOTAS=$(curl -s -b "$COOKIES_FILE" -H "X-WP-Nonce: $NONCE" "$API_URL/cobros/cuotas-pendientes/$SOCIO_ID")
    CUOTA_ID=$(echo "$CUOTAS" | grep -o '"id":"[0-9]*"' | head -1 | grep -o '[0-9]*')

    # Pay it once
    curl -s -b "$COOKIES_FILE" -X POST "$API_URL/cobros/cuota-socio" \
        -H "Content-Type: application/json" \
        -d "{\"persona_id\":$SOCIO_ID,\"cuota_ids\":[$CUOTA_ID],\"medio_pago\":\"efectivo\"}" > /dev/null

    sleep 1

    # Try to pay again
    RESPONSE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/cobros/cuota-socio" \
        -H "Content-Type: application/json" \
        -d "{\"persona_id\":$SOCIO_ID,\"cuota_ids\":[$CUOTA_ID],\"medio_pago\":\"efectivo\"}")

    if echo "$RESPONSE" | grep -q "ya pagada\|already paid"; then
        print_success "Previene cobrar cuota ya pagada"
    else
        print_fail "Permite cobrar cuota ya pagada (duplicado)"
    fi
else
    print_fail "No se pudo crear socio para test"
fi

# ============================================
# TEST 4: Endpoints No Existentes
# ============================================
print_header "TEST 4: Manejo de Errores"

print_test "Endpoint no existente (404)"
run_test
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$API_URL/noexiste")
if [ "$HTTP_CODE" -eq 404 ]; then
    print_success "Endpoint no existente retorna 404"
else
    print_fail "Endpoint no existente retorna HTTP $HTTP_CODE"
fi

print_test "Persona no existente (404)"
run_test
RESPONSE=$(curl -s -b "$COOKIES_FILE" -H "X-WP-Nonce: $NONCE" "$API_URL/personas/999999")
if echo "$RESPONSE" | grep -q "no encontrad"; then
    print_success "Persona no existente retorna error apropiado"
else
    print_fail "Persona no existente no maneja error"
fi

# ============================================
# RESUMEN
# ============================================
print_header "📊 RESUMEN - Validaciones"

echo "Tests ejecutados: $TESTS_RUN"
echo -e "${GREEN}Tests exitosos:   $TESTS_PASSED${NC}"
echo -e "${RED}Tests fallidos:   $TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ TODAS LAS VALIDACIONES PASARON${NC}"
    exit 0
else
    echo -e "${RED}✗ ALGUNAS VALIDACIONES FALLARON${NC}"
    exit 1
fi
