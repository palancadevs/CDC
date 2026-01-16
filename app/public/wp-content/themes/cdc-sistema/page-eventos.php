<?php
/**
 * Template Name: Eventos
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-eventos">
    <!-- Header -->
    <div class="cdc-page-header">
        <div class="cdc-page-header-actions">
            <button class="cdc-button cdc-button-primary" id="cdc-nuevo-evento">
                <span class="dashicons dashicons-plus"></span> Nuevo evento
            </button>
        </div>
    </div>

    <!-- Results Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div id="cdc-eventos-results">
                <p class="cdc-text-center cdc-text-muted">Cargando eventos...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function loadEventos() {
        $('#cdc-eventos-results').html(`
            <div class="cdc-table-wrapper">
                <table class="cdc-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Precio</th>
                            <th>Inscriptos</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="cdc-text-center cdc-text-muted">
                                No hay eventos programados.<br>
                                <small>Los eventos se mostrarán aquí una vez que se creen en el sistema.</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `);
    }

    $('#cdc-nuevo-evento').on('click', function() {
        alert('Formulario de nuevo evento próximamente');
    });

    loadEventos();
});
</script>

<?php get_footer(); ?>
