<?php
session_start();

if (!isset($_SESSION['restaurante']['correo'])) {
    header("Location: login.php");
    exit();
}

$correoSesion = $_SESSION['restaurante']['correo'];

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$pass = "";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = $conexion->prepare("SELECT Correo FROM restaurante WHERE Correo = ?");
    $sql->execute([$correoSesion]);
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo "Sesión válida. Bienvenido: " . $usuario['Correo'];
    } else {
        echo "Sesión inválida. Redirigiendo...";
        header("Refresh:1; url=login.php");
        exit();
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
