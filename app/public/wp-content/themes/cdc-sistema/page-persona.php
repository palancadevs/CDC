<?php
/**
 * Template Name: Persona (Ficha)
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get persona ID from URL parameter
$persona_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$persona_id) {
    echo '<div class="cdc-persona"><p>ID de persona no especificado.</p></div>';
    get_footer();
    exit;
}
?>

<div class="cdc-persona" id="cdc-persona-detail">
    <p class="cdc-text-center cdc-text-muted">Cargando información...</p>
</div>

<script>
jQuery(document).ready(function($) {
    const personaId = <?php echo $persona_id; ?>;

    // Load persona data
    CDCAPI.personas.get(personaId)
        .then(function(response) {
            if (response.success) {
                renderPersona(response.data);
            } else {
                $('#cdc-persona-detail').html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar la persona.</p>');
                CDC.handleApiError(response, 'Load Persona');
            }
        })
        .catch(function(error) {
            $('#cdc-persona-detail').html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar la persona.</p>');
            CDC.handleApiError(error, 'Load Persona');
        });

    function renderPersona(persona) {
        let html = '';

        // Header with back and edit buttons
        html += `<div class="cdc-page-header">
            <div>
                <h1 class="cdc-page-title">${persona.nombre} ${persona.apellido}</h1>
                <span class="cdc-badge ${persona.tipo === 'socio' ? 'cdc-badge-primary' : 'cdc-badge-info'}">
                    ${persona.tipo === 'socio' ? 'Socio' : 'Cliente'}
                </span>
            </div>
            <div class="cdc-page-header-actions">
                <a href="${cdcData.homeUrl}/editar-persona?id=${persona.id}" class="cdc-button cdc-button-primary">
                    <span class="dashicons dashicons-edit"></span> Editar
                </a>
                <a href="${cdcData.homeUrl}/personas" class="cdc-button cdc-button-secondary">
                    <span class="dashicons dashicons-arrow-left-alt2"></span> Volver
                </a>
            </div>
        </div>`;

        // Personal info card
        html += `<div class="cdc-card">
            <div class="cdc-card-header">
                <h3 class="cdc-card-title">Información Personal</h3>
            </div>
            <div class="cdc-card-body">
                <div class="cdc-info-grid">
                    <div class="cdc-info-item">
                        <strong>DNI:</strong>
                        <span>${persona.dni}</span>
                    </div>
                    <div class="cdc-info-item">
                        <strong>Teléfono:</strong>
                        <span>${persona.telefono || '-'}</span>
                    </div>
                    <div class="cdc-info-item">
                        <strong>Email:</strong>
                        <span>${persona.email || '-'}</span>
                    </div>
                    <div class="cdc-info-item">
                        <strong>Dirección:</strong>
                        <span>${persona.direccion || '-'}</span>
                    </div>
                </div>
            </div>
        </div>`;

        // If socio, load cuotas
        if (persona.tipo === 'socio' || persona.tipo === 'ambos') {
            html += `<div class="cdc-card">
                <div class="cdc-card-header">
                    <h3 class="cdc-card-title">Estado de Cuotas 2026</h3>
                    <div id="cdc-cuotas-estado"></div>
                </div>
                <div class="cdc-card-body">
                    <div id="cdc-cuotas-grid">
                        <p class="cdc-text-center cdc-text-muted">Cargando cuotas...</p>
                    </div>
                </div>
            </div>`;
        }

        $('#cdc-persona-detail').html(html);

        // Load cuotas if socio
        if (persona.tipo === 'socio' || persona.tipo === 'ambos') {
            loadCuotas(personaId);
        }
    }

    function loadCuotas(personaId) {
        CDCAPI.personas.cuotas(personaId, 2026)
            .then(function(response) {
                if (response.success) {
                    renderCuotasGrid(response.data, personaId);
                } else {
                    $('#cdc-cuotas-grid').html('<p class="cdc-text-muted">Error al cargar cuotas.</p>');
                }
            })
            .catch(function(error) {
                $('#cdc-cuotas-grid').html('<p class="cdc-text-muted">Error al cargar cuotas.</p>');
                console.error('Error loading cuotas:', error);
            });
    }

    function renderCuotasGrid(cuotas, personaId) {
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        // Create 12-month grid
        const grid = {};
        for (let i = 1; i <= 12; i++) {
            grid[i] = null;
        }

        // Fill with existing cuotas
        cuotas.forEach(function(cuota) {
            grid[parseInt(cuota.mes)] = cuota;
        });

        // Calculate status
        const currentMonth = new Date().getMonth() + 1;
        let mesesDebe = 0;
        for (let i = 1; i <= currentMonth; i++) {
            const cuota = grid[i];
            if (!cuota || cuota.pagada == '0') {
                mesesDebe++;
            }
        }

        // Show status
        let statusHtml = '';
        if (mesesDebe === 0) {
            statusHtml = '<span class="cdc-badge cdc-badge-success">Al día</span>';
        } else {
            statusHtml = `<span class="cdc-badge cdc-badge-warning">Debe ${mesesDebe} mes(es)</span>`;
        }
        statusHtml += ` <a href="${cdcData.homeUrl}/cobrar" class="cdc-button cdc-button-small cdc-button-primary" style="margin-left: 10px;">Cobrar cuota</a>`;
        $('#cdc-cuotas-estado').html(statusHtml);

        // Render grid
        let html = '<div class="cdc-cuotas-grid-container">';
        html += '<div class="cdc-cuotas-grid">';

        for (let mes = 1; mes <= 12; mes++) {
            const cuota = grid[mes];
            let cardClass = 'cdc-cuota-card';
            let statusText = 'Pendiente';
            let statusClass = 'cdc-status-pendiente';

            if (cuota && cuota.pagada == '1') {
                cardClass += ' cdc-cuota-pagada';
                statusText = 'Pagada';
                statusClass = 'cdc-status-pagada';
            } else if (mes < currentMonth) {
                cardClass += ' cdc-cuota-vencida';
                statusText = 'Vencida';
                statusClass = 'cdc-status-vencida';
            }

            const monto = cuota ? parseFloat(cuota.monto) : 0;

            html += `<div class="${cardClass}">
                <div class="cdc-cuota-mes">${meses[mes - 1]}</div>
                <div class="cdc-cuota-monto">$${monto.toFixed(2)}</div>
                <div class="cdc-cuota-status ${statusClass}">${statusText}</div>
            </div>`;
        }

        html += '</div></div>';
        $('#cdc-cuotas-grid').html(html);
    }
});
</script>

<style>
.cdc-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.cdc-info-item {
    display: flex;
    gap: 10px;
}

.cdc-info-item strong {
    min-width: 80px;
}

.cdc-cuotas-grid-container {
    overflow-x: auto;
}

.cdc-cuotas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 15px;
    min-width: 600px;
}

.cdc-cuota-card {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s;
}

.cdc-cuota-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cdc-cuota-pagada {
    background: #e8f5e9;
    border-color: #4caf50;
}

.cdc-cuota-vencida {
    background: #ffebee;
    border-color: #f44336;
}

.cdc-cuota-mes {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    color: #333;
}

.cdc-cuota-monto {
    font-size: 18px;
    font-weight: 700;
    color: #2196f3;
    margin-bottom: 8px;
}

.cdc-cuota-status {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
    display: inline-block;
}

.cdc-status-pagada {
    background: #4caf50;
    color: white;
}

.cdc-status-pendiente {
    background: #ff9800;
    color: white;
}

.cdc-status-vencida {
    background: #f44336;
    color: white;
}

.cdc-page-header {
    margin-bottom: 20px;
}
</style>

<?php get_footer(); ?>
