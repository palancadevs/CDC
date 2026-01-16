<?php
/**
 * Taller Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Taller Model Class
 */
class CDC_Taller extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_talleres';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'nombre',
        'descripcion',
        'profesor',
        'horario',
        'dia_semana',
        'cupo_maximo',
        'inscriptos',
        'precio_mensual',
        'precio_inscripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'sala_id',
        'notas',
    );

    /**
     * Get active talleres
     *
     * @param array $args Additional arguments
     * @return array
     */
    public function get_active($args = array()) {
        $args['where'] = "estado = 'activo'";
        return $this->get_all($args);
    }

    /**
     * Get talleres by status
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
     * Check if taller has available spots
     *
     * @param int $id Taller ID
     * @return bool
     */
    public function has_available_spots($id) {
        $taller = $this->find($id);

        if (!$taller || !$taller->cupo_maximo) {
            return true;
        }

        return $taller->inscriptos < $taller->cupo_maximo;
    }

    /**
     * Increment inscriptos count
     *
     * @param int $id Taller ID
     * @return bool
     */
    public function increment_inscriptos($id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "UPDATE {$this->table_name} SET inscriptos = inscriptos + 1 WHERE id = %d",
            $id
        );

        return $wpdb->query($query) !== false;
    }

    /**
     * Decrement inscriptos count
     *
     * @param int $id Taller ID
     * @return bool
     */
    public function decrement_inscriptos($id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "UPDATE {$this->table_name} SET inscriptos = inscriptos - 1 WHERE id = %d AND inscriptos > 0",
            $id
        );

        return $wpdb->query($query) !== false;
    }
}
