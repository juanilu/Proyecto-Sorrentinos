<?php
include 'conexion.php';

// 1. Consultar la fecha de la próxima tanda pendiente
$sql_proxima = "SELECT fecha FROM produccion WHERE finalizada = 0 AND fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1";
$resultado_proxima = $conexion->query($sql_proxima);
$proxima_fecha = "Próximamente";

if ($resultado_proxima && $resultado_proxima->num_rows > 0) {
    $fila_prox = $resultado_proxima->fetch_assoc();
    $proxima_fecha = date("d/m/Y", strtotime($fila_prox['fecha']));
}

// 2. Obtener todas las tandas incluyendo el id_prod
$sql_tandas = "SELECT id_prod, nombre, fecha, finalizada FROM produccion ORDER BY fecha DESC";
$resultado_tandas = $conexion->query($sql_tandas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tandas de producción - Sorrentinos</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="tandas.css">
</head>
<body>

    <!-- Barra Superior -->
    <header class="barra-superior">
        <h1 class="marca">🥟 GESTIÓN DE SORRENTINOS</h1>
        
        <div class="usuario-menu">
            <span class="usuario-texto">👤 (Admin) ▾</span>
            <div class="usuario-dropdown">
                <a href="logout.php">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="contenido-tandas">

        <div class="contenedor-volver">
            <a href="index.php" class="btn-volver">←</a>
        </div>

        <div class="cabecera-seccion">
            <h2>Tandas de producción</h2>
            <a href="nueva_tanda.php" class="btn-nueva-tanda">+ Nueva tanda</a>
        </div>

        <div class="tarjeta-resumen">
            <div class="icono-calendario">📅</div>
            <div>
                <span class="etiqueta-proxima">PRÓXIMA TANDA</span>
                <span class="fecha-proxima"><?php echo $proxima_fecha; ?></span>
            </div>
        </div>

        <!-- Tabla de Tandas -->
        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Nombre de Producción</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_tandas && $resultado_tandas->num_rows > 0): ?>
                        <?php while($tanda = $resultado_tandas->fetch_assoc()): ?>
                            <tr>
                                <td class="col-nombre"><?php echo htmlspecialchars($tanda['nombre']); ?></td>
                                <td class="col-fecha"><?php echo date("d/m/Y", strtotime($tanda['fecha'])); ?></td>
                                <td class="col-estado">
                                    <?php if ($tanda['finalizada'] == 1): ?>
                                        <span class="badge-estado finalizada">Finalizada</span>
                                    <?php else: ?>
                                        <span class="badge-estado pendiente">Pendiente / Activa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-accion">
                                    <a href="cambiar_estado.php?id=<?php echo $tanda['id_prod']; ?>" class="btn-accion-estado">
                                        <?php echo ($tanda['finalizada'] == 1) ? 'Activar' : 'Finalizar'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="tabla-vacia">No hay tandas registradas en el sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>