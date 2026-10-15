<?php
include 'conexion.php';

$id_tanda = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (isset($_GET['eliminar'])) {
    if (intval($_GET['eliminar']) > 0) {$id_ped_eliminar = intval($_GET['eliminar']);$conexion->query("DELETE FROM pedido WHERE id_pedido = $id_ped_eliminar");
        header("Location: detalle_tanda.php?id=" . $id_tanda);
        exit();
    }
}

$es_post = ($_SERVER['REQUEST_METHOD'] === 'POST');
$tiene_accion = isset($_POST['accion']);

if ($es_post AND$tiene_accion) {
    if ($_POST['accion'] === 'actualizar') {
        $id_pedido = intval($_POST['id_pedido'] ?? 0);
        if ($id_pedido > 0) {$conexion->query("DELETE FROM detallepedido WHERE id_pedido = $id_pedido");
            if (isset($_POST['sabores']) AND is_array($_POST['sabores'])) {
                foreach ($_POST['sabores'] as $id_sabor =>$cantidad) {
                    $cantidad = intval($cantidad);
                    if ($cantidad > 0) {
                        $sabor_seguro =$conexion->real_escape_string($id_sabor);$conexion->query("INSERT INTO detallepedido (id_pedido, id_sabor, cantidad) VALUES ($id_pedido, '$sabor_seguro',$cantidad)");
                    }
                }
            }
        }
        header("Location: detalle_tanda.php?id=" . $id_tanda);
        exit();
    }
}

$sql_tanda = "SELECT * FROM produccion WHERE id_prod = $id_tanda";
$res_tanda = $conexion->query($sql_tanda);
if (!$res_tanda OR$res_tanda->num_rows === 0) {
    echo "Tanda de producción no encontrada.";
    exit();
}
$tanda =$res_tanda->fetch_assoc();

$sql_pedidos = "SELECT p.id_pedido, c.nombre AS nombre_cliente, c.telefono 
                FROM pedido p 
                JOIN cliente c ON p.id_cliente = c.id_cliente 
                WHERE p.id_prod = $id_tanda";
$resultado_pedidos = $conexion->query($sql_pedidos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Tanda - <?php echo htmlspecialchars($tanda['nombre']); ?></title>
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
            <a href="tandas.php" class="btn-volver">←</a>
        </div>

        <div class="cabecera-seccion">
            <h2><?php echo htmlspecialchars($tanda['nombre']); ?></h2>
            <span class="badge-estado <?php echo ($tanda['finalizada'] == 1) ? 'finalizada' : 'pendiente'; ?>">
                <?php echo ($tanda['finalizada'] == 1) ? 'Finalizada' : 'Pendiente / Activa'; ?>
            </span>
        </div>

        <div class="tarjeta-resumen" style="width: 100% !important; display: flex; justify-content: space-between; box-sizing: border-box;">
            <div>
                <span class="etiqueta-proxima">FECHA PROGRAMADA</span>
                <span class="fecha-proxima" style="font-size: 1.1em;"><?php echo date("d/m/Y", strtotime($tanda['fecha'])); ?></span>
            </div>
        </div>

        <h3 style="margin-top: 30px; margin-bottom: 15px; color: #111;">Pedidos cargados en esta tanda</h3>

        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th style="width: 35%;">CLIENTE</th>
                        <th style="width: 40%;">DETALLE DE SABORES</th>
                        <th style="width: 12%;" class="text-right">TOTAL</th>
                        <th style="width: 13%;" class="text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_pedidos AND$resultado_pedidos->num_rows > 0): ?>
                        <?php while($pedido =$resultado_pedidos->fetch_assoc()): ?>
                            <?php 
                                $id_ped = $pedido['id_pedido'];$sql_detalles = "SELECT d.cantidad, s.id_sabor, s.nombre AS nombre_sabor, s.precio 
                                                 FROM detallepedido d 
                                                 JOIN sabor s ON d.id_sabor = s.id_sabor 
                                                 WHERE d.id_pedido = $id_ped";
                                $res_detalles =$conexion->query($sql_detalles);$total_pedido = 0;
                                $detalles_array = array();$sabores_pedido = array();
                                
                                if ($res_detalles AND$res_detalles->num_rows > 0) {
                                    while($det = $res_detalles->fetch_assoc()) {$subtotal_item = $det['cantidad'] *$det['precio'];
                                        $total_pedido +=$subtotal_item;
                                        $detalles_array[] =$det['cantidad'] . " doc. de " . $det['nombre_sabor'];$sabores_pedido[$det['id_sabor']] =$det['cantidad'];
                                    }
                                }
                                $texto_detalles = !empty($detalles_array) ? implode(', ', $detalles_array) : 'Sin detalles';
                            ?>
                            <tr>
                                <td class="col-fecha">
                                    <strong><?php echo htmlspecialchars($pedido['nombre_cliente']); ?></strong><br>
                                    <span style="font-size: 0.85em; color: #777;">Tel: <?php echo htmlspecialchars($pedido['telefono']); ?></span>
                                </td>
                                <td class="col-fecha"><?php echo htmlspecialchars($texto_detalles); ?></td>
                                <td class="col-nombre text-right"><strong>$<?php echo number_format($total_pedido, 2, ',', '.'); ?></strong></td>
                                <td class="text-center" style="padding: 10px;">
                                    <div class="contenedor-acciones">
                                        <button class="btn-icono-editar" title="Editar" onclick='abrirModalEditar(<?php echo json_encode($id_ped); ?>, <?php echo json_encode($pedido['nombre_cliente']); ?>, <?php echo json_encode($sabores_pedido); ?>)'>✏️</button>
                                        <a href="detalle_tanda.php?id=<?php echo $id_tanda; ?>&eliminar=<?php echo$id_ped; ?>" class="btn-icono-eliminar" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este pedido?');">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="tabla-vacia">No hay pedidos cargados para esta tanda todavía.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal para Editar Pedido -->
    <div id="modalEditar" class="modal-overlay">
        <div class="modal-contenedor">
            <div class="modal-header">
                <h3 id="modalTitulo">Editar Pedido</h3>
                <span class="modal-cerrar" onclick="cerrarModalEditar()">&times;</span>
            </div>
            <form action="detalle_tanda.php?id=<?php echo $id_tanda; ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" id="edit_id_pedido" name="id_pedido">
                    <p style="margin: 0; font-size: 0.9em; color: #555;">Modificá la cantidad en docenas para cada sabor:</p>
                    <div id="contenedorSaboresEditables" style="display: flex; flex-direction: column; gap: 10px; max-height: 250px; overflow-y: auto; padding-right: 5px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secundario" onclick="cerrarModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-principal">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalEditar(idPedido, nombreCliente, saboresActuales) {
            document.getElementById('edit_id_pedido').value = idPedido;
            document.getElementById('modalTitulo').textContent = 'Editar Pedido - ' + nombreCliente;
            
            let contenedor = document.getElementById('contenedorSaboresEditables');
            contenedor.innerHTML = '';

            <?php 
                $res_todos =$conexion->query("SELECT id_sabor, nombre, precio FROM sabor ORDER BY nombre ASC");
                $array_sabores_js = array();
                if ($res_todos) {
                    while($s =$res_todos->fetch_assoc()) {
                        $array_sabores_js[] =$s;
                    }
                }
            ?>
            let catalogoSabores = <?php echo json_encode($array_sabores_js); ?>;

            catalogoSabores.forEach(sabor => {
                let cantidadActual = saboresActuales[sabor.id_sabor] || 0;
                let div = document.createElement('div');
                div.style.cssText = "display: flex; justify-content: space-between; align-items: center; background: #f9f6f0; padding: 10px; border-radius: 6px; border: 1px solid #e2d3c1;";
                div.innerHTML = `
                    <span style="font-size: 0.9em; font-weight: bold; color: #333;">${sabor.nombre} ($${sabor.precio})</span>
                    <input type="number" min="0" value="${cantidadActual}" name="sabores[${sabor.id_sabor}]" class="input-estandar-modal" style="width: 70px; text-align: center; padding: 6px;">
                `;
                contenedor.appendChild(div);
            });

            document.getElementById('modalEditar').classList.add('activo');
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditar').classList.remove('activo');
        }
    </script>
</body>
</html>