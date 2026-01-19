<?php
/**
 * Personas REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Personas REST Controller Class
 */
class CDC_Personas_Controller extends CDC_Base_Controller {
    /**
     * Rest base
     */
    protected $rest_base = 'personas';

    /**
     * Service instance
     */
    private $service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new CDC_Persona_Service();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /personas - Get all personas
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /personas - Create persona
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /personas/{id} - Get single persona
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // PUT /personas/{id} - Update persona
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /personas/search - Search personas
        register_rest_route($this->namespace, '/' . $this->rest_base . '/search', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'search_items'),
                'permission_callback' => array($this, 'check_auth'),
                'args' => array(
                    'query' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
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
        $persona_model = new CDC_Persona();

        $args = array(
            'limit' => $request->get_param('per_page') ?: 50,
            'offset' => $request->get_param('offset') ?: 0,
        );

        $personas = $persona_model->get_all($args);

        return $this->prepare_response(array(
            'success' => true,
            'data' => $personas,
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
        $persona = $this->service->get_persona_full($id);

        if (!$persona) {
            return $this->prepare_error('Persona no encontrada', 404);
        }

        return $this->prepare_response(array(
            'success' => true,
            'data' => $persona,
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
        $result = $this->service->create_persona($data);

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

        $result = $this->service->update_persona($id, $data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result);
    }

    /**
     * Search items
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function search_items($request) {
        $query = $request->get_param('query');
        $personas = $this->service->search_personas($query);

        return $this->prepare_response(array(
            'success' => true,
            'data' => $personas,
        ));
    }
}
