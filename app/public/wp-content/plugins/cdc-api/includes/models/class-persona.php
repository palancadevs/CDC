<?php
/**
 * Persona Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Persona Model Class
 */
class CDC_Persona extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_personas';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'nombre',
        'apellido',
        'dni',
        'email',
        'telefono',
        'direccion',
        'fecha_nacimiento',
        'tipo',
        'notas',
    );

    /**
     * Search personas by name, lastname or DNI
     *
     * @param string $query Search query
     * @param array $args Additional arguments
     * @return array
     */
    public function search_personas($query, $args = array()) {
        return $this->search(
            $query,
            array('nombre', 'apellido', 'dni', 'email'),
            $args
        );
    }

    /**
     * Find persona by DNI
     *
     * @param string $dni DNI
     * @return object|null
     */
    public function find_by_dni($dni) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE dni = %s",
            $dni
        );

        return $wpdb->get_row($query);
    }

    /**
     * Find persona by email
     *
     * @param string $email Email
     * @return object|null
     */
    public function find_by_email($email) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE email = %s",
            $email
        );

        return $wpdb->get_row($query);
    }

    /**
     * Get personas by type
     *
     * @param string $type Type (socio, cliente, ambos)
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_type($type, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('tipo = %s', $type);
        return $this->get_all($args);
    }

    /**
     * Get socios (members)
     *
     * @param array $args Additional arguments
     * @return array
     */
    public function get_socios($args = array()) {
        $args['where'] = "tipo IN ('socio', 'ambos')";
        return $this->get_all($args);
    }

    /**
     * Get clientes (clients)
     *
     * @param array $args Additional arguments
     * @return array
     */
    public function get_clientes($args = array()) {
        $args['where'] = "tipo IN ('cliente', 'ambos')";
        return $this->get_all($args);
    }

    /**
     * Get full persona data with socio/cliente info
     *
     * @param int $id Persona ID
     * @return object|null
     */
    public function get_full_data($id) {
        global $wpdb;

        $persona = $this->find($id);

        if (!$persona) {
            return null;
        }

        // Get socio data if applicable
        if (in_array($persona->tipo, array('socio', 'ambos'))) {
            $socio_table = $wpdb->prefix . 'cdc_socios';
            $socio = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $socio_table WHERE persona_id = %d",
                $id
            ));
            $persona->socio = $socio;
        }

        // Get cliente data if applicable
        if (in_array($persona->tipo, array('cliente', 'ambos'))) {
            $cliente_table = $wpdb->prefix . 'cdc_clientes';
            $cliente = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $cliente_table WHERE persona_id = %d",
                $id
            ));
            $persona->cliente = $cliente;
        }

        return $persona;
    }

    /**
     * Check if DNI exists
     *
     * @param string $dni DNI
     * @param int $exclude_id Exclude this ID from check
     * @return bool
     */
    public function dni_exists($dni, $exclude_id = null) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE dni = %s",
            $dni
        );

        if ($exclude_id) {
            $query .= $wpdb->prepare(" AND id != %d", $exclude_id);
        }

        return (int) $wpdb->get_var($query) > 0;
    }
}
