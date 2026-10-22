<?php
session_start();

// Validar que el usuario haya iniciado sesión; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include 'conexion.php';

// PROCESAR ELIMINACIÓN DE UN SABOR
if (isset($_GET['eliminar'])) {
    $id_sabor_eliminar = intval($_GET['eliminar']);
    if ($id_sabor_eliminar > 0) {
        $conexion->query("DELETE FROM sabor WHERE id_sabor = $id_sabor_eliminar");
    }
    header("Location: sabores.php");
    exit();
}

// PROCESAR CREACIÓN O ACTUALIZACIÓN DE SABOR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);

    if ($_POST['accion'] === 'crear_sabor') {
        if (!empty($nombre) && $precio >= 0) {
            $nombre_seguro = $conexion->real_escape_string($nombre);
            $descripcion_segura = $conexion->real_escape_string($descripcion);

            // Al ser id_sabor INT AUTO_INCREMENT, no necesitamos indicarlo en el INSERT[cite: 9]
            $conexion->query("INSERT INTO sabor (nombre, descripcion, precio) VALUES ('$nombre_seguro', '$descripcion_segura', $precio)");
        }
    } elseif ($_POST['accion'] === 'actualizar_sabor') {
        $id_sabor_act = intval($_POST['id_sabor'] ?? 0);
        if ($id_sabor_act > 0 && !empty($nombre) && $precio >= 0) {
            $nombre_seguro = $conexion->real_escape_string($nombre);
            $descripcion_segura = $conexion->real_escape_string($descripcion);
            
            $conexion->query("UPDATE sabor SET nombre = '$nombre_seguro', descripcion = '$descripcion_segura', precio = $precio WHERE id_sabor = $id_sabor_act");
        }
    }
    header("Location: sabores.php");
    exit();
}

// OBTENER LISTA DE SABORES
$sql_sabores = "SELECT * FROM sabor ORDER BY nombre ASC";
$resultado_sabores = $conexion->query($sql_sabores);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Sabores</title>
    <link rel="stylesheet" href="estilos.css?v=<?php echo filemtime('estilos.css'); ?>">
    <link rel="stylesheet" href="tandas.css?v=<?php echo filemtime('tandas.css'); ?>">
</head>
<body>

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
            <h2>Catálogo de Sabores</h2>
            <button class="btn-nueva-tanda" onclick="abrirModalCrear()">+ Nuevo Sabor</button>
        </div>

        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th style="width: 30%;">NOMBRE</th>
                        <th style="width: 38%;">DESCRIPCIÓN</th>
                        <th style="width: 17%;" class="text-right">PRECIO (DOCENA)</th>
                        <th style="width: 15%;" class="text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_sabores && $resultado_sabores->num_rows > 0): ?>
                        <?php while($sabor = $resultado_sabores->fetch_assoc()): ?>
                            <tr>
                                <td class="col-nombre"><strong><?php echo htmlspecialchars($sabor['nombre']); ?></strong></td>
                                <td class="col-fecha"><?php echo htmlspecialchars($sabor['descripcion'] ?? ''); ?></td>
                                <td class="col-nombre text-right"><strong>$ <?php echo number_format($sabor['precio'], 2, ',', '.'); ?></strong></td>
                                <td class="text-center" style="padding: 10px;">
                                    <div class="contenedor-acciones">
                                        <button class="btn-icono-editar" title="Editar" onclick='abrirModalEditar(<?php echo $sabor['id_sabor']; ?>, <?php echo json_encode($sabor['nombre']); ?>, <?php echo json_encode($sabor['descripcion'] ?? ''); ?>, <?php echo json_encode($sabor['precio']); ?>)'>✏️</button>
                                        <a href="sabores.php?eliminar=<?php echo $sabor['id_sabor']; ?>" class="btn-icono-eliminar" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este sabor?');">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="tabla-vacia">No hay sabores cargados todavía.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal para Crear / Editar Sabor -->
    <div id="modalSabor" class="modal-overlay">
        <div class="modal-contenedor">
            <div class="modal-header">
                <h3 id="modalTitulo">Registrar Nuevo Sabor</h3>
                <span class="modal-cerrar" onclick="cerrarModalSabor()">&times;</span>
            </div>
            <form action="sabores.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="accion" name="accion" value="crear_sabor">
                    <input type="hidden" id="edit_id_sabor" name="id_sabor">
                    
                    <div class="grupo-input-modal">
                        <label for="nombre">Nombre del sabor:</label>
                        <input type="text" id="nombre" name="nombre" class="input-estandar-modal" required placeholder="Ej: Calabaza y Queso">
                    </div>

                    <div class="grupo-input-modal">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" class="input-estandar-modal" rows="3" placeholder="Ej: Calabaza con queso y condimentos"></textarea>
                    </div>

                    <div class="grupo-input-modal">
                        <label for="precio">Precio por docena ($):</label>
                        <input type="number" step="0.01" min="0" id="precio" name="precio" class="input-estandar-modal" required placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secundario" onclick="cerrarModalSabor()">Cancelar</button>
                    <button type="submit" class="btn-principal" id="btnGuardar">Guardar Sabor</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalCrear() {
            document.getElementById('accion').value = 'crear_sabor';
            document.getElementById('edit_id_sabor').value = '';
            document.getElementById('modalTitulo').textContent = 'Registrar Nuevo Sabor';
            document.getElementById('btnGuardar').textContent = 'Guardar Sabor';
            
            document.getElementById('nombre').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('precio').value = '';

            document.getElementById('modalSabor').classList.add('activo');
        }

        function abrirModalEditar(idSabor, nombre, descripcion, precio) {
            document.getElementById('accion').value = 'actualizar_sabor';
            document.getElementById('edit_id_sabor').value = idSabor;
            document.getElementById('modalTitulo').textContent = 'Editar Sabor - ' + nombre;
            document.getElementById('btnGuardar').textContent = 'Guardar Cambios';
            
            document.getElementById('nombre').value = nombre;
            document.getElementById('descripcion').value = descripcion;
            document.getElementById('precio').value = precio;

            document.getElementById('modalSabor').classList.add('activo');
        }

        function cerrarModalSabor() {
            document.getElementById('modalSabor').classList.remove('activo');
        }
    </script>
</body>
</html>