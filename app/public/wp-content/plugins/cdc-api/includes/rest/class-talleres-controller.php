<?php
/**
 * Talleres REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Talleres REST Controller Class
 */
class CDC_Talleres_Controller extends CDC_Base_Controller {
    /**
     * Rest base
     */
    protected $rest_base = 'talleres';

    /**
     * Service instance
     */
    private $service;

    /**
     * Inscripcion service instance
     */
    private $inscripcion_service;

    /**
     * WooCommerce service instance
     */
    private $wc_service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new CDC_Taller_Service();
        $this->inscripcion_service = new CDC_Inscripcion_Service();
        $this->wc_service = new CDC_WooCommerce_Service();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /talleres - Get all talleres
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /talleres - Create taller
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /talleres/{id} - Get single taller
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // PUT /talleres/{id} - Update taller
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /talleres/{id}/inscribir - Inscribe person to taller
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/inscribir', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'inscribir_persona'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /talleres/{id}/inscripciones - Get inscripciones for taller
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/inscripciones', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_inscripciones'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // PUT /talleres/{taller_id}/inscripciones/{inscripcion_id}/baja - Dar de baja inscripcion
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<taller_id>[\d]+)/inscripciones/(?P<inscripcion_id>[\d]+)/baja', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'dar_de_baja_inscripcion'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // POST /talleres/sync-wc-products - Sync all talleres as WooCommerce products
        register_rest_route($this->namespace, '/' . $this->rest_base . '/sync-wc-products', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'sync_wc_products'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));
    }

    /**
     * Get items
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_items($request) {
        $talleres = $this->service->get_all_talleres();

        return $this->prepare_response(array(
            'success' => true,
            'data' => $talleres,
        ));
    }

    /**
     * Get single item
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function get_item($request) {
        $id = $request->get_param('id');
        $taller = $this->service->get_taller($id);

        if (!$taller) {
            return $this->prepare_error('Taller no encontrado', 404);
        }

        return $this->prepare_response(array(
            'success' => true,
            'data' => $taller,
        ));
    }

    /**
     * Create item
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function create_item($request) {
        $data = $request->get_json_params();
        $result = $this->service->create_taller($data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result, 201);
    }

    /**
     * Update item
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function update_item($request) {
        $id = $request->get_param('id');
        $data = $request->get_json_params();

        $result = $this->service->update_taller($id, $data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result);
    }

    /**
     * Inscribe person to taller
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function inscribir_persona($request) {
        $taller_id = $request->get_param('id');
        $data = $request->get_json_params();

        if (empty($data['persona_id'])) {
            return $this->prepare_error('persona_id es requerido', 400);
        }

        $persona_id = (int) $data['persona_id'];

        $result = $this->inscripcion_service->inscribir_persona($taller_id, $persona_id, $data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result, 201);
    }

    /**
     * Get inscripciones for taller
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_inscripciones($request) {
        $taller_id = $request->get_param('id');
        $estado = $request->get_param('estado');

        $inscripciones = $this->inscripcion_service->get_inscripciones_taller($taller_id, $estado);

        return $this->prepare_response(array(
            'success' => true,
            'data' => $inscripciones,
        ));
    }

    /**
     * Dar de baja inscripcion
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function dar_de_baja_inscripcion($request) {
        $inscripcion_id = $request->get_param('inscripcion_id');
        $data = $request->get_json_params();

        $result = $this->inscripcion_service->dar_de_baja($inscripcion_id, $data);

        if (!$result['success']) {
            return $this->prepare_error($result['message'], 400);
        }

        return $this->prepare_response($result);
    }

    /**
     * Sync all talleres as WooCommerce products
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function sync_wc_products($request) {
        $talleres = $this->service->get_all_talleres();

        $synced = 0;
        $errors = array();

        foreach ($talleres as $taller) {
            $taller_data = array(
                'nombre' => $taller['nombre'],
                'precio_mensual' => $taller['precio_mensual'],
                'descripcion' => isset($taller['descripcion']) ? $taller['descripcion'] : '',
            );

            $product_id = $this->wc_service->sync_taller_product($taller['id'], $taller_data);

            if ($product_id) {
                $synced++;
            } else {
                $errors[] = 'Error syncing taller ID: ' . $taller['id'];
            }
        }

        return $this->prepare_response(array(
            'success' => true,
            'message' => "Sincronizados $synced talleres como productos WooCommerce",
            'data' => array(
                'total_talleres' => count($talleres),
                'synced' => $synced,
                'errors' => $errors,
            ),
        ));
    }
}
