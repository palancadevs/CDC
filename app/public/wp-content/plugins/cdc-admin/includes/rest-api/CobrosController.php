<?php
/**
 * Cobros REST API Controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDC_Cobros_Controller extends WP_REST_Controller {

    protected $namespace = 'cdc/v1';
    protected $rest_base = 'cobros';

    public function register_routes() {
        // To be implemented in Phase 4
    }
}
