<?php
session_start();

// Validar que el usuario haya iniciado sesión; si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include 'conexion.php';

// Obtener la lista de clientes[cite: 10]
$sql_clientes = "SELECT id_cliente, nombre, telefono FROM cliente ORDER BY nombre ASC";
$resultado_clientes = $conexion->query($sql_clientes);

// Obtener las tandas activas[cite: 10]
$sql_tandas = "SELECT id_prod, nombre, fecha FROM produccion WHERE finalizada = 0 ORDER BY fecha ASC";
$resultado_tandas = $conexion->query($sql_tandas);

// Obtener los sabores del catálogo[cite: 10]
$sql_sabores = "SELECT id_sabor, nombre, precio FROM sabor ORDER BY nombre ASC";
$resultado_sabores = $conexion->query($sql_sabores);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pedido - Sorrentinos</title>
    <link rel="stylesheet" href="estilos.css?v=<?php echo filemtime('estilos.css'); ?>">
    <link rel="stylesheet" href="pedidos.css?v=<?php echo filemtime('pedidos.css'); ?>">
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

    <main class="contenido-pedido">

        <div class="contenedor-volver">
            <a href="index.php" class="btn-volver">←</a>
        </div>

        <div class="cabecera-seccion">
            <h2>Registrar Pedido</h2>
        </div>

        <!-- Formulario apuntando correctamente a guardar_pedido.php[cite: 10] -->
        <form action="guardar_pedido.php" method="POST" id="formPedido">
            <div class="grid-registro-pedido">

                <!-- Columna Izquierda -->
                <div class="columna-izquierda">

                    <!-- 1. Módulo: Buscar Cliente -->
                    <section class="tarjeta-modulo">
                        <div class="tarjeta-modulo-header">
                            <span class="icono-modulo">👤</span>
                            <h3>SELECCIONAR CLIENTE</h3>
                        </div>
                        <div class="grupo-input">
                            <label for="buscadorCliente">Buscar cliente registrado <span class="obligatorio">*</span></label>
                            <div class="buscador-input-contenedor">
                                <span class="icono-lupa">🔍</span>
                                <input type="text" id="buscadorCliente" placeholder="Escribí para buscar cliente..." class="input-estandar" autocomplete="off" onkeyup="filtrarClientes()">
                            </div>
                            <input type="hidden" id="idClienteSeleccionado" name="id_cliente" required>
                            
                            <div id="listaClientesDropdown" class="dropdown-clientes-resultados" style="display: none;">
                                <?php if ($resultado_clientes &&$resultado_clientes->num_rows > 0): ?>
                                    <?php while($cli =$resultado_clientes->fetch_assoc()): ?>
                                        <div class="item-cliente-opcion" onclick="seleccionarCliente('<?php echo $cli['id_cliente']; ?>', '<?php echo addslashes($cli['nombre']); ?>')">
                                            <strong><?php echo htmlspecialchars($cli['nombre']); ?></strong> 
                                            <span class="text-muted">(Tel: <?php echo htmlspecialchars($cli['telefono']); ?>)</span>
                                        </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <!-- 2. Módulo: Seleccionar Tanda Activa -->
                    <section class="tarjeta-modulo">
                        <div class="tarjeta-modulo-header">
                            <span class="icono-modulo">📅</span>
                            <h3>ASIGNAR A TANDA DE PRODUCCIÓN</h3>
                        </div>
                        <div class="grupo-input">
                            <label for="selectTanda">Tanda activa <span class="obligatorio">*</span></label>
                            <select id="selectTanda" name="id_prod" class="input-estandar" required>
                                <option value="">Seleccioná una tanda de producción...</option>
                                <?php if ($resultado_tandas &&$resultado_tandas->num_rows > 0): ?>
                                    <?php while($tanda =$resultado_tandas->fetch_assoc()): ?>
                                        <option value="<?php echo $tanda['id_prod']; ?>">
                                            <?php echo htmlspecialchars($tanda['nombre']); ?> (<?php echo date('d/m/Y', strtotime($tanda['fecha'])); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </section>

                    <!-- 3. Módulo: Sabores y Cantidades -->
                    <section class="tarjeta-modulo">
                        <div class="tarjeta-modulo-header">
                            <span class="icono-modulo">📦</span>
                            <h3>SABORES Y CANTIDADES</h3>
                        </div>
                        
                        <table class="tabla-sabores" id="tablaSabores">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">SABOR</th>
                                    <th class="text-center" style="width: 25%;">CANT. (DOCENAS)</th>
                                    <th class="text-right" style="width: 30%;">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado_sabores &&$resultado_sabores->num_rows > 0): ?>
                                    <?php while($sab =$resultado_sabores->fetch_assoc()): ?>
                                        <tr data-sabor="<?php echo htmlspecialchars($sab['nombre']); ?>" data-precio="<?php echo $sab['precio']; ?>">
                                            <td class="col-sabor-nombre">
                                                <strong><?php echo htmlspecialchars($sab['nombre']); ?></strong>
                                                <span style="display:block; font-size:0.8em; color:#777;">$<?php echo number_format($sab['precio'], 2, ',', '.'); ?> c/doc</span>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" min="0" value="0" name="sabores[<?php echo $sab['id_sabor']; ?>]" class="input-cantidad" onchange="calcularTotales()" onkeyup="calcularTotales()">
                                            </td>
                                            <td class="text-right subtotal-item">$ 0,00</td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center" style="color: #777; padding: 20px;">No hay sabores cargados en el catálogo.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>

                </div>

                <!-- Columna Derecha (Resumen y Guardar) -->
                <div class="columna-derecha">

                    <section class="tarjeta-resumen-fija">
                        <div class="tarjeta-modulo-header">
                            <span class="icono-modulo">📋</span>
                            <h3>RESUMEN DEL PEDIDO</h3>
                        </div>

                        <div id="resumenClienteInfo" class="resumen-cliente-caja">
                            <span class="cliente-no-seleccionado">⚠️ Ningún cliente seleccionado</span>
                        </div>

                        <div class="resumen-linea">
                            <span>Total de docenas</span>
                            <span id="totalDocenas" class="valor-resumen-num">0</span>
                        </div>

                        <div class="resumen-desglose-seccion">
                            <p class="titulo-desglose">Detalle por sabor:</p>
                            <div id="contenedorDesgloseSabores" class="lista-desglose-sabores">
                                <span class="texto-sin-sabores">Sin sabores seleccionados</span>
                            </div>
                        </div>

                        <hr class="divisor-resumen">
                        
                        <div class="resumen-linea total-final-linea">
                            <span>Total</span>
                            <span id="totalPrecio" class="valor-total-dinero">$ 0,00</span>
                        </div>
                    </section>

                    <div class="contenedor-btn-guardar">
                        <button type="submit" class="btn-guardar-pedido">
                            💾 Guardar Pedido
                        </button>
                    </div>

                </div>

            </div>
        </form>

    </main>

    <script>
        function filtrarClientes() {
            let filtro = document.getElementById('buscadorCliente').value.toLowerCase();
            let items = document.querySelectorAll('#listaClientesDropdown .item-cliente-opcion');
            let dropdown = document.getElementById('listaClientesDropdown');

            if (filtro.trim() === '') {
                dropdown.style.display = 'none';
                return;
            }

            dropdown.style.display = 'block';
            items.forEach(item => {
                let texto = item.textContent.toLowerCase();
                item.style.display = texto.includes(filtro) ? 'block' : 'none';
            });
        }

        function seleccionarCliente(id, nombre) {
            document.getElementById('buscadorCliente').value = nombre;
            document.getElementById('idClienteSeleccionado').value = id;
            document.getElementById('listaClientesDropdown').style.display = 'none';

            let cajaResumenCli = document.getElementById('resumenClienteInfo');
            cajaResumenCli.innerHTML = '<strong>Cliente:</strong> ' + nombre;
            cajaResumenCli.style.color = '#2e7d32';
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.grupo-input')) {
                let dropdown = document.getElementById('listaClientesDropdown');
                if (dropdown) dropdown.style.display = 'none';
            }
        });

        function calcularTotales() {
            let filas = document.querySelectorAll('#tablaSabores tbody tr');
            let totalDocenas = 0;
            let totalPrecio = 0;
            let htmlDesglose = '';

            filas.forEach(fila => {
                let nombreSabor = fila.getAttribute('data-sabor');
                let precioUnitario = parseFloat(fila.getAttribute('data-precio')) || 0;
                let inputCantidad = fila.querySelector('.input-cantidad');
                if(!inputCantidad) return;
                
                let cantidad = parseInt(inputCantidad.value) || 0;
                if (cantidad < 0) { cantidad = 0; inputCantidad.value = 0; }

                let subtotal = cantidad * precioUnitario;
                fila.querySelector('.subtotal-item').textContent = '$ ' + subtotal.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                totalDocenas += cantidad;
                totalPrecio += subtotal;

                if (cantidad > 0) {
                    htmlDesglose += `<div class="item-desglose-fila"><span>${nombreSabor}</span> <strong>${cantidad} doc.</strong></div>`;
                }
            });

            document.getElementById('totalDocenas').textContent = totalDocenas;
            document.getElementById('totalPrecio').textContent = '$ ' + totalPrecio.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            let contenedorDesglose = document.getElementById('contenedorDesgloseSabores');
            contenedorDesglose.innerHTML = (htmlDesglose === '') ? '<span class="texto-sin-sabores">Sin sabores seleccionados</span>' : htmlDesglose;
        }
    </script>

</body>
</html>