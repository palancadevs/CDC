-- CDC Sistema - Datos de Prueba
-- Ejecutar este script después de activar el plugin cdc-api

-- Insertar persona de prueba para login
INSERT INTO wp_cdc_personas (nombre, apellido, dni, email, telefono, tipo, created_at, updated_at)
VALUES ('Juan', 'Pérez', '12345678', 'juan.perez@example.com', '1234567890', 'socio', NOW(), NOW())
ON DUPLICATE KEY UPDATE dni=dni;

-- Obtener el ID de la persona recién creada
SET @persona_id = LAST_INSERT_ID();

-- Crear registro de socio asociado
INSERT INTO wp_cdc_socios (persona_id, numero_socio, fecha_alta, estado, monto_cuota, dia_cobro, created_at, updated_at)
VALUES (@persona_id, 'S-0001', CURDATE(), 'activo', 1500.00, 10, NOW(), NOW())
ON DUPLICATE KEY UPDATE persona_id=persona_id;

-- Insertar más personas de prueba
INSERT INTO wp_cdc_personas (nombre, apellido, dni, email, telefono, tipo, created_at, updated_at)
VALUES
    ('María', 'González', '87654321', 'maria.gonzalez@example.com', '0987654321', 'cliente', NOW(), NOW()),
    ('Carlos', 'Rodríguez', '11223344', 'carlos.rodriguez@example.com', '1122334455', 'ambos', NOW(), NOW()),
    ('Ana', 'Martínez', '44332211', 'ana.martinez@example.com', '4433221100', 'socio', NOW(), NOW())
ON DUPLICATE KEY UPDATE dni=dni;

-- Mensaje de confirmación
SELECT 'Datos de prueba insertados correctamente. Usa DNI 12345678 para hacer login.' AS mensaje;

-- Ver todas las personas creadas
SELECT id, nombre, apellido, dni, tipo FROM wp_cdc_personas ORDER BY id;
