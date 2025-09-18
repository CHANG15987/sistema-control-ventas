<?php
// sistema/ajax/ajax_reporte_ventas.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔹 Aseguramos la zona horaria de Perú
date_default_timezone_set('America/Lima');

require_once __DIR__ . '/../../conexion.php';

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

// (opcional) log para depurar
$logfile = __DIR__ . '/debug_filtro.log';
@file_put_contents(
    $logfile,
    "POST: " . print_r($_POST, true) . "\nRANGO: $fecha_ini_dt a $fecha_fin_dt\nSQL_ESTADO: $sql_estado\n------\n",
    FILE_APPEND
);

// Consulta
$sql = "
    SELECT 
        f.nofactura, 
        f.fecha, 
        cl.nombre AS cliente, 
        u.nombre AS vendedor, 
        f.totalfactura,
        f.estatus
    FROM factura f
    INNER JOIN cliente cl ON f.codcliente = cl.idcliente
    INNER JOIN usuario u  ON f.usuario   = u.idusuario
    WHERE f.fecha BETWEEN '$fecha_ini_dt' AND '$fecha_fin_dt'
    $sql_estado
    ORDER BY f.fecha DESC
";

$query = mysqli_query($conection, $sql);

// Armar respuesta
$data = [];
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $estado_texto = ($row['estatus'] == 1) ? 'PAGADO' : 'ANULADO';
        $total = is_null($row['totalfactura']) ? '0.00' : number_format((float)$row['totalfactura'], 2);

        $data[] = [
            'nofactura' => $row['nofactura'],
            'fecha'     => $row['fecha'],
            'cliente'   => $row['cliente'],
            'vendedor'  => $row['vendedor'],
            'total'     => $total,
            'estado'    => $estado_texto
        ];
    }
} else {
    // Log de error SQL si falla
    @file_put_contents($logfile, "ERROR SQL: " . mysqli_error($conection) . "\n", FILE_APPEND);
}

echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
