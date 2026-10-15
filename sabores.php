<?php
// Incluir la conexión centralizada a la base de datos
include 'conexion.php';

// Intentar consultar la tabla 'sabor' de manera segura
$resultado_sabores = null;
if ($conexion) {
    $resultado_sabores = @$conexion->query("SELECT * FROM sabor ORDER BY nombre ASC");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Sabores - Gestión de Sorrentinos</title>
    <link rel="stylesheet" href="estilos.css?v=<?php echo filemtime('estilos.css'); ?>">
    <link rel="stylesheet" href="sabores.css?v=<?php echo filemtime('sabores.css'); ?>">
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

    <!-- Contenido Principal -->
    <main class="contenido-sabores">

        <!-- Botón de Volver -->
        <div class="contenedor-volver">
            <a href="index.php" class="btn-volver">←</a>
        </div>

        <!-- Cabecera de la Sección (Título y Botón Nuevo Sabor) -->
        <div class="cabecera-seccion-sabores">
            <h2>Catálogo de Sabores</h2>
            <a href="nuevo_sabor.php" class="btn-nuevo-sabor">+ Nuevo sabor</a>
        </div>

        <!-- Tarjeta Contenedora de la Tabla -->
        <section class="tarjeta-tabla-sabores">
            <table class="tabla-sabores-catalogo">
                <thead>
                    <tr>
                        <th style="width: 30%;">NOMBRE</th>
                        <th style="width: 55%;">DESCRIPCIÓN</th>
                        <th class="text-center" style="width: 15%;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_sabores && $resultado_sabores->num_rows > 0): ?>
                        <?php while($sabor = $resultado_sabores->fetch_assoc()): ?>
                            <?php 
                                // Detectar el nombre correcto de la columna ID en tu tabla 'sabor'
                                $id_sabor = isset($sabor['id']) ? $sabor['id'] : (isset($sabor['id_sabor']) ? $sabor['id_sabor'] : 1); 
                            ?>
                            <tr>
                                <td class="sabor-nombre-td">
                                    <strong><?php echo htmlspecialchars($sabor['nombre']); ?></strong>
                                </td>
                                <td class="sabor-desc-td">
                                    <?php echo htmlspecialchars($sabor['descripcion']); ?>
                                </td>
                                <td class="text-center">
                                    <div class="acciones-botones">
                                        <a href="editar_sabor.php?id=<?php echo $id_sabor; ?>" class="btn-accion btn-editar" title="Editar sabor">
                                            ✏️
                                        </a>
                                        <a href="eliminar_sabor.php?id=<?php echo $id_sabor; ?>" class="btn-accion btn-eliminar" title="Eliminar sabor" onclick="return confirm('¿Estás seguro de eliminar este sabor?');">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Datos por defecto para visualización limpia si la tabla está vacía -->
                        <tr>
                            <td class="sabor-nombre-td"><strong>Jamón y Queso</strong></td>
                            <td class="sabor-desc-td">Clásico relleno de jamón cocido y queso cremoso.</td>
                            <td class="text-center">
                                <div class="acciones-botones">
                                    <a href="#" class="btn-accion btn-editar" title="Editar">✏️</a>
                                    <a href="#" class="btn-accion btn-eliminar" title="Eliminar">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="sabor-nombre-td"><strong>Calabaza</strong></td>
                            <td class="sabor-desc-td">Calabaza asada con especias y queso parmesano.</td>
                            <td class="text-center">
                                <div class="acciones-botones">
                                    <a href="#" class="btn-accion btn-editar" title="Editar">✏️</a>
                                    <a href="#" class="btn-accion btn-eliminar" title="Eliminar">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="sabor-nombre-td"><strong>Caprese</strong></td>
                            <td class="sabor-desc-td">Tomate, muzzarella y albahaca fresca.</td>
                            <td class="text-center">
                                <div class="acciones-botones">
                                    <a href="#" class="btn-accion btn-editar" title="Editar">✏️</a>
                                    <a href="#" class="btn-accion btn-eliminar" title="Eliminar">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

</body>
</html>