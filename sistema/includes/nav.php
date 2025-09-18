<nav>
			<ul>
				<li><a href="index.php"><i class="fas fa-home"></i>Inicio</a></li>
				<?php 
					if($_SESSION['rol'] == 1){
					?>
				<li class="principal">
					
					<a href="#"><i class="fas fa-users"></i>Usuarios</a>
					<ul>
						<li><a href="registro_usuario.php"><i class="fas fa-user-plus"></i>Nuevo Usuario</a></li>
						<li><a href="lista_usuarios.php"><i class="fas fa-users"></i>Lista de Usuarios</a></li>
					</ul>
				</li>
				<?php } ?>
				<li class="principal">
					<a href="#">Clientes</a>
					<ul>
						<li><a href="registro_cliente.php">Nuevo Cliente</a></li>
						<li><a href="lista_clientes.php">Lista de Clientes</a></li>
					</ul>
				</li>
				<?php 
					if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2){
					?>
				<li class="principal">
					<a href="#"><i class="far fa-building"></i>Proveedores</a>
					<ul>
						<li><a href="registro_proveedor.php">Nuevo Proveedor</a></li>
						<li><a href="lista_proveedor.php">Lista de Proveedores</a></li>
					</ul>
				</li>
				<?php  } ?>
				<li class="principal">
					<a href="#">Productos</a>
					<ul>
						<?php
						if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2){
						?>
						<li><a href="registro_producto.php"><i class="fas fa-plus"></i>Nuevo Producto</a></li>
						<?php } ?>
						<li><a href="lista_producto.php"><i class="fas fa-cube"></i>Lista de Productos</a></li>
					</ul>
				</li>
				<li class="principal">
					<a href="#"><i class="far fa-file-alt"></i>Ventas</a>
					<ul>
						<li><a href="nueva_venta.php"><i class="fas fa-plus"></i>Nueva Venta</a></li>
						<li><a href="ventas.php"><i class="far fa-newspaper"></i>Ventas</a></li>
					</ul>
				</li>

				<?php 
				if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
				?>
				<li class="principal">
				<a href="#"><i class="fas fa-chart-bar"></i>Reportes</a>
				<ul>
					<li><a href="reporte_ventas.php"><i class="fas fa-chart-line"></i>Ventas</a></li>
					<li><a href="reporte_productos.php"><i class="fas fa-box-open"></i>Productos más vendidos</a></li>
					<li><a href="reporte_clientes.php"><i class="fas fa-user-friends"></i>Clientes frecuentes</a></li>
				</ul>
				</li>
				<?php 
				}
				?>


				<?php
				if($_SESSION['rol'] == 1 ){
					?>
					<li class="principal">
						<a href="#">Configuración</a>
						<ul>
					<li><a href="configuracion_personal.php"><i class="fas fa-plus"></i> Información personal</a></li>
					<li><a href="configuracion_empresa.php"><i class="fas fa-cube"></i> Datos de la Empresa</a></li>
						</ul>
					</li>
					<?php
					}
					?>

			</ul>
		</nav>