<?php
session_start();

$mensaje_error = "";
$mensaje_exito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $host = "localhost";
    $dbname = "BDrestaurante";
    $user = "root";
    $password = "";

    $correo    = $_POST['correo'] ?? '';
    $clave     = $_POST['clave'] ?? '';
    $pais      = $_POST['pais'] ?? '';
    $cp        = $_POST['cp'] ?? '';
    $ciudad    = $_POST['ciudad'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    if (empty($correo) || empty($clave) || empty($pais) || empty($cp) || empty($ciudad) || empty($direccion)) {
        $mensaje_error = "Por favor, rellena todos los campos.";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlCheck = "SELECT Count(*) FROM Restaurantes WHERE Correo = ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$correo]);
            $existe = $stmtCheck->fetchColumn();

            if ($existe > 0) {
                $mensaje_error = "Error: Ya existe un usuario registrado con ese Correo.";
            } else {
                $sqlInsert = "INSERT INTO Restaurantes (Correo, Clave, Pais, CP, Ciudad, Direccion) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                
                if ($stmtInsert->execute([$correo, $clave, $pais, $cp, $ciudad, $direccion])) {
                    $mensaje_exito = "¡Usuario creado con éxito! Ahora puedes iniciar sesión.";
                } else {
                    $mensaje_error = "Hubo un error al guardar los datos.";
                }
            }

        } catch(PDOException $e) {
            $mensaje_error = "Error de base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nuevo Usuario</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-mode">

    <div class="login-card" style="max-width: 500px;">
        <h1>Registro</h1>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-msg" style="color: #dc3545; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje_exito)): ?>
            <div class="success-msg" style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $mensaje_exito; ?>
            </div>
            <a href="FormularioRestaurante.php" class="btn btn-primary btn-block" style="text-decoration:none; display:block;">Ir al Login</a>
        <?php else: ?>

            <form action="registro.php" method="POST">
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" name="correo" id="correo" placeholder="ejemplo@email.com" required>
                </div>

                <div class="form-group">
                    <label for="clave">Contraseña</label>
                    <input type="password" name="clave" id="clave" placeholder="Tu contraseña" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="pais">País</label>
                        <input type="text" name="pais" id="pais" placeholder="España" required>
                    </div>
                    <div class="form-group">
                        <label for="cp">Código Postal (CP)</label>
                        <input type="text" name="cp" id="cp" placeholder="28000" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ciudad">Ciudad</label>
                    <input type="text" name="ciudad" id="ciudad" placeholder="Madrid" required>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" name="direccion" id="direccion" placeholder="Calle Gran Via" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Crear Usuario</button>
                
                <p style="margin-top: 20px; font-size: 0.9rem;">
                    ¿Ya tienes cuenta? <a href="FormularioRestaurante.php" style="color: var(--color-primary); font-weight: bold;">Inicia sesión aquí</a>
                </p>

            </form>
        <?php endif; ?>
    </div>

</body>
</html>