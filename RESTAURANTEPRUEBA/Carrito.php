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


$carrito = $_SESSION['carrito'] ?? [];
$productos_carrito = [];


if (empty($carrito)) {
    $contenido_vacio = '
        <div class="cart-empty">
            <h2>Carrito de la Compra</h2>
            <p>No has añadido nada al carrito</p>
            <a href="Categorias.php" class="btn btn-primary">Seguir comprando</a>
        </div>';
    
    goto render_html;
}


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $placeholders = implode(',', array_fill(0, count($carrito), '?'));
    $sqlProd = "SELECT CodProd, Nombre, Descripción, Peso FROM Productos WHERE CodProd IN ($placeholders)";
    
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute(array_keys($carrito));
    
    while ($row = $stmtProd->fetch(PDO::FETCH_ASSOC)) {
        $productos_carrito[$row['CodProd']] = $row;
    }

} catch(PDOException $e) {
    die("Error al cargar productos del carrito desde la BD: " . $e->getMessage());
}

render_html:
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu Carrito</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'cabecera.php'; ?>
    
    <?php 
    if (empty($carrito)) {
        echo $contenido_vacio;
    } else {
    ?>
    <div class="cart-container">
        <h2>Carrito de la Compra</h2>
        
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Peso (Kg)</th>
                    <th>Unidades</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carrito as $codProd => $cantidad) {
                    if (isset($productos_carrito[$codProd])) {
                        $prod = $productos_carrito[$codProd];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($prod['Nombre']); ?></td>
                    <td><?php echo htmlspecialchars($prod['Descripción']); ?></td>
                    <td><?php echo htmlspecialchars($prod['Peso']); ?></td>
                    <td><?php echo $cantidad; ?></td>
                    
                    <td>
                        <form action="eliminar.php" method="post" class="remove-form">
                            <input type="hidden" name="cod" value="<?php echo htmlspecialchars($codProd); ?>">
                            
                            <input type="number" name="unidades" 
                                   value="1" 
                                   min="1" 
                                   max="<?php echo $cantidad; ?>">
                            
                            <button type="submit" name="eliminar" class="btn btn-remove">Quitar</button>
                        </form>
                    </td>
                </tr>
                <?php } else { ?>
                <tr>
                    <td colspan="5">Producto ID: <?php echo $codProd; ?> no encontrado.</td>
                </tr>
                <?php } 
                } ?>
            </tbody>
        </table>

        <div class="cart-actions">
            <a href="Categorias.php" class="btn btn-secondary">Seguir comprando</a>
            
            <form action="procesar_pedido.php" method="POST">
                <button type="submit" name="confirmar" class="btn btn-primary">
                    Realizar Pedido
                </button>
            </form>
        </div>
    </div>
    <?php } ?>

</body>
</html>