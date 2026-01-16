<?php
/**
 * Sidebar Navigation
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_page = get_query_var('pagename');
if (empty($current_page)) {
    $current_page = 'dashboard';
}

// Menu items based on user permissions
$menu_items = array();

// Siempre mostrar inicio
$menu_items[] = array(
    'slug' => 'home',
    'title' => 'Inicio',
    'icon' => 'dashicons-dashboard',
    'url' => home_url('/'),
);

// Personas
$menu_items[] = array(
    'slug' => 'personas',
    'title' => 'Personas',
    'icon' => 'dashicons-groups',
    'url' => home_url('/personas'),
);

// Cobrar
$menu_items[] = array(
    'slug' => 'cobrar',
    'title' => 'Cobrar',
    'icon' => 'dashicons-money-alt',
    'url' => home_url('/cobrar'),
);

// Registrar Gasto
$menu_items[] = array(
    'slug' => 'registrar-gasto',
    'title' => 'Registrar Gasto',
    'icon' => 'dashicons-clipboard',
    'url' => home_url('/registrar-gasto'),
);

// Caja
$menu_items[] = array(
    'slug' => 'caja',
    'title' => 'Caja',
    'icon' => 'dashicons-calculator',
    'url' => home_url('/caja'),
);

// Talleres
$menu_items[] = array(
    'slug' => 'talleres',
    'title' => 'Talleres',
    'icon' => 'dashicons-book-alt',
    'url' => home_url('/talleres'),
);

// Eventos
$menu_items[] = array(
    'slug' => 'eventos',
    'title' => 'Eventos',
    'icon' => 'dashicons-calendar-alt',
    'url' => home_url('/eventos'),
);

// Alquiler de Salas
$menu_items[] = array(
    'slug' => 'alquiler-salas',
    'title' => 'Alquiler de Salas',
    'icon' => 'dashicons-admin-multisite',
    'url' => home_url('/alquiler-salas'),
);

// Salas
$menu_items[] = array(
    'slug' => 'salas',
    'title' => 'Salas',
    'icon' => 'dashicons-admin-home',
    'url' => home_url('/salas'),
);
?>

<aside class="cdc-sidebar">
    <div class="cdc-sidebar-header">
        <h2>Casa de la Cultura</h2>
        <p>Sistema de Administración</p>
    </div>

    <nav class="cdc-sidebar-nav">
        <?php foreach ($menu_items as $item): ?>
            <a href="<?php echo esc_url($item['url']); ?>"
               class="cdc-sidebar-item <?php echo ($current_page === $item['slug']) ? 'active' : ''; ?>">
                <span class="dashicons <?php echo esc_attr($item['icon']); ?>"></span>
                <span class="cdc-sidebar-text"><?php echo esc_html($item['title']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
