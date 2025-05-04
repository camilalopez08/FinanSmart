<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ya ha iniciado sesión
verificarNoSesion();

// Definir variables e inicializar con valores vacíos
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Procesar datos del formulario cuando se envía
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Verificar si el email está vacío
    if(empty(trim($_POST["email"]))){
        $email_err = "Por favor ingrese su email.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Verificar si la contraseña está vacía
    if(empty(trim($_POST["password"]))){
        $password_err = "Por favor ingrese su contraseña.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validar credenciales
    if(empty($email_err) && empty($password_err)){
        // Preparar una declaración select
        $sql = "SELECT id, nombre, email, password FROM usuarios WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Vincular variables a la declaración preparada como parámetros
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Establecer parámetros
            $param_email = $email;
            
            // Intentar ejecutar la declaración preparada
            if(mysqli_stmt_execute($stmt)){
                // Almacenar resultado
                mysqli_stmt_store_result($stmt);
                
                // Verificar si el email existe, si es así, verificar la contraseña
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Vincular variables de resultado
                    mysqli_stmt_bind_result($stmt, $id, $nombre, $email, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // La contraseña es correcta, iniciar una nueva sesión
                            session_start();
                            
                            // Almacenar datos en variables de sesión
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nombre"] = $nombre;
                            $_SESSION["email"] = $email;                            
                            
                            // Redirigir al usuario a la página de bienvenida
                            header("location: dashboard.php");
                        } else{
                            // La contraseña no es válida, mostrar mensaje de error
                            $login_err = "Email o contraseña incorrectos.";
                        }
                    }
                } else{
                    // El email no existe, mostrar mensaje de error
                    $login_err = "Email o contraseña incorrectos.";
                }
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
                                <i class="fas fa-user text-success fa-2x"></i>
                            </div>
                            <h2 class="card-title">Iniciar Sesión</h2>
                            <p class="text-muted">Ingresa tus credenciales para acceder a tu cuenta</p>
                        </div>
                        
                        <?php 
                        if(!empty($login_err)){
                            echo '<div class="alert alert-danger">' . $login_err . '</div>';
                        }        
                        ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="tu@ejemplo.com">
                                <div class="invalid-feedback"><?php echo $email_err; ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $password_err; ?></div>
                            </div>
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="remember">
                                    <label class="form-check-label" for="remember">
                                        Recordarme
                                    </label>
                                </div>
                                <a href="recuperar-password.php" class="text-success">¿Olvidaste tu contraseña?</a>
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-success btn-lg">Iniciar Sesión</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0 text-muted">¿No tienes una cuenta? <a href="register.php" class="text-success">Regístrate</a></p>
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
