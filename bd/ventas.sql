-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-06-2025 a las 02:40:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ventas`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_precio_producto` (`n_cantidad` INT, `n_precio` DECIMAL(10,2), `codigo` INT)   BEGIN
    	DECLARE nueva_existencia int;
		DECLARE nuevo_total decimal(10,2);
        DECLARE nuevo_precio decimal(10,2);
        
        DECLARE cant_actual int;
		DECLARE pre_actual decimal(10,2);
        
        DECLARE actual_existencia int;
		DECLARE actual_precio decimal(10,2);
        
        SELECT precio,existencia INTO actual_precio,actual_existencia FROM producto WHERE codproducto = codigo;
        SET nueva_existencia = actual_existencia + n_cantidad;
        SET nuevo_total = (actual_existencia * actual_precio) + (n_cantidad * n_precio);
        SET nuevo_precio = nuevo_total / nueva_existencia;
        
        UPDATE producto SET existencia = nueva_existencia, precio = nuevo_precio WHERE codproducto = codigo;
		
        SELECT nueva_existencia,nuevo_precio;
        END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_temp` (`codigo` INT, `cantidad` INT, `token_user` VARCHAR(50))   BEGIN
DECLARE precio_actual decimal(10,2);
SELECT precio INTO precio_actual FROM producto WHERE codproducto = codigo;
INSERT INTO detalle_temp(token_user, codproducto, cantidad, precio_venta) VALUES (token_user, codigo, cantidad, precio_actual);
SELECT tmp.correlativo, tmp.codproducto,p.descripcion,tmp.cantidad, tmp.precio_venta FROM detalle_temp tmp
INNER JOIN producto p
ON tmp.codproducto = p.codproducto
WHERE tmp.token_user = token_user;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `anular_factura` (`no_factura` INT)   BEGIN
    	DECLARE existe_factura int;
        DECLARE registros int;
        DECLARE a int;
        
        DECLARE cod_producto int;
        DECLARE cant_producto int;
        DECLARE existencia_actual int;
        DECLARE nueva_existencia int;
        
        SET existe_factura = (SELECT COUNT(*) FROM factura WHERE nofactura = no_factura and estatus = 1);
        
        IF existe_factura > 0 THEN
        	CREATE TEMPORARY TABLE tbl_tmp(
                id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 cod_prod BIGINT,
              	cant_prod int);
                
                SET a=1;
                SET registros = (SELECT COUNT(*) FROM detallefactura WHERE nofactura = no_factura);
                
                IF registros > 0 THEN	
                	INSERT INTO tbl_tmp(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detallefactura WHERE nofactura = no_factura;
                    WHILE a <= registros DO
                    	SELECT cod_prod,cant_prod INTO cod_producto,cant_producto FROM tbl_tmp WHERE id = a;
                        SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
                        SET nueva_existencia = existencia_actual + cant_producto;
                        UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto;
                        SET a=a+1;
                        
                    END WHILE;
                    	UPDATE factura SET estatus = 2 WHERE nofactura = no_factura;
                        DROP TABLE tbl_tmp;
                        SELECT * FROM factura WHERE nofactura = no_factura;
                END IF;
        ELSE
        	SELECT 0 factura;
        END IF;
        END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `del_detalle_temp` (`id_detalle` INT, `token` VARCHAR(50))   BEGIN
DELETE FROM detalle_temp WHERE correlativo = id_detalle;
SELECT tmp.correlativo, tmp.codproducto,p.descripcion,tmp.cantidad, tmp.precio_venta FROM detalle_temp tmp INNER JOIN producto p
ON tmp.codproducto = p.codproducto WHERE tmp.token_user = token;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `procesar_venta` (`cod_usuario` INT, `cod_cliente` INT, `token` VARCHAR(50))   BEGIN
    	DECLARE factura INT;
        DECLARE registros INT;
        DECLARE total DECIMAL(10,2);
        DECLARE nueva_existencia int;
        DECLARE existencia_actual int;
        DECLARE tmp_cod_producto int;
        DECLARE tmp_cant_producto int;
        DECLARE a INT;
        SET a = 1;
        
        CREATE TEMPORARY TABLE tbl_tmp_tokenuser (
            id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            cod_prod BIGINT,
            cant_prod int);
          SET registros = (SELECT COUNT(*) FROM detalle_temp WHERE token_user = token);
          
          IF registros > 0 THEN
          		INSERT INTO tbl_tmp_tokenuser(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalle_temp WHERE token_user = token;
                
                INSERT INTO factura(usuario,codcliente) VALUES(cod_usuario,cod_cliente);
                SET factura = LAST_INSERT_ID();
                
                INSERT INTO detallefactura(nofactura,codproducto,cantidad,precio_venta) SELECT (factura) as nofactura, codproducto,cantidad,precio_venta FROM detalle_temp WHERE token_user = token;
                
                WHILE a <= registros DO
                	SELECT cod_prod,cant_prod INTO tmp_cod_producto,tmp_cant_producto FROM tbl_tmp_tokenuser WHERE id = a;
                    SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = tmp_cod_producto;
                    
                    SET nueva_existencia = existencia_actual - tmp_cant_producto;
                    UPDATE producto SET existencia = nueva_existencia WHERE codproducto = tmp_cod_producto;
                    
                    SET a=a+1;
                END WHILE;
                
                SET total = (SELECT SUM(cantidad * precio_venta) FROM detalle_temp WHERE token_user = token);
                UPDATE factura SET totalfactura = total WHERE nofactura = factura;
                DELETE FROM detalle_temp WHERE token_user = token;
                TRUNCATE TABLE tbl_tmp_tokenuser;
                SELECT * FROM factura WHERE nofactura = factura;
                
          ELSE
          	SELECT 0;
          END IF;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `ruc` varchar(11) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `dateadd` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `ruc`, `nombre`, `telefono`, `direccion`, `dateadd`, `usuario_id`, `estatus`) VALUES
(1, '11111111', 'Kevin', 963852741, 'av.15ads', '2024-12-01 21:47:39', 1, 1),
(2, '22222222', 'abc', 159951159, 'abc123', '2024-12-01 22:18:55', 2, 1),
(3, '33333333', 'aaa', 123456789, 'aaa111', '2024-12-01 22:22:05', 2, 1),
(4, '44444444', 'aaa', 123456789, 'aaa111', '2024-12-01 22:33:26', 2, 0),
(5, '123456', 'Kevin2', 222222222, 'abc222', '2024-12-01 22:33:47', 2, 1),
(6, '123456789', 'prueba', 123456789, 'av cantuta 123', '2025-05-16 00:22:11', 1, 1),
(7, '2147483647', 'prueba 2', 321456897, 'ciudadprueba', '2025-05-16 00:34:59', 1, 1),
(8, '987654321', 'prueba3', 789456123, 'av.ciudad5', '2025-05-16 00:37:48', 1, 1),
(9, '2147483647', 'prueba5', 852852963, 'av.ciudad6', '2025-05-16 00:38:53', 1, 1),
(10, '98765432101', 'prueba 6', 123123321, 'av.cusco.15', '2025-05-16 00:51:36', 1, 1),
(11, '99999999999', 'prueba7', 987789963, 'avcusco,2025', '2025-05-16 00:52:33', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) NOT NULL,
  `ruc` varchar(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `telefono` int(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `direccion` text NOT NULL,
  `igv` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `ruc`, `nombre`, `razon_social`, `telefono`, `email`, `direccion`, `igv`) VALUES
(1, '10783774866', 'BODEGA BENJA', '', 974713554, 'chang.contratos@gmail.com', 'Av.Alejandro Bertello 381, Lima.', 18.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallefactura`
--

CREATE TABLE `detallefactura` (
  `correlativo` bigint(11) NOT NULL,
  `nofactura` bigint(11) DEFAULT NULL,
  `codproducto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `detallefactura`
--

INSERT INTO `detallefactura` (`correlativo`, `nofactura`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(1, 1, 1, 11, 121.31),
(2, 2, 1, 1, 121.31),
(3, 3, 1, 1, 121.31),
(4, 4, 1, 1, 121.31),
(5, 5, 16, 10, 500.00),
(6, 6, 16, 10, 500.00),
(7, 6, 14, 10, 1366.67),
(9, 7, 1, 11, 121.31),
(10, 8, 12, 10, 50.00),
(11, 9, 1, 10, 121.31),
(12, 10, 1, 10, 121.31);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_temp`
--

CREATE TABLE `detalle_temp` (
  `correlativo` int(11) NOT NULL,
  `token_user` varchar(50) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

CREATE TABLE `entradas` (
  `correlativo` int(11) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`correlativo`, `codproducto`, `fecha`, `cantidad`, `precio`, `usuario_id`) VALUES
(1, 1, '2025-04-07 11:27:04', 150, 110.00, 1),
(2, 2, '2025-04-07 11:30:39', 100, 1500.00, 1),
(6, 6, '2025-04-08 12:30:59', 500, 54000.00, 1),
(7, 7, '2025-04-08 12:43:17', 500, 54000.00, 1),
(8, 8, '2025-04-08 12:43:40', 500, 5000.00, 1),
(9, 9, '2025-04-08 12:54:52', 101, 500.00, 1),
(10, 10, '2025-04-08 12:57:41', 22002, 5000.00, 1),
(11, 11, '2025-04-14 09:10:59', 50, 500.00, 1),
(12, 12, '2025-04-14 09:12:53', 100, 50.00, 1),
(13, 13, '2025-04-14 10:06:23', 10, 1200.00, 1),
(14, 14, '2025-04-14 10:21:25', 10, 1200.00, 1),
(15, 15, '2025-04-14 12:11:17', 200, 500.00, 1),
(16, 16, '2025-04-14 12:11:50', 200, 500.00, 1),
(17, 17, '2025-04-14 12:26:50', 200, 2000.00, 1),
(18, 18, '2025-04-14 12:29:23', 100, 5000.00, 1),
(19, 19, '2025-04-14 12:30:05', 1, 5000.00, 1),
(20, 20, '2025-04-14 12:36:26', 200, 25.00, 1),
(21, 21, '2025-04-14 12:38:38', 70, 40.00, 1),
(22, 14, '2025-04-20 23:59:46', 10, 1400.00, 1),
(23, 14, '2025-04-21 00:04:20', 10, 1400.00, 1),
(24, 14, '2025-04-21 00:11:08', 10, 1500.00, 1),
(25, 17, '2025-04-21 00:23:21', 100, 3000.00, 1),
(26, 17, '2025-04-21 00:26:04', 100, 3000.00, 1),
(27, 21, '2025-04-21 09:22:17', 10, 60.00, 1),
(28, 21, '2025-04-21 09:26:01', 10, 50.00, 1),
(29, 21, '2025-04-21 09:52:03', 10, 50.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `nofactura` bigint(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `totalfactura` decimal(10,2) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`nofactura`, `fecha`, `usuario`, `codcliente`, `totalfactura`, `estatus`) VALUES
(1, '2025-06-08 18:03:02', 1, 6, 1334.41, 2),
(2, '2025-06-08 18:04:39', 1, 2, 121.31, 2),
(3, '2025-06-08 18:06:00', 1, 1, 121.31, 2),
(4, '2025-06-08 18:09:58', 1, 1, 121.31, 2),
(5, '2025-06-15 15:03:17', 1, 2, 5000.00, 2),
(6, '2025-06-15 16:47:27', 1, 1, 18666.70, 2),
(7, '2025-06-15 17:14:02', 1, 1, 1334.41, 2),
(8, '2025-06-15 17:15:41', 1, 1, 500.00, 2),
(9, '2025-06-15 17:16:15', 1, 1, 1213.10, 2),
(10, '2025-06-15 17:55:17', 1, 1, 1213.10, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `codproducto` int(11) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `proveedor` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `existencia` int(11) DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `foto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`codproducto`, `descripcion`, `proveedor`, `precio`, `existencia`, `date_add`, `usuario_id`, `estatus`, `foto`) VALUES
(1, 'Mouse USB', 14, 121.31, 250, '2025-04-07 11:27:04', 1, 1, 'MOUSE.JPG'),
(2, 'Monitor LCD', 14, 1500.00, 100, '2025-04-07 11:30:39', 1, 1, 'monitor.jpj'),
(6, 'cAMA', 15, 54000.00, 500, '2025-04-08 12:30:59', 1, 1, 'img_4855d2496779e66756fc94051d956dca.jpg'),
(7, 'cAMA', 15, 54000.00, 500, '2025-04-08 12:43:17', 1, 1, 'img_1e9185fb4fa76addd97fd38351385aa3.jpg'),
(8, 'ACER20', 15, 5000.00, 500, '2025-04-08 12:43:40', 1, 1, 'img_f1f8565ed6d9c58651c3b50e286265ec.jpg'),
(9, 'Lenovo', 15, 500.00, 101, '2025-04-08 12:54:52', 1, 1, 'img_producto.png'),
(10, 'Lenovo', 15, 5000.00, 22002, '2025-04-08 12:57:41', 1, 1, 'img_436556729d0b9a8beeaf1115fdf217b9.jpg'),
(11, 'Plancha', 16, 500.00, 50, '2025-04-14 09:10:59', 1, 1, 'img_a5f5bfd791e3cb71c8216ccf78463883.jpg'),
(12, 'Licuadora', 16, 50.00, 100, '2025-04-14 09:12:53', 1, 1, 'img_producto.png'),
(13, 'laptop', 15, 1200.00, 10, '2025-04-14 10:06:23', 1, 1, 'img_e4c59fb78eb03f63366bdf04edd94f77.jpg'),
(14, 'laptop', 15, 1366.67, 30, '2025-04-14 10:21:25', 1, 1, 'img_cc79034bf819ed13d812909ddfd3ae16.jpg'),
(15, 'Procesador de Alimentos', 16, 500.00, 200, '2025-04-14 12:11:17', 1, 1, 'img_5ddfa5b2328e313defc501c22400835b.jpg'),
(16, 'Procesador de Alimentos', 16, 500.00, 200, '2025-04-14 12:11:50', 1, 1, 'img_f6debdd74f327f1dba594acefa58149b.jpg'),
(17, 'Tostadora', 16, 2500.00, 400, '2025-04-14 12:26:50', 1, 0, 'img_f6debdd74f327f1dba594acefa58149b.jpg'),
(18, 'Freidora de Aire', 16, 5000.00, 100, '2025-04-14 12:29:23', 1, 0, 'img_6f1d5753c39c6fd463b107cfbe3b7a33.jpg'),
(19, 'Freidora de Aire', 15, 5000.00, 1, '2025-04-14 12:30:05', 1, 1, 'img_producto.png'),
(20, 'Batidora de Mano', 16, 25.00, 200, '2025-04-14 12:36:26', 1, 0, 'img_ffe9788b607cfff08ce17a25afb5ff67.jpg'),
(21, 'Cafetera', 16, 44.00, 100, '2025-04-14 12:38:38', 1, 0, 'img_9492fd746c4895ee25f477ca71306c98.jpg');

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `entradas_A_I` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
    INSERT INTO entradas(codproducto, cantidad, precio, usuario_id)
    VALUES(new.codproducto, new.existencia, new.precio, new.usuario_id);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `codproveedor` int(11) NOT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` bigint(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`codproveedor`, `proveedor`, `contacto`, `telefono`, `direccion`, `date_add`, `usuario_id`, `estatus`) VALUES
(1, 'BIC', 'Claudia Rosales', 789877889, 'Avenida las Americas', '2024-12-02 02:03:31', 1, 0),
(13, 'abc', 'juan perez', 963852741, 'av. las avenidas', '2024-12-02 02:52:25', 1, 0),
(14, 'COBRA', 'Cobra Peru SAC', 963852741, 'av alejandro bertello111', '2025-04-07 09:35:25', 1, 1),
(15, 'ACER', 'Rodrigo A n', 987456321, 'cercado de lima 123, 12', '2025-04-08 01:07:03', 1, 1),
(16, 'OSTER', 'OSTER 159', 852963741, 'AV. OSTER .COM', '2025-04-14 09:05:02', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idrol` int(11) NOT NULL,
  `rol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idrol`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `usuario` varchar(15) DEFAULT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `rol` int(11) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `correo`, `usuario`, `clave`, `rol`, `estatus`) VALUES
(1, 'Chang', 'chang.contratos@gmail.com', 'admin', '0192023a7bbd73250516f069df18b500', 1, 1),
(2, 'Kevin2', 'kevin@gmail.com', 'kevin', 'd2e7a2105d0fb461fe6f2858cc33942f', 3, 0),
(8, 'Guevara', 'guevara@gmail.com', 'guevara', 'a14c147048df7f5f4bc4c32762d32457', 2, 1),
(9, 'vendedor1', 'vendedor1@gmail.com', 'vendedor1', 'd66b1b3e65cbc2e117733e980aa1ed30', 3, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `nofactura` (`nofactura`);

--
-- Indices de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `nofactura` (`token_user`),
  ADD KEY `codproducto` (`codproducto`);

--
-- Indices de la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`nofactura`),
  ADD KEY `usuario` (`usuario`),
  ADD KEY `codcliente` (`codcliente`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`codproducto`),
  ADD KEY `proveedor` (`proveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`codproveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idrol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  MODIFY `correlativo` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `entradas`
--
ALTER TABLE `entradas`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `nofactura` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `codproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `codproveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`);

--
-- Filtros para la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD CONSTRAINT `detallefactura_ibfk_1` FOREIGN KEY (`nofactura`) REFERENCES `factura` (`nofactura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detallefactura_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD CONSTRAINT `detalle_temp_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`codcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`codproveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `proveedor_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`idrol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
