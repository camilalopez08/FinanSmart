<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener datos del usuario
$id_usuario = $_SESSION["id"];

// Definir período de reporte
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mes';

// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "includes/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Reportes Financieros</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <select class="form-select form-select-sm" id="periodoSelect">
                            <option value="semana" <?php echo $periodo == 'semana' ? 'selected' : ''; ?>>Esta semana</option>
                            <option value="mes" <?php echo $periodo == 'mes' ? 'selected' : ''; ?>>Este mes</option>
                            <option value="trimestre" <?php echo $periodo == 'trimestre' ? 'selected' : ''; ?>>Este trimestre</option>
                            <option value="año" <?php echo $periodo == 'año' ? 'selected' : ''; ?>>Este año</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="exportarBtn">
                        <i class="fas fa-download me-1"></i> Exportar
                    </button>
                </div>
            </div>
            
            <ul class="nav nav-tabs mb-4" id="reportesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active d-flex align-items-center" id="gastos-tab" data-bs-toggle="tab" data-bs-target="#gastos" type="button" role="tab" aria-controls="gastos" aria-selected="true">
                        <i class="fas fa-chart-pie me-2"></i> Gastos por Categoría
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center" id="comparacion-tab" data-bs-toggle="tab" data-bs-target="#comparacion" type="button" role="tab" aria-controls="comparacion" aria-selected="false">
                        <i class="fas fa-chart-bar me-2"></i> Ingresos vs Gastos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center" id="tendencia-tab" data-bs-toggle="tab" data-bs-target="#tendencia" type="button" role="tab" aria-controls="tendencia" aria-selected="false">
                        <i class="fas fa-chart-line me-2"></i> Tendencia de Ahorro
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="reportesTabContent">
                <div class="tab-pane fade show active" id="gastos" role="tabpanel" aria-labelledby="gastos-tab">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Distribución de Gastos por Categoría</h5>
                            <p class="card-text text-muted">
                                Visualiza cómo se distribuyen tus gastos entre las diferentes categorías durante 
                                <?php 
                                    echo $periodo == 'semana' ? 'esta semana' : 
                                        ($periodo == 'mes' ? 'este mes' : 
                                        ($periodo == 'trimestre' ? 'este trimestre' : 'este año')); 
                                ?>
                            </p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    <canvas id="gastosPorCategoriaChart" height="300"></canvas>
                                </div>
                                <div class="col-lg-4">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Categoría</th>
                                                    <th>Monto</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody id="gastosPorCategoriaTabla">
                                                <!-- Los datos se cargarán dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="comparacion" role="tabpanel" aria-labelledby="comparacion-tab">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Comparación de Ingresos vs Gastos</h5>
                            <p class="card-text text-muted">
                                Compara tus ingresos y gastos durante 
                                <?php 
                                    echo $periodo == 'semana' ? 'esta semana' : 
                                        ($periodo == 'mes' ? 'este mes' : 
                                        ($periodo == 'trimestre' ? 'este trimestre' : 'este año')); 
                                ?>
                            </p>
                        </div>
                        <div class="card-body">
                            <canvas id="ingresosVsGastosChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tendencia" role="tabpanel" aria-labelledby="tendencia-tab">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Tendencia de Ahorro</h5>
                            <p class="card-text text-muted">
                                Visualiza la evolución de tus ahorros durante 
                                <?php 
                                    echo $periodo == 'semana' ? 'esta semana' : 
                                        ($periodo == 'mes' ? 'este mes' : 
                                        ($periodo == 'trimestre' ? 'este trimestre' : 'este año')); 
                                ?>
                            </p>
                        </div>
                        <div class="card-body">
                            <canvas id="tendenciaAhorroChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Resumen Financiero</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h6 class="text-muted mb-2">Ingresos Totales</h6>
                                <h3 class="text-success mb-0">$2,500.00</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h6 class="text-muted mb-2">Gastos Totales</h6>
                                <h3 class="text-danger mb-0">$1,850.00</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h6 class="text-muted mb-2">Balance</h6>
                                <h3 class="mb-0">$650.00</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h6 class="text-muted mb-2">Tasa de Ahorro</h6>
                                <h3 class="text-primary mb-0">26%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Cambiar período de reporte
document.getElementById('periodoSelect').addEventListener('change', function() {
    window.location.href = 'reportes.php?periodo=' + this.value;
});

// Datos de ejemplo para los gráficos
var gastosPorCategoria = {
    labels: ['Alimentación', 'Transporte', 'Entretenimiento', 'Servicios', 'Salud', 'Educación', 'Otros'],
    data: [450, 300, 250, 400, 150, 200, 100]
};

var ingresosVsGastosPorMes = {
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
    ingresos: [1200, 1900, 1500, 2000, 2200, 2500],
    gastos: [900, 1200, 1300, 1400, 1600, 1850]
};

var tendenciaAhorro = {
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
    data: [300, 700, 900, 1500, 2100, 2750]
};

// Gráfico de Gastos por Categoría
var ctx1 = document.getElementById('gastosPorCategoriaChart').getContext('2d');
var gastosPorCategoriaChart = new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: gastosPorCategoria.labels,
        datasets: [{
            data: gastosPorCategoria.data,
            backgroundColor: [
                'rgba(40, 167, 69, 0.7)',
                'rgba(0, 123, 255, 0.7)',
                'rgba(255, 193, 7, 0.7)',
                'rgba(23, 162, 184, 0.7)',
                'rgba(108, 117, 125, 0.7)',
                'rgba(111, 66, 193, 0.7)',
                'rgba(220, 53, 69, 0.7)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(0, 123, 255, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(108, 117, 125, 1)',
                'rgba(111, 66, 193, 1)',
                'rgba(220, 53, 69, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});

// Llenar tabla de gastos por categoría
var totalGastos = gastosPorCategoria.data.reduce((a, b) => a + b, 0);
var tablaGastos = document.getElementById('gastosPorCategoriaTabla');
for (var i = 0; i < gastosPorCategoria.labels.length; i++) {
    var porcentaje = ((gastosPorCategoria.data[i] / totalGastos) * 100).toFixed(1);
    var fila = document.createElement('tr');
    fila.innerHTML = `
        <td>${gastosPorCategoria.labels[i]}</td>
        <td>$${gastosPorCategoria.data[i].toFixed(2)}</td>
        <td>${porcentaje}%</td>
    `;
    tablaGastos.appendChild(fila);
}

// Gráfico de Ingresos vs Gastos
var ctx2 = document.getElementById('ingresosVsGastosChart').getContext('2d');
var ingresosVsGastosChart = new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: ingresosVsGastosPorMes.labels,
        datasets: [
            {
                label: 'Ingresos',
                data: ingresosVsGastosPorMes.ingresos,
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            },
            {
                label: 'Gastos',
                data: ingresosVsGastosPorMes.gastos,
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});

// Gráfico de Tendencia de Ahorro
var ctx3 = document.getElementById('tendenciaAhorroChart').getContext('2d');
var tendenciaAhorroChart = new Chart(ctx3, {
    type: 'line',
    data: {
        labels: tendenciaAhorro.labels,
        datasets: [{
            label: 'Ahorro Acumulado',
            data: tendenciaAhorro.data,
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderColor: 'rgba(0, 123, 255, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});

// Exportar reportes
document.getElementById('exportarBtn').addEventListener('click', function() {
    // Aquí iría el código para exportar los reportes
    alert('Exportando reportes...');
});
</script>

<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
