<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener datos del usuario
$id_usuario = $_SESSION["id"];

// Obtener presupuestos activos
$sql_presupuestos = "SELECT p.id, p.categoria, p.monto_limite, p.periodo, 
                     (SELECT COALESCE(SUM(t.monto), 0) FROM transacciones t 
                      WHERE t.id_usuario = p.id_usuario AND t.categoria = p.categoria AND t.tipo = 'gasto'
                      AND t.fecha BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01') AND LAST_DAY(NOW())) as monto_gastado
                     FROM presupuestos p 
                     WHERE p.id_usuario = ?";
$stmt_presupuestos = mysqli_prepare($conn, $sql_presupuestos);
mysqli_stmt_bind_param($stmt_presupuestos, "i", $id_usuario);
mysqli_stmt_execute($stmt_presupuestos);
$resultado_presupuestos = mysqli_stmt_get_result($stmt_presupuestos);
$presupuestos = mysqli_fetch_all($resultado_presupuestos, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_presupuestos);

// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "includes/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Presupuestos</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-success d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#nuevoPresupuestoModal">
                        <i class="fas fa-plus me-2"></i> Nuevo Presupuesto
                    </button>
                </div>
            </div>
            
            <?php if (count($presupuestos) > 0): ?>
                <?php
                // Verificar si hay presupuestos cerca del límite
                $alertas = false;
                foreach ($presupuestos as $presupuesto) {
                    $porcentaje = ($presupuesto['monto_gastado'] / $presupuesto['monto_limite']) * 100;
                    if ($porcentaje >= 80) {
                        $alertas = true;
                        break;
                    }
                }
                
                if ($alertas):
                ?>
                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>
                        <strong>Atención:</strong> Estás cerca de alcanzar el límite en algunos de tus presupuestos. Revisa tus gastos.
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <ul class="nav nav-tabs mb-4" id="presupuestosTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true">Activos</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completados-tab" data-bs-toggle="tab" data-bs-target="#completados" type="button" role="tab" aria-controls="completados" aria-selected="false">Completados</button>
                </li>
            </ul>
            
            <div class="tab-content" id="presupuestosTabContent">
                <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
                    <?php if (empty($presupuestos)): ?>
                        <div class="text-center py-5 my-4 bg-light rounded">
                            <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                            <h5>No tienes presupuestos activos</h5>
                            <p class="text-muted">Crea un presupuesto para controlar tus gastos por categoría</p>
                            <button type="button" class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#nuevoPresupuestoModal">
                                <i class="fas fa-plus me-2"></i> Crear Presupuesto
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($presupuestos as $presupuesto): ?>
                                <?php
                                $monto_gastado = $presupuesto['monto_gastado'];
                                $monto_limite = $presupuesto['monto_limite'];
                                $porcentaje = ($monto_gastado / $monto_limite) * 100;
                                $monto_restante = $monto_limite - $monto_gastado;
                                
                                // Determinar clase de color según el porcentaje
                                $progress_class = "bg-success";
                                if ($porcentaje >= 90) {
                                    $progress_class = "bg-danger";
                                } elseif ($porcentaje >= 75) {
                                    $progress_class = "bg-warning";
                                }
                                ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white">
                                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($presupuesto['categoria']); ?></h5>
                                            <p class="text-muted small mb-0">Presupuesto <?php echo $presupuesto['periodo']; ?></p>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span>Gastado: $<?php echo number_format($monto_gastado, 2); ?></span>
                                                    <span class="<?php echo $porcentaje > 90 ? 'text-danger fw-bold' : ''; ?>">
                                                        <?php echo round($porcentaje); ?>%
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 10px;">
                                                    <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" style="width: <?php echo $porcentaje; ?>%" aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Límite: $<?php echo number_format($monto_limite, 2); ?></span>
                                                <span class="<?php echo $monto_restante < 50 ? 'text-danger fw-bold' : 'text-success fw-bold'; ?>">
                                                    Restante: $<?php echo number_format($monto_restante, 2); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white border-top-0">
                                            <div class="d-flex justify-content-between">
                                                <button type="button" class="btn btn-sm btn-outline-primary editar-presupuesto" data-id="<?php echo $presupuesto['id']; ?>">
                                                    <i class="fas fa-edit me-1"></i> Editar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger eliminar-presupuesto" data-id="<?php echo $presupuesto['id']; ?>">
                                                    <i class="fas fa-trash me-1"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="completados" role="tabpanel" aria-labelledby="completados-tab">
                    <div class="text-center py-5 my-4 bg-light rounded">
                        <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                        <h5>No hay presupuestos completados</h5>
                        <p class="text-muted">Aquí se mostrarán los presupuestos de períodos anteriores</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Nuevo Presupuesto -->
<div class="modal fade" id="nuevoPresupuestoModal" tabindex="-1" aria-labelledby="nuevoPresupuestoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoPresupuestoModalLabel">Nuevo Presupuesto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="nuevoPresupuestoForm">
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="categoria" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <option value="Alimentación">Alimentación</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Entretenimiento">Entretenimiento</option>
                            <option value="Servicios">Servicios</option>
                            <option value="Salud">Salud</option>
                            <option value="Educación">Educación</option>
                            <option value="Otros Gastos">Otros Gastos</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="monto_limite" class="form-label">Monto Límite</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="monto_limite" name="monto_limite" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="periodo" class="form-label">Período</label>
                        <select class="form-select" id="periodo" name="periodo" required>
                            <option value="mensual">Mensual</option>
                            <option value="semanal">Semanal</option>
                            <option value="trimestral">Trimestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="guardarPresupuesto">Guardar Presupuesto</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Presupuesto -->
<div class="modal fade" id="editarPresupuestoModal" tabindex="-1" aria-labelledby="editarPresupuestoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarPresupuestoModalLabel">Editar Presupuesto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editarPresupuestoForm">
                    <input type="hidden" id="editar_id" name="id">
                    <div class="mb-3">
                        <label  id="editar_id" name="id">
                    <div class="mb-3">
                        <label for="editar_categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="editar_categoria" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <option value="Alimentación">Alimentación</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Entretenimiento">Entretenimiento</option>
                            <option value="Servicios">Servicios</option>
                            <option value="Salud">Salud</option>
                            <option value="Educación">Educación</option>
                            <option value="Otros Gastos">Otros Gastos</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editar_monto_limite" class="form-label">Monto Límite</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="editar_monto_limite" name="monto_limite" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editar_periodo" class="form-label">Período</label>
                        <select class="form-select" id="editar_periodo" name="periodo" required>
                            <option value="mensual">Mensual</option>
                            <option value="semanal">Semanal</option>
                            <option value="trimestral">Trimestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="actualizarPresupuesto">Actualizar Presupuesto</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-labelledby="confirmarEliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este presupuesto? Esta acción no se puede deshacer.</p>
                <input type="hidden" id="eliminar_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="eliminarPresupuesto">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Guardar nuevo presupuesto
document.getElementById('guardarPresupuesto').addEventListener('click', function() {
    var form = document.getElementById('nuevoPresupuestoForm');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Crear objeto FormData
    const formData = new FormData(form);
    formData.append('accion', 'agregar');
    
    // Enviar datos al servidor
    fetch('procesar_presupuesto.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('nuevoPresupuestoModal'));
            modal.hide();
            
            // Mostrar mensaje de éxito
            alert(data.message);
            
            // Recargar la página para ver los cambios
            window.location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión. Por favor, inténtelo de nuevo más tarde.');
    });
});

// Abrir modal de edición
document.querySelectorAll('.editar-presupuesto').forEach(function(button) {
    button.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        
        // Aquí iría el código para cargar los datos del presupuesto desde el servidor
        // Por ahora, usamos datos de ejemplo
        document.getElementById('editar_id').value = id;
        document.getElementById('editar_categoria').value = 'Alimentación';
        document.getElementById('editar_monto_limite').value = '500.00';
        document.getElementById('editar_periodo').value = 'mensual';
        
        // Abrir el modal
        var modal = new bootstrap.Modal(document.getElementById('editarPresupuestoModal'));
        modal.show();
    });
});

// Actualizar presupuesto
document.getElementById('actualizarPresupuesto').addEventListener('click', function() {
    var form = document.getElementById('editarPresupuestoForm');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Aquí iría el código para enviar los datos al servidor mediante AJAX
    // Por ahora, solo cerramos el modal
    var modal = bootstrap.Modal.getInstance(document.getElementById('editarPresupuestoModal'));
    modal.hide();
    
    // Mostrar mensaje de éxito
    alert('Presupuesto actualizado correctamente');
    
    // Recargar la página para ver los cambios
    // window.location.reload();
});

// Abrir modal de confirmación de eliminación
document.querySelectorAll('.eliminar-presupuesto').forEach(function(button) {
    button.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        document.getElementById('eliminar_id').value = id;
        
        var modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
        modal.show();
    });
});

// Eliminar presupuesto
document.getElementById('eliminarPresupuesto').addEventListener('click', function() {
    var id = document.getElementById('eliminar_id').value;
    
    // Aquí iría el código para enviar la solicitud de eliminación al servidor mediante AJAX
    // Por ahora, solo cerramos el modal
    var modal = bootstrap.Modal.getInstance(document.getElementById('confirmarEliminarModal'));
    modal.hide();
    
    // Mostrar mensaje de éxito
    alert('Presupuesto eliminado correctamente');
    
    // Recargar la página para ver los cambios
    // window.location.reload();
});
</script>

<script src="assets/js/presupuestos.js"></script>

<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
