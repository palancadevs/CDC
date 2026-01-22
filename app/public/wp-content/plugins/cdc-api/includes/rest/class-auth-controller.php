<?php
/**
 * Auth REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Auth REST Controller Class
 * Handles DNI-based authentication
 */
class CDC_Auth_Controller extends WP_REST_Controller {
    /**
     * Namespace
     */
    protected $namespace = 'cdc/v1';

    /**
     * Rest base
     */
    protected $rest_base = 'auth';

    /**
     * Persona model
     */
    private $persona_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->persona_model = new CDC_Persona();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // POST /auth/login - Login with DNI
        register_rest_route($this->namespace, '/' . $this->rest_base . '/login', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'login'),
                'permission_callback' => '__return_true', // Public endpoint
            ),
        ));

        // POST /auth/logout - Logout current user
        register_rest_route($this->namespace, '/' . $this->rest_base . '/logout', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'logout'),
                'permission_callback' => '__return_true', // Public endpoint
            ),
        ));

        // GET /auth/me - Get current authenticated user info
        register_rest_route($this->namespace, '/' . $this->rest_base . '/me', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_current_user'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));
    }

    /**
     * Login with DNI
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function login($request) {
        $dni = $request->get_param('dni');

        if (empty($dni)) {
            return new WP_Error(
                'dni_required',
                'El DNI es requerido',
                array('status' => 400)
            );
        }

        // Sanitize DNI
        $dni = sanitize_text_field($dni);

        // Find persona by DNI
        global $wpdb;
        $persona = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cdc_persona WHERE dni = %s",
            $dni
        ));

        if (!$persona) {
            return new WP_Error(
                'invalid_dni',
                'DNI no encontrado en el sistema',
                array('status' => 401)
            );
        }

        // Find or create WordPress user for this persona
        $username = 'cdc_' . $persona->dni;
        $user = get_user_by('login', $username);

        if (!$user) {
            // Create WordPress user
            $user_id = wp_create_user(
                $username,
                wp_generate_password(20, true, true), // Random password
                '' // No email required
            );

            if (is_wp_error($user_id)) {
                return new WP_Error(
                    'user_creation_failed',
                    'Error al crear el usuario',
                    array('status' => 500)
                );
            }

            // Link persona to user
            update_user_meta($user_id, 'cdc_persona_id', $persona->id);

            // Assign role based on persona type or default to recepcion
            $role = $this->determine_user_role($persona);
            $user = new WP_User($user_id);
            $user->set_role($role);
        } else {
            $user_id = $user->ID;

            // Verify persona link still exists
            $stored_persona_id = get_user_meta($user_id, 'cdc_persona_id', true);
            if ($stored_persona_id != $persona->id) {
                update_user_meta($user_id, 'cdc_persona_id', $persona->id);
            }
        }

        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        do_action('wp_login', $user->user_login, $user);

        // Get full persona data
        $persona_full = $this->persona_model->get_full_data($persona->id);

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Autenticación exitosa',
            'data' => array(
                'user_id' => $user_id,
                'persona' => $persona_full,
                'role' => $user->roles[0],
            ),
        ), 200);
    }

    /**
     * Logout current user
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function logout($request) {
        wp_logout();

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ), 200);
    }

    /**
     * Get current authenticated user info
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function get_current_user($request) {
        $user_id = get_current_user_id();
        $persona_id = get_user_meta($user_id, 'cdc_persona_id', true);

        if (!$persona_id) {
            return new WP_Error(
                'no_persona',
                'Usuario no vinculado a persona',
                array('status' => 500)
            );
        }

        $persona = $this->persona_model->get_full_data($persona_id);

        if (!$persona) {
            return new WP_Error(
                'persona_not_found',
                'Persona no encontrada',
                array('status' => 404)
            );
        }

        $user = wp_get_current_user();

        return new WP_REST_Response(array(
            'success' => true,
            'data' => array(
                'user_id' => $user_id,
                'username' => $user->user_login,
                'role' => $user->roles[0],
                'persona' => $persona,
            ),
        ), 200);
    }

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
                'No tiene permisos para realizar esta acción',
                array('status' => 401)
            );
        }

        return true;
    }

    /**
     * Determine user role based on persona
     *
     * @param object $persona Persona object
     * @return string WordPress role
     */
    private function determine_user_role($persona) {
        // Check if persona has specific role metadata
        global $wpdb;
        $role_meta = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}cdc_persona_meta
             WHERE persona_id = %d AND meta_key = 'sistema_rol'",
            $persona->id
        ));

        if ($role_meta) {
            $valid_roles = array('cdc_admin', 'cdc_tesoreria', 'cdc_recepcion');
            if (in_array($role_meta, $valid_roles)) {
                return $role_meta;
            }
        }

        // Default role
        return 'cdc_recepcion';
    }
}
