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
    <!-- Page Title -->
    <div class="cdc-page-header">
        <h1 class="cdc-page-title">Inicio</h1>
    </div>

    <!-- Search Bar -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <h3 style="margin-bottom: 15px;">Consulta rápida</h3>
            <form id="cdc-quick-search" class="cdc-search-form">
                <div class="cdc-search-wrapper">
                    <input type="text"
                           name="query"
                           id="cdc-search-query"
                           class="cdc-form-control"
                           placeholder="Buscar socio/cliente por Nombre, Apellido o DNI..."
                           autocomplete="off">
                    <button type="submit" class="cdc-button cdc-button-primary">
                        <span class="dashicons dashicons-search"></span> Buscar
                    </button>
                </div>
            </form>
            <div id="cdc-search-results" style="margin-top: 15px;"></div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="cdc-dashboard-summary">
        <div class="cdc-summary-card cdc-summary-ingresos">
            <div class="cdc-summary-icon">
                <span class="dashicons dashicons-arrow-down-alt"></span>
            </div>
            <div class="cdc-summary-content">
                <span class="cdc-summary-label">Ingresos hoy</span>
                <span class="cdc-summary-value" id="cdc-ingresos-hoy">$0.00</span>
            </div>
        </div>

        <div class="cdc-summary-card cdc-summary-egresos">
            <div class="cdc-summary-icon">
                <span class="dashicons dashicons-arrow-up-alt"></span>
            </div>
            <div class="cdc-summary-content">
                <span class="cdc-summary-label">Egresos hoy</span>
                <span class="cdc-summary-value" id="cdc-egresos-hoy">$0.00</span>
            </div>
        </div>

        <div class="cdc-summary-card cdc-summary-balance">
            <div class="cdc-summary-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="cdc-summary-content">
                <span class="cdc-summary-label">Balance actual</span>
                <span class="cdc-summary-value" id="cdc-balance-actual">$0.00</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Acciones rápidas</h3>
        </div>
        <div class="cdc-card-body">
            <div class="cdc-quick-actions">
                <a href="<?php echo esc_url(home_url('/cobrar')); ?>" class="cdc-action-card cdc-action-primary">
                    <span class="dashicons dashicons-money-alt"></span>
                    <strong>Cobrar</strong>
                    <small>Registrar un cobro</small>
                </a>

                <a href="<?php echo esc_url(home_url('/registrar-gasto')); ?>" class="cdc-action-card cdc-action-secondary">
                    <span class="dashicons dashicons-clipboard"></span>
                    <strong>Registrar gasto</strong>
                    <small>Registrar un egreso</small>
                </a>

                <a href="<?php echo esc_url(home_url('/personas')); ?>" class="cdc-action-card cdc-action-secondary">
                    <span class="dashicons dashicons-groups"></span>
                    <strong>Personas</strong>
                    <small>Ver socios y clientes</small>
                </a>

                <a href="<?php echo esc_url(home_url('/talleres')); ?>" class="cdc-action-card cdc-action-secondary">
                    <span class="dashicons dashicons-book-alt"></span>
                    <strong>Talleres</strong>
                    <small>Gestionar talleres</small>
                </a>

                <a href="<?php echo esc_url(home_url('/salas')); ?>" class="cdc-action-card cdc-action-secondary">
                    <span class="dashicons dashicons-admin-multisite"></span>
                    <strong>Salas</strong>
                    <small>Gestionar salas</small>
                </a>

                <a href="<?php echo esc_url(home_url('/caja')); ?>" class="cdc-action-card cdc-action-secondary">
                    <span class="dashicons dashicons-calculator"></span>
                    <strong>Caja</strong>
                    <small>Ver movimientos</small>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Últimos movimientos (hoy)</h3>
            <a href="<?php echo esc_url(home_url('/caja')); ?>" class="cdc-button cdc-button-small">Ver todos</a>
        </div>
        <div class="cdc-card-body">
            <div id="cdc-recent-movements">
                <p class="cdc-text-center cdc-text-muted">Cargando movimientos...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Quick search
    $('#cdc-quick-search').on('submit', function(e) {
        e.preventDefault();
        const query = $('#cdc-search-query').val().trim();
        const $results = $('#cdc-search-results');

        if (query.length < 3) {
            CDC.showNotification('Ingrese al menos 3 caracteres', 'warning');
            return;
        }

        $results.html('<p class="cdc-text-muted">Buscando...</p>');

        CDCAPI.personas.search(query)
            .then(function(response) {
                if (response.success) {
                    if (response.data && response.data.length > 0) {
                        let html = '<div class="cdc-search-results-list">';
                        response.data.forEach(function(person) {
                            const tipo = person.tipo === 'socio' ? 'Socio' : 'Cliente';
                            const tipoBadge = person.tipo === 'socio' ? 'cdc-badge-primary' : 'cdc-badge-secondary';

                            html += `<div class="cdc-search-result-item">
                                <div class="cdc-search-result-info">
                                    <strong>${person.nombre} ${person.apellido}</strong>
                                    <span class="cdc-badge ${tipoBadge}">${tipo}</span>
                                    <br>
                                    <small>DNI: ${person.dni}${person.telefono ? ' - Tel: ' + person.telefono : ''}</small>
                                </div>
                                <div class="cdc-search-result-actions">
                                    <a href="${cdcData.homeUrl}/persona?id=${person.id}" class="cdc-button cdc-button-small">Ver ficha</a>
                                </div>
                            </div>`;
                        });
                        html += '</div>';
                        $results.html(html);
                    } else {
                        $results.html('<p class="cdc-text-muted">No se encontraron resultados.</p>');
                    }
                } else {
                    CDC.handleApiError(response, 'Quick Search');
                }
            })
            .catch(function(error) {
                $results.html('<p style="color: #d63638;">Error en la búsqueda.</p>');
                CDC.handleApiError(error, 'Quick Search');
            });
    });

    // Load today's summary
    function loadSummary() {
        const today = new Date().toISOString().split('T')[0];

        CDCAPI.caja.resumen({ fecha_desde: today, fecha_hasta: today })
            .then(function(response) {
                if (response.success && response.data) {
                    const ingresos = parseFloat(response.data.total_ingresos || 0).toFixed(2);
                    const egresos = parseFloat(response.data.total_egresos || 0).toFixed(2);
                    const balance = parseFloat(response.data.saldo_actual || 0).toFixed(2);

                    $('#cdc-ingresos-hoy').text('$' + ingresos);
                    $('#cdc-egresos-hoy').text('$' + egresos);
                    $('#cdc-balance-actual').text('$' + balance);
                }
            })
            .catch(function(error) {
                console.error('Error loading summary:', error);
            });
    }

    // Load recent movements
    function loadRecentMovements() {
        const today = new Date().toISOString().split('T')[0];

        CDCAPI.caja.movimientos({ fecha_desde: today, fecha_hasta: today })
            .then(function(response) {
                if (response.success) {
                    renderRecentMovements(response.data);
                }
            })
            .catch(function(error) {
                $('#cdc-recent-movements').html('<p style="color: #d63638;">Error al cargar movimientos.</p>');
            });
    }

    // Render recent movements
    function renderRecentMovements(movements) {
        const $container = $('#cdc-recent-movements');

        if (!movements || movements.length === 0) {
            $container.html('<p class="cdc-text-center cdc-text-muted">No hay movimientos registrados hoy.</p>');
            return;
        }

        // Show last 10 movements
        const recent = movements.slice(0, 10);
        let html = '<div class="cdc-table-wrapper"><table class="cdc-table">';
        html += '<thead><tr><th>Hora</th><th>Tipo</th><th>Concepto</th><th>Monto</th></tr></thead><tbody>';

        recent.forEach(function(mov) {
            const fecha = new Date(mov.fecha_movimiento);
            const hora = fecha.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
            const tipoClass = mov.tipo === 'ingreso' ? 'cdc-badge-success' : 'cdc-badge-danger';
            const monto = parseFloat(mov.monto || 0).toFixed(2);
            const montoClass = mov.tipo === 'ingreso' ? 'cdc-text-success' : 'cdc-text-danger';
            const montoPrefix = mov.tipo === 'ingreso' ? '+' : '-';

            html += `<tr>
                <td>${hora}</td>
                <td><span class="cdc-badge ${tipoClass}">${mov.tipo}</span></td>
                <td>${mov.concepto}</td>
                <td class="${montoClass}"><strong>${montoPrefix}$${monto}</strong></td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        $container.html(html);
    }

    // Initial load
    loadSummary();
    loadRecentMovements();
});
</script>

<style>
.cdc-dashboard-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.cdc-summary-card {
    display: flex;
    align-items: center;
    padding: 20px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cdc-summary-ingresos {
    border-left: 4px solid #28a745;
}

.cdc-summary-egresos {
    border-left: 4px solid #dc3545;
}

.cdc-summary-balance {
    border-left: 4px solid #007bff;
}

.cdc-summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    font-size: 24px;
}

.cdc-summary-ingresos .cdc-summary-icon {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.cdc-summary-egresos .cdc-summary-icon {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.cdc-summary-balance .cdc-summary-icon {
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.cdc-summary-content {
    display: flex;
    flex-direction: column;
}

.cdc-summary-label {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.cdc-summary-value {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a1a;
}

.cdc-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
}

.cdc-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 20px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
    text-align: center;
    border: 2px solid #e0e0e0;
    background: #fff;
}

.cdc-action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.cdc-action-primary {
    background: #007bff;
    color: #fff;
    border-color: #007bff;
}

.cdc-action-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    color: #fff;
}

.cdc-action-card .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
    margin-bottom: 10px;
}

.cdc-action-card strong {
    display: block;
    font-size: 16px;
    margin-bottom: 5px;
}

.cdc-action-card small {
    font-size: 12px;
    color: #666;
}

.cdc-action-primary small {
    color: rgba(255,255,255,0.8);
}

.cdc-search-results-list {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.cdc-search-result-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.cdc-search-result-item:last-child {
    border-bottom: none;
}

.cdc-search-result-item:hover {
    background: #f9f9f9;
}

.cdc-search-result-info {
    flex: 1;
}

.cdc-search-result-actions {
    margin-left: 15px;
}

.cdc-text-success {
    color: #28a745;
}

.cdc-text-danger {
    color: #dc3545;
}

.cdc-badge-success {
    background: #d4edda;
    color: #155724;
}

.cdc-badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.cdc-badge-primary {
    background: #cce5ff;
    color: #004085;
}

.cdc-badge-secondary {
    background: #e2e3e5;
    color: #383d41;
}
</style>

<?php get_footer(); ?>
