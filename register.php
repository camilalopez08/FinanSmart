<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ya ha iniciado sesión
verificarNoSesion();

// Definir variables e inicializar con valores vacíos
$nombre = $email = $password = $confirm_password = "";
$nombre_err = $email_err = $password_err = $confirm_password_err = "";

// Procesar datos del formulario cuando se envía
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validar nombre
    if(empty(trim($_POST["nombre"]))){
        $nombre_err = "Por favor ingrese su nombre.";
    } else{
        $nombre = trim($_POST["nombre"]);
    }
    
    // Validar email
    if(empty(trim($_POST["email"]))){
        $email_err = "Por favor ingrese un email.";
    } else{
        // Preparar una declaración select
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Vincular variables a la declaración preparada como parámetros
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Establecer parámetros
            $param_email = trim($_POST["email"]);
            
            // Intentar ejecutar la declaración preparada
            if(mysqli_stmt_execute($stmt)){
                // Almacenar resultado
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "Este email ya está en uso.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }

            // Cerrar declaración
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validar contraseña
    if(empty(trim($_POST["password"]))){
        $password_err = "Por favor ingrese una contraseña.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "La contraseña debe tener al menos 6 caracteres.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validar confirmación de contraseña
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Por favor confirme la contraseña.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }
    
    // Verificar errores de entrada antes de insertar en la base de datos
    if(empty($nombre_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Preparar una declaración de inserción
        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Vincular variables a la declaración preparada como parámetros
            mysqli_stmt_bind_param($stmt, "sss", $param_nombre, $param_email, $param_password);
            
            // Establecer parámetros
            $param_nombre = $nombre;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Crear un hash de contraseña
            
            // Intentar ejecutar la declaración preparada
            if(mysqli_stmt_execute($stmt)){
                // Redirigir a la página de inicio de sesión
                header("location: login.php");
            } else{
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }

            // Cerrar declaración
            mysqli_stmt_close($stmt);
        }
    }
    
    // Cerrar conexión
    mysqli_close($conn);
}
?>

<?php
// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                                <i class="fas fa-user-plus text-success fa-2x"></i>
                            </div>
                            <h2 class="card-title">Crear Cuenta</h2>
                            <p class="text-muted">Ingresa tus datos para registrarte en FinanSmart</p>
                        </div>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" name="nombre" id="nombre" class="form-control <?php echo (!empty($nombre_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nombre; ?>" placeholder="Juan Pérez">
                                <div class="invalid-feedback"><?php echo $nombre_err; ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="tu@ejemplo.com">
                                <div class="invalid-feedback"><?php echo $email_err; ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                                <div class="invalid-feedback"><?php echo $password_err; ?></div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                                <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">Registrarse</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0 text-muted">¿Ya tienes una cuenta? <a href="login.php" class="text-success">Iniciar Sesión</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
