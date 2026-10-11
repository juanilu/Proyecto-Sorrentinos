<?php
    // Datos de conexión centralizados
    $servidor = "localhost";
    $usuario = "root";
    $password = "";
    $base_datos = "sorrentinos";

    // Crear conexión usando MySQLi
    $conexion = new mysqli($servidor, $usuario, $password, $base_datos);

    // Verificar si hubo algún error de conexión
    if ($conexion->connect_error) {
        die("Error de conexión a la base de datos: " . $conexion->connect_error);
    }

    // Opcional: configurar codificación de caracteres para tildes y eñes
    $conexion->set_charset("utf8");
?>