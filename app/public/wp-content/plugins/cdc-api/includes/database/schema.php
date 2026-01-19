<?php
/**
 * Database Schema
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDC Database Schema Class
 */
class CDC_Database_Schema {
    /**
     * Create all database tables
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // 1. Personas - Base table for all persons
        $table_personas = $wpdb->prefix . 'cdc_personas';
        $sql_personas = "CREATE TABLE $table_personas (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            apellido varchar(100) NOT NULL,
            dni varchar(20) NOT NULL,
            email varchar(100) DEFAULT NULL,
            telefono varchar(50) DEFAULT NULL,
            direccion text DEFAULT NULL,
            fecha_nacimiento date DEFAULT NULL,
            tipo enum('socio','cliente','ambos') NOT NULL DEFAULT 'cliente',
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY dni (dni),
            KEY email (email),
            KEY tipo (tipo)
        ) $charset_collate;";
        dbDelta($sql_personas);

        // 2. Socios - Members (extends personas)
        $table_socios = $wpdb->prefix . 'cdc_socios';
        $sql_socios = "CREATE TABLE $table_socios (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            persona_id bigint(20) unsigned NOT NULL,
            numero_socio varchar(50) NOT NULL,
            fecha_alta date NOT NULL,
            fecha_baja date DEFAULT NULL,
            estado enum('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
            monto_cuota decimal(10,2) NOT NULL DEFAULT 0.00,
            dia_cobro tinyint(2) DEFAULT NULL,
            notas_pago text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY numero_socio (numero_socio),
            UNIQUE KEY persona_id (persona_id),
            KEY estado (estado),
            KEY fecha_alta (fecha_alta)
        ) $charset_collate;";
        dbDelta($sql_socios);

        // 3. Clientes - Clients (extends personas)
        $table_clientes = $wpdb->prefix . 'cdc_clientes';
        $sql_clientes = "CREATE TABLE $table_clientes (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            persona_id bigint(20) unsigned NOT NULL,
            primera_visita date NOT NULL,
            ultima_visita date DEFAULT NULL,
            total_gastado decimal(10,2) NOT NULL DEFAULT 0.00,
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY persona_id (persona_id),
            KEY primera_visita (primera_visita),
            KEY ultima_visita (ultima_visita)
        ) $charset_collate;";
        dbDelta($sql_clientes);

        // 4. Recibos - Receipts
        $table_recibos = $wpdb->prefix . 'cdc_recibos';
        $sql_recibos = "CREATE TABLE $table_recibos (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            numero_recibo varchar(50) NOT NULL,
            persona_id bigint(20) unsigned NOT NULL,
            tipo enum('cuota','taller','evento','sala','otro') NOT NULL,
            concepto varchar(255) NOT NULL,
            monto_total decimal(10,2) NOT NULL DEFAULT 0.00,
            fecha_emision datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            metodo_pago enum('efectivo','transferencia','tarjeta') NOT NULL DEFAULT 'efectivo',
            estado enum('pagado','pendiente','anulado') NOT NULL DEFAULT 'pagado',
            usuario_id bigint(20) unsigned NOT NULL,
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY numero_recibo (numero_recibo),
            KEY persona_id (persona_id),
            KEY tipo (tipo),
            KEY fecha_emision (fecha_emision),
            KEY estado (estado),
            KEY usuario_id (usuario_id)
        ) $charset_collate;";
        dbDelta($sql_recibos);

        // 5. Items de Recibo - Receipt line items
        $table_items_recibo = $wpdb->prefix . 'cdc_items_recibo';
        $sql_items_recibo = "CREATE TABLE $table_items_recibo (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            recibo_id bigint(20) unsigned NOT NULL,
            descripcion varchar(255) NOT NULL,
            cantidad int(11) NOT NULL DEFAULT 1,
            precio_unitario decimal(10,2) NOT NULL DEFAULT 0.00,
            subtotal decimal(10,2) NOT NULL DEFAULT 0.00,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY recibo_id (recibo_id)
        ) $charset_collate;";
        dbDelta($sql_items_recibo);

        // 6. Movimientos de Caja - Cash register movements
        $table_movimientos = $wpdb->prefix . 'cdc_movimientos_caja';
        $sql_movimientos = "CREATE TABLE $table_movimientos (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            tipo enum('ingreso','egreso','apertura','cierre') NOT NULL,
            concepto varchar(255) NOT NULL,
            monto decimal(10,2) NOT NULL DEFAULT 0.00,
            saldo_anterior decimal(10,2) NOT NULL DEFAULT 0.00,
            saldo_nuevo decimal(10,2) NOT NULL DEFAULT 0.00,
            recibo_id bigint(20) unsigned DEFAULT NULL,
            gasto_id bigint(20) unsigned DEFAULT NULL,
            usuario_id bigint(20) unsigned NOT NULL,
            fecha_movimiento datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY tipo (tipo),
            KEY fecha_movimiento (fecha_movimiento),
            KEY usuario_id (usuario_id),
            KEY recibo_id (recibo_id),
            KEY gasto_id (gasto_id)
        ) $charset_collate;";
        dbDelta($sql_movimientos);

        // 7. Gastos - Expenses
        $table_gastos = $wpdb->prefix . 'cdc_gastos';
        $sql_gastos = "CREATE TABLE $table_gastos (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            concepto varchar(255) NOT NULL,
            categoria enum('servicios','mantenimiento','compras','sueldos','impuestos','otros') NOT NULL DEFAULT 'otros',
            monto decimal(10,2) NOT NULL DEFAULT 0.00,
            fecha_gasto date NOT NULL,
            comprobante_tipo enum('factura','recibo','ticket','ninguno') DEFAULT NULL,
            comprobante_numero varchar(100) DEFAULT NULL,
            proveedor varchar(255) DEFAULT NULL,
            usuario_id bigint(20) unsigned NOT NULL,
            estado enum('registrado','aprobado','pagado','anulado') NOT NULL DEFAULT 'registrado',
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY categoria (categoria),
            KEY fecha_gasto (fecha_gasto),
            KEY usuario_id (usuario_id),
            KEY estado (estado)
        ) $charset_collate;";
        dbDelta($sql_gastos);

        // 8. Talleres - Workshops
        $table_talleres = $wpdb->prefix . 'cdc_talleres';
        $sql_talleres = "CREATE TABLE $table_talleres (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            nombre varchar(255) NOT NULL,
            descripcion text DEFAULT NULL,
            profesor varchar(255) DEFAULT NULL,
            horario varchar(255) DEFAULT NULL,
            dia_semana varchar(100) DEFAULT NULL,
            cupo_maximo int(11) DEFAULT NULL,
            inscriptos int(11) NOT NULL DEFAULT 0,
            precio_mensual decimal(10,2) NOT NULL DEFAULT 0.00,
            precio_inscripcion decimal(10,2) DEFAULT 0.00,
            fecha_inicio date DEFAULT NULL,
            fecha_fin date DEFAULT NULL,
            estado enum('activo','inactivo','finalizado') NOT NULL DEFAULT 'activo',
            sala_id bigint(20) unsigned DEFAULT NULL,
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY estado (estado),
            KEY sala_id (sala_id)
        ) $charset_collate;";
        dbDelta($sql_talleres);

        // 9. Eventos - Events
        $table_eventos = $wpdb->prefix . 'cdc_eventos';
        $sql_eventos = "CREATE TABLE $table_eventos (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            nombre varchar(255) NOT NULL,
            descripcion text DEFAULT NULL,
            tipo enum('show','concierto','conferencia','proyeccion','otro') NOT NULL DEFAULT 'otro',
            fecha_evento datetime NOT NULL,
            duracion_minutos int(11) DEFAULT NULL,
            cupo_maximo int(11) DEFAULT NULL,
            inscriptos int(11) NOT NULL DEFAULT 0,
            precio_entrada decimal(10,2) NOT NULL DEFAULT 0.00,
            precio_socio decimal(10,2) DEFAULT NULL,
            sala_id bigint(20) unsigned DEFAULT NULL,
            estado enum('programado','en_curso','finalizado','cancelado') NOT NULL DEFAULT 'programado',
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fecha_evento (fecha_evento),
            KEY tipo (tipo),
            KEY estado (estado),
            KEY sala_id (sala_id)
        ) $charset_collate;";
        dbDelta($sql_eventos);

        // 10. Salas - Rooms
        $table_salas = $wpdb->prefix . 'cdc_salas';
        $sql_salas = "CREATE TABLE $table_salas (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            nombre varchar(255) NOT NULL,
            descripcion text DEFAULT NULL,
            capacidad int(11) DEFAULT NULL,
            precio_hora decimal(10,2) DEFAULT 0.00,
            equipamiento text DEFAULT NULL,
            estado enum('disponible','en_uso','mantenimiento') NOT NULL DEFAULT 'disponible',
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY estado (estado)
        ) $charset_collate;";
        dbDelta($sql_salas);

        // 11. Reservas de Salas - Room reservations
        $table_reservas = $wpdb->prefix . 'cdc_reservas_salas';
        $sql_reservas = "CREATE TABLE $table_reservas (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            sala_id bigint(20) unsigned NOT NULL,
            persona_id bigint(20) unsigned NOT NULL,
            fecha_inicio datetime NOT NULL,
            fecha_fin datetime NOT NULL,
            duracion_horas decimal(4,2) NOT NULL,
            monto_total decimal(10,2) NOT NULL DEFAULT 0.00,
            estado enum('pendiente','confirmada','en_curso','finalizada','cancelada') NOT NULL DEFAULT 'pendiente',
            motivo varchar(255) DEFAULT NULL,
            recibo_id bigint(20) unsigned DEFAULT NULL,
            notas text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY sala_id (sala_id),
            KEY persona_id (persona_id),
            KEY fecha_inicio (fecha_inicio),
            KEY fecha_fin (fecha_fin),
            KEY estado (estado),
            KEY recibo_id (recibo_id)
        ) $charset_collate;";
        dbDelta($sql_reservas);

        // 12. Pagos Mensuales - Monthly payments (for members)
        $table_pagos_mensuales = $wpdb->prefix . 'cdc_pagos_mensuales';
        $sql_pagos_mensuales = "CREATE TABLE $table_pagos_mensuales (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            socio_id bigint(20) unsigned NOT NULL,
            recibo_id bigint(20) unsigned NOT NULL,
            mes int(2) NOT NULL,
            anio int(4) NOT NULL,
            monto_pagado decimal(10,2) NOT NULL DEFAULT 0.00,
            fecha_pago datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY socio_id (socio_id),
            KEY recibo_id (recibo_id),
            KEY mes (mes),
            KEY anio (anio),
            UNIQUE KEY socio_mes_anio (socio_id, mes, anio)
        ) $charset_collate;";
        dbDelta($sql_pagos_mensuales);

        // 13. Pagos de Eventos - Event payments
        $table_pagos_eventos = $wpdb->prefix . 'cdc_pagos_eventos';
        $sql_pagos_eventos = "CREATE TABLE $table_pagos_eventos (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            evento_id bigint(20) unsigned NOT NULL,
            persona_id bigint(20) unsigned NOT NULL,
            recibo_id bigint(20) unsigned NOT NULL,
            monto_pagado decimal(10,2) NOT NULL DEFAULT 0.00,
            fecha_pago datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            es_socio tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY evento_id (evento_id),
            KEY persona_id (persona_id),
            KEY recibo_id (recibo_id)
        ) $charset_collate;";
        dbDelta($sql_pagos_eventos);
    }

    /**
     * Drop all database tables
     */
    public static function drop_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'cdc_pagos_eventos',
            $wpdb->prefix . 'cdc_pagos_mensuales',
            $wpdb->prefix . 'cdc_reservas_salas',
            $wpdb->prefix . 'cdc_salas',
            $wpdb->prefix . 'cdc_eventos',
            $wpdb->prefix . 'cdc_talleres',
            $wpdb->prefix . 'cdc_gastos',
            $wpdb->prefix . 'cdc_movimientos_caja',
            $wpdb->prefix . 'cdc_items_recibo',
            $wpdb->prefix . 'cdc_recibos',
            $wpdb->prefix . 'cdc_clientes',
            $wpdb->prefix . 'cdc_socios',
            $wpdb->prefix . 'cdc_personas',
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}
