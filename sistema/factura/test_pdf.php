<?php
// Ignorar avisos deprecated de PHP
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Cargar el autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

// Capturar HTML con Bootstrap
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Prueba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Factura de Venta</h2>
        <p><strong>Cliente:</strong> Juan Pérez</p>
        <p><strong>Fecha:</strong> <?= date('d/m/Y') ?></p>

        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Cantidad</th>
                    <th>Descripción</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2</td>
                    <td>Producto A</td>
                    <td>$10.00</td>
                    <td>$20.00</td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Producto B</td>
                    <td>$15.00</td>
                    <td>$15.00</td>
                </tr>
                <tr class="table-primary">
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td>$35.00</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

$mpdf = new Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('factura_test.pdf', 'I');
