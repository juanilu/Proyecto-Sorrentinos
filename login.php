<?php
session_start();
include 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if (!empty($usuario) && !empty($contrasena)) {
        $usuario_seguro = $conexion->real_escape_string($usuario);
        
        // Consulta buscando por la columna 'usuario' en la tabla 'administrador'
        $resultado = $conexion->query("SELECT * FROM administrador WHERE usuario = '$usuario_seguro' LIMIT 1");
        
        if ($resultado && $resultado->num_rows === 1) {
            $user = $resultado->fetch_assoc();
            
            // Obtener el hash de la contraseña (soporta 'contrasena' o 'password')
            $hash_guardado = $user['contrasena'] ?? $user['password'] ?? '';

            if (password_verify($contrasena, $hash_guardado) || $contrasena === $hash_guardado) {
                $_SESSION['usuario'] = $user['usuario'];
                header("Location: index.php");
                exit();
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'El usuario no existe.';
        }
    } else {
        $error = 'Por favor, complete todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Gestión de Sorrentinos</title>
    <!-- Archivos CSS externos -->
    <link rel="stylesheet" href="estilos.css?v=<?php echo filemtime('estilos.css'); ?>">
    <link rel="stylesheet" href="tandas.css?v=<?php echo filemtime('tandas.css'); ?>">
    <link rel="stylesheet" href="login.css?v=<?php echo filemtime('login.css'); ?>">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <h1>Iniciar sesión</h1>
            <p>Ingresá tus credenciales para acceder al sistema.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mensaje-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="login-form-group">
                <label for="usuario">Usuario</label>
                <div class="input-con-icono">
                    <span class="icono-input">👤</span>
                    <input type="text" id="usuario" name="usuario" required placeholder="Ingresá tu usuario" autocomplete="off">
                </div>
            </div>

            <div class="login-form-group">
                <label for="contrasena">Contraseña</label>
                <div class="input-con-icono">
                    <span class="icono-input">🔒</span>
                    <input type="password" id="contrasena" name="contrasena" required placeholder="Ingresá tu contraseña">
                    <button type="button" class="btn-toggle-password" onclick="togglePassword()" title="Mostrar/Ocultar contraseña">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-login-submit">
                <span>➜</span> Iniciar sesión
            </button>
        </form>
    </div>

    <script>
        function togglePassword() {
            const inputPass = document.getElementById('contrasena');
            if (inputPass.type === 'password') {
                inputPass.type = 'text';
            } else {
                inputPass.type = 'password';
            }
        }
    </script>
</body>
</html>