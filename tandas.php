<?php
session_start();

// Validar que el usuario haya iniciado sesión; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include 'conexion.php';

// Procesar el formulario cuando se envía una nueva producción desde el modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_tanda'])) {
    $nombre_tanda = $conexion->real_escape_string($_POST['nombre']);
    $fecha_tanda = $conexion->real_escape_string($_POST['fecha']);
    
    if (!empty($nombre_tanda) && !empty($fecha_tanda)) {
        $sql_insert = "INSERT INTO produccion (nombre, fecha, finalizada) VALUES ('$nombre_tanda', '$fecha_tanda', 0)";
        $conexion->query($sql_insert);
        header("Location: tandas.php");
        exit();
    }
}

// 1. Consultar la fecha de la próxima tanda pendiente[cite: 7]
$sql_proxima = "SELECT fecha FROM produccion WHERE finalizada = 0 AND fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1";
$resultado_proxima = $conexion->query($sql_proxima);
$proxima_fecha = "Próximamente";

if ($resultado_proxima && $resultado_proxima->num_rows > 0) {
    $fila_prox = $resultado_proxima->fetch_assoc();
    $proxima_fecha = date("d/m/Y", strtotime($fila_prox['fecha']));
}

// 2. Obtener todas las tandas incluyendo el id_prod[cite: 7]
$sql_tandas = "SELECT id_prod, nombre, fecha, finalizada FROM produccion ORDER BY fecha DESC";
$resultado_tandas = $conexion->query($sql_tandas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tandas de producción - Sorrentinos</title>
    <!-- Vinculación de estilos con Cache Busting en PHP -->
    <link rel="stylesheet" href="estilos.css?v=<?php echo file_exists('estilos.css') ? filemtime('estilos.css') : time(); ?>">
    <link rel="stylesheet" href="tandas.css?v=<?php echo file_exists('tandas.css') ? filemtime('tandas.css') : time(); ?>">
</head>
<body>

    <!-- Barra Superior -->
    <header class="barra-superior">
        <h1 class="marca">🥟 GESTIÓN DE SORRENTINOS</h1>
        
        <div class="usuario-menu">
            <span class="usuario-texto">👤 (<?php echo htmlspecialchars($_SESSION['usuario']); ?>) ▾</span>
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
            <button type="button" class="btn-nueva-tanda" onclick="abrirModalNueva()">+ Nueva tanda</button>
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
                            <?php 
                                $estado_texto = ($tanda['finalizada'] == 1) ? 'Finalizada' : 'Pendiente / Activa';
                                $estado_clase = ($tanda['finalizada'] == 1) ? 'finalizada' : 'pendiente';
                                $id_tanda = $tanda['id_prod'];
                            ?>
                            <tr class="fila-tanda-clic" onclick="window.location.href='detalle_tanda.php?id=<?php echo $id_tanda; ?>'">
                                <td class="col-nombre"><strong><?php echo htmlspecialchars($tanda['nombre']); ?></strong></td>
                                <td class="col-fecha"><?php echo date("d/m/Y", strtotime($tanda['fecha'])); ?></td>
                                <td class="col-estado">
                                    <span class="badge-estado <?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></span>
                                </td>
                                <td class="col-accion" onclick="event.stopPropagation();">
                                    <a href="cambiar_estado.php?id=<?php echo $id_tanda; ?>" class="btn-accion-estado">
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

    <!-- MODAL: CREAR NUEVA TANDA -->
    <div id="modalNuevaTanda" class="modal-overlay">
        <div class="modal-contenedor">
            <div class="modal-header">
                <h3>Crear Nueva Tanda de Producción</h3>
                <span class="modal-cerrar" onclick="cerrarModalNueva()">&times;</span>
            </div>
            <form action="tandas.php" method="POST">
                <div class="modal-body">
                    <div class="grupo-input-modal">
                        <label for="nombre">Nombre de Producción <span class="obligatorio">*</span></label>
                        <input type="text" id="nombre" name="nombre" class="input-estandar-modal" placeholder="Ej: Tanda Fin de Semana" required autocomplete="off">
                    </div>
                    <div class="grupo-input-modal">
                        <label for="fecha">Fecha de Producción <span class="obligatorio">*</span></label>
                        <input type="date" id="fecha" name="fecha" class="input-estandar-modal" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secundario" onclick="cerrarModalNueva()">Cancelar</button>
                    <button type="submit" name="crear_tanda" class="btn-principal">Guardar Tanda</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script de control del Modal -->
    <script>
        function abrirModalNueva() {
            document.getElementById('modalNuevaTanda').classList.add('activo');
        }
        function cerrarModalNueva() {
            document.getElementById('modalNuevaTanda').classList.remove('activo');
        }

        window.onclick = function(event) {
            let modalNueva = document.getElementById('modalNuevaTanda');
            if (event.target === modalNueva) {
                modalNueva.classList.remove('activo');
            }
        }
    </script>

</body>
</html>