<?php
/**
 * CDC Admin Menu
 *
 * Creates the main menu and all submenus for CDC administration
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'cdc_admin_menu');

/**
 * Register CDC Admin menu
 */
function cdc_admin_menu() {
    // Main menu icon (SVG encoded)
    $icon = 'dashicons-building';

    // Main menu page
    add_menu_page(
        __('CDC - Casa de la Cultura', 'cdc-admin'),
        __('CDC', 'cdc-admin'),
        'cdc_manage_personas',
        'cdc-inicio',
        'cdc_render_inicio_page',
        $icon,
        3
    );

    // Submenu: Inicio (Dashboard)
    add_submenu_page(
        'cdc-inicio',
        __('Inicio', 'cdc-admin'),
        __('Inicio', 'cdc-admin'),
        'cdc_manage_personas',
        'cdc-inicio',
        'cdc_render_inicio_page'
    );

    // Submenu: Personas
    add_submenu_page(
        'cdc-inicio',
        __('Personas', 'cdc-admin'),
        __('Personas', 'cdc-admin'),
        'cdc_manage_personas',
        'cdc-personas',
        'cdc_render_personas_page'
    );

    // Submenu: Cobrar
    add_submenu_page(
        'cdc-inicio',
        __('Cobrar', 'cdc-admin'),
        __('Cobrar', 'cdc-admin'),
        'cdc_manage_cobros',
        'cdc-cobrar',
        'cdc_render_cobrar_page'
    );

    // Submenu: Registrar Gasto
    add_submenu_page(
        'cdc-inicio',
        __('Registrar Gasto', 'cdc-admin'),
        __('Registrar Gasto', 'cdc-admin'),
        'cdc_register_expenses',
        'cdc-registrar-gasto',
        'cdc_render_registrar_gasto_page'
    );

    // Submenu: Caja
    add_submenu_page(
        'cdc-inicio',
        __('Caja', 'cdc-admin'),
        __('Caja', 'cdc-admin'),
        'cdc_view_caja',
        'cdc-caja',
        'cdc_render_caja_page'
    );

    // Submenu: Talleres
    add_submenu_page(
        'cdc-inicio',
        __('Talleres', 'cdc-admin'),
        __('Talleres', 'cdc-admin'),
        'cdc_manage_talleres',
        'cdc-talleres',
        'cdc_render_talleres_page'
    );

    // Submenu: Eventos
    add_submenu_page(
        'cdc-inicio',
        __('Eventos', 'cdc-admin'),
        __('Eventos', 'cdc-admin'),
        'cdc_manage_talleres',
        'cdc-eventos',
        'cdc_render_eventos_page'
    );

    // Submenu: Alquiler de Salas
    add_submenu_page(
        'cdc-inicio',
        __('Alquiler de Salas', 'cdc-admin'),
        __('Alquiler de Salas', 'cdc-admin'),
        'cdc_manage_salas',
        'cdc-alquiler-salas',
        'cdc_render_alquiler_salas_page'
    );

    // Submenu: Salas
    add_submenu_page(
        'cdc-inicio',
        __('Salas', 'cdc-admin'),
        __('Salas', 'cdc-admin'),
        'cdc_manage_salas',
        'cdc-salas',
        'cdc_render_salas_page'
    );

    // Submenu: Configuración (only for admin)
    if (current_user_can('administrator')) {
        add_submenu_page(
            'cdc-inicio',
            __('Configuración', 'cdc-admin'),
            __('Configuración', 'cdc-admin'),
            'administrator',
            'cdc-configuracion',
            'cdc_render_configuracion_page'
        );
    }
}

/**
 * Get current CDC page
 */
function cdc_get_current_page() {
    return isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'cdc-inicio';
}

/**
 * Check if current page is CDC page
 */
function cdc_is_cdc_page() {
    $page = cdc_get_current_page();
    return strpos($page, 'cdc-') === 0;
}

/**
 * Render CDC admin header
 */
function cdc_render_admin_header($page_title = '') {
    $user_role = CDC_Permissions::get_user_role_display();
    $current_user = wp_get_current_user();
    ?>
    <div class="cdc-admin-header">
        <div class="cdc-header-left">
            <h1><?php echo esc_html($page_title); ?></h1>
        </div>
        <div class="cdc-header-right">
            <span class="cdc-user-role"><?php echo esc_html($user_role); ?></span>
            <span class="cdc-user-name"><?php echo esc_html($current_user->display_name); ?></span>
        </div>
    </div>
    <?php
}

/**
 * Render CDC sidebar navigation
 */
function cdc_render_sidebar() {
    $current_page = cdc_get_current_page();

    $menu_items = array();

    // Inicio
    if (CDC_Permissions::can_manage_personas()) {
        $menu_items[] = array(
            'slug' => 'cdc-inicio',
            'title' => __('Inicio', 'cdc-admin'),
            'icon' => 'dashicons-dashboard',
        );
    }

    // Personas
    if (CDC_Permissions::can_manage_personas()) {
        $menu_items[] = array(
            'slug' => 'cdc-personas',
            'title' => __('Personas', 'cdc-admin'),
            'icon' => 'dashicons-groups',
        );
    }

    // Cobrar
    if (CDC_Permissions::can_manage_cobros()) {
        $menu_items[] = array(
            'slug' => 'cdc-cobrar',
            'title' => __('Cobrar', 'cdc-admin'),
            'icon' => 'dashicons-money-alt',
        );
    }

    // Registrar Gasto
    if (CDC_Permissions::can_register_expenses()) {
        $menu_items[] = array(
            'slug' => 'cdc-registrar-gasto',
            'title' => __('Registrar Gasto', 'cdc-admin'),
            'icon' => 'dashicons-clipboard',
        );
    }

    // Caja
    if (CDC_Permissions::can_view_caja()) {
        $menu_items[] = array(
            'slug' => 'cdc-caja',
            'title' => __('Caja', 'cdc-admin'),
            'icon' => 'dashicons-calculator',
        );
    }

    // Talleres
    if (CDC_Permissions::can_manage_talleres()) {
        $menu_items[] = array(
            'slug' => 'cdc-talleres',
            'title' => __('Talleres', 'cdc-admin'),
            'icon' => 'dashicons-book-alt',
        );
    }

    // Eventos
    if (CDC_Permissions::can_manage_talleres()) {
        $menu_items[] = array(
            'slug' => 'cdc-eventos',
            'title' => __('Eventos', 'cdc-admin'),
            'icon' => 'dashicons-calendar-alt',
        );
    }

    // Alquiler de Salas
    if (CDC_Permissions::can_manage_salas()) {
        $menu_items[] = array(
            'slug' => 'cdc-alquiler-salas',
            'title' => __('Alquiler de Salas', 'cdc-admin'),
            'icon' => 'dashicons-admin-multisite',
        );
    }

    // Salas
    if (CDC_Permissions::can_manage_salas()) {
        $menu_items[] = array(
            'slug' => 'cdc-salas',
            'title' => __('Salas', 'cdc-admin'),
            'icon' => 'dashicons-admin-home',
        );
    }

    ?>
    <div class="cdc-sidebar">
        <div class="cdc-sidebar-header">
            <h2><?php _e('Casa de la Cultura', 'cdc-admin'); ?></h2>
            <p class="cdc-sidebar-subtitle"><?php _e('Sistema de Administración', 'cdc-admin'); ?></p>
        </div>
        <nav class="cdc-sidebar-nav">
            <?php foreach ($menu_items as $item): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $item['slug'])); ?>"
                   class="cdc-sidebar-item <?php echo ($current_page === $item['slug']) ? 'active' : ''; ?>">
                    <span class="dashicons <?php echo esc_attr($item['icon']); ?>"></span>
                    <span class="cdc-sidebar-item-text"><?php echo esc_html($item['title']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    <?php
}

/**
 * Render CDC page wrapper start
 */
function cdc_page_wrapper_start($page_title = '') {
    ?>
    <div class="wrap cdc-admin-wrap">
        <?php cdc_render_sidebar(); ?>
        <div class="cdc-main-content">
            <?php cdc_render_admin_header($page_title); ?>
            <div class="cdc-content-body">
    <?php
}

/**
 * Render CDC page wrapper end
 */
function cdc_page_wrapper_end() {
    ?>
            </div><!-- .cdc-content-body -->
        </div><!-- .cdc-main-content -->
    </div><!-- .cdc-admin-wrap -->
    <?php
}
