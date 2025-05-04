<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener datos del usuario
$id_usuario = $_SESSION["id"];

// Obtener configuración del usuario
$sql_config = "SELECT moneda, tema, notificaciones_email, notificaciones_presupuesto 
               FROM configuracion_usuario WHERE id_usuario = ?";
$stmt_config = mysqli_prepare($conn, $sql_config);
mysqli_stmt_bind_param($stmt_config, "i", $id_usuario);
mysqli_stmt_execute($stmt_config);
$resultado_config = mysqli_stmt_get_result($stmt_config);

// Si no existe configuración, crear con valores predeterminados
if (mysqli_num_rows($resultado_config) == 0) {
    $sql_insert = "INSERT INTO configuracion_usuario (id_usuario, moneda, tema, notificaciones_email, notificaciones_presupuesto) 
                   VALUES (?, '$', 'light', 1, 1)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "i", $id_usuario);
    mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);
    
    // Obtener la configuración recién creada
    mysqli_stmt_execute($stmt_config);
    $resultado_config = mysqli_stmt_get_result($stmt_config);
}

$config = mysqli_fetch_assoc($resultado_config);
mysqli_stmt_close($stmt_config);

// Definir variables para mensajes de error/éxito
$mensaje = "";
$tipo_mensaje = "";

// Procesar formulario de actualización de configuración
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_config'])) {
    $moneda = mysqli_real_escape_string($conn, $_POST['moneda']);
    $tema = mysqli_real_escape_string($conn, $_POST['tema']);
    $notificaciones_email = isset($_POST['notificaciones_email']) ? 1 : 0;
    $notificaciones_presupuesto = isset($_POST['notificaciones_presupuesto']) ? 1 : 0;
    
    // Actualizar configuración
    $sql_update = "UPDATE configuracion_usuario 
                   SET moneda = ?, tema = ?, notificaciones_email = ?, notificaciones_presupuesto = ? 
                   WHERE id_usuario = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssiii", $moneda, $tema, $notificaciones_email, $notificaciones_presupuesto, $id_usuario);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $mensaje = "Configuración actualizada correctamente.";
        $tipo_mensaje = "success";
        
        // Actualizar variable $config para mostrar los datos actualizados
        $config['moneda'] = $moneda;
        $config['tema'] = $tema;
        $config['notificaciones_email'] = $notificaciones_email;
        $config['notificaciones_presupuesto'] = $notificaciones_presupuesto;
    } else {
        $mensaje = "Error al actualizar la configuración: " . mysqli_error($conn);
        $tipo_mensaje = "danger";
    }
    
    mysqli_stmt_close($stmt_update);
}

// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "includes/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Configuración</h1>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensaje; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Preferencias Generales</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-4">
                            <h6 class="mb-3">Moneda</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-select" id="moneda" name="moneda">
                                        <option value="$" <?php echo $config['moneda'] == '$' ? 'selected' : ''; ?>>Dólar ($)</option>
                                        <option value="€" <?php echo $config['moneda'] == '€' ? 'selected' : ''; ?>>Euro (€)</option>
                                        <option value="£" <?php echo $config['moneda'] == '£' ? 'selected' : ''; ?>>Libra Esterlina (£)</option>
                                        <option value="¥" <?php echo $config['moneda'] == '¥' ? 'selected' : ''; ?>>Yen (¥)</option>
                                        <option value="₽" <?php echo $config['moneda'] == '₽' ? 'selected' : ''; ?>>Rublo (₽)</option>
                                        <option value="₹" <?php echo $config['moneda'] == '₹' ? 'selected' : ''; ?>>Rupia (₹)</option>
                                        <option value="R$" <?php echo $config['moneda'] == 'R$' ? 'selected' : ''; ?>>Real (R$)</option>
                                        <option value="$MXN" <?php echo $config['moneda'] == '$MXN' ? 'selected' : ''; ?>>Peso Mexicano ($MXN)</option>
                                        <option value="$ARS" <?php echo $config['moneda'] == '$ARS' ? 'selected' : ''; ?>>Peso Argentino ($ARS)</option>
                                        <option value="$CLP" <?php echo $config['moneda'] == '$CLP' ? 'selected' : ''; ?>>Peso Chileno ($CLP)</option>
                                        <option value="$COP" <?php echo $config['moneda'] == '$COP' ? 'selected' : ''; ?>>Peso Colombiano ($COP)</option>
                                    </select>
                                    <div class="form-text">Seleccione la moneda que desea utilizar en la aplicación.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="mb-3">Tema</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tema" id="tema_light" value="light" <?php echo $config['tema'] == 'light' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tema_light">Claro</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tema" id="tema_dark" value="dark" <?php echo $config['tema'] == 'dark' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tema_dark">Oscuro</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="mb-3">Notificaciones</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="notificaciones_email" name="notificaciones_email" <?php echo $config['notificaciones_email'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="notificaciones_email">Recibir notificaciones por correo electrónico</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notificaciones_presupuesto" name="notificaciones_presupuesto" <?php echo $config['notificaciones_presupuesto'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="notificaciones_presupuesto">Alertas cuando me acerque al límite de presupuesto</label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" name="actualizar_config" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Categorías Personalizadas</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Próximamente podrá crear y gestionar categorías personalizadas para sus transacciones.</p>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">Categorías de Ingresos</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-money-bill-wave text-success me-2"></i> Salario
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-laptop text-info me-2"></i> Freelance
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-chart-line text-primary me-2"></i> Inversiones
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-gift text-danger me-2"></i> Regalos
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-plus-circle text-secondary me-2"></i> Otros Ingresos
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="mb-3">Categorías de Gastos</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-utensils text-danger me-2"></i> Alimentación
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-car text-warning me-2"></i> Transporte
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-film text-warning me-2"></i> Entretenimiento
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-home text-success me-2"></i> Servicios
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-heartbeat text-danger me-2"></i> Salud
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-graduation-cap text-primary me-2"></i> Educación
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-shopping-bag text-secondary me-2"></i> Otros Gastos
                                    </div>
                                    <span class="badge bg-secondary">Predeterminada</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-primary" disabled>
                            <i class="fas fa-plus me-2"></i> Añadir Categoría
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Exportar Datos</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Exporte sus datos financieros para hacer copias de seguridad o para analizarlos en otras herramientas.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary mb-2">
                                    <i class="fas fa-file-export me-2"></i> Exportar Transacciones
                                </button>
                                <button type="button" class="btn btn-outline-primary mb-2">
                                    <i class="fas fa-file-export me-2"></i> Exportar Presupuestos
                                </button>
                                <button type="button" class="btn btn-outline-primary">
                                    <i class="fas fa-file-export me-2"></i> Exportar Metas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
