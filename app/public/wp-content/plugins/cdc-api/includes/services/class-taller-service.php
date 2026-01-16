<?php
/**
 * Taller Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Taller Service Class
 */
class CDC_Taller_Service {
    /**
     * Taller model
     */
    private $taller_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->taller_model = new CDC_Taller();
    }

    /**
     * Create taller
     *
     * @param array $data Taller data
     * @return array Result
     */
    public function create_taller($data) {
        if (empty($data['nombre'])) {
            return array(
                'success' => false,
                'message' => 'El nombre es requerido',
            );
        }

        $taller_id = $this->taller_model->create($data);

        if (!$taller_id) {
            return array(
                'success' => false,
                'message' => 'Error al crear el taller',
            );
        }

        return array(
            'success' => true,
            'message' => 'Taller creado correctamente',
            'data' => array('id' => $taller_id),
        );
    }

    /**
     * Update taller
     *
     * @param int $id Taller ID
     * @param array $data Update data
     * @return array Result
     */
    public function update_taller($id, $data) {
        $taller = $this->taller_model->find($id);

        if (!$taller) {
            return array(
                'success' => false,
                'message' => 'Taller no encontrado',
            );
        }

        $result = $this->taller_model->update($id, $data);

        return array(
            'success' => $result,
            'message' => $result ? 'Taller actualizado correctamente' : 'Error al actualizar el taller',
        );
    }

    /**
     * Get active talleres
     *
     * @return array
     */
    public function get_active_talleres() {
        return $this->taller_model->get_active();
    }

    /**
     * Get all talleres
     *
     * @param array $args Arguments
     * @return array
     */
    public function get_all_talleres($args = array()) {
        return $this->taller_model->get_all($args);
    }

    /**
     * Get taller by ID
     *
     * @param int $id Taller ID
     * @return object|null
     */
    public function get_taller($id) {
        return $this->taller_model->find($id);
    }
}
