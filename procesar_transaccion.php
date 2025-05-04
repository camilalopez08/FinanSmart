<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener datos del usuario
$id_usuario = $_SESSION["id"];

// Verificar si se recibió una solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determinar la acción a realizar
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    
    // Acción: Agregar nueva transacción
    if ($accion == 'agregar') {
        // Obtener datos del formulario
        $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
        $monto = floatval($_POST['monto']);
        $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
        $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
        $fecha = mysqli_real_escape_string($conn, $_POST['fecha']);
        
        // Validar datos
        if (empty($tipo) || $monto <= 0 || empty($categoria) || empty($fecha)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos.']);
            exit;
        }
        
        // Preparar la consulta SQL para insertar la transacción
        $sql = "INSERT INTO transacciones (id_usuario, tipo, monto, categoria, descripcion, fecha) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "isdsss", $id_usuario, $tipo, $monto, $categoria, $descripcion, $fecha);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Transacción agregada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al agregar la transacción: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Actualizar transacción existente
    elseif ($accion == 'actualizar') {
        // Obtener datos del formulario
        $id = intval($_POST['id']);
        $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
        $monto = floatval($_POST['monto']);
        $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
        $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
        $fecha = mysqli_real_escape_string($conn, $_POST['fecha']);
        
        // Validar datos
        if ($id <= 0 || empty($tipo) || $monto <= 0 || empty($categoria) || empty($fecha)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos.']);
            exit;
        }
        
        // Verificar que la transacción pertenece al usuario
        $sql_verificar = "SELECT id FROM transacciones WHERE id = ? AND id_usuario = ?";
        $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $id_usuario);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_store_result($stmt_verificar);
        
        if (mysqli_stmt_num_rows($stmt_verificar) == 0) {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para modificar esta transacción.']);
            mysqli_stmt_close($stmt_verificar);
            exit;
        }
        
        mysqli_stmt_close($stmt_verificar);
        
        // Preparar la consulta SQL para actualizar la transacción
        $sql = "UPDATE transacciones SET tipo = ?, monto = ?, categoria = ?, descripcion = ?, fecha = ? 
                WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "sdssii", $tipo, $monto, $categoria, $descripcion, $fecha, $id, $id_usuario);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Transacción actualizada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la transacción: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Eliminar transacción
    elseif ($accion == 'eliminar') {
        // Obtener ID de la transacción
        $id = intval($_POST['id']);
        
        // Validar datos
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de transacción inválido.']);
            exit;
        }
        
        // Verificar que la transacción pertenece al usuario
        $sql_verificar = "SELECT id FROM transacciones WHERE id = ? AND id_usuario = ?";
        $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $id_usuario);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_store_result($stmt_verificar);
        
        if (mysqli_stmt_num_rows($stmt_verificar) == 0) {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para eliminar esta transacción.']);
            mysqli_stmt_close($stmt_verificar);
            exit;
        }
        
        mysqli_stmt_close($stmt_verificar);
        
        // Preparar la consulta SQL para eliminar la transacción
        $sql = "DELETE FROM transacciones WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "ii", $id, $id_usuario);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Transacción eliminada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la transacción: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Obtener datos de una transacción
    elseif ($accion == 'obtener') {
        // Obtener ID de la transacción
        $id = intval($_POST['id']);
        
        // Validar datos
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de transacción inválido.']);
            exit;
        }
        
        // Preparar la consulta SQL para obtener la transacción
        $sql = "SELECT id, tipo, monto, categoria, descripcion, fecha 
                FROM transacciones 
                WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "ii", $id, $id_usuario);
        
        // Ejecutar la consulta
        mysqli_stmt_execute($stmt);
        
        // Obtener resultados
        $resultado = mysqli_stmt_get_result($stmt);
        
        if ($transaccion = mysqli_fetch_assoc($resultado)) {
            echo json_encode(['success' => true, 'data' => $transaccion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la transacción.']);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción no reconocida
    else {
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    }
} else {
    // Si no es una solicitud POST, redirigir a la página de transacciones
    header("Location: transacciones.php");
    exit;
}

// Cerrar la conexión
mysqli_close($conn);
?>
