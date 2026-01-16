# Guía de Pruebas - CDC Sistema

## Prerequisitos

### 1. Activar plugin cdc-api
1. Ve a: http://localhost:10013/wp-admin/
2. Login con tus credenciales de WordPress admin
3. Ve a Plugins → Plugins instalados
4. Busca "CDC API" y haz click en "Activar"
5. Verifica que se hayan creado las tablas en la base de datos

### 2. Insertar datos de prueba
Ejecuta el archivo `test-data.sql` en tu base de datos:

**Opción A: Usando phpMyAdmin**
1. Ve a http://localhost:10013/phpmyadmin (o tu gestor de BD)
2. Selecciona la base de datos de WordPress
3. Ve a la pestaña "SQL"
4. Copia el contenido de `test-data.sql`
5. Haz click en "Continuar"

**Opción B: Usando Local by Flywheel**
1. Click derecho en tu sitio → "Open site shell"
2. Ejecuta: `wp db query < test-data.sql`

**Opción C: Usando línea de comandos**
```bash
cd "/Users/joanromero/Local Sites/cdc-gestion"
# Reemplaza USER y PASSWORD con tus credenciales de MySQL
mysql -u root -p local < test-data.sql
```

### 3. Activar el tema cdc-sistema (si no está activado)
1. Ve a: http://localhost:10013/wp-admin/themes.php
2. Activa "CDC Sistema"
3. Las páginas (Login, Personas, Cobrar, etc.) se crearán automáticamente

---

## Pruebas del Sistema de Autenticación

### Test 1: Acceso sin login (Protección de páginas)
**Objetivo:** Verificar que todas las páginas redirigen a login

1. **Abre navegador en modo incógnito**
2. Ve a: http://localhost:10013/
3. **Resultado esperado:** Debes ser redirigido a http://localhost:10013/login
4. Prueba otras URLs:
   - http://localhost:10013/personas → Debe redirigir a /login
   - http://localhost:10013/cobrar → Debe redirigir a /login
   - http://localhost:10013/caja → Debe redirigir a /login

**✅ PASS:** Todas las páginas redirigen a login
**❌ FAIL:** Puedes ver contenido sin estar logueado

---

### Test 2: Login con DNI válido
**Objetivo:** Autenticación exitosa crea usuario WordPress y da acceso

1. **En la página de login** (http://localhost:10013/login)
2. **Ingresa DNI:** `12345678`
3. **Haz click en "Ingresar"**
4. **Resultados esperados:**
   - Aparece mensaje "Verificando credenciales..."
   - Eres redirigido a la página de inicio (dashboard)
   - En el header ves: "Socio" y "Juan Pérez"
   - Hay un botón "Cerrar sesión"

**✅ PASS:** Login exitoso, ves el dashboard
**❌ FAIL:** Error o no redirige

**Si falla:**
- Abre la consola del navegador (F12)
- Ve a la pestaña "Network" y busca errores
- Verifica que el plugin cdc-api esté activado
- Verifica que la tabla wp_cdc_personas tenga el DNI 12345678

---

### Test 3: Login con DNI inválido
**Objetivo:** Validación de DNI y manejo de errores

**Pruebas:**

1. **DNI no numérico:**
   - Ingresa: `abcd1234`
   - **Esperado:** No permite ingresar letras (campo solo acepta números)

2. **DNI muy corto:**
   - Ingresa: `123`
   - Click "Ingresar"
   - **Esperado:** Error "El DNI debe tener 7 u 8 dígitos"

3. **DNI que no existe:**
   - Ingresa: `99999999`
   - Click "Ingresar"
   - **Esperado:** Error "DNI no encontrado en el sistema"

**✅ PASS:** Todos los errores se muestran correctamente
**❌ FAIL:** Permite DNI inválidos o no muestra errores

---

### Test 4: Header con datos reales
**Objetivo:** Verificar que el header muestre información de la persona logueada

1. **Estando logueado** con DNI 12345678
2. **Mira el header** (esquina superior derecha)
3. **Debes ver:**
   - Badge azul con texto "Socio"
   - Nombre "Juan Pérez"
   - Link "Cerrar sesión" con icono

**✅ PASS:** Header muestra datos correctos
**❌ FAIL:** Muestra "Usuario Sistema" o datos dummy

---

### Test 5: Navegación entre páginas
**Objetivo:** Verificar que la sesión persiste

1. **Estando logueado**, navega por el menú lateral:
   - Personas
   - Cobrar
   - Caja
   - Talleres
   - Eventos
   - Salas

2. **Resultado esperado:**
   - Puedes acceder a todas las páginas
   - El header siempre muestra tus datos
   - No te pide login nuevamente

**✅ PASS:** Navegación fluida, sesión persistente
**❌ FAIL:** Te pide login al cambiar de página

---

### Test 6: Logout
**Objetivo:** Cerrar sesión correctamente

1. **Estando logueado**, haz click en "Cerrar sesión"
2. **Resultado esperado:**
   - Eres redirigido a /login
   - Si intentas ir a / te redirige a /login
   - No puedes acceder a ninguna página sin login

**✅ PASS:** Logout funciona, no puedes acceder sin login
**❌ FAIL:** Puedes acceder después de hacer logout

---

### Test 7: Sesión en múltiples tabs
**Objetivo:** Verificar comportamiento de sesión

1. **Estando logueado**, abre una nueva pestaña
2. Ve a: http://localhost:10013/personas
3. **Resultado esperado:**
   - Puedes ver la página (sesión compartida)
   - El header muestra tus datos

4. **En la primera tab**, haz logout
5. **En la segunda tab**, intenta navegar o refrescar
6. **Resultado esperado:**
   - Te redirige a /login (sesión cerrada en todas las tabs)

**✅ PASS:** Sesión consistente en todas las tabs
**❌ FAIL:** Comportamiento inconsistente

---

### Test 8: Login con diferentes tipos de persona
**Objetivo:** Verificar que el rol se muestra correctamente

**Logout primero, luego prueba con estos DNI:**

1. **DNI: 87654321** (María González - Cliente)
   - **Esperado:** Badge muestra "Cliente"

2. **DNI: 11223344** (Carlos Rodríguez - Ambos)
   - **Esperado:** Badge muestra "Socio/Cliente"

3. **DNI: 44332211** (Ana Martínez - Socio)
   - **Esperado:** Badge muestra "Socio"

**✅ PASS:** Cada tipo muestra el badge correcto
**❌ FAIL:** Todos muestran el mismo rol

---

### Test 9: Persistencia de sesión tras refresh
**Objetivo:** Sesión sobrevive a recargas de página

1. **Estando logueado**
2. Presiona F5 o Cmd+R (refresh)
3. **Resultado esperado:**
   - Sigues logueado
   - No te pide login nuevamente

**✅ PASS:** Sesión persiste tras refresh
**❌ FAIL:** Te pide login de nuevo

---

### Test 10: Creación automática de usuario WordPress
**Objetivo:** Verificar que se crea usuario en WordPress

1. **Login con DNI nuevo** (por ejemplo, uno de los otros DNI de prueba)
2. **Ve a WordPress admin:** http://localhost:10013/wp-admin/users.php
3. **Busca el usuario** con username = DNI (ej: 87654321)
4. **Verifica:**
   - Usuario existe
   - Email coincide con cdc_personas
   - Rol es "CDC User"

**✅ PASS:** Usuario WordPress creado correctamente
**❌ FAIL:** No se crea usuario

---

## Pruebas de Integración API (Preparación)

### Test 11: API disponible
**Objetivo:** Verificar que el REST API funciona

1. **Estando logueado**
2. **Abre consola del navegador** (F12)
3. **Ejecuta este código:**
```javascript
CDCAPI.personas.list()
  .then(response => console.log('API Response:', response))
  .catch(error => console.error('API Error:', error));
```

4. **Resultado esperado:**
   - Si hay personas: `{success: true, data: [...]}`
   - Si no hay personas: `{success: true, data: []}`

**✅ PASS:** API responde correctamente
**❌ FAIL:** Error 401, 404, o sin respuesta

---

### Test 12: Notificaciones Toast
**Objetivo:** Verificar sistema de notificaciones

1. **Estando logueado**
2. **Abre consola del navegador** (F12)
3. **Ejecuta estos comandos:**
```javascript
// Prueba notificación de éxito
CDC.showNotification('Prueba exitosa', 'success');

// Espera 4 segundos, luego prueba error
setTimeout(() => CDC.showNotification('Prueba de error', 'error'), 4000);

// Espera más, prueba warning
setTimeout(() => CDC.showNotification('Prueba de advertencia', 'warning'), 8000);

// Prueba info
setTimeout(() => CDC.showNotification('Prueba informativa', 'info'), 12000);
```

4. **Resultado esperado:**
   - Aparecen toast notifications en la esquina superior derecha
   - Cada una con color e icono diferente
   - Se auto-cierran después de 3.5 segundos
   - Puedes cerrarlas manualmente con la X

**✅ PASS:** Notificaciones funcionan correctamente
**❌ FAIL:** No aparecen o no se ven bien

---

## Problemas Comunes y Soluciones

### Problema: "Error de conexión" al hacer login
**Causa:** Plugin cdc-api no está activado o tablas no existen
**Solución:**
1. Verifica activación del plugin
2. Desactiva y reactiva el plugin para crear tablas
3. Verifica que existan las tablas en la base de datos

### Problema: "DNI no encontrado" con DNI correcto
**Causa:** Datos de prueba no insertados
**Solución:**
1. Ejecuta test-data.sql nuevamente
2. Verifica manualmente en phpMyAdmin que existe el registro

### Problema: Redirige a /login infinitamente
**Causa:** Error en session-guard.php o persona_id no vinculado
**Solución:**
1. Revisa errores PHP en logs/php/error.log
2. Verifica que wp_usermeta tenga cdc_persona_id

### Problema: Header muestra "Usuario Sistema"
**Causa:** Funciones dummy no fueron eliminadas correctamente
**Solución:** Ya fueron eliminadas en el commit, verifica que tengas la última versión

### Problema: API devuelve 401
**Causa:** Sesión no está establecida correctamente
**Solución:**
1. Verifica que wp_set_auth_cookie() se ejecutó
2. Prueba logout y login nuevamente
3. Limpia cookies del navegador

---

## Checklist Final

Antes de continuar con las pantallas, verifica:

- [ ] Plugin cdc-api activado
- [ ] Tema cdc-sistema activado
- [ ] Datos de prueba insertados
- [ ] Puedes hacer login con DNI 12345678
- [ ] Header muestra "Juan Pérez" y "Socio"
- [ ] Logout funciona
- [ ] API responde (Test 11)
- [ ] Notificaciones funcionan (Test 12)
- [ ] No hay errores en consola del navegador
- [ ] No hay errores en logs/php/error.log

**Si todos los checks están ✅**, el sistema está listo para continuar con la implementación de las pantallas con API real.

---

## Próximos Pasos

Una vez completadas las pruebas exitosamente:
1. Conectar página Personas con API real
2. Crear formularios Nuevo Socio / Nuevo Cliente
3. Implementar flujo completo de Cobrar
4. Conectar Caja con movimientos reales
5. Implementar resto de pantallas (Talleres, Eventos, Salas)
6. Conectar Dashboard con datos reales

**¡El sistema de autenticación está completo y listo para usar!**
