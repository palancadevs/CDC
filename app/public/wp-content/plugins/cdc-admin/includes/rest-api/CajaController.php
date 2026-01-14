<?php
/**
 * Caja REST API Controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDC_Caja_Controller extends WP_REST_Controller {

    protected $namespace = 'cdc/v1';
    protected $rest_base = 'caja';

    public function register_routes() {
        // To be implemented in Phase 3
    }
}
