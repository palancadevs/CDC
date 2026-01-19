<?php
/**
 * Gasto Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gasto Model Class
 */
class CDC_Gasto extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_gastos';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'concepto',
        'categoria',
        'monto',
        'fecha_gasto',
        'comprobante_tipo',
        'comprobante_numero',
        'proveedor',
        'usuario_id',
        'estado',
        'notas',
    );

    /**
     * Get gastos by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_date_range($fecha_inicio, $fecha_fin, $args = array()) {
        global $wpdb;

        $args['where'] = $wpdb->prepare(
            'fecha_gasto >= %s AND fecha_gasto <= %s',
            $fecha_inicio,
            $fecha_fin
        );

        return $this->get_all($args);
    }

    /**
     * Get gastos by category
     *
     * @param string $categoria Category
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_categoria($categoria, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('categoria = %s', $categoria);
        return $this->get_all($args);
    }

    /**
     * Get gastos by status
     *
     * @param string $estado Status
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_estado($estado, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('estado = %s', $estado);
        return $this->get_all($args);
    }

    /**
     * Get total by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @param string $categoria Optional category filter
     * @return float
     */
    public function get_total_by_date_range($fecha_inicio, $fecha_fin, $categoria = null) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT SUM(monto) FROM {$this->table_name}
            WHERE fecha_gasto >= %s
            AND fecha_gasto <= %s",
            $fecha_inicio,
            $fecha_fin
        );

        if ($categoria) {
            $query .= $wpdb->prepare(" AND categoria = %s", $categoria);
        }

        return (float) $wpdb->get_var($query);
    }
}
