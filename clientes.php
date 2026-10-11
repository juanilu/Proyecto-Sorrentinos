<?php
// Incluir la conexión centralizada
include 'conexion.php';

// Obtener todos los clientes de la base de datos
$sql_clientes = "SELECT nombre, telefono, direccion FROM cliente ORDER BY id_cliente ASC";
$resultado_clientes = $conexion->query($sql_clientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Sorrentinos</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="clientes.css">
</head>
<body>

    <!-- Barra Superior -->
    <header class="barra-superior">
        <h1 class="marca">🥟 GESTIÓN DE SORRENTINOS</h1>
        
        <!-- Menú de Usuario con Desplegable -->
        <div class="usuario-menu">
            <span class="usuario-texto">👤 (Admin) ▾</span>
            <div class="usuario-dropdown">
                <a href="logout.php">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="contenido-clientes">

        <!-- Botón de Volver -->
        <div class="contenedor-volver">
            <a href="index.php" class="btn-volver">←</a>
        </div>

        <!-- Cabecera con Título y Botón Nuevo Cliente -->
        <div class="cabecera-seccion">
            <h2>Clientes</h2>
            <a href="nuevo_cliente.php" class="btn-nuevo-cliente">+ Nuevo cliente</a>
        </div>

        <!-- Buscador -->
        <div class="buscador-contenedor">
            <span class="icono-lupa">🔍</span>
            <input type="text" id="buscador" placeholder="Buscar clientes..." class="input-buscador">
        </div>

        <!-- Tabla de Clientes -->
        <div class="tabla-contenedor">
            <table class="tabla-datos" id="tablaClientes">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>NÚMERO DE TELÉFONO</th>
                        <th>DIRECCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_clientes && $resultado_clientes->num_rows > 0): ?>
                        <?php while($cliente = $resultado_clientes->fetch_assoc()): ?>
                            <tr>
                                <td class="col-nombre"><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                <td class="col-telefono"><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                <td class="col-direccion"><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="tabla-vacia">No hay clientes registrados en el sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <!-- Script de búsqueda enfocado exclusivamente en el nombre (Ignora tildes y mayúsculas) -->
    <script>
        function normalizarTexto(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        document.getElementById('buscador').addEventListener('keyup', function() {
            let filtro = normalizarTexto(this.value);
            let filas = document.querySelectorAll('#tablaClientes tbody tr');

            filas.forEach(fila => {
                let celdaNombre = fila.querySelector('.col-nombre');
                if (celdaNombre) {
                    let textoNombre = normalizarTexto(celdaNombre.textContent);
                    if (textoNombre.includes(filtro)) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                }
            });
        });
    </script>

</body>
</html>