<?php
/**
 * Caja REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Caja REST Controller Class
 */
class CDC_Caja_Controller extends CDC_Base_Controller {
    /**
     * Rest base
     */
    protected $rest_base = 'caja';

    /**
     * Service instance
     */
    private $service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new CDC_Caja_Service();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /caja/balance - Get current balance
        register_rest_route($this->namespace, '/' . $this->rest_base . '/balance', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_balance'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /caja/movimientos/today - Get today's movements
        register_rest_route($this->namespace, '/' . $this->rest_base . '/movimientos/today', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_today_movements'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /caja/summary/today - Get today's summary
        register_rest_route($this->namespace, '/' . $this->rest_base . '/summary/today', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_today_summary'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /caja/apertura - Open cash register
        register_rest_route($this->namespace, '/' . $this->rest_base . '/apertura', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'apertura'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /caja/cierre - Close cash register
        register_rest_route($this->namespace, '/' . $this->rest_base . '/cierre', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'cierre'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));
    }

    /**
     * Get balance
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_balance($request) {
        $balance = $this->service->get_current_balance();

        return $this->prepare_response(array(
            'success' => true,
            'data' => array('balance' => $balance),
        ));
    }

    /**
     * Get today's movements
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_today_movements($request) {
        $movements = $this->service->get_today_movements();

        return $this->prepare_response(array(
            'success' => true,
            'data' => $movements,
        ));
    }

    /**
     * Get today's summary
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_today_summary($request) {
        $summary = $this->service->get_today_summary();

        return $this->prepare_response(array(
            'success' => true,
            'data' => $summary,
        ));
    }

    /**
     * Apertura caja
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function apertura($request) {
        $data = $request->get_json_params();
        $monto_inicial = isset($data['monto_inicial']) ? $data['monto_inicial'] : 0.00;
        $notas = isset($data['notas']) ? $data['notas'] : '';

        $result = $this->service->apertura_caja($monto_inicial, $notas);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result, 201);
    }

    /**
     * Cierre caja
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function cierre($request) {
        $data = $request->get_json_params();
        $notas = isset($data['notas']) ? $data['notas'] : '';

        $result = $this->service->cierre_caja($notas);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result, 201);
    }
}
