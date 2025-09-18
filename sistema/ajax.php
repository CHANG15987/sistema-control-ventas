<?php 
    include "../conexion.php";
    session_start();
    //print_r($_POST);
    //exit;

    if(!empty($_POST)){
        //Extraer datos del producto
        if($_POST['action']== 'infoProducto')
        {
            $producto_id = $_POST['producto'];
            $query = mysqli_query($conection,"SELECT codproducto,descripcion,existencia,precio FROM producto WHERE codproducto = $producto_id AND estatus = 1");
            mysqli_close($conection);
            $result = mysqli_num_rows($query);
            if($result> 0){
                $data = mysqli_fetch_assoc($query);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                exit;
            }
            echo 'error';
            exit;         
        }
        
        //Agregar productos a entrada
        if($_POST['action']== 'addProduct'){
            if(!empty($_POST['cantidad']) || !empty($_POST['precio']) || !empty($_POST['producto_id'])){
                $cantidad = $_POST['cantidad'];
                $precio = $_POST['precio'];
                $producto_id = $_POST['producto_id'];
                $usuario_id = $_SESSION['idUser'];

                $query_insert = mysqli_query($conection,"INSERT INTO entradas(codproducto,cantidad,precio,usuario_id) VALUES($producto_id,$cantidad,$precio,$usuario_id)");

                if($query_insert){
                    //Ejecutar procedimiento almacenado
                    $query_upd = mysqli_query($conection,"CALL actualizar_precio_producto($cantidad,$precio,$producto_id)");
                    $result_pro = mysqli_num_rows($query_upd);
                    if($result_pro > 0){
                        $data = mysqli_fetch_assoc($query_upd);
                        $data['producto_id'] = $producto_id;

                        echo json_encode($data, JSON_UNESCAPED_UNICODE);
                        exit;
                }
        }       else {
                    echo 'error';
            }
            mysqli_close($conection);
         }else{
            echo 'error';
      }
        exit;
   }
   //Eliminar producto
   if($_POST['action']== 'delProduct')
   {
    if(empty($_POST['producto_id'])|| !is_numeric($_POST['producto_id'])){
        echo 'error';
    }else{
    $idproducto = $_POST['producto_id'];
     $query_delete = mysqli_query($conection, "UPDATE producto SET estatus = 0 WHERE codproducto=$idproducto");
     mysqli_close($conection);

     if($query_delete){
        echo 'ok';
     }else{
         echo "Error al eliminar";
     }
       }
        echo'error';
        exit;
}
    //Buscar cliente
    if($_POST['action']  == 'searchCliente')
    {
        if(!empty($_POST['cliente'])){
            $ruc = $_POST['cliente'];
            $query = mysqli_query($conection,"SELECT * FROM cliente WHERE ruc LIKE '$ruc' and estatus = 1");
            mysqli_close($conection);
            $result = mysqli_num_rows($query);
            
            $data = '';
            if($result > 0){
                $data = mysqli_fetch_assoc($query);
            }else{
                $data = 0;
            }
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    //Registrar cliente - Ventas
     if($_POST['action']  == 'addCliente')
     {
        $ruc = $_POST['ruc_cliente'];
        $nombre = $_POST['nom_cliente'];
        $telefono = $_POST['tel_cliente'];
        $direccion = $_POST['dir_cliente'];
        $usuario_id = $_SESSION['idUser'];

        $query_insert = mysqli_query($conection,"INSERT INTO cliente(
                                                ruc,nombre,telefono,direccion,usuario_id)
                                                VALUES('$ruc','$nombre','$telefono','$direccion','$usuario_id')");
        if($query_insert){
            $codCliente = mysqli_insert_id($conection);
            $msg = $codCliente;
        }else{
            $msg = 'error';
        }
        mysqli_close($conection);
        echo $msg;
        exit;
     }
     //Agregar producto al detalle temporal
     if($_POST['action']  == 'addProductoDetalle'){
        
        if (empty($_POST['producto']) || empty($_POST['cantidad']))
        {
        echo 'error';
        }else{
            $codproducto = $_POST['producto'];
            $cantidad    = $_POST['cantidad'];
            $token       = md5($_SESSION['idUser']);

            $query_igv = mysqli_query($conection, "SELECT igv FROM configuracion");
            $result_igv = mysqli_num_rows($query_igv);
            
            $query_detalle_temp = mysqli_query($conection, "CALL add_detalle_temp($codproducto, $cantidad, '$token')");
            $result = mysqli_num_rows($query_detalle_temp);
            $detalleTabla = '';
            $sub_total =0;
            $igv =0;
            $total =0;
            $arrayData = array();

            if($result > 0){
                if($result > 0){
                    $info_igv = mysqli_fetch_assoc($query_igv);
                    $igv = $info_igv['igv'];
                }
                while($data = mysqli_fetch_assoc($query_detalle_temp)){
                    $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                    $sub_total = round($sub_total + $precioTotal, 2);
                    $total = round($total + $precioTotal, 2);
                    $detalleTabla .= '<tr>
                                    <td>'.$data['codproducto'].'</td>
                                    <td colspan="2">'.$data['descripcion'].'</td>
                                    <td class="textcenter">'.$data['cantidad'].'</td>
                                    <td class="textright">'.$data['precio_venta'].'</td>
                                    <td class="textright">'.$precioTotal.'</td>
                                    <td class="">
                                        <a class="link_delete" href="#" onclick="event.preventDefault();
                                        del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                    </td>
                                </tr>';
                }
                
                    $impuesto = round($sub_total * ($igv / 100), 2);
                    $tl_snigv = round($sub_total - $impuesto, 2);
                    $total = round($tl_snigv + $impuesto, 2);

                    $detalleTotales =  '<tr>
                                    <td colspan="5" class="textright">SUBTOTAL Q.</td>
                                    <td class="textright">'.$tl_snigv.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">IGV ('.$igv.'%)</td>
                                    <td class="textright">'.$impuesto.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">TOTAL Q.</td>
                                    <td class="textright">'.$total.'</td>
                                </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;
                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
            }else{
                echo 'error';
            }
            mysqli_close($conection);
           }
           exit;
     }
     //Extraer datos del detalle_temp
      if($_POST['action']  == 'serchForDetalle'){
        
        if (empty($_POST['user']))
        {
        echo 'error';
        }else{
            $token       = md5($_SESSION['idUser']);
            
            $query = mysqli_query($conection,"SELECT tmp.correlativo,
                                                    tmp.token_user,
                                                    tmp.cantidad,
                                                    tmp.precio_venta,
                                                    p.codproducto,
                                                    p.descripcion
                                                    FROM detalle_temp tmp
                                                    INNER JOIN producto p
                                                    ON tmp.codproducto = p.codproducto
                                                    WHERE token_user = '$token'");
            $result = mysqli_num_rows($query);

            $query_igv = mysqli_query($conection, "SELECT igv FROM configuracion");
            $result_igv = mysqli_num_rows($query_igv);

            $detalleTabla = '';
            $sub_total =0;
            $igv =0;
            $total =0;
            $arrayData = array();

            if($result > 0){
                if($result > 0){
                    $info_igv = mysqli_fetch_assoc($query_igv);
                    $igv = $info_igv['igv'];
                }
                while($data = mysqli_fetch_assoc($query)){
                    $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                    $sub_total = round($sub_total + $precioTotal, 2);
                    $total = round($total + $precioTotal, 2);
                    $detalleTabla .= '<tr>
                                    <td>'.$data['codproducto'].'</td>
                                    <td colspan="2">'.$data['descripcion'].'</td>
                                    <td class="textcenter">'.$data['cantidad'].'</td>
                                    <td class="textright">'.$data['precio_venta'].'</td>
                                    <td class="textright">'.$precioTotal.'</td>
                                    <td class="">
                                        <a class="link_delete" href="#" onclick="event.preventDefault();
                                        del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                    </td>
                                </tr>';
                }
                
                    $impuesto = round($sub_total * ($igv / 100), 2);
                    $tl_snigv = round($sub_total - $impuesto, 2);
                    $total = round($tl_snigv + $impuesto, 2);

                    $detalleTotales =  '<tr>
                                    <td colspan="5" class="textright">SUBTOTAL Q.</td>
                                    <td class="textright">'.$tl_snigv.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">IGV ('.$igv.'%)</td>
                                    <td class="textright">'.$impuesto.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">TOTAL Q.</td>
                                    <td class="textright">'.$total.'</td>
                                </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;
                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
            }else{
                echo 'error';
            }
            mysqli_close($conection);
           }
           exit;
     }

     if($_POST['action']  == 'delProductoDetalle'){
        if (empty($_POST['id_detalle']))
        {
            echo 'error';
        }else{
            $id_detalle = $_POST['id_detalle'];
            $token       = md5($_SESSION['idUser']);
            
            $query_igv = mysqli_query($conection, "SELECT igv FROM configuracion");
            $result_igv = mysqli_num_rows($query_igv);

            $query_detalle_temp = mysqli_query($conection,"CALL del_detalle_temp($id_detalle,'$token')");
            $result = mysqli_num_rows($query_detalle_temp);

            $detalleTabla = '';
            $sub_total =0;
            $igv =0;
            $total =0;
            $arrayData = array();

            if($result > 0){
                if($result > 0){
                    $info_igv = mysqli_fetch_assoc($query_igv);
                    $igv = $info_igv['igv'];
                }
                while($data = mysqli_fetch_assoc($query_detalle_temp)){
                    $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                    $sub_total = round($sub_total + $precioTotal, 2);
                    $total = round($total + $precioTotal, 2);
                    $detalleTabla .= '<tr>
                                    <td>'.$data['codproducto'].'</td>
                                    <td colspan="2">'.$data['descripcion'].'</td>
                                    <td class="textcenter">'.$data['cantidad'].'</td>
                                    <td class="textright">'.$data['precio_venta'].'</td>
                                    <td class="textright">'.$precioTotal.'</td>
                                    <td class="">
                                        <a class="link_delete" href="#" onclick="event.preventDefault();
                                        del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                    </td>
                                </tr>';
                }
                
                    $impuesto = round($sub_total * ($igv / 100), 2);
                    $tl_snigv = round($sub_total - $impuesto, 2);
                    $total = round($tl_snigv + $impuesto, 2);

                    $detalleTotales =  '<tr>
                                    <td colspan="5" class="textright">SUBTOTAL Q.</td>
                                    <td class="textright">'.$tl_snigv.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">IGV ('.$igv.'%)</td>
                                    <td class="textright">'.$impuesto.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">TOTAL Q.</td>
                                    <td class="textright">'.$total.'</td>
                                </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;
                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
            }else{
                echo 'error';
            }
            mysqli_close($conection);
           }
           exit;
     }
     //Anular Venta
     if($_POST['action'] == 'anularVenta'){
        $token   = md5($_SESSION['idUser']);
        $query_del = mysqli_query($conection,"DELETE FROM detalle_temp WHERE token_user = '$token' ");
        mysqli_close($conection);
        if($query_del){
            echo 'ok';
        }else{
            echo 'error';
        }
        exit;
     }
     //Procesar Venta
//Procesar Venta usando PROCEDIMIENTO almacenado
if ($_POST['action'] == 'procesarVenta') {
    if (empty($_POST['codcliente'])) {
        $codcliente = 1;
    } else {
        $codcliente = intval($_POST['codcliente']);
    }

    $tipo_comprobante = (isset($_POST['tipo_comprobante']) && $_POST['tipo_comprobante'] == 'boleta') ? 'boleta' : 'factura';

    $token = md5($_SESSION['idUser']);
    $usuario = $_SESSION['idUser'];

    // Llamar al procedimiento almacenado
    $query = mysqli_query($conection, "CALL procesar_venta($usuario, $codcliente, '$token', '$tipo_comprobante')");

    $result = mysqli_num_rows($query);
    mysqli_close($conection);
    if ($result > 0) {
        $data = mysqli_fetch_assoc($query);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        echo "error";
    }
     exit;
}


     //Info Factura
     if($_POST['action'] == 'infoFactura'){
        if(!empty($_POST['nofactura'])){

        $nofactura = $_POST['nofactura'];
        
        $query = mysqli_query($conection,"SELECT * FROM factura WHERE nofactura = '$nofactura' AND estatus = 1");
        mysqli_close($conection);
        $result = mysqli_num_rows($query);
        if($result > 0){
            $data = mysqli_fetch_assoc($query);
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
            exit;
        }
     }
     echo "error";
     exit;
    }
    //Anular Factura
    if($_POST['action'] == 'anularFactura'){
        if(!empty($_POST['noFactura'])){

        $nofactura = $_POST['noFactura'];
        
        $query_anular = mysqli_query($conection,"CALL anular_factura($nofactura)");
        mysqli_close($conection);
        $result = mysqli_num_rows($query_anular);
        if($result > 0){
            $data = mysqli_fetch_assoc($query_anular);
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
            exit;
        }
     }
     echo "error";
     exit;
    }
    // Obtener tipo de comprobante (boleta o factura)
if ($_POST['action'] == 'obtenerTipoComprobante') {
    if (!empty($_POST['nofactura'])) {
        $nofactura = intval($_POST['nofactura']);
        $query = mysqli_query($conection, "SELECT tipo_comprobante FROM factura WHERE nofactura = $nofactura LIMIT 1");
        $result = mysqli_num_rows($query);
        mysqli_close($conection);

        if ($result > 0) {
            $data = mysqli_fetch_assoc($query);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    echo "error";
    exit;
}

}
exit;
?>