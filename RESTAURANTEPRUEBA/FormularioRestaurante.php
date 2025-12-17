<?php
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dosis Formulario</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-mode"> <div class="login-card">
        <h1>DOSIS</h1>
        
        <?php if ($error == 'incorrecto'): ?>
            <div class="error-msg">Usuario o contraseña incorrectos</div>
        <?php elseif ($error == 'acceso_restringido'): ?>
            <div class="error-msg">Debes iniciar sesión para continuar</div>
        <?php endif; ?>

        <form action="comprobar.php" method="POST">
            
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" name="usu" id="usuario" placeholder="Introduce tu usuario" required>
            </div>

            <div class="form-group">
                <label for="clave">Contraseña</label>
                <input type="password" name="clave" id="clave" placeholder="Introduce tu contraseña" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            
            <p style="margin-top: 20px; font-size: 0.9rem;">
                ¿No tienes cuenta? <a href="registro.php" style="color: var(--color-primary); font-weight: bold;">Regístrate aquí</a>
            </p>
            
        </form>
    </div>

</body>
</html>