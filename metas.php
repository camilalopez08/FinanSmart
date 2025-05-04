<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener datos del usuario
$id_usuario = $_SESSION["id"];

// Obtener metas de ahorro
$sql_metas = "SELECT id, titulo, monto_objetivo, monto_actual, fecha_limite FROM metas WHERE id_usuario = ?";
$stmt_metas = mysqli_prepare($conn, $sql_metas);
mysqli_stmt_bind_param($stmt_metas, "i", $id_usuario);
mysqli_stmt_execute($stmt_metas);
$resultado_metas = mysqli_stmt_get_result($stmt_metas);
$metas = mysqli_fetch_all($resultado_metas, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_metas);

// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "includes/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Metas de Ahorro</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-success d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#nuevaMetaModal">
                        <i class="fas fa-plus me-2"></i> Nueva Meta
                    </button>
                </div>
            </div>
            
            <?php if (empty($metas)): ?>
                <div class="text-center py-5 my-4 bg-light rounded">
                    <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                    <h5>No tienes metas de ahorro</h5>
                    <p class="text-muted">Crea una meta para empezar a ahorrar para tus objetivos financieros</p>
                    <button type="button" class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#nuevaMetaModal">
                        <i class="fas fa-plus me-2"></i> Crear Meta
                    </button>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($metas as $meta): ?>
                        <?php
                        $monto_actual = $meta['monto_actual'];
                        $monto_objetivo = $meta['monto_objetivo'];
                        $porcentaje = ($monto_actual / $monto_objetivo) * 100;
                        $monto_restante = $monto_objetivo - $monto_actual;
                        $fecha_limite = new DateTime($meta['fecha_limite']);
                        $hoy = new DateTime();
                        $dias_restantes = $hoy->diff($fecha_limite)->days;
                        $fecha_pasada = $fecha_limite < $hoy;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($meta['titulo']); ?></h5>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Fecha límite: <?php echo $fecha_limite->format('d/m/Y'); ?>
                                        <?php if ($fecha_pasada): ?>
                                            <span class="badge bg-danger ms-1">Vencida</span>
                                        <?php elseif ($dias_restantes <= 7): ?>
                                            <span class="badge bg-warning text-dark ms-1">Próxima</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span>Progreso:</span>
                                            <span class="fw-bold"><?php echo round($porcentaje); ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $porcentaje; ?>%" aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Ahorrado:</span>
                                        <span class="text-success fw-bold">$<?php echo number_format($monto_actual, 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Objetivo:</span>
                                        <span class="fw-bold">$<?php echo number_format($monto_objetivo, 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Restante:</span>
                                        <span class="fw-bold">$<?php echo number_format($monto_restante, 2); ?></span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-sm btn-outline-primary editar-meta" data-id="<?php echo $meta['id']; ?>">
                                            <i class="fas fa-edit me-1"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success aportar-meta" data-id="<?php echo $meta['id']; ?>">
                                            <i class="fas fa-plus me-1"></i> Aportar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Modal Nueva Meta -->
<div class="modal fade" id="nuevaMetaModal" tabindex="-1" aria-labelledby="nuevaMetaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaMetaModalLabel">Nueva Meta de Ahorro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="nuevaMetaForm">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título de la Meta</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ej: Viaje a Europa" required>
                    </div>
                    <div class="mb-3">
                        <label for="monto_objetivo" class="form-label">Monto Objetivo</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="monto_objetivo" name="monto_objetivo" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="monto_inicial" class="form-label">Monto Inicial (opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" 
                            id="monto_inicial" name="monto_inicial" step="0.01" min="0" >
                            
                        </div>  
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_limite" class="form-label">Fecha Límite</label>
                        <input type="date" class="form-control" id="fecha_limite" name="fecha_limite" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="guardarMeta">Guardar Meta</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Meta -->
<div class="modal fade" id="editarMetaModal" tabindex="-1" aria-labelledby="editarMetaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarMetaModalLabel">Editar Meta de Ahorro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editarMetaForm">
                    <input type="hidden" id="editar_id" name="id">
                    <div class="mb-3">
                        <label for="editar_titulo" class="form-label">Título de la Meta</label>
                        <input type="text" class="form-control" id="editar_titulo" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_monto_objetivo" class="form-label">Monto Objetivo</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="editar_monto_objetivo" name="monto_objetivo" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editar_monto_actual" class="form-label">Monto Actual</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="editar_monto_actual" name="monto_actual" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editar_fecha_limite" class="form-label">Fecha Límite</label>
                        <input type="date" class="form-control" id="editar_fecha_limite" name="fecha_limite" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="editar_descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="actualizarMeta">Actualizar Meta</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aportar a Meta -->
<div class="modal fade" id="aportarMetaModal" tabindex="-1" aria-labelledby="aportarMetaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aportarMetaModalLabel">Aportar a Meta de Ahorro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="aportarMetaForm">
                    <input type="hidden" id="aportar_id" name="id">
                    <div class="mb-3">
                        <label for="aportar_titulo" class="form-label">Meta</label>
                        <input type="text" class="form-control" id="aportar_titulo" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="aportar_monto" class="form-label">Monto a Aportar</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="aportar_monto" name="monto" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="aportar_fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="aportar_fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="aportar_nota" class="form-label">Nota (opcional)</label>
                        <textarea class="form-control" id="aportar_nota" name="nota" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="guardarAporte">Guardar Aporte</button>
            </div>
        </div>
    </div>
</div>

<script>
// Establecer fecha mínima para fecha límite (hoy)
document.getElementById('fecha_limite').min = new Date().toISOString().split('T')[0];

// Guardar nueva meta
document.getElementById('guardarMeta').addEventListener('click', function() {
    var form = document.getElementById('nuevaMetaForm');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Crear objeto FormData
    const formData = new FormData(form);
    formData.append('accion', 'agregar');
    
    // Enviar datos al servidor
    fetch('procesar_meta.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('nuevaMetaModal'));
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
document.querySelectorAll('.editar-meta').forEach(function(button) {
    button.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        
        // Aquí iría el código para cargar los datos de la meta desde el servidor
        // Por ahora, usamos datos de ejemplo
        document.getElementById('editar_id').value = id;
        document.getElementById('editar_titulo').value = 'Viaje a Europa';
        document.getElementById('editar_monto_objetivo').value = '5000.00';
        document.getElementById('editar_monto_actual').value = '2500.00';
        document.getElementById('editar_fecha_limite').value = '2023-12-31';
        document.getElementById('editar_descripcion').value = 'Vacaciones en Europa';
        
        // Abrir el modal
        var modal = new bootstrap.Modal(document.getElementById('editarMetaModal'));
        modal.show();
    });
});

// Actualizar meta
document.getElementById('actualizarMeta').addEventListener('click', function() {
    var form = document.getElementById('editarMetaForm');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Aquí iría el código para enviar los datos al servidor mediante AJAX
    // Por ahora, solo cerramos el modal
    var modal = bootstrap.Modal.getInstance(document.getElementById('editarMetaModal'));
    modal.hide();
    
    // Mostrar mensaje de éxito
    alert('Meta actualizada correctamente');
    
    // Recargar la página para ver los cambios
    // window.location.reload();
});

// Abrir modal de aporte
document.querySelectorAll('.aportar-meta').forEach(function(button) {
    button.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        
        // Aquí iría el código para cargar los datos de la meta desde el servidor
        // Por ahora, usamos datos de ejemplo
        document.getElementById('aportar_id').value = id;
        document.getElementById('aportar_titulo').value = 'Viaje a Europa';
        
        // Abrir el modal
        var modal = new bootstrap.Modal(document.getElementById('aportarMetaModal'));
        modal.show();
    });
});

// Guardar aporte
document.getElementById('guardarAporte').addEventListener('click', function() {
    var form = document.getElementById('aportarMetaForm');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Aquí iría el código para enviar los datos al servidor mediante AJAX
    // Por ahora, solo cerramos el modal
    var modal = bootstrap.Modal.getInstance(document.getElementById('aportarMetaModal'));
    modal.hide();
    
    // Mostrar mensaje de éxito
    alert('Aporte guardado correctamente');
    
    // Recargar la página para ver los cambios
    // window.location.reload();
});
</script>

<script src="assets/js/metas.js"></script>

<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
