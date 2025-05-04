<?php
// Incluir archivos de configuración
require_once "config/database.php";
require_once "config/session.php";

// Verificar si el usuario ha iniciado sesión
verificarSesion();

// Obtener el tipo de categoría solicitado (ingreso o gasto)
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Validar el tipo
if ($tipo != 'ingreso' && $tipo != 'gasto') {
    echo json_encode(['success' => false, 'message' => 'Tipo de categoría inválido.']);
    exit;
}

// Preparar la consulta SQL para obtener las categorías
$sql = "SELECT id, nombre, icono, color FROM categorias WHERE tipo = ? ORDER BY nombre";

// Preparar la declaración
$stmt = mysqli_prepare($conn, $sql);

// Vincular parámetros
mysqli_stmt_bind_param($stmt, "s", $tipo);

// Ejecutar la consulta
mysqli_stmt_execute($stmt);

// Obtener resultados
$resultado = mysqli_stmt_get_result($stmt);
$categorias = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

// Cerrar la declaración
mysqli_stmt_close($stmt);

// Devolver las categorías en formato JSON
header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $categorias]);

// Cerrar la conexión
mysqli_close($conn);
?>
