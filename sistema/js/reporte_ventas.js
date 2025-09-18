// sistema/js/reporte_ventas.js
$(document).ready(function () {
    let tabla;
    let rangoActual = { inicio: '', fin: '' };
    let estadoActual = '';

    // 🔹 Función para obtener fecha local (no UTC)
    function getFechaLocalYYYYMMDD(fecha) {
        const anio = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return `${anio}-${mes}-${dia}`;
    }

    function actualizarRangoFechas(inicio, fin) {
        rangoActual.inicio = inicio;
        rangoActual.fin = fin;
        const texto = `Desde: ${inicio} hasta: ${fin}` + (estadoActual ? ` | Estado: ${estadoActual.toUpperCase()}` : '');
        $('#rango_fechas').text(texto);
    }

    function cargarReporte(fecha_inicio = '', fecha_fin = '', estado = '') {
        estadoActual = estado; // guarda para export
        if (tabla) tabla.destroy();

        tabla = $('#tabla_reporte').DataTable({
            ajax: {
                url: 'ajax/ajax_reporte_ventas.php',
                type: 'POST',
                data: { 
                    fecha_inicio: fecha_inicio, 
                    fecha_fin: fecha_fin,
                    estado: estado
                },
                error: function (xhr) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Ocurrió un error al cargar el reporte. Revisa la consola para más detalles.');
                }
            },
            columns: [
                { data: 'nofactura', title: 'Nro Factura' },
                { data: 'fecha', title: 'Fecha' },
                { data: 'cliente', title: 'Cliente' },
                { data: 'vendedor', title: 'Vendedor' },
                { data: 'total', title: 'Total' },
                { data: 'estado', title: 'Estado' }
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: function() {
                        return `Reporte de Ventas (${rangoActual.inicio} a ${rangoActual.fin}${estadoActual ? ' - ' + estadoActual.toUpperCase() : ''})`;
                    },
                    messageTop: function() {
                        return `Rango: ${rangoActual.inicio} a ${rangoActual.fin}` + (estadoActual ? ` | Estado: ${estadoActual.toUpperCase()}` : '');
                    },
                    className: 'btn_export_excel'
                },
                {
    extend: 'pdfHtml5',
    title: function() {
        return `Reporte de Ventas (${rangoActual.inicio} a ${rangoActual.fin}${estadoActual ? ' - ' + estadoActual.toUpperCase() : ''})`;
    },
    messageTop: function() {
        return `Rango: ${rangoActual.inicio} a ${rangoActual.fin}` + (estadoActual ? ` | Estado: ${estadoActual.toUpperCase()}` : '');
    },
    orientation: 'landscape',
    pageSize: 'A4',
    className: 'btn_export_pdf',
    customize: function(doc) {
        // Centrar título
        doc.styles.title.alignment = 'center';
        // Centrar subtítulo si existe
        if (doc.content[1]) {
            doc.content[1].alignment = 'center';
        }
        // Márgenes de página
        doc.pageMargins = [40, 40, 40, 40];
        // Centrar y distribuir columnas de la tabla
        if (doc.content[2] && doc.content[2].table) {
            doc.content[2].layout = 'lightHorizontalLines';
            doc.content[2].table.widths = Array(doc.content[2].table.body[0].length).fill('*');
        }
    }
}

            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
            }
        });
    }

    function cargarGrafico(fecha_inicio = '', fecha_fin = '', estado = '') {
        $.ajax({
            url: 'ajax/ajax_grafico_ventas.php',
            type: 'POST',
            data: { 
                fecha_inicio: fecha_inicio, 
                fecha_fin: fecha_fin,
                estado: estado
            },
            dataType: 'json',
            success: function (data) {
                const ctx = document.getElementById('graficoVentas').getContext('2d');
                if (window.graficoVentas instanceof Chart) {
                    window.graficoVentas.destroy();
                }

                window.graficoVentas = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.fechas || [],
                        datasets: [{
                            label: 'Ventas Totales',
                            data: data.totales || []
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            },
            error: function (xhr) {
                console.error('AJAX error (gráfico):', xhr.responseText);
            }
        });
    }

    // Cambios de filtro de fecha
    $('#filtro_fecha').on('change', function () {
        let opcion = $(this).val();
        let inicio = '';
        let fin = '';
        let estado = $('#filtro_estado').val(); // estado seleccionado

        $('#fecha_inicio, #fecha_fin').hide();

        switch (opcion) {
            case 'hoy': {
                const d = new Date();
                inicio = fin = getFechaLocalYYYYMMDD(d);
                break;
            }
            case 'semana': {
                const d1 = new Date();
                d1.setDate(d1.getDate() - 6);
                inicio = getFechaLocalYYYYMMDD(d1);
                fin = getFechaLocalYYYYMMDD(new Date());
                break;
            }
            case '30dias': {
                const d1 = new Date();
                d1.setDate(d1.getDate() - 29);
                inicio = getFechaLocalYYYYMMDD(d1);
                fin = getFechaLocalYYYYMMDD(new Date());
                break;
            }
            case '6meses': {
                const d1 = new Date();
                d1.setMonth(d1.getMonth() - 6);
                inicio = getFechaLocalYYYYMMDD(d1);
                fin = getFechaLocalYYYYMMDD(new Date());
                break;
            }
            case 'personalizado':
                $('#fecha_inicio, #fecha_fin').show();
                return; // Espera al botón Filtrar
        }

        actualizarRangoFechas(inicio, fin);
        cargarReporte(inicio, fin, estado);
        cargarGrafico(inicio, fin, estado);
    });

    // Botón Filtrar (para personalizado)
    $('#btn_filtrar').on('click', function () {
        const inicio = $('#fecha_inicio').val();
        const fin = $('#fecha_fin').val();
        const estado = $('#filtro_estado').val();
        if (inicio && fin) {
            actualizarRangoFechas(inicio, fin);
            cargarReporte(inicio, fin, estado);
            cargarGrafico(inicio, fin, estado);
        } else {
            alert("Selecciona un rango de fechas.");
        }
    });

    // Cambio de estado -> recarga con el rango actual (o recalcula por el filtro de fecha)
    $('#filtro_estado').on('change', function () {
        const estado = $(this).val();
        if (rangoActual.inicio && rangoActual.fin) {
            actualizarRangoFechas(rangoActual.inicio, rangoActual.fin);
            cargarReporte(rangoActual.inicio, rangoActual.fin, estado);
            cargarGrafico(rangoActual.inicio, rangoActual.fin, estado);
        } else {
            $('#filtro_fecha').trigger('change');
        }
    });

    // Carga inicial
    $('#filtro_fecha').trigger('change');
});
