<?php 
session_start();
include "../conexion.php";

// Funciones de conteo
function getUsuariosCount($con) {
    return mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM usuario WHERE estatus = 1"))['total'];
}
function getClientesCount($con) {
    return mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM cliente WHERE estatus = 1"))['total'];
}
function getProveedoresCount($con) {
    return mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM proveedor WHERE estatus = 1"))['total'];
}
function getProductosCount($con) {
    return mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM producto WHERE estatus = 1"))['total'];
}
function getVentasCount($con) {
    return mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM factura WHERE estatus = 1"))['total'];
}
function getVentasHoy($con) {
    return mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total, COALESCE(SUM(totalfactura), 0) as monto FROM factura WHERE DATE(fecha) = CURDATE() AND estatus = 1"));
}
function getVentasMes($con) {
    return mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total, COALESCE(SUM(totalfactura), 0) as monto FROM factura WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND estatus = 1"));
}
function getTopProductos($con) {
    $res = mysqli_query($con, "SELECT p.descripcion, SUM(df.cantidad) as total_vendido FROM detallefactura df INNER JOIN producto p ON df.codproducto = p.codproducto INNER JOIN factura f ON df.nofactura = f.nofactura WHERE f.estatus = 1 GROUP BY p.codproducto ORDER BY total_vendido DESC LIMIT 5");
    $out = []; while($r = mysqli_fetch_assoc($res)) $out[] = $r; return $out;
}
function getVentasSemana($con) {
    $res = mysqli_query($con, "SELECT DATE(fecha) as fecha, COUNT(*) as ventas, SUM(totalfactura) as monto FROM factura WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND estatus = 1 GROUP BY DATE(fecha) ORDER BY fecha ASC");
    $out = []; while($r = mysqli_fetch_assoc($res)) $out[] = $r; return $out;
}
function getProductosBajoStock($con) {
    $res = mysqli_query($con, "SELECT descripcion, existencia FROM producto WHERE existencia < 10 AND estatus = 1 ORDER BY existencia ASC LIMIT 5");
    $out = []; while($r = mysqli_fetch_assoc($res)) $out[] = $r; return $out;
}

// Llamado a funciones
$usuarios = getUsuariosCount($conection);
$clientes = getClientesCount($conection);
$proveedores = getProveedoresCount($conection);
$productos = getProductosCount($conection);
$ventas = getVentasCount($conection);
$ventasHoy = getVentasHoy($conection);
$ventasMes = getVentasMes($conection);
$topProductos = getTopProductos($conection);
$ventasSemana = getVentasSemana($conection);
$productosBajoStock = getProductosBajoStock($conection);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Ventas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "includes/scripts.php"; ?>
    <link rel="stylesheet" href="css/style.css"> <!-- tu estilo global -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999;
            background: #333;
            color: white;
            padding: 10px 20px;
        }

        body {
            margin: 0;
            padding-top: 70px; /* para que no se tape el contenido */
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #667eea;
        }

        .card .number {
            font-size: 2rem;
            font-weight: bold;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .alerts {
            margin-top: 30px;
            padding: 20px;
            background: #fff3f3;
            border-left: 5px solid #ff6b6b;
            border-radius: 10px;
        }

        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    <div class="container">
        <br>
        <h2>Bienvenido </h2>
        <div class="dashboard-grid">
    <a href="lista_usuarios.php" class="card-link">
        <div class="card">
            <i class="fas fa-users"></i>
            <div>Usuarios</div>
            <div class="number"><?php echo $usuarios; ?></div>
        </div>
    </a>
    <a href="lista_clientes.php" class="card-link">
        <div class="card">
            <i class="fas fa-user"></i>
            <div>Clientes</div>
            <div class="number"><?php echo $clientes; ?></div>
        </div>
    </a>
    <a href="lista_proveedor.php" class="card-link">
        <div class="card">
            <i class="fas fa-truck"></i>
            <div>Proveedores</div>
            <div class="number"><?php echo $proveedores; ?></div>
        </div>
    </a>
    <a href="lista_producto.php" class="card-link">
        <div class="card">
            <i class="fas fa-cubes"></i>
            <div>Productos</div>
            <div class="number"><?php echo $productos; ?></div>
        </div>
    </a>
    <a href="ventas.php" class="card-link">
        <div class="card">
            <i class="fas fa-receipt"></i>
            <div>Ventas</div>
            <div class="number"><?php echo $ventas; ?></div>
        </div>
    </a>
</div>


        <div class="dashboard-grid" style="margin-top: 30px;">
            <div class="card">
                <i class="fas fa-calendar-day"></i>
                <div>Ventas de Hoy</div>
                <div class="number">S/. <?php echo number_format($ventasHoy['monto'], 2); ?></div>
            </div>
            <div class="card">
                <i class="fas fa-calendar-alt"></i>
                <div>Ventas del Mes</div>
                <div class="number">S/. <?php echo number_format($ventasMes['monto'], 2); ?></div>
            </div>
        </div>

        <div class="chart-container">
            <h3>Ventas de los últimos 7 días</h3>
            <canvas id="ventasChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>Top Productos</h3>
            <canvas id="productosChart"></canvas>
        </div>

        <?php if (!empty($productosBajoStock)): ?>
        <div class="alerts">
            <h4><i class="fas fa-exclamation-circle"></i> Productos con bajo stock:</h4>
            <ul>
                <?php foreach ($productosBajoStock as $p): ?>
                    <li><strong><?php echo $p['descripcion']; ?></strong> - Solo quedan <?php echo $p['existencia']; ?> unidades</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <button class="refresh-btn" onclick="location.reload()" title="Actualizar"><i class="fas fa-sync-alt"></i></button>

    <script>
        const ventasData = <?php echo json_encode($ventasSemana); ?>;
        const productosData = <?php echo json_encode($topProductos); ?>;

        const ventasChart = new Chart(document.getElementById('ventasChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: ventasData.map(d => d.fecha),
                datasets: [{
                    label: 'Ventas',
                    data: ventasData.map(d => d.monto),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102,126,234,0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        const productosChart = new Chart(document.getElementById('productosChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: productosData.map(p => p.descripcion),
                datasets: [{
                    data: productosData.map(p => p.total_vendido),
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>