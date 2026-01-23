<?php
/**
 * ARCA REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ARCA REST Controller Class
 */
class CDC_ARCA_Controller extends CDC_Base_Controller {
    /**
     * Rest base
     */
    protected $rest_base = 'arca';

    /**
     * ARCA service instance
     */
    private $arca_service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->arca_service = new CDC_ARCA_Service();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // POST /arca/retry/{order_id} - Retry invoice generation for failed order
        register_rest_route($this->namespace, '/' . $this->rest_base . '/retry/(?P<order_id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'retry_invoice'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /arca/failed-orders - Get orders with failed invoicing
        register_rest_route($this->namespace, '/' . $this->rest_base . '/failed-orders', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_failed_orders'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /arca/status/{order_id} - Get invoice status for order
        register_rest_route($this->namespace, '/' . $this->rest_base . '/status/(?P<order_id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_invoice_status'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));
    }

    /**
     * Retry invoice generation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function retry_invoice($request) {
        $order_id = $request->get_param('order_id');

        $result = $this->arca_service->retry_invoice($order_id);

        if ($result['success']) {
            return $this->prepare_response(array(
                'success' => true,
                'message' => $result['message'],
            ));
        } else {
            return $this->prepare_error($result['message'], 400);
        }
    }

    /**
     * Get orders with failed invoicing
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_failed_orders($request) {
        $args = array(
            'limit' => $request->get_param('limit') ?: 50,
            'meta_query' => array(
                array(
                    'key' => '_cdc_factura_status',
                    'value' => 'error',
                    'compare' => '=',
                ),
            ),
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $orders = wc_get_orders($args);

        $failed_orders = array();
        foreach ($orders as $order) {
            $failed_orders[] = array(
                'order_id' => $order->get_id(),
                'date' => $order->get_date_created()->format('Y-m-d H:i:s'),
                'total' => $order->get_total(),
                'movimiento_id' => $order->get_meta('_cdc_movimiento_id'),
                'persona_id' => $order->get_meta('_cdc_persona_id'),
                'tipo_cobro' => $order->get_meta('_cdc_tipo_cobro'),
                'error' => $order->get_meta('_cdc_factura_error'),
                'error_date' => $order->get_meta('_cdc_factura_fecha'),
            );
        }

        return $this->prepare_response(array(
            'success' => true,
            'data' => $failed_orders,
            'total' => count($failed_orders),
        ));
    }

    /**
     * Get invoice status for order
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_invoice_status($request) {
        $order_id = $request->get_param('order_id');
        $order = wc_get_order($order_id);

        if (!$order) {
            return $this->prepare_error('Orden no encontrada', 404);
        }

        $status = array(
            'order_id' => $order->get_id(),
            'order_status' => $order->get_status(),
            'factura_status' => $order->get_meta('_cdc_factura_status'),
            'comprobante_id' => $order->get_meta('_cdc_comprobante_id'),
            'error' => $order->get_meta('_cdc_factura_error'),
            'fecha' => $order->get_meta('_cdc_factura_fecha'),
            'movimiento_id' => $order->get_meta('_cdc_movimiento_id'),
            'tipo_cobro' => $order->get_meta('_cdc_tipo_cobro'),
        );

        return $this->prepare_response(array(
            'success' => true,
            'data' => $status,
        ));
    }
}
