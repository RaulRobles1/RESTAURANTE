<?php
session_start();

require __DIR__ . '/PHPMailer.php';
require __DIR__ . '/SMTP.php';
require __DIR__ . '/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



$CORREO_GMAIL = 'raulroblesclase@gmail.com'; 

$PASSWORD_APP = 'tddl ncxt emdp ioub'; 

$NOMBRE_REMITENTE = 'Restaurante Raul y Mario';


if (!isset($_SESSION['username']) || !isset($_SESSION['codRes'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

$codRes = $_SESSION['codRes']; 
$CORREO_DESTINATARIO = $_SESSION['username']; 
$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    header("Location: Carrito.php");
    exit;
}

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

$pdo = null; 
$mensaje_resultado = "";
$codPed = 0; 
$pesoTotal = 0;
$detallePedido = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $codigos = array_keys($carrito);
    $placeholders = implode(',', array_fill(0, count($codigos), '?'));
    
    $sqlProd = "SELECT CodProd, Nombre, Peso, Descripción FROM Productos WHERE CodProd IN ($placeholders)";
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute($codigos);
    $productos_bd = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

    foreach ($productos_bd as $prod) {
        $cod = $prod['CodProd'];
        $unidades = $carrito[$cod];
        $pesoTotal += $prod['Peso'] * $unidades;
        
        $detallePedido[] = [
            'CodProd' => $cod,
            'Nombre' => $prod['Nombre'],
            'Unidades' => $unidades,
            'Peso' => $prod['Peso'],
            'Descripción' => $prod['Descripción'], 
        ];
    }

    $fecha = date('Y-m-d H:i:s');
    $sqlPedido = "INSERT INTO Pedidos (Fecha, Enviado, Peso, Restaurante) VALUES (?, 0, ?, ?)";
    $stmtPedido = $pdo->prepare($sqlPedido);
    $stmtPedido->execute([$fecha, $pesoTotal, $codRes]);
    
    $codPed = $pdo->lastInsertId();

    $sqlDetalle = "INSERT INTO PedidosProductos (Pedido, Producto, Unidades) VALUES (?, ?, ?)";
    $stmtDetalle = $pdo->prepare($sqlDetalle);

    foreach ($detallePedido as $item) {
        $stmtDetalle->execute([$codPed, $item['CodProd'], $item['Unidades']]);
    }
    
    unset($_SESSION['carrito']);

    $cuerpo_correo_html = generarCuerpoHTML($codPed, $CORREO_DESTINATARIO, $detallePedido, $pesoTotal);

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $CORREO_GMAIL;   
    $mail->Password   = $PASSWORD_APP;   
    
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port       = 465;

    $mail->setFrom($CORREO_GMAIL, $NOMBRE_REMITENTE);
    $mail->addAddress($CORREO_DESTINATARIO); 
    
    $mail->isHTML(true);
    $mail->Subject = "Ticket Pedido #" . $codPed;
    $mail->Body    = $cuerpo_correo_html; 
    $mail->AltBody = "Nuevo Pedido #" . $codPed . " registrado con éxito.";
    $mail->CharSet = 'UTF-8';

    $mail->send();

    $mensaje_resultado = "<h2 style='color: green;'>¡Pedido N. " . $codPed . " confirmado!</h2>";
    $mensaje_resultado .= "<p>Se ha enviado un correo de confirmación a: <strong>" . htmlspecialchars($CORREO_DESTINATARIO) . "</strong>.</p>";

} catch(PDOException $e) {
    $mensaje_resultado = "<h2 style='color: red;'>Error en la Base de Datos.</h2>";
    $mensaje_resultado .= "<p>No se pudo registrar el pedido. Detalle: " . htmlspecialchars($e->getMessage()) . "</p>";
    
} catch (Exception $e) {
    if ($codPed > 0) {
        $mensaje_resultado = "<h2 style='color: orange;'>Pedido N. " . $codPed . " registrado, pero falló el correo.</h2>";
        $mensaje_resultado .= "<p>Error de Gmail: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
    } else {
         $mensaje_resultado = "<h2 style='color: red;'>Error interno del sistema.</h2>";
         $mensaje_resultado .= "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

function generarCuerpoHTML($id_pedido, $correo_restaurante, $productos, $total_peso) {
    $filas_tabla = '';
    foreach ($productos as $p) {
        $peso_linea = $p['Peso'] * $p['Unidades'];
        $filas_tabla .= "
        <tr>
            <td style='padding: 15px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 14px;'>
                <strong>{$p['Nombre']}</strong>
                <br><span style='font-size:12px; color:#64748b;'>{$p['Descripción']}</span>
            </td>
            <td style='padding: 15px; border-bottom: 1px solid #f1f5f9; color: #0f172a; text-align: center;'>
                {$p['Unidades']}
            </td>
            <td style='padding: 15px; border-bottom: 1px solid #f1f5f9; color: #0f172a; text-align: right;'>
                " . number_format($peso_linea, 2) . "
            </td>
        </tr>";
    }

    $html_body = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <style>
            body { font-family: "Helvetica", "Arial", sans-serif; background-color: #f0f9ff; margin: 0; padding: 0; color: #1e293b; }
            .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
            .header { background-color: #0284c7; padding: 30px; text-align: center; }
            .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
            .content { padding: 30px; }
            .intro { font-size: 16px; line-height: 1.6; color: #475569; margin-bottom: 25px; }
            .order-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .order-table th { background-color: #f8fafc; color: #64748b; padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e2e8f0; }
            .total-section { background-color: #f0f9ff; padding: 20px; text-align: right; border-top: 1px solid #e2e8f0; }
            .total-value { font-size: 20px; font-weight: bold; color: #0284c7; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #94a3b8; background-color: #f8fafc; }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <h1>Pedido Confirmado #' . $id_pedido . '</h1>
            </div>

            <div class="content">
                <p class="intro">
                    Hola <strong>' . $correo_restaurante . '</strong>,<br>
                    Tu pedido ha sido procesado correctamente. Aquí tienes el resumen:
                </p>
                
                <table class="order-table">
                    <thead>
                        <tr>
                            <th width="50%">Producto</th>
                            <th width="20%" style="text-align: center;">Cant.</th>
                            <th width="30%" style="text-align: right;">Peso Total (Kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $filas_tabla . '
                    </tbody>
                </table>
            </div>

            <div class="total-section">
                <span style="font-size: 14px; color: #64748b; margin-right: 10px;">PESO TOTAL:</span>
                <span class="total-value">' . number_format($total_peso, 2) . ' Kg</span>
            </div>

            <div class="footer">
                Gracias por confiar en nosotros.<br>
                Restaurante DOSIS
            </div>
        </div>
    </body>
    </html>';
    
    return $html_body;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado del Pedido</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <?php include 'cabecera.php'; ?>
    
    <div class="app-container" style="text-align: center; padding-top: 50px;"> 
        <div class="login-card" style="max-width: 600px; margin: 0 auto;">
            <?php echo $mensaje_resultado; ?>
            <br><br>
            <a href="Categorias.php" class="btn btn-primary">Volver a Categorías</a>
        </div>
    </div>
    
</body>
</html>