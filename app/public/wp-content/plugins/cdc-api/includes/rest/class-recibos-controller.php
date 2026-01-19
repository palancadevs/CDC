<?php
/**
 * Recibos REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recibos REST Controller Class
 */
class CDC_Recibos_Controller extends CDC_Base_Controller {
    /**
     * Rest base
     */
    protected $rest_base = 'recibos';

    /**
     * Service instance
     */
    private $service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new CDC_Recibo_Service();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // POST /recibos - Create recibo
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /recibos/{id} - Get single recibo
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /recibos/recent - Get recent recibos
        register_rest_route($this->namespace, '/' . $this->rest_base . '/recent', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_recent'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /recibos/today - Get today's recibos
        register_rest_route($this->namespace, '/' . $this->rest_base . '/today', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_today'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /recibos/{id}/anular - Anular recibo
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/anular', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'anular_recibo'),
                'permission_callback' => array($this, 'check_auth'),
            ),
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
        $result = $this->service->create_recibo($data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result, 201);
    }

    /**
     * Get single item
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function get_item($request) {
        $id = $request->get_param('id');
        $recibo = $this->service->get_recibo_full($id);

        if (!$recibo) {
            return $this->prepare_error('Recibo no encontrado', 404);
        }

        return $this->prepare_response(array(
            'success' => true,
            'data' => $recibo,
        ));
    }

    /**
     * Get recent recibos
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_recent($request) {
        $limit = $request->get_param('limit') ?: 10;
        $recibos = $this->service->get_recent_receipts($limit);

        return $this->prepare_response(array(
            'success' => true,
            'data' => $recibos,
        ));
    }

    /**
     * Get today's recibos
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_today($request) {
        $recibos = $this->service->get_today_receipts();

        return $this->prepare_response(array(
            'success' => true,
            'data' => $recibos,
        ));
    }

    /**
     * Anular recibo
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function anular_recibo($request) {
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        $motivo = isset($data['motivo']) ? $data['motivo'] : '';

        $result = $this->service->anular_recibo($id, $motivo);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result);
    }
}
