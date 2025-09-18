<?php
session_start();
include "../conexion.php";

if(!empty($_POST))
{
    $alert='';
    if(empty($_POST['nombre'])|| empty($_POST['telefono'])||empty($_POST['direccion']))
    {
        $alert='<p class="msg_error">Todos los campos son obligatorios.</p>';
    }else{
            //recibir datos del formulario
            $ruc =  $_POST['ruc'];
            $nombre = $_POST['nombre'];
            $telefono = $_POST['telefono'];
            $direccion = $_POST['direccion'];
            $usuario_id= $_SESSION['idUser'];
            

            $result=0;
            if(is_numeric($ruc) and $ruc !=0)
            {
                $query = mysqli_query($conection,"SELECT * FROM cliente WHERE ruc = '$ruc' ");
                $result = mysqli_fetch_array($query);
            }
            if($result > 0){
                $alert='<p class="msg_error">El número de RUC ya existe.</p>';
                }else{
                    $query_insert= mysqli_query($conection, "INSERT INTO cliente(ruc,nombre,telefono,direccion,usuario_id) VALUES('$ruc','$nombre','$telefono','$direccion','$usuario_id')");

                    if($query_insert){
                        $alert='<p class="msg_save">Cliente guardado correctamente.</p>';
                                    }else{
                                        $alert='<p class="msg_error">Error al guardar el cliente.</p>';
                                         }
                }
                mysqli_close($conection);
        }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Registro Cliente</title>
</head>
<body>
	<?php include "includes/header.php";?>
	<section id="container">
		<div class="form_register">
            <h1>Registro Cliente</h1>
            <hr>
            <div class="alert"><?php echo isset($alert) ? $alert:''; ?></div>

            <form action=""method="post">
                <label for="ruc">RUC</label>
                <input type="number" name="ruc" id="ruc" placeholder="Número de RUC">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre Completo">
                <label for="telefono">Teléfono</label>
                <input type="number" name="telefono" id="telefono" placeholder="Teléfono">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" placeholder="Dirección completa">
                

                <input type="submit" value="Guardar Cliente" class="btn_save">
            </form>

        </div>
	</section>
	<?php include "includes/footer.php";?>
</body>
</html>