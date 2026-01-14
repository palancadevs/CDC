<?php
/**
 * Personas REST API Controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDC_Personas_Controller extends WP_REST_Controller {

    protected $namespace = 'cdc/v1';
    protected $rest_base = 'personas';

    public function register_routes() {
        // To be implemented in Phase 2
    }
}
