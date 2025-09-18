<?php
// sistema/ajax/ajax_reporte_productos.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../conexion.php';

// Forzar zona horaria Perú
date_default_timezone_set('America/Lima');

// Recibir filtros
$fecha_inicio  = $_POST['fecha_inicio'] ?? null;
$fecha_fin     = $_POST['fecha_fin'] ?? null;
$estado_filtro = $_POST['estado'] ?? '';

// Validación básica
if (!$fecha_inicio || !$fecha_fin) {
    echo json_encode(['data' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

// Normaliza a rango completo del día
$fecha_ini_dt = $fecha_inicio . ' 00:00:00';
$fecha_fin_dt = $fecha_fin . ' 23:59:59';

// Filtro por estado
$sql_estado = "";
if ($estado_filtro === 'pagado') {
    $sql_estado = " AND f.estatus = 1 ";
} elseif ($estado_filtro === 'anulado') {
    $sql_estado = " AND f.estatus = 2 ";
}

// Consulta
$sql = "
    SELECT 
        p.descripcion AS producto,
        u.nombre AS vendedor,
        c.nombre AS cliente,
        SUM(d.cantidad) AS cantidad_vendida,
        SUM(d.cantidad * d.precio) AS total_facturado
    FROM detallefactura d
    INNER JOIN factura f   ON d.nofactura  = f.nofactura
    INNER JOIN producto p  ON d.codproducto = p.codproducto
    INNER JOIN cliente c   ON f.codcliente = c.idcliente
    INNER JOIN usuario u   ON f.usuario    = u.idusuario
    WHERE f.fecha BETWEEN '$fecha_ini_dt' AND '$fecha_fin_dt'
    $sql_estado
    GROUP BY p.descripcion, u.nombre, c.nombre
    ORDER BY cantidad_vendida DESC
    LIMIT 50
";

$query = mysqli_query($conection, $sql);

// Armar respuesta
$data = [];
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = [
            'producto'         => $row['producto'],
            'vendedor'         => $row['vendedor'],
            'cliente'          => $row['cliente'],
            'cantidad_vendida' => (int)$row['cantidad_vendida'],
            'total_facturado'  => number_format((float)$row['total_facturado'], 2)
        ];
    }
} else {
    // Log de error SQL si falla
    @file_put_contents(__DIR__ . '/debug_productos.log', "ERROR SQL: " . mysqli_error($conection) . "\n", FILE_APPEND);
}

echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
