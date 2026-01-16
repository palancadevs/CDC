<?php
/**
 * Front Page - Dashboard CDC
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-dashboard">
    <!-- Search Bar -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <h3>Consulta rápida</h3>
            <form id="cdc-quick-search" class="cdc-search-form">
                <div class="cdc-search-wrapper">
                    <input type="text"
                           name="query"
                           id="cdc-search-query"
                           class="cdc-form-control"
                           placeholder="Buscar socio/cliente por Nombre, Apellido o DNI...">
                    <button type="submit" class="cdc-button cdc-button-primary">
                        Buscar
                    </button>
                </div>
            </form>
            <div id="cdc-search-results"></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Acciones rápidas</h3>
        </div>
        <div class="cdc-card-body">
            <div class="cdc-quick-actions">
                <a href="<?php echo home_url('/cobrar'); ?>" class="cdc-action-card cdc-action-primary">
                    <span class="dashicons dashicons-money-alt"></span>
                    <strong>Cobrar</strong>
                </a>

                <a href="<?php echo home_url('/registrar-gasto'); ?>" class="cdc-action-card cdc-action-secondary">
                    <span class="dashicons dashicons-clipboard"></span>
                    <strong>Registrar gasto</strong>
                </a>

                <a href="<?php echo home_url('/personas'); ?>" class="cdc-action-card cdc-action-secondary">
                    <span class="dashicons dashicons-groups"></span>
                    <strong>Personas</strong>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Últimos movimientos (hoy)</h3>
        </div>
        <div class="cdc-card-body">
            <div id="cdc-recent-movements">
                <p class="cdc-text-center cdc-text-muted">
                    Cargando movimientos...
                </p>
            </div>
        </div>
    </div>

    <!-- Welcome Message -->
    <div class="cdc-card cdc-card-info">
        <div class="cdc-card-body">
            <h3>
                <span class="dashicons dashicons-info"></span>
                Bienvenido al Sistema CDC
            </h3>
            <p>
                Sistema de gestión para Casa de la Cultura. Use el menú lateral para navegar entre las diferentes secciones.
            </p>
            <p>
                <strong>Estado:</strong> Frontend funcional - Fase 1 implementada.<br>
                <strong>Pendiente:</strong> Integración con API REST (plugin cdc-api).
            </p>
        </div>
    </div>
</div>

<?php get_footer(); ?>
