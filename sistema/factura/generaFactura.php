<?php
session_start();
if (empty($_SESSION['active'])) {
    header('location: ../');
    exit;
}

include "../../conexion.php";
require_once __DIR__ . '/../vendor/autoload.php'; // Ruta corregida a mPDF

use Mpdf\Mpdf;

if (empty($_REQUEST['cl']) || empty($_REQUEST['f'])) {
    echo "No es posible generar la factura.";
    exit;
}

$codCliente = intval($_REQUEST['cl']);
$noFactura = intval($_REQUEST['f']);
$anulada = '';

$query_config = mysqli_query($conection, "SELECT * FROM configuracion");
$result_config = mysqli_num_rows($query_config);
if ($result_config > 0) {
    $configuracion = mysqli_fetch_assoc($query_config);
}

$query = mysqli_query($conection, "
    SELECT f.nofactura, 
        DATE_FORMAT(f.fecha, '%d/%m/%Y') as fecha, 
        DATE_FORMAT(f.fecha,'%H:%i:%s') as hora, 
        f.codcliente, 
        f.estatus,
        v.nombre as vendedor,
        cl.ruc, cl.nombre, cl.telefono, cl.direccion
    FROM factura f
    INNER JOIN usuario v ON f.usuario = v.idusuario
    INNER JOIN cliente cl ON f.codcliente = cl.idcliente
    WHERE f.nofactura = $noFactura AND f.codcliente = $codCliente AND f.estatus != 10
");

$result = mysqli_num_rows($query);
if ($result > 0) {
    $factura = mysqli_fetch_assoc($query);
    $no_factura = $factura['nofactura'];

    $query_productos = mysqli_query($conection, "
        SELECT p.descripcion, dt.cantidad, dt.precio_venta, (dt.cantidad * dt.precio_venta) as precio_total
        FROM factura f
        INNER JOIN detallefactura dt ON f.nofactura = dt.nofactura
        INNER JOIN producto p ON dt.codproducto = p.codproducto
        WHERE f.nofactura = $no_factura
    ");

    $result_detalle = mysqli_num_rows($query_productos);

    ob_start();
    include(dirname(__FILE__) . '/factura.php');
    $html = ob_get_clean();

    $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'letter']);
$mpdf->SetBasePath(__DIR__); // Permite acceso a recursos locales
$mpdf->showImageErrors = true;

// Agregar marca de agua si la factura está anulada
if ($factura['estatus'] == 2) {
    $mpdf->SetWatermarkImage(__DIR__ . '/img/anulado.png', 5);
    $mpdf->showWatermarkImage = true;
}



    $mpdf->WriteHTML($html);
    $mpdf->Output('factura_' . $noFactura . '.pdf', 'I');
    exit;
} else {
    echo "Factura no encontrada o inválida.";
    exit;
}