<?php
session_start(); 

if (!isset($_SESSION['username'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

if (!isset($_GET['categoria'])) {
    header("Location: Categorias.php");
    exit;
}

$codCategoria = htmlspecialchars($_GET['categoria']);
$nombreCategoria = 'Productos';

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

$productos = [];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sqlCat = 'SELECT Nombre FROM Categorias WHERE CodCat = ?';
    $stmtCat = $pdo->prepare($sqlCat);
    $stmtCat->execute([$codCategoria]);
    $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
    if ($catRow) {
        $nombreCategoria = $catRow['Nombre'];
    }

    $sqlProd = 'SELECT CodProd, Nombre, Descripción, Peso, Stock FROM Productos WHERE Categoria = ? ORDER BY Nombre';
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute([$codCategoria]);
    
    $productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error al cargar productos desde la BD: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos: <?php echo htmlspecialchars($nombreCategoria); ?></title>
    <link rel="stylesheet" href="estilos.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'cabecera.php'; ?>

    <div class="app-container">
        <h2>Categoría: <?php echo htmlspecialchars($nombreCategoria); ?></h2>

        <a href="Categorias.php" class="btn btn-secondary" style="margin-bottom: 30px; display: inline-block;">
            ← Volver a Categorías
        </a>

        <?php if (count($productos) > 0) { ?>
            <div class="product-grid">
                <?php foreach ($productos as $prod) { 
                    $stock = (int)$prod['Stock'];
                    $disponible = $stock > 0;
                ?>
                    <div class="product-card">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($prod['Nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($prod['Descripción']); ?></p>
                            <p>Peso: <strong><?php echo htmlspecialchars($prod['Peso']); ?> Kg</strong></p>
                            <p style="font-weight: 600; color: <?php echo $disponible ? '#28a745' : '#dc3545'; ?>;">
                                Stock: <?php echo $disponible ? htmlspecialchars($prod['Stock']) . ' unidades' : 'Agotado'; ?>
                            </p>
                        </div>
                        
                        <div class="product-actions">
                            <?php if ($disponible) { ?>
                                <form action="anadir.php" method="post">
                                    <input type="hidden" name="cod" value="<?php echo htmlspecialchars($prod['CodProd']); ?>">
                                    
                                    <input type="hidden" name="cod_cat" value="<?php echo htmlspecialchars($codCategoria); ?>">
                                    
                                    <input type="number" name="unidades" 
                                           value="1" 
                                           min="1" 
                                           max="<?php echo $stock; ?>"
                                           required>
                                    
                                    <button type="submit" name="anadir" class="btn btn-primary">
                                        Añadir al Carrito
                                    </button>
                                </form>
                            <?php } else { ?>
                                <p style="color: red; font-weight: 600; margin: 0;">No disponible</p>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p style="text-align: center; font-size: 1.2em; color: #6c757d; margin-top: 40px;">
                No hay productos disponibles para esta categoría.
            </p>
        <?php } ?>
    </div>

</body>
</html>