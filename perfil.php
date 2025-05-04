<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener datos del usuario
$id_usuario = $_SESSION["id"];

// Obtener información del usuario
$sql_usuario = "SELECT nombre, email, fecha_registro FROM usuarios WHERE id = ?";
$stmt_usuario = mysqli_prepare($conn, $sql_usuario);
mysqli_stmt_bind_param($stmt_usuario, "i", $id_usuario);
mysqli_stmt_execute($stmt_usuario);
$resultado_usuario = mysqli_stmt_get_result($stmt_usuario);
$usuario = mysqli_fetch_assoc($resultado_usuario);
mysqli_stmt_close($stmt_usuario);

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

// Procesar formulario de actualización de perfil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_perfil'])) {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Validar campos obligatorios
    if (empty($nombre) || empty($email)) {
        $mensaje = "Por favor complete todos los campos obligatorios.";
        $tipo_mensaje = "danger";
    } else {
        // Verificar si el email ya está en uso por otro usuario
        $sql_verificar_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt_verificar_email = mysqli_prepare($conn, $sql_verificar_email);
        mysqli_stmt_bind_param($stmt_verificar_email, "si", $email, $id_usuario);
        mysqli_stmt_execute($stmt_verificar_email);
        mysqli_stmt_store_result($stmt_verificar_email);
        
        if (mysqli_stmt_num_rows($stmt_verificar_email) > 0) {
            $mensaje = "El email ya está en uso por otro usuario.";
            $tipo_mensaje = "danger";
        } else {
            // Si se proporcionó una contraseña actual, verificar y actualizar la contraseña
            if (!empty($password_actual)) {
                // Obtener la contraseña actual del usuario
                $sql_password = "SELECT password FROM usuarios WHERE id = ?";
                $stmt_password = mysqli_prepare($conn, $sql_password);
                mysqli_stmt_bind_param($stmt_password, "i", $id_usuario);
                mysqli_stmt_execute($stmt_password);
                mysqli_stmt_bind_result($stmt_password, $hashed_password);
                mysqli_stmt_fetch($stmt_password);
                mysqli_stmt_close($stmt_password);
                
                // Verificar la contraseña actual
                if (!password_verify($password_actual, $hashed_password)) {
                    $mensaje = "La contraseña actual es incorrecta.";
                    $tipo_mensaje = "danger";
                } 
                // Verificar que la nueva contraseña y la confirmación coincidan
                elseif (empty($password_nueva) || empty($confirmar_password)) {
                    $mensaje = "Por favor ingrese la nueva contraseña y su confirmación.";
                    $tipo_mensaje = "danger";
                }
                elseif ($password_nueva != $confirmar_password) {
                    $mensaje = "La nueva contraseña y la confirmación no coinciden.";
                    $tipo_mensaje = "danger";
                }
                // Verificar que la nueva contraseña tenga al menos 6 caracteres
                elseif (strlen($password_nueva) < 6) {
                    $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
                    $tipo_mensaje = "danger";
                }
                else {
                    // Actualizar nombre, email y contraseña
                    $hashed_password = password_hash($password_nueva, PASSWORD_DEFAULT);
                    $sql_update = "UPDATE usuarios SET nombre = ?, email = ?, password = ? WHERE id = ?";
                    $stmt_update = mysqli_prepare($conn, $sql_update);
                    mysqli_stmt_bind_param($stmt_update, "sssi", $nombre, $email, $hashed_password, $id_usuario);
                    
                    if (mysqli_stmt_execute($stmt_update)) {
                        $mensaje = "Perfil actualizado correctamente.";
                        $tipo_mensaje = "success";
                        
                        // Actualizar datos de sesión
                        $_SESSION["nombre"] = $nombre;
                        $_SESSION["email"] = $email;
                        
                        // Actualizar variable $usuario para mostrar los datos actualizados
                        $usuario['nombre'] = $nombre;
                        $usuario['email'] = $email;
                    } else {
                        $mensaje = "Error al actualizar el perfil: " . mysqli_error($conn);
                        $tipo_mensaje = "danger";
                    }
                    
                    mysqli_stmt_close($stmt_update);
                }
            } else {
                // Actualizar solo nombre y email
                $sql_update = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conn, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "ssi", $nombre, $email, $id_usuario);
                
                if (mysqli_stmt_execute($stmt_update)) {
                    $mensaje = "Perfil actualizado correctamente.";
                    $tipo_mensaje = "success";
                    
                    // Actualizar datos de sesión
                    $_SESSION["nombre"] = $nombre;
                    $_SESSION["email"] = $email;
                    
                    // Actualizar variable $usuario para mostrar los datos actualizados
                    $usuario['nombre'] = $nombre;
                    $usuario['email'] = $email;
                } else {
                    $mensaje = "Error al actualizar el perfil: " . mysqli_error($conn);
                    $tipo_mensaje = "danger";
                }
                
                mysqli_stmt_close($stmt_update);
            }
        }
        
        mysqli_stmt_close($stmt_verificar_email);
    }
}

// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "includes/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Mi Perfil</h1>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensaje; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Información Personal</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="fecha_registro" class="form-label">Fecha de Registro</label>
                                    <input type="text" class="form-control" id="fecha_registro" value="<?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>" readonly>
                                </div>
                                
                                <hr class="my-4">
                                <h5 class="mb-3">Cambiar Contraseña</h5>
                                <p class="text-muted small mb-3">Deje estos campos en blanco si no desea cambiar su contraseña.</p>
                                
                                <div class="mb-3">
                                    <label for="password_actual" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="password_actual" name="password_actual">
                                </div>
                                <div class="mb-3">
                                    <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nueva" name="password_nueva">
                                    <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmar_password" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="confirmar_password" name="confirmar_password">
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" name="actualizar_perfil" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Resumen de Cuenta</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="avatar-circle mx-auto mb-3">
                                    <span class="avatar-initials"><?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?></span>
                                </div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($usuario['email']); ?></p>
                            </div>
                            
                            <div class="list-group list-group-flush">
                                <a href="configuracion.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-cog me-2"></i> Configuración
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </a>
                                <a href="transacciones.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-exchange-alt me-2"></i> Mis Transacciones
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </a>
                                <a href="metas.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-bullseye me-2"></i> Mis Metas
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </a>
                                <a href="presupuestos.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-chart-pie me-2"></i> Mis Presupuestos
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    background-color: #28a745;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.avatar-initials {
    color: white;
    font-size: 32px;
    font-weight: bold;
}
</style>

<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
