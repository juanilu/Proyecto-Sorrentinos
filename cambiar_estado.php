<?php
include 'conexion.php';

if (isset($_GET['id'])) {
    $id_prod = intval($_GET['id']);

    // 1. Consultar el estado actual de la producción
    $sql = "SELECT finalizada FROM produccion WHERE id_prod = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_prod);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        // Invertir el estado (si es 0 pasa a 1, si es 1 pasa a 0)
        $nuevo_estado = ($fila['finalizada'] == 1) ? 0 : 1;

        // 2. Actualizar en la base de datos
        $sql_update = "UPDATE produccion SET finalizada = ? WHERE id_prod = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param("ii", $nuevo_estado, $id_prod);
        $stmt_update->execute();
        $stmt_update->close();
    }
    $stmt->close();
}

// Redirigir de regreso al listado de tandas
header("Location: tandas.php");
exit();
?>