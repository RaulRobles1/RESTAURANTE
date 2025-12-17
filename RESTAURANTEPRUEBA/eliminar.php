<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

$codProducto = $_POST['cod'] ?? null;
$unidadesAEliminar = $_POST['unidades'] ?? 0;

$unidadesAEliminar = (int)$unidadesAEliminar;

if (is_null($codProducto) || $unidadesAEliminar <= 0) {
    header("Location: Carrito.php");
    exit;
}

if (isset($_SESSION['carrito'][$codProducto])) {
    $cantidadActual = $_SESSION['carrito'][$codProducto];
    
    $nuevaCantidad = $cantidadActual - $unidadesAEliminar;
    
    if ($nuevaCantidad > 0) {
        $_SESSION['carrito'][$codProducto] = $nuevaCantidad;
    } else {
        unset($_SESSION['carrito'][$codProducto]);
    }
}

header("Location: Carrito.php");
exit;
?>