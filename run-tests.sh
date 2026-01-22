#!/bin/bash
#
# CDC Sistema - Test Runner Principal
# Ejecuta toda la suite de tests y genera reporte
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
TESTS_DIR="$SCRIPT_DIR/tests/integration"

# Test files
TEST_SUITE="$TESTS_DIR/test-suite.sh"
TEST_VALIDATIONS="$TESTS_DIR/test-validations.sh"

# Results
TOTAL_SUITES=0
PASSED_SUITES=0
FAILED_SUITES=0

# Banner
clear
echo -e "${CYAN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                                                           ║
║   ██████╗██████╗  ██████╗                                ║
║  ██╔════╝██╔══██╗██╔════╝                                ║
║  ██║     ██║  ██║██║                                     ║
║  ██║     ██║  ██║██║                                     ║
║  ╚██████╗██████╔╝╚██████╗                                ║
║   ╚═════╝╚═════╝  ╚═════╝                                ║
║                                                           ║
║         Sistema de Gestión - Test Suite                  ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo ""
echo -e "${BOLD}Ejecutando tests automatizados...${NC}"
echo ""
echo "Ubicación: $TESTS_DIR"
echo "Fecha: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Check if tests exist
if [ ! -f "$TEST_SUITE" ] || [ ! -f "$TEST_VALIDATIONS" ]; then
    echo -e "${RED}Error: Archivos de test no encontrados${NC}"
    exit 1
fi

# Make scripts executable
chmod +x "$TEST_SUITE"
chmod +x "$TEST_VALIDATIONS"

# ============================================
# RUN TEST SUITE 1: Integration Tests
# ============================================
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BOLD}Suite 1: Tests de Integración${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""

((TOTAL_SUITES++))
if bash "$TEST_SUITE"; then
    ((PASSED_SUITES++))
    SUITE1_STATUS="${GREEN}✓ PASSED${NC}"
else
    ((FAILED_SUITES++))
    SUITE1_STATUS="${RED}✗ FAILED${NC}"
fi

echo ""
echo ""

# ============================================
# RUN TEST SUITE 2: Validation Tests
# ============================================
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BOLD}Suite 2: Tests de Validación${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""

((TOTAL_SUITES++))
if bash "$TEST_VALIDATIONS"; then
    ((PASSED_SUITES++))
    SUITE2_STATUS="${GREEN}✓ PASSED${NC}"
else
    ((FAILED_SUITES++))
    SUITE2_STATUS="${RED}✗ FAILED${NC}"
fi

echo ""
echo ""

# ============================================
# FINAL REPORT
# ============================================
echo -e "${CYAN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                    REPORTE FINAL                          ║
╚═══════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"
echo ""

echo "Suites ejecutadas: $TOTAL_SUITES"
echo -e "Suites exitosas:   ${GREEN}$PASSED_SUITES${NC}"
echo -e "Suites fallidas:   ${RED}$FAILED_SUITES${NC}"
echo ""

echo "Resultados por suite:"
echo -e "  1. Tests de Integración:  $SUITE1_STATUS"
echo -e "  2. Tests de Validación:   $SUITE2_STATUS"
echo ""

# Generate log file
LOG_DIR="$SCRIPT_DIR/tests/logs"
mkdir -p "$LOG_DIR"
LOG_FILE="$LOG_DIR/test-run-$(date +%Y%m%d-%H%M%S).log"

{
    echo "CDC Test Run - $(date)"
    echo "========================"
    echo ""
    echo "Suites: $TOTAL_SUITES"
    echo "Passed: $PASSED_SUITES"
    echo "Failed: $FAILED_SUITES"
    echo ""
} > "$LOG_FILE"

echo -e "${YELLOW}Log guardado en: $LOG_FILE${NC}"
echo ""

# Exit with appropriate code
if [ $FAILED_SUITES -eq 0 ]; then
    echo -e "${GREEN}${BOLD}"
    cat << "EOF"
    ███████╗██╗   ██╗ ██████╗ ██████╗███████╗███████╗███████╗
    ██╔════╝██║   ██║██╔════╝██╔════╝██╔════╝██╔════╝██╔════╝
    ███████╗██║   ██║██║     ██║     █████╗  ███████╗███████╗
    ╚════██║██║   ██║██║     ██║     ██╔══╝  ╚════██║╚════██║
    ███████║╚██████╔╝╚██████╗╚██████╗███████╗███████║███████║
    ╚══════╝ ╚═════╝  ╚═════╝ ╚═════╝╚══════╝╚══════╝╚══════╝
EOF
    echo -e "${NC}"
    echo ""
    echo -e "${GREEN}✓ Todos los tests pasaron exitosamente${NC}"
    echo ""
    exit 0
else
    echo -e "${RED}${BOLD}"
    cat << "EOF"
    ███████╗ █████╗ ██╗██╗     ██╗   ██╗██████╗ ███████╗
    ██╔════╝██╔══██╗██║██║     ██║   ██║██╔══██╗██╔════╝
    █████╗  ███████║██║██║     ██║   ██║██████╔╝█████╗
    ██╔══╝  ██╔══██║██║██║     ██║   ██║██╔══██╗██╔══╝
    ██║     ██║  ██║██║███████╗╚██████╔╝██║  ██║███████╗
    ╚═╝     ╚═╝  ╚═╝╚═╝╚══════╝ ╚═════╝ ╚═╝  ╚═╝╚══════╝
EOF
    echo -e "${NC}"
    echo ""
    echo -e "${RED}✗ Algunos tests fallaron. Revisa el log para más detalles.${NC}"
    echo ""
    exit 1
fi
