<?php
	$es_boleta = isset($es_boleta) && $es_boleta == true;

	$subtotal 	= 0;
	$igv 	 	= 0;
	$impuesto 	= 0;
	$tl_snigv   = 0;
	$total 		= 0;
	//print_r($configuracion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title><?php echo $es_boleta ? 'Boleta' : 'Factura'; ?></title>

	<style>
		
<?php
    $cssFile = __DIR__ . '/style.css';
    if (file_exists($cssFile)) {
        echo file_get_contents($cssFile);
    }
	if ($es_boleta) {
    echo '<style>
        * { color: #000 !important; }
        .info_empresa, .h3, #detalle_totales span, .label_gracias {
            color: #000 !important;
        }
    </style>';
}

?>
</style>
</head>
<body>

<div id="page_pdf">
	<table id="factura_head">
		<tr>
			<td class="logo_factura">
				<div>
					<img src="logo.jpg" alt="Logo" style="width: 100px; height:auto;">
				</div>
			</td>
			<td class="info_empresa">
				<?php if($result_config > 0): 
					$igv = $configuracion['igv'];
				?>
				<div>
					<span class="h2"><?php echo strtoupper($configuracion['nombre']); ?></span>
					<p><?php echo $configuracion['razon_social']; ?></p>
					<p><?php echo $configuracion['direccion']; ?></p>
					<p>RUC: <?php echo $configuracion['ruc']; ?></p>
					<p>Teléfono: <?php echo $configuracion['telefono']; ?></p>
					<p>Email: <?php echo $configuracion['email']; ?></p>
				</div>
				<?php endif; ?>
			</td>
			<td class="info_factura">
				<div class="round">
					<span class="h3"><?php echo $es_boleta ? 'BOLETA DE VENTA' : 'FACTURA'; ?></span>
					<p><?php echo $es_boleta ? 'No. Boleta:' : 'No. Factura:'; ?> <strong><?php echo $factura['nofactura']; ?></strong></p>
					<p>Fecha: <?php echo $factura['fecha']; ?></p>
					<p>Hora: <?php echo $factura['hora']; ?></p>
					<p>Vendedor: <?php echo $factura['vendedor']; ?></p>
				</div>
			</td>
		</tr>
	</table>

	<table id="factura_cliente">
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">Cliente</span>
					<table class="datos_cliente">
						<tr>
							<td><label>RUC:</label><p><?php echo $factura['ruc']; ?></p></td>
							<td><label>Teléfono:</label><p><?php echo $factura['telefono']; ?></p></td>
						</tr>
						<tr>
							<td><label>Nombre:</label><p><?php echo $factura['nombre']; ?></p></td>
							<td><label>Dirección:</label><p><?php echo $factura['direccion']; ?></p></td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>

	<table id="factura_detalle">
		<thead>
			<tr>
				<th width="50px">Cant.</th>
				<th class="textleft">Descripción</th>
				<th class="textright" width="150px">Precio Unitario</th>
				<th class="textright" width="150px">Precio Total</th>
			</tr>
		</thead>
		<tbody id="detalle_productos">
		<?php if($result_detalle > 0):
			while ($row = mysqli_fetch_assoc($query_productos)):
		?>
			<tr>
				<td class="textcenter"><?php echo $row['cantidad']; ?></td>
				<td><?php echo $row['descripcion']; ?></td>
				<td class="textright"><?php echo number_format($row['precio_venta'], 2); ?></td>
				<td class="textright"><?php echo number_format($row['precio_total'], 2); ?></td>
			</tr>
		<?php
				$subtotal += $row['precio_total'];
			endwhile;
		endif;

		$impuesto 	= round($subtotal * ($igv / 100), 2);
		$tl_snigv 	= round($subtotal - $impuesto, 2);
		$total 		= round($tl_snigv + $impuesto, 2);
		?>
		</tbody>
		<tfoot id="detalle_totales">
			<tr>
				<td colspan="3" class="textright"><span>SUBTOTAL Q.</span></td>
				<td class="textright"><span><?php echo number_format($tl_snigv, 2); ?></span></td>
			</tr>
			<tr>
				<td colspan="3" class="textright"><span>IGV (<?php echo $igv; ?> %)</span></td>
				<td class="textright"><span><?php echo number_format($impuesto, 2); ?></span></td>
			</tr>
			<tr>
				<td colspan="3" class="textright"><span>TOTAL Q.</span></td>
				<td class="textright"><span><?php echo number_format($total, 2); ?></span></td>
			</tr>
		</tfoot>
	</table>

	<div>
		<p class="nota">
        Si usted tiene preguntas sobre esta <?php echo $es_boleta ? 'boleta' : 'factura'; ?>, <br>
        póngase en contacto con nombre, teléfono y Email
       </p>
		<h4 class="label_gracias">¡Gracias por su compra!</h4>
	</div>
</div>
</body>
</html>
