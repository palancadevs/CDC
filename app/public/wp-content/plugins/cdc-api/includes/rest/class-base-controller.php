<?php
/**
 * Base REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base REST Controller Class
 */
abstract class CDC_Base_Controller extends WP_REST_Controller {
    /**
     * Namespace
     */
    protected $namespace = 'cdc/v1';

    /**
     * Check if user is authenticated
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error
     */
    public function check_auth($request) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('No tiene permisos para realizar esta acción', 'cdc-api'),
                array('status' => 401)
            );
        }

        return true;
    }

    /**
     * Check if user has specific role or capability
     *
     * @param array $roles_or_caps Allowed roles or capabilities
     * @return bool|WP_Error
     */
    public function check_role($roles_or_caps = array()) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('No tiene permisos para realizar esta acción', 'cdc-api'),
                array('status' => 401)
            );
        }

        $user = wp_get_current_user();

        // Administrator always has access
        if (in_array('administrator', $user->roles) || $user->has_cap('cdc_full_access')) {
            return true;
        }

        // Check if user has any of the specified roles
        if (array_intersect($roles_or_caps, $user->roles)) {
            return true;
        }

        // Check if user has any of the specified capabilities
        foreach ($roles_or_caps as $cap) {
            if ($user->has_cap($cap)) {
                return true;
            }
        }

        return new WP_Error(
            'rest_forbidden',
            __('No tiene permisos suficientes para esta acción', 'cdc-api'),
            array('status' => 403)
        );
    }

    /**
     * Prepare response
     *
     * @param mixed $data Data to send
     * @param int $status HTTP status code
     * @return WP_REST_Response
     */
    protected function prepare_response($data, $status = 200) {
        return new WP_REST_Response($data, $status);
    }

    /**
     * Prepare error response
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return WP_Error
     */
    protected function prepare_error($message, $status = 400) {
        return new WP_Error(
            'cdc_error',
            $message,
            array('status' => $status)
        );
    }
}
