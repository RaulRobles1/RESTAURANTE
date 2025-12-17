<?php
session_start();

$codProducto = $_POST['cod'] ?? null;
$unidades = $_POST['unidades'] ?? 0;
$codCategoria = $_POST['cod_cat'] ?? null; 

$unidades = (int)$unidades;

if (is_null($codProducto) || $unidades <= 0 || is_null($codCategoria)) {
    header("Location: Categorias.php");
    exit;
}

if (!isset($_SESSION['carrito'])){
    $_SESSION['carrito'] = [];
}

if (isset($_SESSION['carrito'][$codProducto])) {
    $_SESSION['carrito'][$codProducto] += $unidades;
} else {
    $_SESSION['carrito'][$codProducto] = $unidades;
}

header("Location: Productos.php?categoria=" . $codCategoria);
exit;
?>