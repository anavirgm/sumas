<?php
session_start();
include('conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id']; // Usuario autenticado

// Eliminar el progreso del usuario
$sql = "DELETE FROM progreso WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);

if ($stmt->execute()) {
    // Redirigir al usuario a la página actual después de reiniciar
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    echo "Error al reiniciar el progreso.";
}
?>
