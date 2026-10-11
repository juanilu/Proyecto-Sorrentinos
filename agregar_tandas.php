<?php
// Incluir la conexión centralizada
include 'conexion.php';

// Arreglo con tandas de prueba para insertar
$tandas_prueba = [
    ["Sorrentinos de Jamón y Queso", "2026-06-20", 0],
    ["Sorrentinos de Ricota y Espinaca", "2026-06-18", 0],
    ["Sorrentinos de Calabaza", "2026-06-15", 1],
    ["Sorrentinos de Verdura", "2026-06-12", 1],
    ["Sorrentinos de Pollo y Verdura", "2026-06-10", 1]
];

$sql = "INSERT INTO produccion (nombre, fecha, finalizada) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);

$insertadas = 0;
foreach ($tandas_prueba as $tanda) {
    $stmt->bind_param("ssi", $tanda[0], $tanda[1], $tanda[2]);
    if ($stmt->execute()) {
        $insertadas++;
    }
}

$stmt->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalación de Datos de Prueba</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="tandas.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #faf8f5;">
    <div style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #d8cec2; text-align: center; max-width: 400px; box-shadow: 0 4px 15px rgba(80, 50, 20, 0.05);">
        <h2 style="color: #2e7d32; margin-bottom: 15px;">¡Listo!</h2>
        <p style="color: #333; margin-bottom: 20px;">Se han agregado <b><?php echo $insertadas; ?> tandas de prueba</b> correctamente a la base de datos.</p>
        <a href="tandas.php" class="btn-nueva-tanda" style="text-decoration: none; display: inline-block;">Ver listado de tandas</a>
    </div>
</body>
</html>