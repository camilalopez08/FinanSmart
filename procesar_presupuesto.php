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
    
    // Acción: Agregar nuevo presupuesto
    if ($accion == 'agregar') {
        // Obtener datos del formulario
        $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
        $monto_limite = floatval($_POST['monto_limite']);
        $periodo = mysqli_real_escape_string($conn, $_POST['periodo']);
        
        // Validar datos
        if (empty($categoria) || $monto_limite <= 0 || empty($periodo)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos.']);
            exit;
        }
        
        // Establecer fechas de inicio y fin según el período
        $fecha_inicio = date('Y-m-d'); // Hoy
        
        switch ($periodo) {
            case 'semanal':
                $fecha_fin = date('Y-m-d', strtotime('+1 week'));
                break;
            case 'mensual':
                $fecha_fin = date('Y-m-d', strtotime('+1 month'));
                break;
            case 'trimestral':
                $fecha_fin = date('Y-m-d', strtotime('+3 months'));
                break;
            case 'anual':
                $fecha_fin = date('Y-m-d', strtotime('+1 year'));
                break;
            default:
                $fecha_fin = date('Y-m-d', strtotime('+1 month')); // Por defecto mensual
        }
        
        // Verificar si ya existe un presupuesto para esta categoría y período
        $sql_verificar = "SELECT id FROM presupuestos 
                         WHERE id_usuario = ? AND categoria = ? AND periodo = ? 
                         AND (fecha_fin >= CURDATE())";
        
        $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "iss", $id_usuario, $categoria, $periodo);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_store_result($stmt_verificar);
        
        if (mysqli_stmt_num_rows($stmt_verificar) > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un presupuesto activo para esta categoría y período.']);
            mysqli_stmt_close($stmt_verificar);
            exit;
        }
        
        mysqli_stmt_close($stmt_verificar);
        
        // Preparar la consulta SQL para insertar el presupuesto
        $sql = "INSERT INTO presupuestos (id_usuario, categoria, monto_limite, periodo, fecha_inicio, fecha_fin) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "isdsss", $id_usuario, $categoria, $monto_limite, $periodo, $fecha_inicio, $fecha_fin);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Presupuesto agregado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al agregar el presupuesto: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Actualizar presupuesto existente
    elseif ($accion == 'actualizar') {
        // Obtener datos del formulario
        $id = intval($_POST['id']);
        $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
        $monto_limite = floatval($_POST['monto_limite']);
        $periodo = mysqli_real_escape_string($conn, $_POST['periodo']);
        
        // Validar datos
        if ($id <= 0 || empty($categoria) || $monto_limite <= 0 || empty($periodo)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos.']);
            exit;
        }
        
        // Verificar que el presupuesto pertenece al usuario
        $sql_verificar = "SELECT id FROM presupuestos WHERE id = ? AND id_usuario = ?";
        $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $id_usuario);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_store_result($stmt_verificar);
        
        if (mysqli_stmt_num_rows($stmt_verificar) == 0) {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para modificar este presupuesto.']);
            mysqli_stmt_close($stmt_verificar);
            exit;
        }
        
        mysqli_stmt_close($stmt_verificar);
        
        // Preparar la consulta SQL para actualizar el presupuesto
        $sql = "UPDATE presupuestos SET categoria = ?, monto_limite = ?, periodo = ? 
                WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "sdsii", $categoria, $monto_limite, $periodo, $id, $id_usuario);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Presupuesto actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el presupuesto: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Eliminar presupuesto
    elseif ($accion == 'eliminar') {
        // Obtener ID del presupuesto
        $id = intval($_POST['id']);
        
        // Validar datos
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de presupuesto inválido.']);
            exit;
        }
        
        // Verificar que el presupuesto pertenece al usuario
        $sql_verificar = "SELECT id FROM presupuestos WHERE id = ? AND id_usuario = ?";
        $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $id_usuario);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_store_result($stmt_verificar);
        
        if (mysqli_stmt_num_rows($stmt_verificar) == 0) {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para eliminar este presupuesto.']);
            mysqli_stmt_close($stmt_verificar);
            exit;
        }
        
        mysqli_stmt_close($stmt_verificar);
        
        // Preparar la consulta SQL para eliminar el presupuesto
        $sql = "DELETE FROM presupuestos WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "ii", $id, $id_usuario);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Presupuesto eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el presupuesto: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Obtener datos de un presupuesto
    elseif ($accion == 'obtener') {
        // Obtener ID del presupuesto
        $id = intval($_POST['id']);
        
        // Validar datos
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de presupuesto inválido.']);
            exit;
        }
        
        // Preparar la consulta SQL para obtener el presupuesto
        $sql = "SELECT id, categoria, monto_limite, periodo, fecha_inicio, fecha_fin 
                FROM presupuestos 
                WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "ii", $id, $id_usuario);
        
        // Ejecutar la consulta
        mysqli_stmt_execute($stmt);
        
        // Obtener resultados
        $resultado = mysqli_stmt_get_result($stmt);
        
        if ($presupuesto = mysqli_fetch_assoc($resultado)) {
            echo json_encode(['success' => true, 'data' => $presupuesto]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el presupuesto.']);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción no reconocida
    else {
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    }
} else {
    // Si no es una solicitud POST, redirigir a la página de presupuestos
    header("Location: presupuestos.php");
    exit;
}

// Cerrar la conexión
mysqli_close($conn);
?>
