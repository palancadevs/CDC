<?php
/**
 * Inscripcion Taller Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inscripcion Taller Model Class
 */
class CDC_Inscripcion_Taller extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_inscripciones_taller';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'taller_id',
        'persona_id',
        'fecha_inscripcion',
        'fecha_baja',
        'estado',
        'monto_mensual',
        'notas',
    );

    /**
     * Get inscripciones by persona
     *
     * @param int $persona_id Persona ID
     * @param string $estado Estado filter (optional)
     * @return array
     */
    public function get_by_persona($persona_id, $estado = null) {
        global $wpdb;

        $query = "SELECT i.*, t.nombre as taller_nombre, t.profesor, t.horario, t.dia_semana
                  FROM {$this->table_name} i
                  LEFT JOIN {$wpdb->prefix}cdc_talleres t ON i.taller_id = t.id
                  WHERE i.persona_id = %d";

        $params = array($persona_id);

        if ($estado) {
            $query .= " AND i.estado = %s";
            $params[] = $estado;
        }

        $query .= " ORDER BY i.fecha_inscripcion DESC";

        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    /**
     * Get inscripciones by taller
     *
     * @param int $taller_id Taller ID
     * @param string $estado Estado filter (optional)
     * @return array
     */
    public function get_by_taller($taller_id, $estado = null) {
        global $wpdb;

        $query = "SELECT i.*,
                  p.nombre as persona_nombre,
                  p.apellido as persona_apellido,
                  p.dni as persona_dni
                  FROM {$this->table_name} i
                  LEFT JOIN {$wpdb->prefix}cdc_personas p ON i.persona_id = p.id
                  WHERE i.taller_id = %d";

        $params = array($taller_id);

        if ($estado) {
            $query .= " AND i.estado = %s";
            $params[] = $estado;
        }

        $query .= " ORDER BY p.apellido, p.nombre";

        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    /**
     * Get active inscripcion for persona + taller
     *
     * @param int $persona_id Persona ID
     * @param int $taller_id Taller ID
     * @return object|null
     */
    public function get_active_inscripcion($persona_id, $taller_id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name}
             WHERE persona_id = %d AND taller_id = %d AND estado = 'activo'
             LIMIT 1",
            $persona_id,
            $taller_id
        );

        return $wpdb->get_row($query);
    }

    /**
     * Check if persona is inscribed to taller
     *
     * @param int $persona_id Persona ID
     * @param int $taller_id Taller ID
     * @return bool
     */
    public function is_inscribed($persona_id, $taller_id) {
        $inscripcion = $this->get_active_inscripcion($persona_id, $taller_id);
        return !empty($inscripcion);
    }
}
