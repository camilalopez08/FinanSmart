<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener datos del usuario
$id_usuario = $_SESSION["id"];

// Definir variables para filtrado
$tipo_filtro = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$categoria_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Construir la consulta SQL base
$sql = "SELECT id, tipo, monto, categoria, descripcion, fecha FROM transacciones WHERE id_usuario = ?";

// Añadir condiciones de filtrado
$params = array($id_usuario);
$types = "i";

if ($tipo_filtro != 'todos') {
    $sql .= " AND tipo = ?";
    $params[] = $tipo_filtro;
    $types .= "s";
}

if (!empty($categoria_filtro)) {
    $sql .= " AND categoria = ?";
    $params[] = $categoria_filtro;
    $types .= "s";
}

if (!empty($fecha_desde)) {
    $sql .= " AND fecha >= ?";
    $params[] = $fecha_desde;
    $types .= "s";
}

if (!empty($fecha_hasta)) {
    $sql .= " AND fecha <= ?";
    $params[] = $fecha_hasta;
    $types .= "s";
}

if (!empty($busqueda)) {
    $sql .= " AND (descripcion LIKE ? OR categoria LIKE ?)";
    $busqueda_param = "%" . $busqueda . "%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "ss";
}

// Ordenar por fecha descendente
$sql .= " ORDER BY fecha DESC";

// Preparar y ejecutar la consulta
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$transacciones = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Obtener categorías para el filtro
$sql_categorias = "SELECT DISTINCT categoria FROM transacciones WHERE id_usuario = ? ORDER BY categoria";
$stmt_categorias = mysqli_prepare($conn, $sql_categorias);
mysqli_stmt_bind_param($stmt_categorias, "i", $id_usuario);
mysqli_stmt_execute($stmt_categorias);
$resultado_categorias = mysqli_stmt_get_result($stmt_categorias);
$categorias = mysqli_fetch_all($resultado_categorias, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_categorias);

// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "includes/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Transacciones</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-success d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#nuevaTransaccionModal">
                        <i class="fas fa-plus me-2"></i> Nueva Transacción
                    </button>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Filtrar Transacciones</h5>
                </div>
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="busqueda" class="form-label">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="todos" <?php echo $tipo_filtro == 'todos' ? 'selected' : ''; ?>>Todos</option>
                                <option value="ingreso" <?php echo $tipo_filtro == 'ingreso' ? 'selected' : ''; ?>>Ingresos</option>
                                <option value="gasto" <?php echo $tipo_filtro == 'gasto' ? 'selected' : ''; ?>>Gastos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" <?php echo $categoria_filtro == $cat['categoria'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['categoria']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_desde" class="form-label">Desde</label>
                            <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_hasta" class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-2"></i> Aplicar Filtros
                            </button>
                            <a href="transacciones.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Limpiar Filtros
                            </a>
                        </div>
                        <div class="col-md-6 d-flex align-items-end justify-content-end">
                            <button type="button" class="btn btn-outline-success" id="exportarBtn">
                                <i class="fas fa-download me-2"></i> Exportar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tipo_filtro == 'todos' ? 'active' : ''; ?>" href="?tipo=todos">Todas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tipo_filtro == 'ingreso' ? 'active' : ''; ?>" href="?tipo=ingreso">Ingresos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tipo_filtro == 'gasto' ? 'active' : ''; ?>" href="?tipo=gasto">Gastos</a>
                        </li>
                    </ul>
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
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transacciones)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No hay transacciones para mostrar</td>
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
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1 editar-transaccion" data-id="<?php echo $transaccion['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger eliminar-transaccion" data-id="<?php echo $transaccion['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

<!-- Modal Editar Transacción -->
<div class="modal fade" id="editarTransaccionModal" tabindex="-1" aria-labelledby="editarTransaccionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarTransaccionModalLabel">Editar Transacción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editarTransaccionForm">
                    <input type="hidden" id="editar_id" name="id">
                    <div class="mb-3">
                        <label for="editar_tipo" class="form-label">Tipo de Transacción</label>
                        <select class="form-select" id="editar_tipo" name="tipo" required>
                            <option value="ingreso">Ingreso</option>
                            <option value="gasto">Gasto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editar_monto" class="form-label">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="editar_monto" name="monto" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editar_categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="editar_categoria" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <!-- Las categorías se cargarán dinámicamente según el tipo -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editar_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="editar_descripcion" name="descripcion" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editar_fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="editar_fecha" name="fecha" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="actualizarTransaccion">Actualizar Transacción</button>
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
                <p>¿Estás seguro de que deseas eliminar esta transacción? Esta acción no se puede deshacer.</p>
                <input type="hidden" id="eliminar_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="eliminarTransaccion">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
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

// Cargar categorías para editar según el tipo de transacción
document.getElementById('editar_tipo').addEventListener('change', function() {
    var tipo = this.value;
    var categoriaSelect = document.getElementById('editar_categoria');
    
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
    
    // Crear objeto FormData
    const formData = new FormData(form);
    formData.append('accion', 'agregar');
    
    // Enviar datos al servidor
    fetch('procesar_transaccion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('nuevaTransaccionModal'));
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
document.querySelectorAll('.editar-transaccion').forEach(function(button) {
    button.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        
        // Aquí iría el código para cargar los datos de la transacción desde el servidor
        // Por ahora, usamos datos de ejemplo
        document.getElementById('editar_id').value = id;
        document.getElementById('editar_tipo').value = 'ingreso';
        document.getElementById('editar_monto').value = '100.00';
        document.getElementById('editar_descripcion').value = 'Ejemplo de transacción';
        document.getElementById('editar_fecha').value = '2023-07-15';
        
        // Disparar el evento change para cargar las categorías
        var event = new Event('change');
        document.getElementById('editar_tipo').dispatchEvent(event);
        
        // Establecer la categoría después de cargar las opciones
        setTimeout(function() {
            document.getElementById('editar_categoria').value = 'Salario';
        }, 100);
        
        // Abrir el modal
        var modal = new bootstrap.Modal(document.getElementById('editarTransaccionModal'));
        modal.show();
    });
});

// Actualizar transacción
document.getElementById('actualizarTransaccion').addEventListener('click', function() {
    var form = document.getElementById('editarTransaccionForm');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Aquí iría el código para enviar los datos al servidor mediante AJAX
    // Por ahora, solo cerramos el modal
    var modal = bootstrap.Modal.getInstance(document.getElementById('editarTransaccionModal'));
    modal.hide();
    
    // Mostrar mensaje de éxito
    alert('Transacción actualizada correctamente');
    
    // Recargar la página para ver los cambios
    // window.location.reload();
});

// Abrir modal de confirmación de eliminación
document.querySelectorAll('.eliminar-transaccion').forEach(function(button) {
    button.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        document.getElementById('eliminar_id').value = id;
        
        var modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
        modal.show();
    });
});

// Eliminar transacción
document.getElementById('eliminarTransaccion').addEventListener('click', function() {
    var id = document.getElementById('eliminar_id').value;
    
    // Aquí iría el código para enviar la solicitud de eliminación al servidor mediante AJAX
    // Por ahora, solo cerramos el modal
    var modal = bootstrap.Modal.getInstance(document.getElementById('confirmarEliminarModal'));
    modal.hide();
    
    // Mostrar mensaje de éxito
    alert('Transacción eliminada correctamente');
    
    // Recargar la página para ver los cambios
    // window.location.reload();
});

// Exportar transacciones
document.getElementById('exportarBtn').addEventListener('click', function() {
    // Aquí iría el código para exportar las transacciones
    alert('Exportando transacciones...');
});
</script>

<script src="assets/js/transacciones.js"></script>
<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
