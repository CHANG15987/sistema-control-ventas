<?php
// sistema/ajax/ajax_grafico_ventas.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../conexion.php';

$fecha_inicio  = $_POST['fecha_inicio'] ?? null;
$fecha_fin     = $_POST['fecha_fin'] ?? null;
$estado_filtro = $_POST['estado'] ?? '';

if (!$fecha_inicio || !$fecha_fin) {
    echo json_encode(['fechas' => [], 'totales' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$fecha_ini_dt = $fecha_inicio . ' 00:00:00';
$fecha_fin_dt = $fecha_fin . ' 23:59:59';

// Filtro por estado
$sql_estado = "";
if ($estado_filtro === 'pagado') {
    $sql_estado = " AND f.estatus = 1 ";
} elseif ($estado_filtro === 'anulado') {
    $sql_estado = " AND f.estatus = 2 ";
}

// (opcional) log
$logfile = __DIR__ . '/debug_grafico.log';
@file_put_contents(
    $logfile,
    "POST: " . print_r($_POST, true) . "\nRANGO: $fecha_ini_dt a $fecha_fin_dt\nSQL_ESTADO: $sql_estado\n------\n",
    FILE_APPEND
);

// Sumar ventas por día (formato YYYY-MM-DD)
$sql = "
    SELECT DATE(f.fecha) AS dia, SUM(COALESCE(f.totalfactura,0)) AS total_dia
    FROM factura f
    WHERE f.fecha BETWEEN '$fecha_ini_dt' AND '$fecha_fin_dt'
    $sql_estado
    GROUP BY DATE(f.fecha)
    ORDER BY dia ASC
";

$query = mysqli_query($conection, $sql);

$fechas  = [];
$totales = [];

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $fechas[]  = $row['dia'];
        $totales[] = (float)$row['total_dia'];
    }
} else {
    @file_put_contents($logfile, "ERROR SQL: " . mysqli_error($conection) . "\n", FILE_APPEND);
}

echo json_encode(['fechas' => $fechas, 'totales' => $totales], JSON_UNESCAPED_UNICODE);
