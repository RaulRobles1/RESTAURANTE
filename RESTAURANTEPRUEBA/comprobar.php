<?php
session_start();

$usu = $_POST['usu'] ?? '';
$clave = $_POST['clave'] ?? '';

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $sql = "SELECT * FROM Restaurantes WHERE Correo = ? AND Clave = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usu, $clave]);
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['username'] = $row['Correo']; 
        $_SESSION['codRes'] = $row['CodRes'];   
        
        header("Location: Categorias.php");
        exit;
    } else {
        header("Location: FormularioRestaurante.php?error=incorrecto");
        exit;
    }

} catch(PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>