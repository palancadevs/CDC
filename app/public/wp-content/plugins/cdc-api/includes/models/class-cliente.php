<?php
/**
 * Cliente Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cliente Model Class
 */
class CDC_Cliente extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_clientes';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'persona_id',
        'primera_visita',
        'ultima_visita',
        'total_gastado',
        'notas',
    );

    /**
     * Find cliente by persona ID
     *
     * @param int $persona_id Persona ID
     * @return object|null
     */
    public function find_by_persona_id($persona_id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE persona_id = %d",
            $persona_id
        );

        return $wpdb->get_row($query);
    }

    /**
     * Get full cliente data with persona info
     *
     * @param int $id Cliente ID
     * @return object|null
     */
    public function get_full_data($id) {
        global $wpdb;

        $cliente = $this->find($id);

        if (!$cliente) {
            return null;
        }

        // Get persona data
        $persona_table = $wpdb->prefix . 'cdc_personas';
        $persona = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $persona_table WHERE id = %d",
            $cliente->persona_id
        ));

        $cliente->persona = $persona;

        return $cliente;
    }

    /**
     * Update last visit
     *
     * @param int $id Cliente ID
     * @return bool
     */
    public function update_last_visit($id) {
        return $this->update($id, array(
            'ultima_visita' => current_time('mysql', false),
        ));
    }

    /**
     * Update total spent
     *
     * @param int $id Cliente ID
     * @param float $amount Amount to add
     * @return bool
     */
    public function add_to_total_spent($id, $amount) {
        global $wpdb;

        $query = $wpdb->prepare(
            "UPDATE {$this->table_name} SET total_gastado = total_gastado + %f WHERE id = %d",
            $amount,
            $id
        );

        return $wpdb->query($query) !== false;
    }
}
