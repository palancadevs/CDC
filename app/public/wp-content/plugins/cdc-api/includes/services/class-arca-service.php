<?php
/**
 * ARCA Integration Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ARCA Service Class
 * Handles electronic invoicing integration with ARCA API
 */
class CDC_ARCA_Service {
    /**
     * ARCA API endpoint
     */
    private $api_endpoint;

    /**
     * ARCA API credentials
     */
    private $api_key;
    private $api_secret;

    /**
     * Sandbox mode flag
     */
    private $sandbox_mode;

    /**
     * Constructor
     */
    public function __construct() {
        // Get ARCA credentials from WordPress options
        $this->api_endpoint = get_option('cdc_arca_api_endpoint', '');
        $this->api_key = get_option('cdc_arca_api_key', '');
        $this->api_secret = get_option('cdc_arca_api_secret', '');

        // Enable sandbox mode if no credentials configured
        $this->sandbox_mode = empty($this->api_endpoint) || empty($this->api_key);

        if ($this->sandbox_mode) {
            error_log('CDC ARCA: Modo SANDBOX activado (sin credenciales configuradas)');
        }

        // Register WooCommerce hooks
        add_action('woocommerce_order_status_processing', array($this, 'process_order_invoice'), 10, 1);
        add_action('woocommerce_order_status_completed', array($this, 'process_order_invoice'), 10, 1);
    }

    /**
     * Process invoice for WooCommerce order
     *
     * @param int $order_id WooCommerce order ID
     */
    public function process_order_invoice($order_id) {
        // Get order
        $order = wc_get_order($order_id);

        if (!$order) {
            error_log('CDC ARCA: Order not found - ' . $order_id);
            return;
        }

        // Check if already invoiced
        $existing_comprobante_id = $order->get_meta('_cdc_comprobante_id');
        if ($existing_comprobante_id) {
            error_log('CDC ARCA: Order already invoiced - ' . $order_id);
            return;
        }

        // Check if CDC order (has movimiento_id)
        $movimiento_id = $order->get_meta('_cdc_movimiento_id');
        if (!$movimiento_id) {
            error_log('CDC ARCA: Not a CDC order - ' . $order_id);
            return;
        }

        // Get order details
        $persona_id = $order->get_meta('_cdc_persona_id');
        $tipo_cobro = $order->get_meta('_cdc_tipo_cobro');

        // Get customer data (persona)
        $persona_model = new CDC_Persona();
        $persona = $persona_model->find($persona_id);

        if (!$persona) {
            error_log('CDC ARCA: Persona not found - ' . $persona_id);
            $this->mark_invoice_failed($order_id, $movimiento_id, 'Persona no encontrada');
            return;
        }

        // Prepare invoice data
        $invoice_data = $this->prepare_invoice_data($order, $persona, $tipo_cobro);

        // Send to ARCA
        $result = $this->send_invoice_to_arca($invoice_data);

        if ($result['success']) {
            $comprobante_id = $result['comprobante_id'];

            // Store comprobante_id in order
            $order->update_meta_data('_cdc_comprobante_id', $comprobante_id);
            $order->update_meta_data('_cdc_factura_status', 'ok');
            $order->update_meta_data('_cdc_factura_fecha', current_time('mysql'));
            $order->save();

            // Update movimiento_caja
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'cdc_movimientos_caja',
                array('comprobante_id' => $comprobante_id),
                array('id' => $movimiento_id),
                array('%s'),
                array('%d')
            );

            // Update related entities based on tipo_cobro
            $this->update_related_entities_with_comprobante($order, $comprobante_id);

            error_log('CDC ARCA: Invoice created successfully - Order: ' . $order_id . ', Comprobante: ' . $comprobante_id);
        } else {
            $this->mark_invoice_failed($order_id, $movimiento_id, $result['message']);
            error_log('CDC ARCA: Invoice failed - Order: ' . $order_id . ', Error: ' . $result['message']);
        }
    }

    /**
     * Prepare invoice data for ARCA API
     *
     * @param WC_Order $order WooCommerce order
     * @param object $persona Persona data
     * @param string $tipo_cobro Type of charge
     * @return array Invoice data
     */
    private function prepare_invoice_data($order, $persona, $tipo_cobro) {
        $items = array();

        foreach ($order->get_items() as $item) {
            $items[] = array(
                'descripcion' => $item->get_name(),
                'cantidad' => $item->get_quantity(),
                'precio_unitario' => $item->get_total(),
                'subtotal' => $item->get_total(),
            );
        }

        return array(
            'tipo_comprobante' => 'B', // Factura B (default)
            'fecha_emision' => $order->get_date_created()->format('Y-m-d'),
            'cliente' => array(
                'nombre' => $persona->nombre . ' ' . $persona->apellido,
                'documento_tipo' => 'DNI',
                'documento_numero' => $persona->dni,
                'email' => $persona->email,
                'domicilio' => $persona->direccion,
            ),
            'items' => $items,
            'total' => $order->get_total(),
            'observaciones' => 'Orden WooCommerce #' . $order->get_id(),
        );
    }

    /**
     * Send invoice to ARCA API
     *
     * @param array $invoice_data Invoice data
     * @return array Result with success flag and comprobante_id or error message
     */
    private function send_invoice_to_arca($invoice_data) {
        // SANDBOX MODE: Generate fake comprobante_id for testing
        if ($this->sandbox_mode) {
            $comprobante_id = 'SANDBOX-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            error_log('CDC ARCA SANDBOX: Generando comprobante ficticio - ' . $comprobante_id);
            error_log('CDC ARCA SANDBOX: Datos de factura - ' . json_encode($invoice_data));

            return array(
                'success' => true,
                'comprobante_id' => $comprobante_id,
                'data' => array(
                    'sandbox' => true,
                    'comprobante_id' => $comprobante_id,
                    'tipo_comprobante' => $invoice_data['tipo_comprobante'],
                    'total' => $invoice_data['total'],
                    'cliente' => $invoice_data['cliente']['nombre'],
                    'fecha_emision' => $invoice_data['fecha_emision'],
                ),
            );
        }

        // PRODUCTION MODE: Real API request to ARCA
        $response = wp_remote_post($this->api_endpoint . '/comprobantes', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'body' => json_encode($invoice_data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Error de conexión: ' . $response->get_error_message(),
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code >= 200 && $status_code < 300) {
            // Success
            return array(
                'success' => true,
                'comprobante_id' => $body['comprobante_id'] ?? $body['id'] ?? null,
                'data' => $body,
            );
        } else {
            // Error
            return array(
                'success' => false,
                'message' => isset($body['message']) ? $body['message'] : 'Error desconocido',
                'data' => $body,
            );
        }
    }

    /**
     * Mark invoice as failed
     *
     * @param int $order_id Order ID
     * @param int $movimiento_id Movimiento ID
     * @param string $error_message Error message
     */
    private function mark_invoice_failed($order_id, $movimiento_id, $error_message) {
        $order = wc_get_order($order_id);

        if ($order) {
            $order->update_meta_data('_cdc_factura_status', 'error');
            $order->update_meta_data('_cdc_factura_error', $error_message);
            $order->update_meta_data('_cdc_factura_fecha', current_time('mysql'));
            $order->save();
        }

        // Optionally update movimiento_caja to mark as pending invoice
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'cdc_movimientos_caja',
            array('comprobante_id' => 'PENDING'),
            array('id' => $movimiento_id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * Update related entities with comprobante_id
     *
     * @param WC_Order $order Order object
     * @param string $comprobante_id ARCA comprobante ID
     */
    private function update_related_entities_with_comprobante($order, $comprobante_id) {
        global $wpdb;

        $tipo_cobro = $order->get_meta('_cdc_tipo_cobro');

        switch ($tipo_cobro) {
            case 'cuota_socio':
                // Update cuotas_socio
                $cuota_ids_str = $order->get_meta('_cdc_cuota_ids');
                if ($cuota_ids_str) {
                    $cuota_ids = explode(',', $cuota_ids_str);
                    foreach ($cuota_ids as $cuota_id) {
                        $wpdb->update(
                            $wpdb->prefix . 'cdc_cuotas_socio',
                            array('comprobante_id' => $comprobante_id),
                            array('id' => intval($cuota_id)),
                            array('%s'),
                            array('%d')
                        );
                    }
                }
                break;

            case 'cuota_taller':
                // Update cuotas_taller
                $cuota_ids_str = $order->get_meta('_cdc_cuota_ids');
                if ($cuota_ids_str) {
                    $cuota_ids = explode(',', $cuota_ids_str);
                    foreach ($cuota_ids as $cuota_id) {
                        $wpdb->update(
                            $wpdb->prefix . 'cdc_cuotas_taller',
                            array('comprobante_id' => $comprobante_id),
                            array('id' => intval($cuota_id)),
                            array('%s'),
                            array('%d')
                        );
                    }
                }
                break;

            case 'alquiler_sala':
                // Update reservas_salas
                $reserva_id = $order->get_meta('_cdc_reserva_id');
                if ($reserva_id) {
                    $wpdb->update(
                        $wpdb->prefix . 'cdc_reservas_salas',
                        array('comprobante_id' => $comprobante_id),
                        array('id' => intval($reserva_id)),
                        array('%s'),
                        array('%d')
                    );
                }
                break;
        }
    }

    /**
     * Retry invoice generation for a failed order
     *
     * @param int $order_id Order ID
     * @return array Result
     */
    public function retry_invoice($order_id) {
        $order = wc_get_order($order_id);

        if (!$order) {
            return array('success' => false, 'message' => 'Orden no encontrada');
        }

        // Clear error status
        $order->delete_meta_data('_cdc_factura_status');
        $order->delete_meta_data('_cdc_factura_error');
        $order->save();

        // Trigger invoice process again
        $this->process_order_invoice($order_id);

        // Check if succeeded
        $status = $order->get_meta('_cdc_factura_status');

        if ($status === 'ok') {
            return array('success' => true, 'message' => 'Factura generada correctamente');
        } else {
            $error = $order->get_meta('_cdc_factura_error');
            return array('success' => false, 'message' => $error ?: 'Error desconocido');
        }
    }
}
