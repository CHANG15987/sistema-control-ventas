<?php
session_start();

// Solo permitir acceso si el usuario está logueado y es administrador
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('Location: ../');
    exit;
}

include "../conexion.php";

// Obtener el ID del usuario desde la sesión
$idUsuario = isset($_SESSION['idUser']) ? intval($_SESSION['idUser']) : 0;

// Evitar errores si no está definido correctamente
if ($idUsuario == 0) {
    echo "<p style='color:red;'>Error: sesión inválida. Vuelve a iniciar sesión.</p>";
    exit;
}

// Obtener los datos del usuario desde la base de datos
$query = mysqli_query($conection, "SELECT * FROM usuario WHERE idusuario = $idUsuario");
$data = mysqli_fetch_assoc($query);

// Mapear rol a texto
$roles = [1 => 'Administrador', 2 => 'Supervisor', 3 => 'Vendedor'];
$rolNombre = isset($roles[$data['rol']]) ? $roles[$data['rol']] : 'Desconocido';

// Cambiar contraseña
$alert = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $passActual = md5($_POST['txtPassUser']);
    $passNueva  = md5($_POST['txtNewPassUser']);
    $passConfirm = md5($_POST['txtNewPassConfirm']);

    if ($passNueva != $passConfirm) {
        $alert = '<p class="msg_error">La nueva contraseña y la confirmación no coinciden.</p>';
    } else {
        if ($passActual != $data['clave']) {
            $alert = '<p class="msg_error">La contraseña actual no es correcta.</p>';
        } else {
            $update = mysqli_query($conection, "UPDATE usuario SET clave = '$passNueva' WHERE idusuario = $idUsuario");
            if ($update) {
                $alert = '<p class="msg_save">Contraseña actualizada correctamente.</p>';
            } else {
                $alert = '<p class="msg_error">Error al actualizar la contraseña.</p>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php"; ?>
	<title>Información Personal</title>
</head>
<body>
	<?php include "includes/header.php"; ?>
	<section id="container">
		<h1 class="titlePanelControl">Configuración - Información Personal</h1>

		<div class="perfilUnico">
			<div class="containerDataUser">
				<div class="logoUser">
					<img src="img/logoUser.png">
				</div>
				<div class="DataUser">
					<h4>Información Personal</h4>
					<div><label>Nombre: </label> <span><?php echo $data['nombre']; ?></span></div>
					<div><label>Correo: </label> <span><?php echo $data['correo']; ?></span></div>

					<h4>Datos Usuario</h4>
					<div><label>Rol: </label> <span><?php echo $rolNombre; ?></span></div>
					<div><label>Usuario: </label> <span><?php echo $data['usuario']; ?></span></div>

					<h4>Cambiar Contraseña</h4>
					<form action="" method="post" name="frmChangePass" id="frmChangePass">
						<div>
							<input type="password" name="txtPassUser" id="txtPassUser" placeholder="Contraseña actual" required>
						</div>
						<div>
							<input type="password" name="txtNewPassUser" id="txtNewPassUser" placeholder="Nueva contraseña" required>
						</div>
						<div>
							<input type="password" name="txtNewPassConfirm" id="txtNewPassConfirm" placeholder="Confirmar contraseña" required>
						</div>
						<div class="alert"><?php echo $alert; ?></div>
						<div>
							<button type="submit" class="btn_save btnChangePass"><i class="fas fa-key"></i> Cambiar contraseña</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>
	<?php include "includes/footer.php"; ?>
</body>
</html>
