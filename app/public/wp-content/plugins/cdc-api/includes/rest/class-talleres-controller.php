<?php
/**
 * Talleres REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Talleres REST Controller Class
 */
class CDC_Talleres_Controller extends CDC_Base_Controller {
    /**
     * Rest base
     */
    protected $rest_base = 'talleres';

    /**
     * Service instance
     */
    private $service;

    /**
     * Inscripcion service instance
     */
    private $inscripcion_service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new CDC_Taller_Service();
        $this->inscripcion_service = new CDC_Inscripcion_Service();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /talleres - Get all talleres
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /talleres - Create taller
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /talleres/{id} - Get single taller
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // PUT /talleres/{id} - Update taller
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /talleres/{id}/inscribir - Inscribe person to taller
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/inscribir', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'inscribir_persona'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /talleres/{id}/inscripciones - Get inscripciones for taller
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/inscripciones', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_inscripciones'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));
    }

    /**
     * Get items
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_items($request) {
        $talleres = $this->service->get_all_talleres();

        return $this->prepare_response(array(
            'success' => true,
            'data' => $talleres,
        ));
    }

    /**
     * Get single item
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function get_item($request) {
        $id = $request->get_param('id');
        $taller = $this->service->get_taller($id);

        if (!$taller) {
            return $this->prepare_error('Taller no encontrado', 404);
        }

        return $this->prepare_response(array(
            'success' => true,
            'data' => $taller,
        ));
    }

    /**
     * Create item
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function create_item($request) {
        $data = $request->get_json_params();
        $result = $this->service->create_taller($data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result, 201);
    }

    /**
     * Update item
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function update_item($request) {
        $id = $request->get_param('id');
        $data = $request->get_json_params();

        $result = $this->service->update_taller($id, $data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result);
    }

    /**
     * Inscribe person to taller
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function inscribir_persona($request) {
        $taller_id = $request->get_param('id');
        $data = $request->get_json_params();

        if (empty($data['persona_id'])) {
            return $this->prepare_error('persona_id es requerido', 400);
        }

        $persona_id = (int) $data['persona_id'];

        $result = $this->inscripcion_service->inscribir_persona($taller_id, $persona_id, $data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result, 201);
    }

    /**
     * Get inscripciones for taller
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_inscripciones($request) {
        $taller_id = $request->get_param('id');
        $estado = $request->get_param('estado');

        $inscripciones = $this->inscripcion_service->get_inscripciones_taller($taller_id, $estado);

        return $this->prepare_response(array(
            'success' => true,
            'data' => $inscripciones,
        ));
    }
}
