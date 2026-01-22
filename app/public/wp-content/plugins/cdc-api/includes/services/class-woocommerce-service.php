<?php
/**
 * WooCommerce Integration Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Service Class
 * Handles product and order creation for CDC transactions
 */
class CDC_WooCommerce_Service {
    /**
     * Product category for CDC products
     */
    const CATEGORY_SLUG = 'cdc-servicios';

    /**
     * Get or create CDC products category
     *
     * @return int Category ID
     */
    public function get_or_create_category() {
        $term = term_exists(self::CATEGORY_SLUG, 'product_cat');

        if ($term) {
            return $term['term_id'];
        }

        // Create category
        $term = wp_insert_term('Servicios CDC', 'product_cat', array(
            'slug' => self::CATEGORY_SLUG,
            'description' => 'Servicios de Casa de la Cultura (cuotas, talleres, alquileres)',
        ));

        if (is_wp_error($term)) {
            error_log('CDC WC Service: Error creating category - ' . $term->get_error_message());
            return 0;
        }

        return $term['term_id'];
    }

    /**
     * Create or get product for Cuota Socio
     *
     * @return int|false Product ID or false on error
     */
    public function get_or_create_cuota_socio_product() {
        // Check if product already exists
        $existing = get_posts(array(
            'post_type' => 'product',
            'meta_key' => '_cdc_product_type',
            'meta_value' => 'cuota_socio',
            'posts_per_page' => 1,
        ));

        if (!empty($existing)) {
            return $existing[0]->ID;
        }

        // Create product
        $product = new WC_Product_Simple();
        $product->set_name('Cuota Socio CDC');
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_price(0); // Price will be set per order
        $product->set_regular_price(0);
        $product->set_virtual(true);
        $product->set_sold_individually(true);
        $product->set_category_ids(array($this->get_or_create_category()));

        $product_id = $product->save();

        if ($product_id) {
            update_post_meta($product_id, '_cdc_product_type', 'cuota_socio');
        }

        return $product_id;
    }

    /**
     * Create or update product for a Taller
     *
     * @param int $taller_id Taller ID
     * @param array $taller_data Taller data
     * @return int|false Product ID or false on error
     */
    public function sync_taller_product($taller_id, $taller_data) {
        // Check if product exists
        $existing = get_posts(array(
            'post_type' => 'product',
            'meta_key' => '_cdc_taller_id',
            'meta_value' => $taller_id,
            'posts_per_page' => 1,
        ));

        $product_id = !empty($existing) ? $existing[0]->ID : 0;

        if ($product_id) {
            $product = wc_get_product($product_id);
        } else {
            $product = new WC_Product_Simple();
        }

        $nombre = isset($taller_data['nombre']) ? $taller_data['nombre'] : "Taller ID $taller_id";
        $precio = isset($taller_data['precio_mensual']) ? floatval($taller_data['precio_mensual']) : 0;
        $descripcion = isset($taller_data['descripcion']) ? $taller_data['descripcion'] : '';

        $product->set_name("Cuota Taller: $nombre");
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_price($precio);
        $product->set_regular_price($precio);
        $product->set_description($descripcion);
        $product->set_virtual(true);
        $product->set_sold_individually(true);
        $product->set_category_ids(array($this->get_or_create_category()));

        $product_id = $product->save();

        if ($product_id) {
            update_post_meta($product_id, '_cdc_taller_id', $taller_id);
            update_post_meta($product_id, '_cdc_product_type', 'cuota_taller');
        }

        return $product_id;
    }

    /**
     * Create or update product for a Sala
     *
     * @param int $sala_id Sala ID
     * @param array $sala_data Sala data
     * @return int|false Product ID or false on error
     */
    public function sync_sala_product($sala_id, $sala_data) {
        // Check if product exists
        $existing = get_posts(array(
            'post_type' => 'product',
            'meta_key' => '_cdc_sala_id',
            'meta_value' => $sala_id,
            'posts_per_page' => 1,
        ));

        $product_id = !empty($existing) ? $existing[0]->ID : 0;

        if ($product_id) {
            $product = wc_get_product($product_id);
        } else {
            $product = new WC_Product_Simple();
        }

        $nombre = isset($sala_data['nombre']) ? $sala_data['nombre'] : "Sala ID $sala_id";
        $precio = isset($sala_data['precio_hora']) ? floatval($sala_data['precio_hora']) : 0;
        $descripcion = isset($sala_data['descripcion']) ? $sala_data['descripcion'] : '';

        $product->set_name("Alquiler Sala: $nombre");
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_price($precio);
        $product->set_regular_price($precio);
        $product->set_description($descripcion);
        $product->set_virtual(true);
        $product->set_category_ids(array($this->get_or_create_category()));

        $product_id = $product->save();

        if ($product_id) {
            update_post_meta($product_id, '_cdc_sala_id', $sala_id);
            update_post_meta($product_id, '_cdc_product_type', 'alquiler_sala');
        }

        return $product_id;
    }

    /**
     * Create WooCommerce order for a CDC transaction
     *
     * @param array $args Order arguments
     * @return int|WP_Error Order ID or error
     */
    public function create_order($args) {
        try {
            $order = wc_create_order();

            // Set customer if provided
            if (isset($args['customer_id'])) {
                $order->set_customer_id($args['customer_id']);
            }

            // Add products
            if (isset($args['items']) && is_array($args['items'])) {
                foreach ($args['items'] as $item) {
                    $product_id = $item['product_id'];
                    $quantity = isset($item['quantity']) ? $item['quantity'] : 1;
                    $price = isset($item['price']) ? $item['price'] : null;

                    $product = wc_get_product($product_id);
                    if (!$product) {
                        throw new Exception("Product not found: $product_id");
                    }

                    $order->add_product($product, $quantity, array(
                        'subtotal' => $price ? $price : $product->get_price(),
                        'total' => $price ? $price : $product->get_price(),
                    ));
                }
            }

            // Set order metadata
            if (isset($args['meta_data']) && is_array($args['meta_data'])) {
                foreach ($args['meta_data'] as $key => $value) {
                    $order->update_meta_data($key, $value);
                }
            }

            // Set payment method
            if (isset($args['payment_method'])) {
                $order->set_payment_method($args['payment_method']);
                $order->set_payment_method_title($this->get_payment_method_title($args['payment_method']));
            }

            // Set order status
            $status = isset($args['status']) ? $args['status'] : 'processing';
            $order->set_status($status);

            // Calculate totals
            $order->calculate_totals();

            // Save order
            $order_id = $order->save();

            // Mark as paid if requested
            if (isset($args['mark_paid']) && $args['mark_paid']) {
                $order->payment_complete();
            }

            return $order_id;

        } catch (Exception $e) {
            error_log('CDC WC Service: Error creating order - ' . $e->getMessage());
            return new WP_Error('order_creation_failed', $e->getMessage());
        }
    }

    /**
     * Get payment method title
     *
     * @param string $method Payment method slug
     * @return string Title
     */
    private function get_payment_method_title($method) {
        $titles = array(
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia Bancaria',
            'tarjeta' => 'Tarjeta de Crédito/Débito',
            'mercadopago' => 'Mercado Pago',
        );

        return isset($titles[$method]) ? $titles[$method] : ucfirst($method);
    }

    /**
     * Get order by CDC movimiento ID
     *
     * @param int $movimiento_id Movimiento ID
     * @return WC_Order|false Order object or false
     */
    public function get_order_by_movimiento($movimiento_id) {
        $orders = wc_get_orders(array(
            'meta_key' => '_cdc_movimiento_id',
            'meta_value' => $movimiento_id,
            'limit' => 1,
        ));

        return !empty($orders) ? $orders[0] : false;
    }
}
