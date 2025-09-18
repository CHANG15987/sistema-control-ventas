<?php
    $alert = '';
    session_start();
    if(!empty($_SESSION['active']))
    {
        header('location: sistema/');
    }else{

       
    if(!empty($_POST))
    {
       if(empty($_POST['usuario'])|| empty($_POST['clave']))
       {
            $alert='Ingrese su usuario y su clave';
       }else{
                require_once "conexion.php";
                //mysqli_real_escape_string= se utiliza en PHP para proteger contra ataques de inyección SQL
                $user = mysqli_real_escape_string($conection,$_POST['usuario']);
                $pass = md5(mysqli_real_escape_string($conection,$_POST['clave']));

                $query = mysqli_query($conection,"SELECT * FROM usuario WHERE usuario='$user' AND clave='$pass'");
                mysqli_close($conection);
                $result =mysqli_num_rows($query);
                if($result > 0)
                {
                    $data = mysqli_fetch_array($query);
                    $_SESSION['active'] = true;
                    $_SESSION['idUser'] = $data['idusuario'];
                    $_SESSION['nombre'] = $data['nombre'];
                    $_SESSION['email'] = $data['correo'];
                    $_SESSION['user'] = $data['usuario'];
                    $_SESSION['rol'] = $data['rol'];
                    header('location: sistema/');
                }else{
                        $alert= 'El usuario o la clave son incorrectos';
                        session_destroy();
                    }
            }
    } 
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <section id="container">
        <form action="" method="post">
            <h3>Iniciar Sesión</h3>
            <img src="img/logo_benja.png" alt="Login">
            <input type="text" name="usuario" placeholder="Usuario">
            <input type="password" name="clave" placeholder="Contraseña">
            <div class="alert"><?php echo isset($alert)? $alert : ''; ?></div>
            <input type="submit" value="Ingresar">

        </form>
    </section>
    
</body>
</html>