<?php
// sistema/reporte_productos.php
session_start();
if (empty($_SESSION['active'])) {
    header('location: ../');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte - Productos más vendidos</title>
    <link rel="stylesheet" href="css/style.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="//cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <style>
        .btn_export_excel {
            background-color: #198754;
            color: white;
        }
        .btn_export_pdf {
            background-color: #dc3545;
            color: white;
        }
        .dataTables_wrapper .dt-buttons {
            margin-bottom: 10px;
        }
        canvas {
            background: #fff;
            border: 1px solid #ccc;
            padding: 10px;
        }
    </style>
</head>
<body>
<?php include "includes/header.php"; ?>
<section id="container" class="p-4">

    <h1 class="mb-4">Reporte - Productos más vendidos</h1>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filtro_fecha_productos" class="form-label">Rango rápido</label>
            <select id="filtro_fecha_productos" class="form-select">
                <option value="hoy">Hoy</option>
                <option value="semana">Últimos 7 días</option>
                <option value="30dias">Últimos 30 días</option>
                <option value="6meses">Últimos 6 meses</option>
                <option value="personalizado">Personalizado</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="fecha_inicio_productos" class="form-label">Fecha inicio</label>
            <input type="date" id="fecha_inicio_productos" class="form-control">
        </div>

        <div class="col-md-3">
            <label for="fecha_fin_productos" class="form-label">Fecha fin</label>
            <input type="date" id="fecha_fin_productos" class="form-control">
        </div>

        <div class="col-md-2">
            <label for="filtro_estado_productos" class="form-label">Estado</label>
            <select id="filtro_estado_productos" class="form-select">
                <option value="">Todos</option>
                <option value="pagado">Pagado</option>
                <option value="anulado">Anulado</option>
            </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button id="btn_filtrar_productos" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </div>

    <div class="mb-3 fw-bold" id="rango_fechas_productos"></div>

    <!-- Tabla -->
    <div class="table-responsive mb-5">
        <table id="tabla_reporte_productos" class="display nowrap table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Vendedor</th>
                    <th>Cliente</th>
                    <th>Cantidad Vendida</th>
                    <th>Total Facturado</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Gráfico -->
    <h4>Gráfico - Productos más vendidos</h4>
    <canvas id="graficoProductos" height="120"></canvas>

</section>
<?php include "includes/footer.php"; ?>

<!-- JS -->
<script src="//code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="//cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="//cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="//cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Nuestro JS -->
<script src="js/reporte_productos.js"></script>

</body>
</html>
