<?php
session_start();
if ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2) {
    header("Location: ./");
}
include "../conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "includes/scripts.php"; ?>
    <title>Reporte de Ventas</title>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>
<?php include "includes/header.php"; ?>
<section id="container">
    <h1><i class="fas fa-chart-line"></i> Reporte de Ventas</h1>
    <p id="rango_fechas" style="margin:6px 0 12px 0; font-weight:600;"></p>

    <!-- FILTROS -->
    <div class="filters">
        <label for="filtro_fecha">Filtrar por:</label>
        <select id="filtro_fecha">
            <option value="hoy">Hoy</option>
            <option value="semana">Últimos 7 días</option>
            <option value="30dias">Últimos 30 días</option>
            <option value="6meses">Últimos 6 meses</option>
            <option value="personalizado">Personalizado</option>
        </select>

        <input type="date" id="fecha_inicio" style="display:none;">
        <input type="date" id="fecha_fin" style="display:none;">
        <button id="btn_filtrar" class="btn_view">Filtrar</button>

        <label for="filtro_estado" style="margin-left:12px;">Estado:</label>
        <select id="filtro_estado">
            <option value="">Todos</option>
            <option value="pagado">Pagado</option>
            <option value="anulado">Anulado</option>
        </select>
    </div>

    <hr>

    <!-- TABLA -->
    <div class="table-responsive">
        <table id="tabla_reporte" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Nro Factura</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Total</th>
                    <th>Estado</th> <!-- Nueva columna para que coincida con el JS -->
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- GRÁFICO -->
    <h2>Gráfico de Ventas</h2>
    <canvas id="graficoVentas" height="100"></canvas>

</section>
<?php include "includes/footer.php"; ?>

<!-- JS para DataTables y Chart.js -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Tu JS personalizado -->
<script src="js/reporte_ventas.js"></script>
</body>
</html>
