<?php
session_start();
include("conexion.php");

if (isset($_POST['usuario_id']) && isset($_POST['nivel_id']) && isset($_POST['completado'])) {
    $usuario_id = $_POST['usuario_id'];
    $nivel_id = $_POST['nivel_id'];
    $completado = $_POST['completado'];

    // Obtener la operación y respuesta del nivel
    $sql = "SELECT operacion, respuesta FROM niveles WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $nivel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $operacion = $row['operacion'];
    $respuesta = $row['respuesta'];

    // Verificar si el nivel ya existe en la tabla de progreso
    $sql = "SELECT * FROM progreso WHERE usuario_id = ? AND nivel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $usuario_id, $nivel_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Actualizar el progreso con operación y respuesta
        $sql = "UPDATE progreso SET completado = ?, operacion = ?, respuesta = ? WHERE usuario_id = ? AND nivel_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issii", $completado, $operacion, $respuesta, $usuario_id, $nivel_id);
    } else {
        // Insertar el nuevo progreso con operación y respuesta
        $sql = "INSERT INTO progreso (usuario_id, nivel_id, completado, operacion, respuesta) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisi", $usuario_id, $nivel_id, $completado, $operacion, $respuesta);
    }

    $stmt->execute();
    $stmt->close();
    $conn->close();
}
?>