-- Fix: Eliminar foreign keys incorrectos de wp_cdc_cuota_socio
-- Problema: Hay constraints con nombre 'socio_id' pero el código usa 'persona_id'

-- 1. Eliminar constraint incorrecto
ALTER TABLE wp_cdc_cuota_socio
DROP FOREIGN KEY IF EXISTS wp_cdc_cuota_socio_ibfk_1;

-- 2. Verificar que no haya más constraints incorrectos en otras tablas
ALTER TABLE wp_cdc_cuotas_taller
DROP FOREIGN KEY IF EXISTS wp_cdc_cuotas_taller_ibfk_1;

ALTER TABLE wp_cdc_socios
DROP FOREIGN KEY IF EXISTS wp_cdc_socios_ibfk_1;

ALTER TABLE wp_cdc_clientes
DROP FOREIGN KEY IF EXISTS wp_cdc_clientes_ibfk_1;

ALTER TABLE wp_cdc_inscripciones_taller
DROP FOREIGN KEY IF EXISTS wp_cdc_inscripciones_taller_ibfk_1;

ALTER TABLE wp_cdc_inscripciones_taller
DROP FOREIGN KEY IF EXISTS wp_cdc_inscripciones_taller_ibfk_2;

ALTER TABLE wp_cdc_reservas_salas
DROP FOREIGN KEY IF EXISTS wp_cdc_reservas_salas_ibfk_1;

ALTER TABLE wp_cdc_reservas_salas
DROP FOREIGN KEY IF EXISTS wp_cdc_reservas_salas_ibfk_2;

-- Verificar que se eliminaron correctamente
SELECT
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'local'
AND TABLE_NAME LIKE 'wp_cdc%'
AND REFERENCED_TABLE_NAME IS NOT NULL;
