<?php
session_start();

// Solo acceso si es administrador
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('Location: ../');
    exit;
}

include "../conexion.php";

// Leer datos actuales de la empresa (se asume un solo registro)
$query = mysqli_query($conection, "SELECT * FROM configuracion LIMIT 1");
$data = mysqli_fetch_assoc($query);
$alert = '';

// Actualizar datos al enviar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'updateDataEmpresa') {
    $ruc     = mysqli_real_escape_string($conection, $_POST['txtRuc']);
    $nombre  = mysqli_real_escape_string($conection, $_POST['txtNombre']);
    $rsocial = mysqli_real_escape_string($conection, $_POST['txtRSocial']);
    $tel     = mysqli_real_escape_string($conection, $_POST['txtTelEmpresa']);
    $email   = mysqli_real_escape_string($conection, $_POST['txtEmailEmpresa']);
    $dir     = mysqli_real_escape_string($conection, $_POST['txtDirEmpresa']);
    $igv     = mysqli_real_escape_string($conection, $_POST['txtIGV']);

    $sqlUpdate = mysqli_query($conection, "UPDATE configuracion SET ruc='$ruc', nombre='$nombre', razon_social='$rsocial', telefono=$tel, email='$email', direccion='$dir', igv=$igv WHERE id = {$data['id']}");

    if ($sqlUpdate) {
        $alert = '<p class="msg_save">Datos actualizados correctamente.</p>';
        $query = mysqli_query($conection, "SELECT * FROM configuracion LIMIT 1");
        $data = mysqli_fetch_assoc($query);
    } else {
        $alert = '<p class="msg_error">Error al actualizar los datos.</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "includes/scripts.php"; ?>
    <title>Datos de la Empresa</title>
    <style>
        .containerDataEmpresa {
            margin: 0 auto;
            float: none;
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    <section id="container">
        <h1 class="titlePanelControl">Configuración - Datos de la Empresa</h1>

        <div class="containerDataEmpresa">
            <div class="logoEmpresa">
                <img src="img/logoEmpresa.png">
            </div>
            <h4 style="text-align:center;">Datos de la Empresa</h4>

            <form action="" method="post" name="frmEmpresa" id="frmEmpresa">
                <input type="hidden" name="action" value="updateDataEmpresa">

                <div>
                    <label>Ruc:</label>
                    <input type="text" name="txtRuc" value="<?php echo $data['ruc']; ?>" required>
                </div>
                <div>
                    <label>Nombre:</label>
                    <input type="text" name="txtNombre" value="<?php echo $data['nombre']; ?>" required>
                </div>
                <div>
                    <label>Razón social:</label>
                    <input type="text" name="txtRSocial" value="<?php echo $data['razon_social']; ?>" required>
                </div>
                <div>
                    <label>Teléfono:</label>
                    <input type="text" name="txtTelEmpresa" value="<?php echo $data['telefono']; ?>" required>
                </div>
                <div>
                    <label>Correo electrónico:</label>
                    <input type="email" name="txtEmailEmpresa" value="<?php echo $data['email']; ?>" required>
                </div>
                <div>
                    <label>Dirección:</label>
                    <input type="text" name="txtDirEmpresa" value="<?php echo $data['direccion']; ?>" required>
                </div>
                <div>
                    <label>IGV (%):</label>
                    <input type="text" name="txtIGV" value="<?php echo $data['igv']; ?>" required>
                </div>

                <div class="alertFormEmpresa"><?php echo $alert; ?></div>
                <div>
                    <button type="submit" class="btn_save btnChangePass"><i class="far fa-save"></i> Guardar datos</button>
                </div>
            </form>
        </div>
    </section>
    <?php include "includes/footer.php"; ?>
</body>
</html>
