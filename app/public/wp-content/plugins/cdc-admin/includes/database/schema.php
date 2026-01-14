<?php
/**
 * Database Schema for CDC Admin
 *
 * Creates all custom tables needed for the CDC management system
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDC_Database_Schema {

    /**
     * Create all database tables
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // cdc_persona table
        $sql_persona = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_persona (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            tipo enum('socio','cliente') NOT NULL DEFAULT 'cliente',
            nombre varchar(100) NOT NULL,
            apellido varchar(100) NOT NULL,
            dni varchar(20) NOT NULL,
            tel varchar(50) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            domicilio varchar(255) DEFAULT NULL,
            fecha_alta date NOT NULL,
            estado enum('activo','inactivo') NOT NULL DEFAULT 'activo',
            categoria varchar(50) DEFAULT NULL,
            subcategoria varchar(50) DEFAULT NULL,
            observaciones text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) DEFAULT NULL,
            updated_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY dni (dni),
            KEY tipo (tipo),
            KEY estado (estado),
            KEY fecha_alta (fecha_alta)
        ) $charset_collate;";

        // cdc_cuota_socio table
        $sql_cuota_socio = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_cuota_socio (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            socio_id bigint(20) NOT NULL,
            anio int(4) NOT NULL,
            mes int(2) NOT NULL,
            monto decimal(10,2) NOT NULL,
            pagada tinyint(1) NOT NULL DEFAULT 0,
            fecha_pago datetime DEFAULT NULL,
            medio_pago varchar(50) DEFAULT NULL,
            comprobante_id varchar(100) DEFAULT NULL,
            observaciones text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY socio_periodo (socio_id, anio, mes),
            KEY pagada (pagada),
            KEY fecha_pago (fecha_pago),
            FOREIGN KEY (socio_id) REFERENCES {$wpdb->prefix}cdc_persona(id) ON DELETE CASCADE
        ) $charset_collate;";

        // cdc_movimiento_caja table (SOURCE OF TRUTH for accounting)
        $sql_movimiento_caja = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_movimiento_caja (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            fecha_hora datetime NOT NULL,
            tipo enum('ingreso','egreso') NOT NULL,
            monto decimal(10,2) NOT NULL,
            medio_pago varchar(50) NOT NULL,
            responsable_user_id bigint(20) NOT NULL,
            concepto_tipo varchar(50) NOT NULL COMMENT 'CuotaSocio, CuotaTaller, EntradaEvento, AlquilerSala, PagoTallerista, Otro',
            concepto_id bigint(20) DEFAULT NULL COMMENT 'ID de la entidad relacionada',
            descripcion text DEFAULT NULL,
            categoria varchar(50) DEFAULT NULL,
            observaciones text DEFAULT NULL,
            comprobante_id varchar(100) DEFAULT NULL COMMENT 'ID del comprobante ARCA',
            factura_status enum('pending','ok','error') DEFAULT 'pending',
            wc_order_id bigint(20) DEFAULT NULL COMMENT 'ID de orden WooCommerce relacionada',
            mp_payment_id varchar(100) DEFAULT NULL COMMENT 'ID de pago Mercado Pago',
            estado enum('activo','anulado') NOT NULL DEFAULT 'activo',
            motivo_anulacion text DEFAULT NULL,
            anulado_at datetime DEFAULT NULL,
            anulado_by bigint(20) DEFAULT NULL,
            adjunto_id bigint(20) DEFAULT NULL COMMENT 'ID del attachment de WP',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fecha_hora (fecha_hora),
            KEY tipo (tipo),
            KEY concepto_tipo (concepto_tipo),
            KEY estado (estado),
            KEY responsable_user_id (responsable_user_id),
            KEY wc_order_id (wc_order_id),
            KEY mp_payment_id (mp_payment_id),
            KEY comprobante_id (comprobante_id)
        ) $charset_collate;";

        // cdc_sala table
        $sql_sala = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_sala (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            capacidad int(11) DEFAULT NULL,
            descripcion text DEFAULT NULL,
            estado enum('disponible','no_disponible','mantenimiento') NOT NULL DEFAULT 'disponible',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY estado (estado)
        ) $charset_collate;";

        // cdc_alquiler_sala table
        $sql_alquiler_sala = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_alquiler_sala (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            sala_id bigint(20) NOT NULL,
            solicitante varchar(200) NOT NULL,
            contacto_tel varchar(50) DEFAULT NULL,
            contacto_email varchar(100) DEFAULT NULL,
            fecha date NOT NULL,
            horario_inicio time NOT NULL,
            horario_fin time NOT NULL,
            motivo text NOT NULL,
            precio_acordado decimal(10,2) NOT NULL,
            sena_monto decimal(10,2) DEFAULT 0,
            saldo decimal(10,2) DEFAULT 0,
            estado enum('pendiente','reservado','finalizado','cancelado') NOT NULL DEFAULT 'pendiente',
            comprobante_sena_id varchar(100) DEFAULT NULL,
            comprobante_saldo_id varchar(100) DEFAULT NULL,
            observaciones text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY sala_id (sala_id),
            KEY fecha (fecha),
            KEY estado (estado),
            FOREIGN KEY (sala_id) REFERENCES {$wpdb->prefix}cdc_sala(id) ON DELETE RESTRICT
        ) $charset_collate;";

        // cdc_tallerista table
        $sql_tallerista = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_tallerista (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            apellido varchar(100) NOT NULL,
            dni varchar(20) DEFAULT NULL,
            tel varchar(50) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            especialidad varchar(100) DEFAULT NULL,
            honorarios_mensuales decimal(10,2) DEFAULT NULL,
            estado enum('activo','inactivo') NOT NULL DEFAULT 'activo',
            observaciones text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY estado (estado)
        ) $charset_collate;";

        // cdc_taller table
        $sql_taller = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_taller (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nombre varchar(200) NOT NULL,
            sala_id bigint(20) DEFAULT NULL,
            tallerista_id bigint(20) DEFAULT NULL,
            dias_horarios varchar(200) DEFAULT NULL COMMENT 'Ej: Lun y Jue 18:30-20:30',
            precio decimal(10,2) NOT NULL,
            cupo_maximo int(11) DEFAULT NULL,
            estado enum('activo','completo','inactivo','finalizado') NOT NULL DEFAULT 'activo',
            descripcion text DEFAULT NULL,
            fecha_inicio date DEFAULT NULL,
            fecha_fin date DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY sala_id (sala_id),
            KEY tallerista_id (tallerista_id),
            KEY estado (estado),
            FOREIGN KEY (sala_id) REFERENCES {$wpdb->prefix}cdc_sala(id) ON DELETE SET NULL,
            FOREIGN KEY (tallerista_id) REFERENCES {$wpdb->prefix}cdc_tallerista(id) ON DELETE SET NULL
        ) $charset_collate;";

        // cdc_inscripcion_taller table
        $sql_inscripcion_taller = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_inscripcion_taller (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            taller_id bigint(20) NOT NULL,
            persona_id bigint(20) NOT NULL,
            fecha_inscripcion date NOT NULL,
            estado enum('activo','inactivo','egresado') NOT NULL DEFAULT 'activo',
            observaciones text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY taller_persona (taller_id, persona_id),
            KEY estado (estado),
            FOREIGN KEY (taller_id) REFERENCES {$wpdb->prefix}cdc_taller(id) ON DELETE CASCADE,
            FOREIGN KEY (persona_id) REFERENCES {$wpdb->prefix}cdc_persona(id) ON DELETE CASCADE
        ) $charset_collate;";

        // cdc_cuota_taller table
        $sql_cuota_taller = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_cuota_taller (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            inscripcion_id bigint(20) NOT NULL,
            anio int(4) NOT NULL,
            mes int(2) NOT NULL,
            monto decimal(10,2) NOT NULL,
            pagada tinyint(1) NOT NULL DEFAULT 0,
            fecha_pago datetime DEFAULT NULL,
            medio_pago varchar(50) DEFAULT NULL,
            comprobante_id varchar(100) DEFAULT NULL,
            observaciones text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY inscripcion_periodo (inscripcion_id, anio, mes),
            KEY pagada (pagada),
            FOREIGN KEY (inscripcion_id) REFERENCES {$wpdb->prefix}cdc_inscripcion_taller(id) ON DELETE CASCADE
        ) $charset_collate;";

        // cdc_asistencia_taller table
        $sql_asistencia_taller = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_asistencia_taller (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            inscripcion_id bigint(20) NOT NULL,
            fecha date NOT NULL,
            presente tinyint(1) NOT NULL DEFAULT 0,
            observaciones text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY inscripcion_fecha (inscripcion_id, fecha),
            KEY fecha (fecha),
            FOREIGN KEY (inscripcion_id) REFERENCES {$wpdb->prefix}cdc_inscripcion_taller(id) ON DELETE CASCADE
        ) $charset_collate;";

        // cdc_evento table
        $sql_evento = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_evento (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nombre varchar(200) NOT NULL,
            fecha date NOT NULL,
            hora time DEFAULT NULL,
            sala_id bigint(20) DEFAULT NULL,
            precio_entrada decimal(10,2) DEFAULT 0,
            capacidad int(11) DEFAULT NULL,
            descripcion text DEFAULT NULL,
            estado enum('publicado','cancelado','finalizado') NOT NULL DEFAULT 'publicado',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fecha (fecha),
            KEY estado (estado),
            FOREIGN KEY (sala_id) REFERENCES {$wpdb->prefix}cdc_sala(id) ON DELETE SET NULL
        ) $charset_collate;";

        // cdc_entrada_evento table
        $sql_entrada_evento = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_entrada_evento (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            evento_id bigint(20) NOT NULL,
            persona_id bigint(20) DEFAULT NULL,
            cantidad int(11) NOT NULL DEFAULT 1,
            monto_total decimal(10,2) NOT NULL,
            comprobante_id varchar(100) DEFAULT NULL,
            fecha_compra datetime NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY evento_id (evento_id),
            KEY persona_id (persona_id),
            FOREIGN KEY (evento_id) REFERENCES {$wpdb->prefix}cdc_evento(id) ON DELETE CASCADE,
            FOREIGN KEY (persona_id) REFERENCES {$wpdb->prefix}cdc_persona(id) ON DELETE SET NULL
        ) $charset_collate;";

        // cdc_mp_events table (for idempotency)
        $sql_mp_events = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cdc_mp_events (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            mp_payment_id varchar(100) NOT NULL,
            event_type varchar(50) NOT NULL,
            event_data longtext DEFAULT NULL,
            processed tinyint(1) NOT NULL DEFAULT 0,
            processed_at datetime DEFAULT NULL,
            wc_order_id bigint(20) DEFAULT NULL,
            movimiento_caja_id bigint(20) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY mp_payment_id (mp_payment_id),
            KEY processed (processed),
            KEY wc_order_id (wc_order_id)
        ) $charset_collate;";

        // Execute all table creations
        dbDelta($sql_persona);
        dbDelta($sql_cuota_socio);
        dbDelta($sql_movimiento_caja);
        dbDelta($sql_sala);
        dbDelta($sql_alquiler_sala);
        dbDelta($sql_tallerista);
        dbDelta($sql_taller);
        dbDelta($sql_inscripcion_taller);
        dbDelta($sql_cuota_taller);
        dbDelta($sql_asistencia_taller);
        dbDelta($sql_evento);
        dbDelta($sql_entrada_evento);
        dbDelta($sql_mp_events);

        // Update plugin version
        update_option('cdc_db_version', CDC_VERSION);
    }

    /**
     * Drop all tables (use with caution!)
     */
    public static function drop_tables() {
        global $wpdb;

        $tables = array(
            'cdc_mp_events',
            'cdc_entrada_evento',
            'cdc_evento',
            'cdc_asistencia_taller',
            'cdc_cuota_taller',
            'cdc_inscripcion_taller',
            'cdc_taller',
            'cdc_tallerista',
            'cdc_alquiler_sala',
            'cdc_sala',
            'cdc_movimiento_caja',
            'cdc_cuota_socio',
            'cdc_persona',
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }

        delete_option('cdc_db_version');
    }
}
