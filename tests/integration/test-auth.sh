#!/bin/bash
#
# CDC Authentication Tests
# Tests for DNI-based login, roles, and protected endpoints
#

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

BASE_URL="http://localhost:10013"
API_URL="${BASE_URL}/wp-json/cdc/v1"
COOKIES_FILE="/tmp/cdc-test-cookies.txt"

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

# Cleanup
cleanup() {
    rm -f "$COOKIES_FILE"
}
trap cleanup EXIT

# Start tests
clear
print_header "🔐 CDC Sistema - Tests de Autenticación"

echo "Base URL: $BASE_URL"
echo "API URL: $API_URL"
echo ""

# ============================================
# TEST 1: Login Page Accessibility
# ============================================
print_header "TEST 1: Accesibilidad de Página de Login"

print_test "Verificar que /login existe y responde"
run_test
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/login")
if [ "$HTTP_CODE" -eq 200 ]; then
    print_success "Página de login accesible (HTTP $HTTP_CODE)"
else
    print_fail "Página de login no accesible (HTTP $HTTP_CODE)"
fi

# ============================================
# TEST 2: Login Endpoint Tests
# ============================================
print_header "TEST 2: Endpoint de Login"

print_test "Login sin DNI (debe fallar)"
run_test
RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{}')
if echo "$RESPONSE" | grep -q 'dni_required\|DNI es requerido\|"code"'; then
    print_success "Validación de DNI requerido funciona"
else
    print_fail "No valida DNI requerido: $RESPONSE"
fi

print_test "Login con DNI inexistente (debe fallar)"
run_test
RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"dni":"99999999"}')
if echo "$RESPONSE" | grep -q 'invalid_dni\|no encontrado\|"code"'; then
    print_success "Rechaza DNI inexistente correctamente"
else
    print_fail "No rechaza DNI inexistente: $RESPONSE"
fi

print_test "Login con DNI válido (debe funcionar)"
run_test
RESPONSE=$(curl -s -c "$COOKIES_FILE" -X POST "$API_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"dni":"12345678"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    USER_ID=$(echo "$RESPONSE" | grep -o '"user_id":[0-9]*' | grep -o '[0-9]*')
    ROLE=$(echo "$RESPONSE" | grep -o '"role":"[^"]*"' | cut -d'"' -f4)
    NONCE=$(echo "$RESPONSE" | grep -o '"nonce":"[^"]*"' | cut -d'"' -f4)
    print_success "Login exitoso (User ID: $USER_ID, Role: $ROLE)"
    LOGIN_SUCCESS=true
else
    print_fail "Login falló: $RESPONSE"
    LOGIN_SUCCESS=false
fi

# ============================================
# TEST 3: Protected Endpoints (Authenticated)
# ============================================
print_header "TEST 3: Endpoints Protegidos (Con Autenticación)"

if [ "$LOGIN_SUCCESS" = true ]; then
    print_test "Acceder a /personas con autenticación"
    run_test
    RESPONSE=$(curl -s -b "$COOKIES_FILE" -H "X-WP-Nonce: $NONCE" "$API_URL/personas")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        print_success "Acceso autorizado a /personas"
    else
        print_fail "Acceso denegado a /personas con autenticación: $RESPONSE"
    fi

    print_test "Crear persona con autenticación"
    run_test
    TEST_DNI="AUTHTEST$(date +%s)"
    RESPONSE=$(curl -s -b "$COOKIES_FILE" -H "X-WP-Nonce: $NONCE" -X POST "$API_URL/personas" \
        -H "Content-Type: application/json" \
        -d "{\"tipo\":\"cliente\",\"nombre\":\"Auth\",\"apellido\":\"Test\",\"dni\":\"$TEST_DNI\"}")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        print_success "Persona creada con autenticación"
    else
        print_fail "No se pudo crear persona con autenticación: $RESPONSE"
    fi

    print_test "Acceder a endpoint /auth/me"
    run_test
    RESPONSE=$(curl -s -b "$COOKIES_FILE" -H "X-WP-Nonce: $NONCE" "$API_URL/auth/me")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        USERNAME=$(echo "$RESPONSE" | grep -o '"username":"[^"]*"' | cut -d'"' -f4)
        print_success "Endpoint /auth/me funciona (Username: $USERNAME)"
    else
        print_fail "Endpoint /auth/me falló: $RESPONSE"
    fi
else
    echo -e "${YELLOW}⚠ Saltando tests de autenticación (login falló)${NC}"
fi

# ============================================
# TEST 4: Protected Endpoints (Unauthenticated)
# ============================================
print_header "TEST 4: Endpoints Protegidos (Sin Autenticación)"

# Remove cookies to test without authentication
rm -f "$COOKIES_FILE"

print_test "Acceder a /personas sin autenticación (debe fallar)"
run_test
RESPONSE=$(curl -s "$API_URL/personas")
if echo "$RESPONSE" | grep -q 'rest_forbidden\|"code"'; then
    print_success "Acceso denegado correctamente (401)"
else
    print_fail "Endpoint no está protegido: $RESPONSE"
fi

print_test "Crear persona sin autenticación (debe fallar)"
run_test
RESPONSE=$(curl -s -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d '{"tipo":"cliente","nombre":"Test","apellido":"Unauth","dni":"11111111"}')
if echo "$RESPONSE" | grep -q 'rest_forbidden\|"code"'; then
    print_success "Creación bloqueada sin autenticación"
else
    print_fail "Endpoint no está protegido: $RESPONSE"
fi

print_test "Acceder a /caja sin autenticación (debe fallar)"
run_test
RESPONSE=$(curl -s "$API_URL/caja/balance")
if echo "$RESPONSE" | grep -q 'rest_forbidden\|"code"'; then
    print_success "Endpoint /caja protegido correctamente"
else
    print_fail "Endpoint /caja no está protegido: $RESPONSE"
fi

# ============================================
# TEST 5: Logout
# ============================================
print_header "TEST 5: Logout"

if [ "$LOGIN_SUCCESS" = true ]; then
    # Login again for logout test
    curl -s -c "$COOKIES_FILE" -X POST "$API_URL/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"dni":"12345678"}' > /dev/null

    print_test "Logout con sesión activa"
    run_test
    RESPONSE=$(curl -s -b "$COOKIES_FILE" -X POST "$API_URL/auth/logout" \
        -H "Content-Type: application/json")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        print_success "Logout exitoso"
    else
        print_fail "Logout falló: $RESPONSE"
    fi

    print_test "Verificar que sesión se cerró (acceso a /personas debe fallar)"
    run_test
    RESPONSE=$(curl -s -b "$COOKIES_FILE" "$API_URL/personas")
    if echo "$RESPONSE" | grep -q 'rest_forbidden\|"code"'; then
        print_success "Sesión cerrada correctamente"
    else
        print_fail "Sesión aún activa después de logout: $RESPONSE"
    fi
fi

# ============================================
# TEST 6: User Auto-Creation
# ============================================
print_header "TEST 6: Creación Automática de Usuarios"

# Create a new persona that doesn't have a WordPress user yet
NEW_DNI="NEWUSER$(date +%s)"

print_test "Crear persona sin usuario WordPress"
run_test
RESPONSE=$(curl -s -c "$COOKIES_FILE" -X POST "$API_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"dni":"12345678"}')
ADMIN_NONCE=$(echo "$RESPONSE" | grep -o '"nonce":"[^"]*"' | cut -d'"' -f4)
# Login as admin to create persona
CREATE_RESPONSE=$(curl -s -b "$COOKIES_FILE" -H "X-WP-Nonce: $ADMIN_NONCE" -X POST "$API_URL/personas" \
    -H "Content-Type: application/json" \
    -d "{\"tipo\":\"socio\",\"nombre\":\"New\",\"apellido\":\"User\",\"dni\":\"$NEW_DNI\",\"generar_cuotas\":true}")
if echo "$CREATE_RESPONSE" | grep -q '"success":true'; then
    NEW_PERSONA_ID=$(echo "$CREATE_RESPONSE" | grep -o '"id":[0-9]*' | grep -o '[0-9]*')
    print_success "Persona creada (ID: $NEW_PERSONA_ID, DNI: $NEW_DNI)"

    # Logout
    curl -s -b "$COOKIES_FILE" -X POST "$API_URL/auth/logout" > /dev/null
    rm -f "$COOKIES_FILE"

    print_test "Login con nueva persona (debe crear usuario automático)"
    run_test
    sleep 1
    RESPONSE=$(curl -s -c "$COOKIES_FILE" -X POST "$API_URL/auth/login" \
        -H "Content-Type: application/json" \
        -d "{\"dni\":\"$NEW_DNI\"}")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        AUTO_USER_ID=$(echo "$RESPONSE" | grep -o '"user_id":[0-9]*' | grep -o '[0-9]*')
        print_success "Usuario creado automáticamente (User ID: $AUTO_USER_ID)"
    else
        print_fail "No se pudo crear usuario automático: $RESPONSE"
    fi
else
    print_fail "No se pudo crear persona de prueba: $CREATE_RESPONSE"
fi

# ============================================
# TEST 7: Roles and Permissions
# ============================================
print_header "TEST 7: Roles y Permisos"

print_test "Verificar que usuario tiene rol asignado"
run_test
if [ -n "$ROLE" ]; then
    if [ "$ROLE" = "cdc_admin" ] || [ "$ROLE" = "cdc_tesoreria" ] || [ "$ROLE" = "cdc_recepcion" ]; then
        print_success "Usuario tiene rol válido: $ROLE"
    else
        print_fail "Rol inválido: $ROLE"
    fi
else
    print_fail "No se pudo obtener rol del usuario"
fi

# ============================================
# RESUMEN FINAL
# ============================================
print_header "📊 RESUMEN - Tests de Autenticación"

echo "Tests ejecutados: $TESTS_RUN"
echo -e "${GREEN}Tests exitosos:   $TESTS_PASSED${NC}"
echo -e "${RED}Tests fallidos:   $TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ TODOS LOS TESTS DE AUTENTICACIÓN PASARON${NC}"
    exit 0
else
    echo -e "${RED}✗ ALGUNOS TESTS DE AUTENTICACIÓN FALLARON${NC}"
    exit 1
fi
