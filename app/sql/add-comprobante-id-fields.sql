-- Migración: Agregar campos comprobante_id para integración ARCA
-- Fecha: 2026-01-22
-- Descripción: Agrega campos para almacenar el ID del comprobante ARCA en las tablas relevantes

-- 1. Agregar comprobante_id a movimientos_caja
ALTER TABLE wp_cdc_movimientos_caja
ADD COLUMN IF NOT EXISTS comprobante_id VARCHAR(100) DEFAULT NULL,
ADD INDEX IF NOT EXISTS idx_comprobante_id (comprobante_id);

-- 2. Agregar comprobante_id a reservas_salas
ALTER TABLE wp_cdc_reservas_salas
ADD COLUMN IF NOT EXISTS comprobante_id VARCHAR(100) DEFAULT NULL,
ADD INDEX IF NOT EXISTS idx_comprobante_id (comprobante_id);

-- Nota: cuota_socio y cuotas_taller ya tienen el campo comprobante_id definido en el schema original

-- Verificar que los campos se agregaron correctamente
SELECT 'Verificando wp_cdc_movimientos_caja' AS tabla;
SHOW COLUMNS FROM wp_cdc_movimientos_caja LIKE 'comprobante_id';

SELECT 'Verificando wp_cdc_reservas_salas' AS tabla;
SHOW COLUMNS FROM wp_cdc_reservas_salas LIKE 'comprobante_id';

SELECT 'Verificando wp_cdc_cuota_socio' AS tabla;
SHOW COLUMNS FROM wp_cdc_cuota_socio LIKE 'comprobante_id';

SELECT 'Verificando wp_cdc_cuotas_taller' AS tabla;
SHOW COLUMNS FROM wp_cdc_cuotas_taller LIKE 'comprobante_id';
