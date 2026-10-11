<?php
// Incluir la conexión centralizada a la base de datos
include 'conexion.php';

// Obtener la próxima tanda pendiente de producción
$sql_proxima = "SELECT fecha FROM produccion WHERE finalizada = 0 ORDER BY fecha ASC LIMIT 1";
$resultado_proxima = $conexion->query($sql_proxima);
$proxima_tanda = "No programada";

if ($resultado_proxima && $resultado_proxima->num_rows > 0) {
    $fila = $resultado_proxima->fetch_assoc();
    $timestamp = strtotime($fila['fecha']);
    $meses = ["", "de Enero", "de Febrero", "de Marzo", "de Abril", "de Mayo", "de Junio", "de Julio", "de Agosto", "de Septiembre", "de Octubre", "de Noviembre", "de Diciembre"];
    $dia = date('d', $timestamp);
    $mes_num = date('n', $timestamp);
    $proxima_tanda = $dia . " " . $meses[$mes_num];
}

// Contar pedidos activos
$pedidos_activos = 24; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Sorrentinos</title>
  <link rel="stylesheet" href="estilos.css">
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
  <main class="contenido">

    <h2 class="saludo">¡Hola, Productor!</h2>

    <!-- Tarjeta de Resumen Operativo (Elementos hacia la derecha) -->
    <section class="tarjeta-resumen">
      <div class="resumen-cabecera">
        <span class="icono-resumen-cabecera">📊</span>
        <h3 class="titulo-resumen">RESUMEN OPERATIVO</h3>
      </div>
      <div class="resumen-elementos">
        <div class="item-resumen">
          <div class="icono-caja">📅</div>
          <div>
            <p class="etiqueta-resumen">Próxima Tanda:</p>
            <p class="valor-resumen"><?php echo $proxima_tanda; ?></p>
          </div>
        </div>
        <div class="item-resumen">
          <div class="icono-caja">🛒</div>
          <div>
            <p class="etiqueta-resumen">Pedidos Activos:</p>
            <p class="valor-resumen"><?php echo $pedidos_activos; ?></p>
          </div>
        </div>
      </div>
    </section>

    <!-- Grilla de Menú Principal -->
    <section class="grilla-menu">

      <!-- Tarjeta 1: Tandas -->
      <a href="tandas.php" class="tarjeta-menu">
        <div class="tarjeta-contenido-izq">
          <div class="icono-menu">👨‍🍳</div>
          <div class="tarjeta-textos">
            <h3 class="titulo-menu">1. TANDAS DE PRODUCCIÓN</h3>
            <ul class="opciones-menu">
              <li>• Ver detalles de las tandas</li>
              <li>• Crear nueva tanda</li>
            </ul>
          </div>
        </div>
        <span class="flecha-indicadora">›</span>
      </a>

      <!-- Tarjeta 2: Clientes -->
      <a href="clientes.php" class="tarjeta-menu">
        <div class="tarjeta-contenido-izq">
          <div class="icono-menu">👥</div>
          <div class="tarjeta-textos">
            <h3 class="titulo-menu">2. CLIENTES</h3>
            <ul class="opciones-menu">
              <li>• Listado de clientes</li>
            </ul>
          </div>
        </div>
        <span class="flecha-indicadora">›</span>
      </a>

      <!-- Tarjeta 3: Sabores -->
      <a href="sabores.html" class="tarjeta-menu">
        <div class="tarjeta-contenido-izq">
          <div class="icono-menu">🌱</div>
          <div class="tarjeta-textos">
            <h3 class="titulo-menu">3. CATÁLOGO DE SABORES</h3>
            <ul class="opciones-menu">
              <li>• Ver catálogo de sabores</li>
              <li>• Agregar nuevo sabor</li>
            </ul>
          </div>
        </div>
        <span class="flecha-indicadora">›</span>
      </a>

      <!-- Tarjeta 4: Registrar Pedido -->
      <a href="pedido.html" class="tarjeta-menu">
        <div class="tarjeta-contenido-izq">
          <div class="icono-menu">📋</div>
          <div class="tarjeta-textos">
            <h3 class="titulo-menu">4. REGISTRAR PEDIDO</h3>
            <ul class="opciones-menu">
              <li>• Cargar pedido nuevo</li>
            </ul>
          </div>
        </div>
        <span class="flecha-indicadora">›</span>
      </a>

    </section>

  </main>

</body>
</html>