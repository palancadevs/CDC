# 🔧 Fix: Problema con Cuotas de Socios

## Problema Detectado

Cuando intentas crear un socio, las cuotas NO se generan porque hay **foreign key constraints incorrectos** en la base de datos que están bloqueando los inserts.

### Error en Logs:
```
Cannot add or update a child row: a foreign key constraint fails
(`local`.`wp_cdc_cuota_socio`, CONSTRAINT `wp_cdc_cuota_socio_ibfk_1`
FOREIGN KEY (`socio_id`) REFERENCES `wp_cdc_persona` (`id`) ON DELETE CASCADE)
```

### Causa:
Alguien creó manualmente foreign keys en la base de datos con nombres de columnas incorrectos:
- El constraint apunta a `socio_id` ❌
- El código usa `persona_id` ✅

## Solución Rápida

### Opción 1: Script PHP Automático (Recomendado)

1. Abre el navegador y visita:
   ```
   http://localhost:10013/fix-database.php
   ```

2. Verás un reporte de todas las operaciones ejecutadas.

3. **IMPORTANTE**: Después de ejecutar, elimina el archivo:
   ```bash
   rm app/public/fix-database.php
   ```

### Opción 2: SQL Manual

Si prefieres ejecutar SQL manualmente, abre **phpMyAdmin** o **MySQL Workbench** y ejecuta:

```bash
# Ejecutar el archivo SQL
mysql -u root -proot local < app/sql/fix-foreign-keys.sql
```

O ejecuta manualmente cada comando:

```sql
-- Eliminar foreign keys incorrectos
ALTER TABLE wp_cdc_cuota_socio DROP FOREIGN KEY IF EXISTS wp_cdc_cuota_socio_ibfk_1;
ALTER TABLE wp_cdc_cuotas_taller DROP FOREIGN KEY IF EXISTS wp_cdc_cuotas_taller_ibfk_1;
ALTER TABLE wp_cdc_socios DROP FOREIGN KEY IF EXISTS wp_cdc_socios_ibfk_1;
ALTER TABLE wp_cdc_clientes DROP FOREIGN KEY IF EXISTS wp_cdc_clientes_ibfk_1;
ALTER TABLE wp_cdc_inscripciones_taller DROP FOREIGN KEY IF EXISTS wp_cdc_inscripciones_taller_ibfk_1;
ALTER TABLE wp_cdc_inscripciones_taller DROP FOREIGN KEY IF EXISTS wp_cdc_inscripciones_taller_ibfk_2;
ALTER TABLE wp_cdc_reservas_salas DROP FOREIGN KEY IF EXISTS wp_cdc_reservas_salas_ibfk_1;
ALTER TABLE wp_cdc_reservas_salas DROP FOREIGN KEY IF EXISTS wp_cdc_reservas_salas_ibfk_2;
```

## Verificar que Funcionó

Después de ejecutar el fix:

### 1. Crear un Socio de Prueba

Ve a: http://localhost:10013/nuevo-socio

Completa el formulario:
- Nombre: Test
- Apellido: Socio
- DNI: 12345678
- ✅ Asegúrate que el checkbox "Generar planilla de cuotas" esté marcado

Haz clic en "Crear socio"

### 2. Verificar que se Crearon las Cuotas

```sql
-- Ver cuotas del último socio creado
SELECT cs.*, p.nombre, p.apellido
FROM wp_cdc_cuota_socio cs
JOIN wp_cdc_personas p ON cs.persona_id = p.id
ORDER BY cs.created_at DESC, cs.mes ASC
LIMIT 12;
```

Deberías ver 12 cuotas (una por cada mes desde enero hasta diciembre 2026).

### 3. Probar Cobrar una Cuota

```bash
curl -X POST http://localhost:10013/wp-json/cdc/v1/cobros/cuota-socio \
  -H "Content-Type: application/json" \
  -d '{
    "persona_id": 6,
    "cuota_ids": [1],
    "medio_pago": "efectivo"
  }'
```

(Reemplaza `persona_id` y `cuota_ids` con los valores correctos)

## ¿Por Qué Pasó Esto?

Los foreign keys fueron creados automáticamente por MySQL/phpMyAdmin en algún momento, probablemente cuando:
- Se importó un SQL dump con constraints
- Se usó una herramienta que agregó constraints automáticamente
- Se ejecutó algún script de migración anterior

**Nuestro schema.php NO define foreign keys** porque preferimos mantener flexibilidad en el modelo de datos y evitar problemas como este.

## Prevenir en el Futuro

1. **NO agregar foreign keys manualmente** a las tablas CDC
2. **Siempre usar schema.php** como fuente de verdad
3. **Revisar imports SQL** antes de ejecutarlos

## Si el Problema Persiste

Si después del fix sigues teniendo problemas:

1. Verifica que NO hay más foreign keys:
   ```sql
   SELECT
       TABLE_NAME,
       CONSTRAINT_NAME,
       COLUMN_NAME,
       REFERENCED_TABLE_NAME
   FROM information_schema.KEY_COLUMN_USAGE
   WHERE TABLE_SCHEMA = 'local'
   AND TABLE_NAME LIKE 'wp_cdc%'
   AND REFERENCED_TABLE_NAME IS NOT NULL;
   ```

   **Resultado esperado**: 0 filas (sin foreign keys)

2. Revisa los logs:
   ```bash
   tail -f logs/php/error.log
   ```

3. Avísame y te ayudo a investigar más.

## Resumen

✅ Ejecuta `http://localhost:10013/fix-database.php`
✅ Elimina el archivo después
✅ Crea un socio de prueba
✅ Verifica que tenga 12 cuotas
✅ Prueba cobrar una cuota

¡Listo! 🎉
