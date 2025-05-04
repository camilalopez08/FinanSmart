<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener datos del usuario
$id_usuario = $_SESSION["id"];

// Obtener saldo total
$sql_saldo = "SELECT 
                (SELECT COALESCE(SUM(monto), 0) FROM transacciones WHERE id_usuario = ? AND tipo = 'ingreso') - 
                (SELECT COALESCE(SUM(monto), 0) FROM transacciones WHERE id_usuario = ? AND tipo = 'gasto') AS saldo";
$stmt_saldo = mysqli_prepare($conn, $sql_saldo);
mysqli_stmt_bind_param($stmt_saldo, "ii", $id_usuario, $id_usuario);
mysqli_stmt_execute($stmt_saldo);
mysqli_stmt_bind_result($stmt_saldo, $saldo);
mysqli_stmt_fetch($stmt_saldo);
mysqli_stmt_close($stmt_saldo);

// Obtener ingresos totales
$sql_ingresos = "SELECT COALESCE(SUM(monto), 0) AS total FROM transacciones WHERE id_usuario = ? AND tipo = 'ingreso'";
$stmt_ingresos = mysqli_prepare($conn, $sql_ingresos);
mysqli_stmt_bind_param($stmt_ingresos, "i", $id_usuario);
mysqli_stmt_execute($stmt_ingresos);
mysqli_stmt_bind_result($stmt_ingresos, $ingresos);
mysqli_stmt_fetch($stmt_ingresos);
mysqli_stmt_close($stmt_ingresos);

// Obtener gastos totales
$sql_gastos = "SELECT COALESCE(SUM(monto), 0) AS total FROM transacciones WHERE id_usuario = ? AND tipo = 'gasto'";
$stmt_gastos = mysqli_prepare($conn, $sql_gastos);
mysqli_stmt_bind_param($stmt_gastos, "i", $id_usuario);
mysqli_stmt_execute($stmt_gastos);
mysqli_stmt_bind_result($stmt_gastos, $gastos);
mysqli_stmt_fetch($stmt_gastos);
mysqli_stmt_close($stmt_gastos);

// Obtener ahorros totales
$sql_ahorros = "SELECT COALESCE(SUM(monto_actual), 0) AS total FROM metas WHERE id_usuario = ?";
$stmt_ahorros = mysqli_prepare($conn, $sql_ahorros);
mysqli_stmt_bind_param($stmt_ahorros, "i", $id_usuario);
mysqli_stmt_execute($stmt_ahorros);
mysqli_stmt_bind_result($stmt_ahorros, $ahorros);
mysqli_stmt_fetch($stmt_ahorros);
mysqli_stmt_close($stmt_ahorros);

// Obtener transacciones recientes
$sql_transacciones = "SELECT id, tipo, monto, categoria, descripcion, fecha FROM transacciones 
                      WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 5";
$stmt_transacciones = mysqli_prepare($conn, $sql_transacciones);
mysqli_stmt_bind_param($stmt_transacciones, "i", $id_usuario);
mysqli_stmt_execute($stmt_transacciones);
$resultado_transacciones = mysqli_stmt_get_result($stmt_transacciones);
$transacciones = mysqli_fetch_all($resultado_transacciones, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_transacciones);

// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "includes/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Panel Principal</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-success d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#nuevaTransaccionModal">
                        <i class="fas fa-plus me-2"></i> Nueva Transacción
                    </button>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1">Saldo Total</h6>
                                    <h3 class="mb-0">$<?php echo number_format($saldo, 2); ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-2 rounded">
                                    <i class="fas fa-wallet text-success"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-arrow-up me-1"></i> 20.1%
                                </span>
                                <span class="text-muted ms-2 small">desde el mes pasado</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1">Ingresos</h6>
                                    <h3 class="text-success mb-0">$<?php echo number_format($ingresos, 2); ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-2 rounded">
                                    <i class="fas fa-arrow-up text-success"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-arrow-up me-1"></i> 10.5%
                                </span>
                                <span class="text-muted ms-2 small">desde el mes pasado</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1">Gastos</h6>
                                    <h3 class="text-danger mb-0">$<?php echo number_format($gastos, 2); ?></h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-2 rounded">
                                    <i class="fas fa-arrow-down text-danger"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    <i class="fas fa-arrow-up me-1"></i> 12.3%
                                </span>
                                <span class="text-muted ms-2 small">desde el mes pasado</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1">Ahorros</h6>
                                    <h3 class="mb-0">$<?php echo number_format($ahorros, 2); ?></h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-2 rounded">
                                    <i class="fas fa-bullseye text-info"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-arrow-up me-1"></i> 35.2%
                                </span>
                                <span class="text-muted ms-2 small">desde el mes pasado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Ingresos vs Gastos</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    Este Mes
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><a class="dropdown-item" href="#">Esta Semana</a></li>
                                    <li><a class="dropdown-item" href="#">Este Mes</a></li>
                                    <li><a class="dropdown-item" href="#">Este Año</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="ingresosGastosChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Distribución de Gastos</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="gastosChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Transacciones Recientes</h5>
                            <a href="transacciones.php" class="btn btn-sm btn-outline-secondary">Ver Todas</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Descripción</th>
                                            <th>Categoría</th>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($transacciones)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-3 text-muted">No hay transacciones para mostrar</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($transacciones as $transaccion): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($transaccion['descripcion']); ?></td>
                                                    <td><?php echo htmlspecialchars($transaccion['categoria']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($transaccion['fecha'])); ?></td>
                                                    <td class="<?php echo $transaccion['tipo'] == 'ingreso' ? 'text-success' : 'text-danger'; ?> fw-bold">
                                                        <?php echo $transaccion['tipo'] == 'ingreso' ? '+' : '-'; ?>$<?php echo number_format($transaccion['monto'], 2); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Metas de Ahorro</h5>
                            <a href="metas.php" class="btn btn-sm btn-outline-secondary">Ver Todas</a>
                        </div>
                        <div class="card-body">
                            <div id="metasContainer">
                                <!-- Las metas se cargarán aquí -->
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-bullseye fa-3x mb-3 opacity-50"></i>
                                    <p>No hay metas de ahorro para mostrar</p>
                                    <a href="metas.php" class="btn btn-sm btn-success mt-2">
                                        <i class="fas fa-plus me-2"></i> Crear Meta
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Nueva Transacción -->
<div class="modal fade" id="nuevaTransaccionModal" tabindex="-1" aria-labelledby="nuevaTransaccionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaTransaccionModalLabel">Nueva Transacción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="nuevaTransaccionForm">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Transacción</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="gasto">Gasto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="categoria" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <!-- Las categorías se cargarán dinámicamente según el tipo -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="guardarTransaccion">Guardar Transacción</button>
            </div>
        </div>
    </div>
</div>

<script>
// Gráfico de Ingresos vs Gastos
var ctx1 = document.getElementById('ingresosGastosChart').getContext('2d');
var ingresosGastosChart = new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        datasets: [
            {
                label: 'Ingresos',
                data: [1200, 1900, 1500, 2000, 2200, 2500],
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            },
            {
                label: 'Gastos',
                data: [900, 1200, 1300, 1400, 1600, 1850],
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

// Gráfico de Distribución de Gastos
var ctx2 = document.getElementById('gastosChart').getContext('2d');
var gastosChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Alimentación', 'Transporte', 'Entretenimiento', 'Servicios', 'Otros'],
        datasets: [{
            data: [450, 300, 250, 400, 450],
            backgroundColor: [
                'rgba(40, 167, 69, 0.7)',
                'rgba(0, 123, 255, 0.7)',
                'rgba(255, 193, 7, 0.7)',
                'rgba(23, 162, 184, 0.7)',
                'rgba(108, 117, 125, 0.7)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(0, 123, 255, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(108, 117, 125, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Cargar categorías según el tipo de transacción
document.getElementById('tipo').addEventListener('change', function() {
    var tipo = this.value;
    var categoriaSelect = document.getElementById('categoria');
    
    // Limpiar opciones actuales
    categoriaSelect.innerHTML = '<option value="">Seleccionar categoría</option>';
    
    if (tipo === 'ingreso') {
        var categorias = ['Salario', 'Freelance', 'Inversiones', 'Regalos', 'Otros Ingresos'];
        categorias.forEach(function(categoria) {
            var option = document.createElement('option');
            option.value = categoria;
            option.textContent = categoria;
            categoriaSelect.appendChild(option);
        });
    } else if (tipo === 'gasto') {
        var categorias = ['Alimentación', 'Transporte', 'Entretenimiento', 'Servicios', 'Salud', 'Educación', 'Otros Gastos'];
        categorias.forEach(function(categoria) {
            var option = document.createElement('option');
            option.value = categoria;
            option.textContent = categoria;
            categoriaSelect.appendChild(option);
        });
    }
});

// Guardar nueva transacción
document.getElementById('guardarTransaccion').addEventListener('click', function() {
    var form = document.getElementById('nuevaTransaccionForm');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Aquí iría el código para enviar los datos al servidor mediante AJAX
    // Por ahora, solo cerramos el modal
    var modal = bootstrap.Modal.getInstance(document.getElementById('nuevaTransaccionModal'));
    modal.hide();
    
    // Mostrar mensaje de éxito
    alert('Transacción guardada correctamente');
    
    // Recargar la página para ver los cambios
    // window.location.reload();
});
</script>

<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
