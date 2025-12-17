<?php
session_start(); 

if (!isset($_SESSION['username'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

$categorias = [];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = 'SELECT CodCat, Nombre FROM Categorias ORDER BY Nombre';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error al cargar categorías desde la BD: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Selección de Categorías</title>
    <link rel="stylesheet" href="estilos.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php 
    include 'cabecera.php'; 
    ?>

    <div class="app-container">
        <h2>Seleccione una Categoría para hacer su Pedido</h2>

        <?php 
        if (count($categorias) > 0) {
        ?>
            <div class="category-grid">
                <?php foreach ($categorias as $cat) { ?>
                    <div class="category-card">
                        <a href="Productos.php?categoria=<?php echo htmlspecialchars($cat['CodCat']); ?>">
                            <p class="category-name"><?php echo htmlspecialchars($cat['Nombre']); ?></p>
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php 
        } else { 
        ?>
            <p style="text-align: center; font-size: 1.2em; color: #6c757d; margin-top: 40px;">
                No hay categorías disponibles en este momento.
            </p>
        <?php 
        } 
        ?>
    </div>

</body>
</html>