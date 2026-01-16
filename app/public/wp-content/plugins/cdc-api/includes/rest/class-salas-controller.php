<?php
/**
 * Salas REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Salas REST Controller Class
 */
class CDC_Salas_Controller extends CDC_Base_Controller {
    /**
     * Rest base
     */
    protected $rest_base = 'salas';

    /**
     * Service instance
     */
    private $service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new CDC_Sala_Service();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /salas - Get all salas
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /salas - Create sala
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /salas/{id} - Get single sala
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // PUT /salas/{id} - Update sala
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /salas/reservas - Create reserva
        register_rest_route($this->namespace, '/' . $this->rest_base . '/reservas', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_reserva'),
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
        $salas = $this->service->get_available_salas();

        return $this->prepare_response(array(
            'success' => true,
            'data' => $salas,
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
        $sala_model = new CDC_Sala();
        $sala = $sala_model->find($id);

        if (!$sala) {
            return $this->prepare_error('Sala no encontrada', 404);
        }

        return $this->prepare_response(array(
            'success' => true,
            'data' => $sala,
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
        $result = $this->service->create_sala($data);

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

        $result = $this->service->update_sala($id, $data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result);
    }

    /**
     * Create reserva
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function create_reserva($request) {
        $data = $request->get_json_params();
        $result = $this->service->create_reserva($data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result, 201);
    }
}
