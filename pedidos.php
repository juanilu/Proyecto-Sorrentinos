<?php
// Incluir la conexión centralizada
include 'conexion.php';

// Obtener la lista de clientes registrados para el buscador
$sql_clientes = "SELECT id_cliente, nombre, telefono FROM cliente ORDER BY nombre ASC";
$resultado_clientes = $conexion->query($sql_clientes);

// Obtener las tandas de producción activas (pendientes) para el selector
$sql_tandas = "SELECT * FROM produccion WHERE finalizada = 0 ORDER BY fecha ASC";
$resultado_tandas = $conexion->query($sql_tandas);
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
        
        <!-- Menú de Usuario con Desplegable -->
        <div class="usuario-menu">
            <span class="usuario-texto">👤 (Admin) ▾</span>
            <div class="usuario-dropdown">
                <a href="logout.php">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="contenido-pedido">

        <!-- Botón de Volver -->
        <div class="contenedor-volver">
            <a href="index.php" class="btn-volver">←</a>
        </div>

        <!-- Título Principal -->
        <div class="cabecera-seccion">
            <h2>Registrar Pedido</h2>
        </div>

        <!-- Layout de Dos Columnas -->
        <div class="grid-registro-pedido">

            <!-- Columna Izquierda -->
            <div class="columna-izquierda">

                <!-- 1. Módulo: Buscar Cliente -->
                <section class="tarjeta-modulo">
                    <div class="tarjeta-modulo-header">
                        <span class="icono-modulo">👤</span>
                        <h3>SELECCIONAR CLIENTE</h3>
                    </div>
                    <div class="grupo-input" style="position: relative;">
                        <label for="buscadorCliente">Buscar cliente registrado <span class="obligatorio">*</span></label>
                        <div class="buscador-input-contenedor">
                            <span class="icono-lupa">🔍</span>
                            <input type="text" id="buscadorCliente" placeholder="Escribí para buscar cliente..." class="input-estandar" autocomplete="off" onkeyup="filtrarClientes()">
                        </div>
                        <input type="hidden" id="idClienteSeleccionado" name="id_cliente">
                        
                        <!-- Lista desplegable de resultados de clientes -->
                        <div id="listaClientesDropdown" class="dropdown-clientes-resultados" style="display: none;">
                            <?php if ($resultado_clientes &&$resultado_clientes->num_rows > 0): ?>
                                <?php while($cli =$resultado_clientes->fetch_assoc()): ?>
                                    <div class="item-cliente-opcion" onclick="seleccionarCliente('<?php echo $cli['id_cliente']; ?>', '<?php echo addslashes($cli['nombre']); ?>')">
                                        <strong><?php echo htmlspecialchars($cli['nombre']); ?></strong> 
                                        <span class="text-muted">(Tel: <?php echo htmlspecialchars($cli['telefono']); ?>)</span>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="item-cliente-opcion">No hay clientes registrados</div>
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
                        <select id="selectTanda" class="input-estandar">
                            <option value="">Seleccioná una tanda de producción...</option>
                            <?php if ($resultado_tandas &&$resultado_tandas->num_rows > 0): ?>
                                <?php while($tanda =$resultado_tandas->fetch_assoc()): ?>
                                    <?php 
                                        $id_tanda = isset($tanda['id_produccion']) ?$tanda['id_produccion'] : (isset($tanda['id']) ?$tanda['id'] : 0);
                                    ?>
                                    <option value="<?php echo $id_tanda; ?>">
                                        Tanda del <?php echo date('d/m/Y', strtotime($tanda['fecha'])); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay tandas activas creadas</option>
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
                            <!-- Sabor 1 -->
                            <tr data-sabor="Jamón y Queso" data-precio="3500">
                                <td class="col-sabor-nombre">Jamón y Queso</td>
                                <td class="text-center">
                                    <input type="number" min="0" value="0" class="input-cantidad" onchange="calcularTotales()" onkeyup="calcularTotales()">
                                </td>
                                <td class="text-right subtotal-item">$ 0,00</td>
                            </tr>
                            <!-- Sabor 2 -->
                            <tr data-sabor="Calabaza" data-precio="3500">
                                <td class="col-sabor-nombre">Calabaza</td>
                                <td class="text-center">
                                    <input type="number" min="0" value="0" class="input-cantidad" onchange="calcularTotales()" onkeyup="calcularTotales()">
                                </td>
                                <td class="text-right subtotal-item">$ 0,00</td>
                            </tr>
                            <!-- Sabor 3 -->
                            <tr data-sabor="Caprese" data-precio="3800">
                                <td class="col-sabor-nombre">Caprese</td>
                                <td class="text-center">
                                    <input type="number" min="0" value="0" class="input-cantidad" onchange="calcularTotales()" onkeyup="calcularTotales()">
                                </td>
                                <td class="text-right subtotal-item">$ 0,00</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

            </div>

            <!-- Columna Derecha (Resumen y Guardar) -->
            <div class="columna-derecha">

                <!-- Tarjeta Resumen del Pedido con Desglose -->
                <section class="tarjeta-modulo tarjeta-resumen-fija">
                    <div class="tarjeta-modulo-header">
                        <span class="icono-modulo">📋</span>
                        <h3>RESUMEN DEL PEDIDO</h3>
                    </div>

                    <!-- Cliente seleccionado info -->
                    <div id="resumenClienteInfo" class="resumen-cliente-caja">
                        <span class="cliente-no-seleccionado">⚠️ Ningún cliente seleccionado</span>
                    </div>

                    <div class="resumen-linea">
                        <span>Total de docenas</span>
                        <span id="totalDocenas" class="valor-resumen-num">0</span>
                    </div>

                    <!-- Desglose por sabor -->
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

                <!-- Botón Guardar Pedido -->
                <div class="contenedor-btn-guardar">
                    <button type="button" class="btn-guardar-pedido" onclick="guardarPedido()">
                        💾 Guardar Pedido
                    </button>
                </div>

            </div>

        </div>

    </main>

    <!-- Script de lógica e interactividad -->
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
            let hayCoincidencias = false;

            items.forEach(item => {
                let texto = item.textContent.toLowerCase();
                if (texto.includes(filtro)) {
                    item.style.display = 'block';
                    hayCoincidencias = true;
                } else {
                    item.style.display = 'none';
                }
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
                let cantidad = parseInt(inputCantidad.value) || 0;

                if (cantidad < 0) {
                    cantidad = 0;
                    inputCantidad.value = 0;
                }

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
            if (htmlDesglose === '') {
                contenedorDesglose.innerHTML = '<span class="texto-sin-sabores">Sin sabores seleccionados</span>';
            } else {
                contenedorDesglose.innerHTML = htmlDesglose;
            }
        }

        function limpiarFormularioPedido() {
            document.getElementById('buscadorCliente').value = '';
            document.getElementById('idClienteSeleccionado').value = '';
            
            let cajaResumenCli = document.getElementById('resumenClienteInfo');
            cajaResumenCli.innerHTML = '<span class="cliente-no-seleccionado">⚠️ Ningún cliente seleccionado</span>';
            cajaResumenCli.style.color = '';

            document.getElementById('selectTanda').selectedIndex = 0;

            document.querySelectorAll('.input-cantidad').forEach(input => {
                input.value = 0;
            });

            calcularTotales();
        }

        function guardarPedido() {
            let idCliente = document.getElementById('idClienteSeleccionado').value;
            let idTanda = document.getElementById('selectTanda').value;
            let totalDocenas = parseInt(document.getElementById('totalDocenas').textContent);

            if (!idCliente) {
                alert('Por favor, seleccioná un cliente válido de la lista.');
                return;
            }
            if (!idTanda) {
                alert('Por favor, seleccioná una tanda de producción activa para este pedido.');
                return;
            }
            if (totalDocenas <= 0) {
                alert('El pedido debe contener al menos una docena de sorrentinos.');
                return;
            }

            alert('¡Pedido registrado y asignado a la tanda correctamente!');
            limpiarFormularioPedido();
        }
    </script>

</body>
</html>