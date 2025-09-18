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
            $idCliente = $_POST['id'];
            $ruc = $_POST['ruc'];
            $nombre = $_POST['nombre'];
            $telefono = $_POST['telefono'];
            $direccion = $_POST['direccion'];

            $result = 0;
            if(is_numeric($ruc) and $ruc !=0)
            {
            $query = mysqli_query($conection,"SELECT * FROM cliente WHERE (ruc = '$ruc' AND idcliente != $idCliente)");
            $result = mysqli_fetch_array($query);
            $result = count((array) $result);

            }

            if($result > 0){
                            $alert='<p class="msg_error">El RUC ya existe, ingrese uno nuevo.</p>';
                            } else{
                                if($ruc == '')
                                {
                                    $ruc = 0;
                                }

                                    $sql_update = mysqli_query($conection,"UPDATE cliente
                                                                            SET ruc = '$ruc', nombre='$nombre', telefono='$telefono', direccion='$direccion'
                                                                            WHERE idcliente=$idCliente ");
                                
                                    if($sql_update){
                                        $alert='<p class="msg_save">Cliente actualizado correctamente.</p>';
                                                    }else{
                                                        $alert='<p class="msg_error">Error al actualizar cliente.</p>';
                                                         }
                                }
         }
        // mysqli_close($conection);
}
//Mostrar Datos
if(empty($_REQUEST['id']))
{
    header('Location: lista_clientes.php');
    mysqli_close($conection);
}
$idcliente = $_REQUEST['id'];
$sql = mysqli_query($conection,"SELECT * FROM cliente WHERE idcliente= $idcliente and estatus = 1");
 mysqli_close($conection);

$result_sql = mysqli_num_rows($sql);
if($result_sql == 0){
    header('Location: lista_clientes.php');
}else{
    while($data = mysqli_fetch_array($sql)){
        $idcliente  = $data['idcliente'];
        $ruc        = $data['ruc'];
        $nombre     = $data['nombre'];
        $telefono   = $data['telefono'];
        $direccion  = $data['direccion'];
        
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Actualizar cliente</title>
</head>
<body>
	<?php include "includes/header.php";?>
	<section id="container">
		<div class="form_register">
            <h1>Actualizar cliente</h1>
            <hr>
            <div class="alert"><?php echo isset($alert) ? $alert:''; ?></div>

            <form action=""method="post">
                <input type="hidden" name="id" value="<?php echo $idcliente; ?>">
                <label for="ruc">RUC</label>
                <input type="number" name="ruc" id="ruc" placeholder="Número de RUC" value="<?php echo $ruc; ?>">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre Completo" value="<?php echo $nombre; ?>">
                <label for="telefono">Teléfono</label>
                <input type="number" name="telefono" id="telefono" placeholder="Teléfono" value="<?php echo $telefono; ?>">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" placeholder="Dirección completa" value="<?php echo $direccion; ?>">
                <input type="submit" value="Actualizar Cliente" class="btn_save">
            </form>

        </div>
	</section>
	<?php include "includes/footer.php";?>
</body>
</html>