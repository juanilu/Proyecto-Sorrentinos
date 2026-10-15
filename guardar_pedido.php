<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = intval($_POST['id_cliente'] ?? 0);
    $id_prod = intval($_POST['id_prod'] ?? 0);

    if ($id_cliente <= 0 || $id_prod <= 0) {
        echo "<script>alert('Error: Debe seleccionar un cliente y una tanda válida.'); window.history.back();</script>";
        exit();
    }

    // 1. Insertar el pedido principal vinculado a la tanda y al cliente
    $sql_pedido = "INSERT INTO pedido (id_cliente, id_prod, observacion) VALUES ($id_cliente, $id_prod, '')";
    
    if ($conexion->query($sql_pedido) === TRUE) {
        $id_pedido_nuevo = $conexion->insert_id;

        // 2. Insertar cada sabor seleccionado en la tabla detallepedido
        if (isset($_POST['sabores']) && is_array($_POST['sabores'])) {
            foreach ($_POST['sabores'] as $id_sabor => $cantidad) {
                $cantidad = intval($cantidad);
                if ($cantidad > 0) {
                    $id_sabor_escapado = $conexion->real_escape_string($id_sabor);
                    $sql_detalle = "INSERT INTO detallepedido (id_pedido, id_sabor, cantidad) VALUES ($id_pedido_nuevo, '$id_sabor_escapado', $cantidad)";
                    $conexion->query($sql_detalle);
                }
            }
        }

        // Redirige correctamente a pedidos.php en plural
        echo "<script>alert('¡Pedido guardado correctamente!'); window.location.href='pedidos.php';</script>";
        exit();
    } else {
        echo "Error al guardar el pedido: " . $conexion->error;
    }
}
?>