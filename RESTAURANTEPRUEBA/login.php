<?php
session_start();

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = $_POST['Usuario'] ?? '';
    $clave = $_POST['Clave'] ?? '';

    if (empty($usuario) || empty($clave)) {
        header("Location: FormularioRestaurante.php?error=TRUE");
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = 'SELECT CodRes, correo FROM Restaurantes WHERE correo = ? AND clave = ?';
        $stmt = $pdo->prepare($sql);

        $stmt->execute([$usuario, $clave]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['codRes'] = $user['CodRes']; 
            $_SESSION['username'] = $user['correo'];
$_SESSION['correo_restaurante_notificacion'] = $user['correo'];
            $redirigir = $_GET['rediregido'] ?? 'Categorias.php';
            header("Location: " . $redirigir); 
            exit;
        } else {
            header("Location: FormularioRestaurante.php?error=TRUE");
            exit;
        }
    } catch (PDOException $e) {
        echo "Error de conexión o BD: " . $e->getMessage();
    }
} else {
    header("Location: FormularioRestaurante.php");
    exit;
}
?>