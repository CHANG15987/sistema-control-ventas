<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../conexion.php');

// Función para ejecutar consultas de manera segura
function ejecutarConsulta($conexion, $query) {
    $result = mysqli_query($conexion, $query);
    if (!$result) {
        return ['error' => mysqli_error($conexion)];
    }
    return $result;
}

// Funciones para obtener datos del dashboard
function getUsuariosCount($conexion) {
    $query = "SELECT COUNT(*) as total FROM usuario WHERE estatus = 1";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return 0;
    return mysqli_fetch_assoc($result)['total'];
}

function getClientesCount($conexion) {
    $query = "SELECT COUNT(*) as total FROM cliente WHERE estatus = 1";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return 0;
    return mysqli_fetch_assoc($result)['total'];
}

function getProveedoresCount($conexion) {
    $query = "SELECT COUNT(*) as total FROM proveedor WHERE estatus = 1";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return 0;
    return mysqli_fetch_assoc($result)['total'];
}

function getProductosCount($conexion) {
    $query = "SELECT COUNT(*) as total FROM producto WHERE estatus = 1";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return 0;
    return mysqli_fetch_assoc($result)['total'];
}

function getVentasCount($conexion) {
    $query = "SELECT COUNT(*) as total FROM factura WHERE estatus = 1";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return 0;
    return mysqli_fetch_assoc($result)['total'];
}

function getVentasHoy($conexion) {
    $query = "SELECT COUNT(*) as total, COALESCE(SUM(totalfactura), 0) as monto 
              FROM factura 
              WHERE DATE(fecha) = CURDATE() AND estatus = 1";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return ['total' => 0, 'monto' => 0];
    return mysqli_fetch_assoc($result);
}

function getVentasMes($conexion) {
    $query = "SELECT COUNT(*) as total, COALESCE(SUM(totalfactura), 0) as monto 
              FROM factura 
              WHERE MONTH(fecha) = MONTH(CURDATE()) 
              AND YEAR(fecha) = YEAR(CURDATE()) 
              AND estatus = 1";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return ['total' => 0, 'monto' => 0];
    return mysqli_fetch_assoc($result);
}

function getTopProductos($conexion) {
    $query = "SELECT p.descripcion, SUM(df.cantidad) as total_vendido 
              FROM detallefactura df 
              INNER JOIN producto p ON df.codproducto = p.codproducto 
              INNER JOIN factura f ON df.nofactura = f.nofactura 
              WHERE f.estatus = 1 
              GROUP BY p.codproducto, p.descripcion 
              ORDER BY total_vendido DESC 
              LIMIT 5";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return [];
    
    $productos = [];
    while($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
    return $productos;
}

function getVentasSemana($conexion) {
    $query = "SELECT DATE(fecha) as fecha, COUNT(*) as ventas, SUM(totalfactura) as monto
              FROM factura 
              WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
              AND estatus = 1
              GROUP BY DATE(fecha)
              ORDER BY fecha ASC";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return [];
    
    $ventas = [];
    while($row = mysqli_fetch_assoc($result)) {
        $ventas[] = $row;
    }
    return $ventas;
}

function getProductosBajoStock($conexion) {
    $query = "SELECT descripcion, existencia 
              FROM producto 
              WHERE existencia < 10 AND estatus = 1 
              ORDER BY existencia ASC 
              LIMIT 5";
    $result = ejecutarConsulta($conexion, $query);
    if (isset($result['error'])) return [];
    
    $productos = [];
    while($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
    return $productos;
}

// Verificar si la conexión existe
if (!$conexion) {
    echo json_encode(['error' => 'No se pudo conectar a la base de datos']);
    exit;
}

// Obtener acción
$action = $_GET['action'] ?? 'dashboard';

try {
    switch($action) {
        case 'dashboard':
            $data = [
                'usuarios' => getUsuariosCount($conexion),
                'clientes' => getClientesCount($conexion),
                'proveedores' => getProveedoresCount($conexion),
                'productos' => getProductosCount($conexion),
                'ventas' => getVentasCount($conexion),
                'ventasHoy' => getVentasHoy($conexion),
                'ventasMes' => getVentasMes($conexion),
                'topProductos' => getTopProductos($conexion),
                'ventasSemana' => getVentasSemana($conexion),
                'productosBajoStock' => getProductosBajoStock($conexion),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            echo json_encode($data);
            break;
            
        case 'usuarios':
            echo json_encode(['count' => getUsuariosCount($conexion)]);
            break;
            
        case 'clientes':
            echo json_encode(['count' => getClientesCount($conexion)]);
            break;
            
        case 'proveedores':
            echo json_encode(['count' => getProveedoresCount($conexion)]);
            break;
            
        case 'productos':
            echo json_encode(['count' => getProductosCount($conexion)]);
            break;
            
        case 'ventas':
            echo json_encode(['count' => getVentasCount($conexion)]);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

// Cerrar conexión
mysqli_close($conexion);
?>