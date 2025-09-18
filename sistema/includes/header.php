<?php
    if(empty($_SESSION['active']))
    {
        header('location: ../index.php');
    }
?>
<header>
		<div class="header">
			
			<h1>Sistema de Control de Ventas BENJA</h1>
			<div class="optionsBar">
				<p>Perú, <?php echo fechaC();?></p>
				<span>|</span>
				<span class="user"><?php echo $_SESSION['user'].' -'.$_SESSION['rol'].' -'.$_SESSION['email'];?></span>
				<img class="photouser" src="img/user.png" alt="Usuario">
				<a href="salir.php"><img class="close" src="img/salir.png" alt="Salir del sistema" title="Salir"></a>
			</div>
		</div>
		<?php include "nav.php";?>
	</header>
	<div class="modal">
		<div class="bodyModal">
		</div>
	</div>