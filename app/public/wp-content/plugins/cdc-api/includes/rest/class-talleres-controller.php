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
     * Constructor
     */
    public function __construct() {
        $this->service = new CDC_Taller_Service();
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
}
