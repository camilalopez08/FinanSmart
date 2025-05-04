<?php

file_put_contents('debug.log', "Archivo ejecutado\n", FILE_APPEND);
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
    
    // Acción: Agregar nueva meta
    if ($accion == 'agregar') {
        // Obtener datos del formulario
        $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
        $monto_objetivo = floatval($_POST['monto_objetivo']);
        $monto_inicial = isset($_POST['monto_inicial']) ? floatval($_POST['monto_inicial']) : 0;
        $fecha_limite = mysqli_real_escape_string($conn, $_POST['fecha_limite']);
        $descripcion = isset($_POST['descripcion']) ? mysqli_real_escape_string($conn, $_POST['descripcion']) : '';
        
        // Validar datos
        if (empty($titulo) || $monto_objetivo <= 0 || empty($fecha_limite)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos.']);
            exit;
        }
        
        // Preparar la consulta SQL para insertar la meta
        $sql = "INSERT INTO metas (id_usuario, titulo, descripcion, monto_objetivo, monto_actual, fecha_limite) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "issdds", $id_usuario, $titulo, $descripcion, $monto_objetivo, $monto_inicial, $fecha_limite);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            $id_meta = mysqli_insert_id($conn);
            
            // Si hay un monto inicial, registrarlo como un aporte
            if ($monto_inicial > 0) {
                $sql_aporte = "INSERT INTO aportes_metas (id_meta, monto, fecha, nota) 
                              VALUES (?, ?, CURDATE(), 'Aporte inicial')";
                $stmt_aporte = mysqli_prepare($conn, $sql_aporte);
                mysqli_stmt_bind_param($stmt_aporte, "id", $id_meta, $monto_inicial);
                mysqli_stmt_execute($stmt_aporte);
                mysqli_stmt_close($stmt_aporte);
            }
            
            echo json_encode(['success' => true, 'message' => 'Meta de ahorro creada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear la meta de ahorro: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Actualizar meta existente
    elseif ($accion == 'actualizar') {
        // Obtener datos del formulario
        $id = intval($_POST['id']);
        $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
        $monto_objetivo = floatval($_POST['monto_objetivo']);
        $monto_actual = floatval($_POST['monto_actual']);
        $fecha_limite = mysqli_real_escape_string($conn, $_POST['fecha_limite']);
        $descripcion = isset($_POST['descripcion']) ? mysqli_real_escape_string($conn, $_POST['descripcion']) : '';
        
        // Validar datos
        if ($id <= 0 || empty($titulo) || $monto_objetivo <= 0 || empty($fecha_limite)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos.']);
            exit;
        }
        
        // Verificar que la meta pertenece al usuario
        $sql_verificar = "SELECT id FROM metas WHERE id = ? AND id_usuario = ?";
        $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $id_usuario);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_store_result($stmt_verificar);
        
        if (mysqli_stmt_num_rows($stmt_verificar) == 0) {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para modificar esta meta.']);
            mysqli_stmt_close($stmt_verificar);
            exit;
        }
        
        mysqli_stmt_close($stmt_verificar);
        
        // Preparar la consulta SQL para actualizar la meta
        $sql = "UPDATE metas SET titulo = ?, descripcion = ?, monto_objetivo = ?, monto_actual = ?, fecha_limite = ? 
                WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "ssddiii", $titulo, $descripcion, $monto_objetivo, $monto_actual, $fecha_limite, $id, $id_usuario);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Meta de ahorro actualizada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la meta de ahorro: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Eliminar meta
    elseif ($accion == 'eliminar') {
        // Obtener ID de la meta
        $id = intval($_POST['id']);
        
        // Validar datos
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de meta inválido.']);
            exit;
        }
        
        // Verificar que la meta pertenece al usuario
        $sql_verificar = "SELECT id FROM metas WHERE id = ? AND id_usuario = ?";
        $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $id_usuario);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_store_result($stmt_verificar);
        
        if (mysqli_stmt_num_rows($stmt_verificar) == 0) {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para eliminar esta meta.']);
            mysqli_stmt_close($stmt_verificar);
            exit;
        }
        
        mysqli_stmt_close($stmt_verificar);
        
        // Eliminar primero los aportes asociados a la meta
        $sql_aportes = "DELETE FROM aportes_metas WHERE id_meta = ?";
        $stmt_aportes = mysqli_prepare($conn, $sql_aportes);
        mysqli_stmt_bind_param($stmt_aportes, "i", $id);
        mysqli_stmt_execute($stmt_aportes);
        mysqli_stmt_close($stmt_aportes);
        
        // Preparar la consulta SQL para eliminar la meta
        $sql = "DELETE FROM metas WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "ii", $id, $id_usuario);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Meta de ahorro eliminada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la meta de ahorro: ' . mysqli_error($conn)]);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción: Aportar a una meta
    elseif ($accion == 'aportar') {
        // Obtener datos del formulario
        $id = intval($_POST['id']);
        $monto = floatval($_POST['monto']);
        $fecha = mysqli_real_escape_string($conn, $_POST['fecha']);
        $nota = isset($_POST['nota']) ? mysqli_real_escape_string($conn, $_POST['nota']) : '';
        
        // Validar datos
        if ($id <= 0 || $monto <= 0 || empty($fecha)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos.']);
            exit;
        }
        
        // Verificar que la meta pertenece al usuario
        $sql_verificar = "SELECT id, monto_objetivo, monto_actual FROM metas WHERE id = ? AND id_usuario = ?";
        $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $id_usuario);
        mysqli_stmt_execute($stmt_verificar);
        $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
        
        if ($meta = mysqli_fetch_assoc($resultado_verificar)) {
            $monto_actual = $meta['monto_actual'];
            $monto_objetivo = $meta['monto_objetivo'];
            
            // Verificar que el aporte no exceda el objetivo
            if (($monto_actual + $monto) > $monto_objetivo) {
                echo json_encode(['success' => false, 'message' => 'El aporte excede el monto objetivo de la meta.']);
                mysqli_stmt_close($stmt_verificar);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la meta o no tiene permiso para modificarla.']);
            mysqli_stmt_close($stmt_verificar);
            exit;
        }
        
        mysqli_stmt_close($stmt_verificar);
        
        // Iniciar transacción
        mysqli_begin_transaction($conn);
        
        try {
            // Insertar el aporte
            $sql_aporte = "INSERT INTO aportes_metas (id_meta, monto, fecha, nota) VALUES (?, ?, ?, ?)";
            $stmt_aporte = mysqli_prepare($conn, $sql_aporte);
            mysqli_stmt_bind_param($stmt_aporte, "idss", $id, $monto, $fecha, $nota);
            mysqli_stmt_execute($stmt_aporte);
            mysqli_stmt_close($stmt_aporte);
            
            // Actualizar el monto actual de la meta
            $nuevo_monto_actual = $monto_actual + $monto;
            $sql_actualizar = "UPDATE metas SET monto_actual = ? WHERE id = ?";
            $stmt_actualizar = mysqli_prepare($conn, $sql_actualizar);
            mysqli_stmt_bind_param($stmt_actualizar, "di", $nuevo_monto_actual, $id);
            mysqli_stmt_execute($stmt_actualizar);
            mysqli_stmt_close($stmt_actualizar);
            
            // Confirmar transacción
            mysqli_commit($conn);
            
            echo json_encode(['success' => true, 'message' => 'Aporte registrado correctamente.']);
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => 'Error al registrar el aporte: ' . $e->getMessage()]);
        }
    }
    
    // Acción: Obtener datos de una meta
    elseif ($accion == 'obtener') {
        // Obtener ID de la meta
        $id = intval($_POST['id']);
        
        // Validar datos
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de meta inválido.']);
            exit;
        }
        
        // Preparar la consulta SQL para obtener la meta
        $sql = "SELECT id, titulo, descripcion, monto_objetivo, monto_actual, fecha_limite 
                FROM metas 
                WHERE id = ? AND id_usuario = ?";
        
        // Preparar la declaración
        $stmt = mysqli_prepare($conn, $sql);
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "ii", $id, $id_usuario);
        
        // Ejecutar la consulta
        mysqli_stmt_execute($stmt);
        
        // Obtener resultados
        $resultado = mysqli_stmt_get_result($stmt);
        
        if ($meta = mysqli_fetch_assoc($resultado)) {
            echo json_encode(['success' => true, 'data' => $meta]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la meta.']);
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Acción no reconocida
    else {
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    }
} else {
    // Si no es una solicitud POST, redirigir a la página de metas
    header("Location: metas.php");
    exit;
}

// Cerrar la conexión
mysqli_close($conn);
?>
