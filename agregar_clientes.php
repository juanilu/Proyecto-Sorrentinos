<?php
// Incluir la conexión centralizada
include 'conexion.php';

// Lista de clientes de ejemplo a insertar
$clientes = [
    ["Juan Pérez", "11 2345 6789", "Av. San Martín 1234, CABA"],
    ["María Gómez", "11 3456 7890", "Belgrano 567, CABA"],
    ["Pedro Fernández", "11 4567 8901", "Güemes 321, CABA"],
    ["Lucía Rodríguez", "11 5678 9012", "Córdoba 789, CABA"],
    ["Diego Martínez", "11 6789 0123", "Av. Corrientes 4321, CABA"],
    ["Carla López", "11 7890 1234", "Lavalle 654, CABA"],
    ["Fernando Sánchez", "11 8901 2345", "Panamericana Km 15, Pilar"],
    ["Sofía Díaz", "11 9012 3456", "Florida 1000, CABA"]
];

$sql = "INSERT INTO cliente (nombre, telefono, direccion) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);

$registros_exitosos = 0;

foreach ($clientes as $c) {
    $stmt->bind_param("sss", $c[0], $c[1], $c[2]);
    if ($stmt->execute()) {
        $registros_exitosos++;
    }
}

$stmt->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carga de Clientes - Sorrentinos</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fcfbfa; text-align: center; padding-top: 50px; }
        .mensaje { background: #fff; border: 1px solid #d8cec2; display: inline-block; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        h2 { color: #2e7d32; }
        a { display: inline-block; margin-top: 15px; background: #2e7d32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        a:hover { background: #235d26; }
    </style>
</head>
<body>
    <div class="mensaje">
        <h2>¡Clientes agregados con éxito!</h2>
        <p>Se han registrado <strong><?php echo $registros_exitosos; ?></strong> clientes en la base de datos.</p>
        <a href="clientes.php">Ir al listado de clientes</a>
    </div>
</body>
</html>