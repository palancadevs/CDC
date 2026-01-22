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
     * PHASE 1: Authentication disabled for testing/development
     * TODO: Enable authentication in future phase
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error
     */
    public function check_auth($request) {
        // PHASE 1: Allow all requests without authentication
        return true;

        // FUTURE PHASE: Uncomment this when authentication is implemented
        // if (!is_user_logged_in()) {
        //     return new WP_Error(
        //         'rest_forbidden',
        //         __('No tiene permisos para realizar esta acción', 'cdc-api'),
        //         array('status' => 401)
        //     );
        // }
        //
        // return true;
    }

    /**
     * Check if user has specific role
     *
     * PHASE 1: Role-based permissions disabled for testing/development
     * TODO: Enable role-based permissions in future phase
     *
     * @param array $roles Allowed roles
     * @return bool|WP_Error
     */
    public function check_role($roles = array()) {
        // PHASE 1: Allow all requests without role checking
        return true;

        // FUTURE PHASE: Uncomment this when authentication is implemented
        // if (!is_user_logged_in()) {
        //     return new WP_Error(
        //         'rest_forbidden',
        //         __('No tiene permisos para realizar esta acción', 'cdc-api'),
        //         array('status' => 401)
        //     );
        // }
        //
        // $user = wp_get_current_user();
        //
        // if (!array_intersect($roles, $user->roles) && !in_array('administrator', $user->roles)) {
        //     return new WP_Error(
        //         'rest_forbidden',
        //         __('No tiene permisos suficientes', 'cdc-api'),
        //         array('status' => 403)
        //     );
        // }
        //
        // return true;
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
