# Guía de Testing - CDC Sistema

Esta guía documenta todas las herramientas de testing y diagnóstico disponibles en el sistema CDC.

---

## 📋 Tabla de Contenidos

1. [Tests Automatizados](#tests-automatizados)
2. [Tests Manuales desde Navegador](#tests-manuales-desde-navegador)
3. [Tests desde Terminal](#tests-desde-terminal)
4. [Herramientas de Diagnóstico](#herramientas-de-diagnóstico)
5. [Verificación de Sistema](#verificación-de-sistema)
6. [Resultados Esperados](#resultados-esperados)
7. [Troubleshooting](#troubleshooting)

---

## 🧪 Tests Automatizados

### Suite Completa de Tests Interactivos

**URL de acceso**: http://localhost:10013/tests-v2/

Esta página ejecuta 13 tests automatizados que verifican todas las funcionalidades principales del sistema.

### Cómo Ejecutar

1. Abrir navegador en: http://localhost:10013/tests-v2/
2. Verificar que aparezca: "✅ Todos los scripts cargados correctamente"
3. Click en botón **"▶️ Ejecutar Todos los Tests"**
4. Observar la barra de progreso y resultados en tiempo real
5. Verificar resumen final

### Tests Incluidos

#### **Sección 1: API REST (4 tests)**
- Test 1.1: API REST disponible
- Test 1.2: Endpoint GET /personas responde
- Test 1.3: Endpoint GET /caja/movimientos/today responde
- Test 1.4: Endpoint GET /talleres responde

#### **Sección 2: Creación de Personas (3 tests)**
- Test 2.1: Crear nuevo socio via API
- Test 2.2: Crear nuevo cliente via API
- Test 2.3: Buscar persona creada

#### **Sección 3: Recibos/Cobros (2 tests)**
- Test 3.1: Crear recibo de cobro
- Test 3.2: Listar recibos

#### **Sección 4: Caja/Gastos (2 tests)**
- Test 4.1: Registrar gasto via API
- Test 4.2: Listar movimientos de caja

#### **Sección 5: Talleres (2 tests)**
- Test 5.1: Crear taller via API
- Test 5.2: Listar talleres

### Interpretación de Resultados

**Estados de los tests**:
- 🟡 **Ejecutando...**: Test en progreso
- ✅ **PASS**: Test exitoso
- ❌ **FAIL**: Test falló

**Resumen final**:
```
Total tests: 13
✅ Pasados: 13
❌ Fallados: 0

🎉 ¡Todos los tests pasaron!
```

---

## 🖱️ Tests Manuales desde Navegador

### Test Manual 1: Dashboard y Navegación

**Objetivo**: Verificar que el sistema carga y la navegación funciona

1. Ir a: http://localhost:10013/
2. **Verificar**:
   - ✅ Dashboard carga correctamente
   - ✅ Sidebar muestra menú de navegación
   - ✅ Header muestra información del sistema
   - ✅ No hay errores en consola del navegador (F12)

3. **Navegar por el menú**:
   - Click en "Personas" → debe cargar lista de personas
   - Click en "Cobrar" → debe mostrar selector de tipo de cobro
   - Click en "Caja" → debe mostrar movimientos
   - Click en "Talleres" → debe listar talleres

**✅ PASS**: Navegación fluida sin errores
**❌ FAIL**: Errores 404, páginas en blanco, o errores en consola

---

### Test Manual 2: Crear Socio/Cliente

**Objetivo**: Verificar formularios de creación

1. Ir a: http://localhost:10013/nuevo-socio/
2. **Completar formulario**:
   - Nombre: Test Manual
   - Apellido: Sistema
   - DNI: 11111111
   - Email: test@manual.com
   - Teléfono: 3815000000
   - Categoría: General

3. Click "Guardar Socio"
4. **Verificar**:
   - ✅ Aparece notificación de éxito
   - ✅ Redirige a lista de personas o ficha
   - ✅ Nuevo socio aparece en lista

**✅ PASS**: Socio creado correctamente
**❌ FAIL**: Error al guardar o no aparece en lista

---

### Test Manual 3: Apertura de Caja

**Objetivo**: Verificar sistema de caja

1. Abrir consola del navegador (F12)
2. **Ejecutar**:
```javascript
CDCAPI.caja.abrirCaja({
  monto_inicial: 10000,
  responsable: "Test Manual"
})
.then(response => console.log('Resultado:', response))
.catch(error => console.error('Error:', error));
```

3. **Verificar**:
   - ✅ Respuesta: `{success: true, message: "Caja abierta correctamente"}`
   - ✅ Balance actualizado a $10,000

4. **Verificar balance**:
```javascript
CDCAPI.caja.balance()
.then(response => console.log('Balance:', response));
```

**Resultado esperado**:
```json
{
  "success": true,
  "data": {
    "balance": 10000
  }
}
```

**✅ PASS**: Caja abierta y balance correcto
**❌ FAIL**: Error o balance incorrecto

---

### Test Manual 4: Crear Recibo de Cobro

**Objetivo**: Verificar flujo completo de cobro

**Pre-requisito**: Tener caja abierta (Test Manual 3)

1. **Obtener ID de un socio**:
```javascript
CDCAPI.personas.list()
.then(response => console.log('Personas:', response.data));
```

2. **Crear recibo** (reemplazar `SOCIO_ID` con un ID real):
```javascript
CDCAPI.recibos.create({
  persona_id: SOCIO_ID,
  tipo: 'cuota-socio',
  items: [{
    descripcion: 'Cuota Test',
    cantidad: 1,
    precio_unitario: 2000,
    subtotal: 2000
  }],
  total: 2000,
  metodo_pago: 'efectivo',
  concepto: 'Cuota mensual'
})
.then(response => console.log('Recibo:', response))
.catch(error => console.error('Error:', error));
```

3. **Verificar respuesta**:
```json
{
  "success": true,
  "message": "Recibo creado correctamente",
  "data": {
    "recibo_id": 1,
    "numero_recibo": "C20260119-001"
  }
}
```

4. **Verificar balance actualizado**:
```javascript
CDCAPI.caja.balance()
.then(response => console.log('Nuevo balance:', response));
```

**Resultado esperado**: Balance = $10,000 + $2,000 = $12,000

**✅ PASS**: Recibo creado y balance actualizado
**❌ FAIL**: Error o balance incorrecto

---

## 💻 Tests desde Terminal

### Test 1: Verificar API REST Disponible

```bash
curl -s "http://localhost:10013/wp-json/cdc/v1/" | python3 -m json.tool
```

**Resultado esperado**: Lista de endpoints disponibles

---

### Test 2: Listar Personas

```bash
curl -s "http://localhost:10013/wp-json/cdc/v1/personas" | python3 -m json.tool
```

**Resultado esperado**:
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "nombre": "Juan",
      "apellido": "Pérez",
      "dni": "12345678",
      "tipo": "socio",
      ...
    }
  ]
}
```

---

### Test 3: Buscar Persona por Query

```bash
curl -s "http://localhost:10013/wp-json/cdc/v1/personas/search?query=Juan" | python3 -m json.tool
```

---

### Test 4: Balance de Caja

```bash
curl -s "http://localhost:10013/wp-json/cdc/v1/caja/balance" | python3 -m json.tool
```

**Resultado esperado**:
```json
{
  "success": true,
  "data": {
    "balance": 10000
  }
}
```

---

### Test 5: Movimientos de Hoy

```bash
curl -s "http://localhost:10013/wp-json/cdc/v1/caja/movimientos/today" | python3 -m json.tool
```

---

### Test 6: Crear Socio (POST)

```bash
curl -X POST "http://localhost:10013/wp-json/cdc/v1/personas" \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "socio",
    "nombre": "Terminal",
    "apellido": "Test",
    "dni": "22222222",
    "email": "terminal@test.com"
  }' | python3 -m json.tool
```

**Resultado esperado**:
```json
{
  "success": true,
  "message": "Persona creada exitosamente",
  "data": {
    "id": 5
  }
}
```

---

### Test 7: Abrir Caja (POST)

```bash
curl -X POST "http://localhost:10013/wp-json/cdc/v1/caja/apertura" \
  -H "Content-Type: application/json" \
  -d '{
    "monto_inicial": 5000,
    "responsable": "Terminal Test"
  }' | python3 -m json.tool
```

---

### Script de Tests Completo

Guardar como `run-tests.sh`:

```bash
#!/bin/bash

API_URL="http://localhost:10013/wp-json/cdc/v1"
PASSED=0
FAILED=0

echo "🧪 Ejecutando tests de CDC API..."

# Test API disponible
if curl -s "$API_URL/" | grep -q "namespace"; then
  echo "✅ Test 1: API disponible"
  ((PASSED++))
else
  echo "❌ Test 1: API no disponible"
  ((FAILED++))
fi

# Test listar personas
if curl -s "$API_URL/personas" | grep -q "success"; then
  echo "✅ Test 2: GET /personas"
  ((PASSED++))
else
  echo "❌ Test 2: GET /personas falló"
  ((FAILED++))
fi

# Test balance
if curl -s "$API_URL/caja/balance" | grep -q "balance"; then
  echo "✅ Test 3: GET /caja/balance"
  ((PASSED++))
else
  echo "❌ Test 3: GET /caja/balance falló"
  ((FAILED++))
fi

echo ""
echo "Resultado: $PASSED pasados, $FAILED fallados"
```

**Ejecutar**:
```bash
chmod +x run-tests.sh
./run-tests.sh
```

---

## 🔍 Herramientas de Diagnóstico

### Panel de Diagnóstico del Sistema

**URL**: http://localhost:10013/diagnostico/

**Información mostrada**:
- ✅ Estado del plugin CDC API (activado/desactivado)
- ✅ Estado del tema CDC Sistema (activado/desactivado)
- ✅ Tablas de base de datos (13 tablas custom)
- ✅ Endpoints REST API disponibles
- ✅ Páginas del sistema creadas
- ✅ Configuración de WordPress

**Usar cuando**:
- Problemas con API (404, errores)
- Verificar instalación inicial
- Debugging general del sistema

---

### Verificador de Páginas

**URL**: http://localhost:10013/verificar-paginas.php

**Verifica**:
- ✅ Página "Personas" existe
- ✅ Página "Cobrar" existe
- ✅ Página "Registrar Gasto" existe
- ✅ Página "Talleres" existe
- ✅ Página "Eventos" existe
- ✅ Página "Salas" existe
- ✅ Página "Diagnóstico" existe
- ✅ Página "Tests" existe

**Acción**: Si faltan páginas, muestra instrucciones para crearlas

---

### Verificador de Templates

**URL**: http://localhost:10013/verificar-templates.php

**Verifica**:
- ✅ Templates del tema existen en disco
- ✅ Templates asignados a páginas
- ✅ Permisos de archivos correctos

---

### Instalador de Tablas

**URL**: http://localhost:10013/instalar-tablas/

**Función**: Crear/reinstalar tablas de base de datos

**Usar cuando**:
- Primera instalación
- Tablas corruptas
- Reset de base de datos

**⚠️ ADVERTENCIA**: Esto puede borrar datos. Usar solo en desarrollo.

---

## ✅ Resultados Esperados

### Estado Óptimo del Sistema

Cuando todo funciona correctamente:

**Tests Automatizados**:
```
Total tests: 13
✅ Pasados: 13
❌ Fallados: 0
```

**Panel de Diagnóstico**:
- Plugin CDC API: ✅ Activado
- Tema CDC Sistema: ✅ Activado
- Tablas creadas: ✅ 13/13
- Páginas creadas: ✅ 8/8
- API REST: ✅ Disponible

**API REST**:
- Todos los endpoints responden con HTTP 200
- Formato de respuesta: `{"success": true, "data": ...}`
- Sin errores en logs

**Base de Datos**:
```sql
-- Tablas que deben existir:
wp_cdc_personas
wp_cdc_recibos
wp_cdc_movimientos_caja
wp_cdc_gastos
wp_cdc_talleres
wp_cdc_eventos
wp_cdc_salas
wp_cdc_reservas_salas
wp_cdc_cuotas_talleres
wp_cdc_inscripciones_talleres
wp_cdc_entradas_eventos
wp_cdc_talleristas
wp_cdc_categorias_gastos
```

---

## 🐛 Troubleshooting

### Problema: Tests Fallan (0/13 pasados)

**Causa probable**: Scripts no cargados o plugin inactivo

**Solución**:
1. Abrir consola del navegador (F12)
2. Buscar errores JavaScript
3. Verificar que plugin CDC API esté activado
4. Ir a http://localhost:10013/diagnostico/ y verificar estado

---

### Problema: API devuelve 404

**Causa probable**: Permalinks no actualizados o plugin inactivo

**Solución**:
1. Ir a http://localhost:10013/wp-admin/options-permalink.php
2. Click "Save Changes" sin cambiar nada
3. Verificar plugin activado
4. Probar endpoint: http://localhost:10013/wp-json/cdc/v1/

---

### Problema: "Saldo insuficiente" al registrar gasto

**Causa**: Caja no tiene saldo o no está abierta

**Solución**:
```javascript
// 1. Verificar balance
CDCAPI.caja.balance().then(r => console.log(r));

// 2. Si es $0, abrir caja
CDCAPI.caja.abrirCaja({
  monto_inicial: 10000,
  responsable: "Admin"
}).then(r => console.log(r));
```

---

### Problema: Páginas muestran 404

**Causa**: Páginas no creadas o permalinks incorrectos

**Solución**:
1. Ir a http://localhost:10013/verificar-paginas.php
2. Si faltan páginas, ejecutar script de creación:
   - WP Admin → Herramientas → Site Health
   - O desactivar/reactivar tema

---

### Problema: Recibo no crea movimiento de caja

**Causa**: Error en CajaService o ReciboService

**Solución**:
1. Revisar logs: `logs/php/error.log`
2. Verificar que tabla `wp_cdc_movimientos_caja` exista
3. Verificar balance de caja antes de crear recibo

---

### Problema: Balance de caja incorrecto

**Causa**: Movimientos sin saldo calculado correctamente

**Solución**:
```sql
-- Verificar movimientos
SELECT * FROM wp_cdc_movimientos_caja ORDER BY id DESC LIMIT 10;

-- Verificar que tengan saldo_anterior y saldo_nuevo
SELECT id, tipo, monto, saldo_anterior, saldo_nuevo
FROM wp_cdc_movimientos_caja
WHERE saldo_nuevo IS NULL OR saldo_anterior IS NULL;
```

---

## 📝 Checklist de Testing Completo

Antes de marcar el sistema como "listo":

### Backend
- [ ] Plugin CDC API activado
- [ ] 13 tablas custom creadas
- [ ] Todos los endpoints responden (GET /wp-json/cdc/v1/)
- [ ] Balance de caja calcula correctamente
- [ ] Movimientos de caja se registran con saldo

### Frontend
- [ ] Tema CDC Sistema activado
- [ ] 8 páginas del sistema creadas
- [ ] Header y sidebar muestran correctamente
- [ ] Notificaciones toast funcionan
- [ ] No hay errores en consola del navegador

### Tests
- [ ] Tests automatizados pasan (13/13)
- [ ] Tests manuales completados exitosamente
- [ ] Panel de diagnóstico muestra todo en verde
- [ ] Verificador de páginas sin errores

### Logs
- [ ] No hay errores PHP en `logs/php/error.log`
- [ ] No hay errores JavaScript en consola
- [ ] No hay warnings de deprecated functions

---

## 📊 Métricas de Éxito

### Métricas Cuantitativas

- **Tests automatizados**: 13/13 pasados (100%)
- **Endpoints API**: 14+ endpoints funcionando
- **Páginas creadas**: 8/8 (100%)
- **Tablas DB**: 13/13 (100%)
- **Cobertura de funcionalidades**: ~60% (Phase 1)

### Métricas Cualitativas

- Sistema carga sin errores
- Navegación fluida entre páginas
- API responde en < 500ms
- UI renderiza correctamente
- Sin memory leaks en navegador

---

**Última actualización**: 2026-01-19
**Versión del sistema**: 0.1.0 (Phase 1)
**Estado**: ✅ Sistema de testing completo y funcional
