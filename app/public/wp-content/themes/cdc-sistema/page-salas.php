<?php
/**
 * Template Name: Salas
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-salas">
    <!-- Header -->
    <div class="cdc-page-header">
        <div class="cdc-page-header-actions">
            <button class="cdc-button cdc-button-primary" id="cdc-nueva-sala">
                <span class="dashicons dashicons-plus"></span> Nueva sala
            </button>
        </div>
    </div>

    <!-- Results Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div id="cdc-salas-results">
                <p class="cdc-text-center cdc-text-muted">Cargando salas...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function loadSalas() {
        $('#cdc-salas-results').html(`
            <div class="cdc-table-wrapper">
                <table class="cdc-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Capacidad</th>
                            <th>Precio/hora</th>
                            <th>Equipamiento</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="cdc-text-center cdc-text-muted">
                                No hay salas registradas.<br>
                                <small>Las salas se mostrarán aquí una vez que se creen en el sistema.</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `);
    }

    $('#cdc-nueva-sala').on('click', function() {
        alert('Formulario de nueva sala próximamente');
    });

    loadSalas();
});
</script>

<?php get_footer(); ?>
