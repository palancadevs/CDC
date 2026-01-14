<?php
/**
 * CDC Inicio Page (Dashboard)
 *
 * Main dashboard page for CDC administration
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render Inicio page
 */
function cdc_render_inicio_page() {
    // Check permissions
    if (!CDC_Permissions::can_manage_personas()) {
        wp_die(__('No tiene permisos para acceder a esta página.', 'cdc-admin'));
    }

    cdc_page_wrapper_start(__('Inicio', 'cdc-admin'));
    ?>

    <div class="cdc-dashboard">
        <!-- Search Bar -->
        <div class="cdc-card">
            <div class="cdc-card-body">
                <h3><?php _e('Consulta rápida', 'cdc-admin'); ?></h3>
                <form id="cdc-quick-search" class="cdc-search-form">
                    <div class="cdc-d-flex cdc-gap-1">
                        <input type="text"
                               name="query"
                               id="cdc-search-query"
                               class="cdc-form-control"
                               placeholder="<?php _e('Buscar socio/cliente por Nombre, Apellido o DNI...', 'cdc-admin'); ?>"
                               style="flex: 1;">
                        <button type="submit" class="cdc-button cdc-button-primary">
                            <?php _e('Buscar', 'cdc-admin'); ?>
                        </button>
                    </div>
                </form>
                <div id="cdc-search-results" style="margin-top: 15px;"></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="cdc-card">
            <div class="cdc-card-header">
                <h3 class="cdc-card-title"><?php _e('Acciones rápidas', 'cdc-admin'); ?></h3>
            </div>
            <div class="cdc-card-body">
                <div class="cdc-quick-actions" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">

                    <?php if (CDC_Permissions::can_manage_cobros()): ?>
                    <a href="<?php echo admin_url('admin.php?page=cdc-cobrar'); ?>" class="cdc-button cdc-button-large cdc-button-primary">
                        <span class="dashicons dashicons-money-alt" style="font-size: 40px; width: 40px; height: 40px;"></span>
                        <br>
                        <strong><?php _e('Cobrar', 'cdc-admin'); ?></strong>
                    </a>
                    <?php endif; ?>

                    <?php if (CDC_Permissions::can_register_expenses()): ?>
                    <a href="<?php echo admin_url('admin.php?page=cdc-registrar-gasto'); ?>" class="cdc-button cdc-button-large cdc-button-secondary">
                        <span class="dashicons dashicons-clipboard" style="font-size: 40px; width: 40px; height: 40px;"></span>
                        <br>
                        <strong><?php _e('Registrar gasto', 'cdc-admin'); ?></strong>
                    </a>
                    <?php endif; ?>

                    <?php if (CDC_Permissions::can_manage_personas()): ?>
                    <a href="<?php echo admin_url('admin.php?page=cdc-personas'); ?>" class="cdc-button cdc-button-large cdc-button-secondary">
                        <span class="dashicons dashicons-groups" style="font-size: 40px; width: 40px; height: 40px;"></span>
                        <br>
                        <strong><?php _e('Personas', 'cdc-admin'); ?></strong>
                    </a>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Recent Movements -->
        <div class="cdc-card">
            <div class="cdc-card-header">
                <h3 class="cdc-card-title"><?php _e('Últimos movimientos (hoy)', 'cdc-admin'); ?></h3>
            </div>
            <div class="cdc-card-body">
                <div id="cdc-recent-movements">
                    <p class="cdc-text-center" style="color: #646970;">
                        <?php _e('Cargando movimientos...', 'cdc-admin'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="cdc-card" style="background: #f0f6fc; border-color: #0073aa;">
            <div class="cdc-card-body">
                <h3 style="color: #0073aa;">
                    <span class="dashicons dashicons-info"></span>
                    <?php _e('Bienvenido al Sistema CDC', 'cdc-admin'); ?>
                </h3>
                <p style="margin: 10px 0 0 0; color: #50575e;">
                    <?php _e('Sistema de gestión para Casa de la Cultura. Use el menú lateral para navegar entre las diferentes secciones.', 'cdc-admin'); ?>
                </p>
                <p style="margin: 10px 0 0 0; color: #50575e;">
                    <strong><?php _e('Fase 1 completada:', 'cdc-admin'); ?></strong>
                    <?php _e('Plugin base instalado correctamente. Las funcionalidades completas estarán disponibles en las próximas fases.', 'cdc-admin'); ?>
                </p>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Quick search functionality (placeholder for now)
        $('#cdc-quick-search').on('submit', function(e) {
            e.preventDefault();
            const query = $('#cdc-search-query').val();

            if (query.length < 3) {
                window.cdcAdmin.app.warning('Por favor ingrese al menos 3 caracteres para buscar.');
                return;
            }

            $('#cdc-search-results').html('<p style="color: #0073aa;">Buscando...</p>');

            // TODO: Implement actual search via REST API
            setTimeout(function() {
                $('#cdc-search-results').html('<p style="color: #646970;">La búsqueda estará disponible en la siguiente fase.</p>');
            }, 500);
        });

        // Load recent movements (placeholder for now)
        function loadRecentMovements() {
            // TODO: Implement via REST API
            setTimeout(function() {
                $('#cdc-recent-movements').html(`
                    <p style="color: #646970; text-align: center;">
                        No hay movimientos registrados hoy.<br>
                        <small>Los movimientos se mostrarán aquí una vez que el módulo de Caja esté implementado.</small>
                    </p>
                `);
            }, 500);
        }

        loadRecentMovements();
    });
    </script>

    <?php
    cdc_page_wrapper_end();
}

// Placeholder functions for other pages (will be implemented in future phases)

function cdc_render_personas_page() {
    cdc_page_wrapper_start(__('Personas', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Módulo de Personas - En desarrollo (Fase 2)', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}

function cdc_render_cobrar_page() {
    cdc_page_wrapper_start(__('Cobrar', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Módulo de Cobros - En desarrollo (Fase 4)', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}

function cdc_render_registrar_gasto_page() {
    cdc_page_wrapper_start(__('Registrar Gasto', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Módulo de Gastos - En desarrollo (Fase 3)', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}

function cdc_render_caja_page() {
    cdc_page_wrapper_start(__('Caja', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Módulo de Caja - En desarrollo (Fase 3)', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}

function cdc_render_talleres_page() {
    cdc_page_wrapper_start(__('Talleres', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Módulo de Talleres - En desarrollo (Fase 7)', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}

function cdc_render_eventos_page() {
    cdc_page_wrapper_start(__('Eventos', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Módulo de Eventos - En desarrollo (Fase 7)', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}

function cdc_render_alquiler_salas_page() {
    cdc_page_wrapper_start(__('Alquiler de Salas', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Módulo de Alquiler de Salas - En desarrollo (Fase 7)', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}

function cdc_render_salas_page() {
    cdc_page_wrapper_start(__('Salas', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Módulo de Salas - En desarrollo (Fase 7)', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}

function cdc_render_configuracion_page() {
    cdc_page_wrapper_start(__('Configuración', 'cdc-admin'));
    echo '<div class="cdc-card"><div class="cdc-card-body">';
    echo '<p>' . __('Configuración del Sistema - En desarrollo', 'cdc-admin') . '</p>';
    echo '</div></div>';
    cdc_page_wrapper_end();
}
