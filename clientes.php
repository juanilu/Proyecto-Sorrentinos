<?php
include 'conexion.php';

// PROCESAR ELIMINACIÓN DE UN CLIENTE
if (isset($_GET['eliminar'])) {
    $id_cliente_eliminar = intval($_GET['eliminar']);
    if ($id_cliente_eliminar > 0) {
        $conexion->query("DELETE FROM cliente WHERE id_cliente = $id_cliente_eliminar");
    }
    header("Location: clientes.php");
    exit();
}

// PROCESAR CREACIÓN O ACTUALIZACIÓN DE CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($_POST['accion'] === 'crear_cliente') {
        if (!empty($nombre)) {
            $nombre_seguro = $conexion->real_escape_string($nombre);
            $telefono_seguro = $conexion->real_escape_string($telefono);
            $direccion_segura = $conexion->real_escape_string($direccion);

            $conexion->query("INSERT INTO cliente (nombre, telefono, direccion) VALUES ('$nombre_seguro', '$telefono_seguro', '$direccion_segura')");
        }
    } elseif ($_POST['accion'] === 'actualizar_cliente') {
        $id_cliente_act = intval($_POST['id_cliente'] ?? 0);
        if ($id_cliente_act > 0 && !empty($nombre)) {
            $nombre_seguro = $conexion->real_escape_string($nombre);
            $telefono_seguro = $conexion->real_escape_string($telefono);
            $direccion_segura = $conexion->real_escape_string($direccion);
            
            $conexion->query("UPDATE cliente SET nombre = '$nombre_seguro', telefono = '$telefono_seguro', direccion = '$direccion_segura' WHERE id_cliente = $id_cliente_act");
        }
    }
    header("Location: clientes.php");
    exit();
}

// OBTENER TODOS LOS CLIENTES
$sql_clientes = "SELECT * FROM cliente ORDER BY nombre ASC";
$resultado_clientes = $conexion->query($sql_clientes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
    <link rel="stylesheet" href="estilos.css?v=<?php echo filemtime('estilos.css'); ?>">
    <link rel="stylesheet" href="tandas.css?v=<?php echo filemtime('tandas.css'); ?>">
</head>
<body>

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
            <h2>Gestión de Clientes</h2>
            <button class="btn-nueva-tanda" onclick="abrirModalCrear()">+ Nuevo Cliente</button>
        </div>

        <!-- Buscador en tiempo real con lupa -->
        <div class="seccion-busqueda" style="margin-bottom: 20px; position: relative; display: flex; align-items: center;">
            <span style="position: absolute; left: 14px; font-size: 1.1em; pointer-events: none;">🔍</span>
            <input type="text" id="inputBuscador" onkeyup="filtrarClientes()" placeholder="Buscar cliente por nombre, teléfono o dirección..." class="input-busqueda" style="width: 100%; padding: 10px 14px 10px 42px; border: 1px solid #d8cec2; border-radius: 6px; font-size: 0.95em; outline: none; background: #fff; box-sizing: border-box;">
        </div>

        <div class="tabla-contenedor">
            <table class="tabla-datos" id="tablaClientes">
                <thead>
                    <tr>
                        <th style="width: 30%;">NOMBRE</th>
                        <th style="width: 25%;">TELÉFONO</th>
                        <th style="width: 30%;">DIRECCIÓN</th>
                        <th style="width: 15%;" class="text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_clientes && $resultado_clientes->num_rows > 0): ?>
                        <?php while($cliente = $resultado_clientes->fetch_assoc()): ?>
                            <tr class="fila-cliente">
                                <td class="col-nombre"><strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong></td>
                                <td class="col-fecha"><?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?></td>
                                <td class="col-fecha"><?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?></td>
                                <td class="text-center" style="padding: 10px;">
                                    <div class="contenedor-acciones">
                                        <button class="btn-icono-editar" title="Editar" onclick='abrirModalEditar(<?php echo $cliente['id_cliente']; ?>, <?php echo json_encode($cliente['nombre']); ?>, <?php echo json_encode($cliente['telefono'] ?? ''); ?>, <?php echo json_encode($cliente['direccion'] ?? ''); ?>)'>✏️</button>
                                        <a href="clientes.php?eliminar=<?php echo $cliente['id_cliente']; ?>" class="btn-icono-eliminar" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este cliente?');">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr id="filaSinDatos">
                            <td colspan="4" class="tabla-vacia">No hay clientes cargados todavía.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal para Crear / Editar Cliente -->
    <div id="modalCliente" class="modal-overlay">
        <div class="modal-contenedor">
            <div class="modal-header">
                <h3 id="modalTitulo">Registrar Nuevo Cliente</h3>
                <span class="modal-cerrar" onclick="cerrarModalCliente()">&times;</span>
            </div>
            <form action="clientes.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="accion" name="accion" value="crear_cliente">
                    <input type="hidden" id="edit_id_cliente" name="id_cliente">
                    
                    <div class="grupo-input-modal">
                        <label for="nombre">Nombre y Apellido:</label>
                        <input type="text" id="nombre" name="nombre" class="input-estandar-modal" required placeholder="Ej: Juan Pérez">
                    </div>

                    <div class="grupo-input-modal">
                        <label for="telefono">Teléfono / WhatsApp:</label>
                        <input type="text" id="telefono" name="telefono" class="input-estandar-modal" placeholder="Ej: 2994123456">
                    </div>

                    <div class="grupo-input-modal">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" class="input-estandar-modal" placeholder="Ej: Av. Argentina 123">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secundario" onclick="cerrarModalCliente()">Cancelar</button>
                    <button type="submit" class="btn-principal" id="btnGuardar">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Función para quitar tildes y normalizar texto
        function normalizarTexto(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        // Búsqueda interactiva en tiempo real (ignora mayúsculas y tildes)
        function filtrarClientes() {
            const inputRaw = document.getElementById('inputBuscador').value;
            const filtro = normalizarTexto(inputRaw);
            const filas = document.querySelectorAll('.fila-cliente');

            filas.forEach(fila => {
                const textoFila = normalizarTexto(fila.textContent);
                if (textoFila.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        function abrirModalCrear() {
            document.getElementById('accion').value = 'crear_cliente';
            document.getElementById('edit_id_cliente').value = '';
            document.getElementById('modalTitulo').textContent = 'Registrar Nuevo Cliente';
            document.getElementById('btnGuardar').textContent = 'Guardar Cliente';
            
            document.getElementById('nombre').value = '';
            document.getElementById('telefono').value = '';
            document.getElementById('direccion').value = '';

            document.getElementById('modalCliente').classList.add('activo');
        }

        function abrirModalEditar(idCliente, nombre, telefono, direccion) {
            document.getElementById('accion').value = 'actualizar_cliente';
            document.getElementById('edit_id_cliente').value = idCliente;
            document.getElementById('modalTitulo').textContent = 'Editar Cliente - ' + nombre;
            document.getElementById('btnGuardar').textContent = 'Guardar Cambios';
            
            document.getElementById('nombre').value = nombre;
            document.getElementById('telefono').value = telefono;
            document.getElementById('direccion').value = direccion;

            document.getElementById('modalCliente').classList.add('activo');
        }

        function cerrarModalCliente() {
            document.getElementById('modalCliente').classList.remove('activo');
        }
    </script>
</body>
</html>