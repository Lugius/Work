<?php
require_once('db.php');
$link=mysql_connect($server,$user,$password);
if (!$link) {
    die('No se ha podido conectar: ' . mysql_error());
}

$query = "CREATE SCHEMA ".$database." DEFAULT CHARACTER SET utf8 ";
if (mysql_query($query, $link)) {
    echo "Base de datos creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

mysql_select_db($database) or die(mysql_error());

$query="CREATE TABLE `amortizacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` varchar(140) DEFAULT NULL,
  `enabled` int(10) DEFAULT NULL,
  `venta_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla de amortizacion creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `amortizacion_cid_multiple` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(10) DEFAULT NULL,
  `master_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla de amortizacion_cid_multiple creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `amortizacion_precio_venta_multiple` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `precio_venta` varchar(140) DEFAULT NULL,
  `master_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla de amortizacion_precio_venta_multiple creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `tipo` int(10) DEFAULT NULL,
  `imagen` varchar(200) DEFAULT NULL,
  `imagen_path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla de categorias creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `cat_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla de cat_tipo creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `cat_tipo_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `icono` varchar(140) DEFAULT NULL,
  `icono_path` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla de cat_tipo_compra creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `cat_tipo_estado_venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `icono` varchar(140) DEFAULT NULL,
  `icono_path` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `cat_tipo_nueva_venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` varchar(21) DEFAULT NULL,
  `cliente` int(10) DEFAULT NULL,
  `venta` int(10) DEFAULT NULL,
  `estado` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `cat_tipo_venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `usuario` varchar(140) DEFAULT NULL,
  `telefono` varchar(140) DEFAULT NULL,
  `tipo_cliente` int(10) DEFAULT NULL,
  `status` int(10) DEFAULT NULL,
  `fecha_comentario` varchar(10) DEFAULT NULL,
  `comentario` varchar(400) DEFAULT NULL,
  `email` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `estado_producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `icono` varchar(140) DEFAULT NULL,
  `icono_path` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_gasto` int(10) DEFAULT NULL,
  `fecha` varchar(10) DEFAULT NULL,
  `notas` varchar(400) DEFAULT NULL,
  `cantidad` int(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `gastosadicionales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `aumento` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `gastos_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `descripcion` varchar(220) DEFAULT NULL,
  `icono` varchar(45) DEFAULT NULL,
  `icono_path` varchar(220) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `nombre_UNIQUE` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `ingresos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_ingreso` int(10) DEFAULT NULL,
  `fecha` varchar(10) DEFAULT NULL,
  `notas` varchar(400) DEFAULT NULL,
  `cantidad` int(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `ingresos_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) NOT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `icono` varchar(140) DEFAULT NULL,
  `icono_path` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `nombre_UNIQUE` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `lotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `tipo_piedra` int(10) DEFAULT NULL,
  `categoria` int(10) DEFAULT NULL,
  `imagen` varchar(200) DEFAULT NULL,
  `tallado` int(10) DEFAULT NULL,
  `tipo_compra` int(10) DEFAULT NULL,
  `estado_producto` int(10) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `proveedor` int(10) DEFAULT NULL,
  `GR` varchar(140) DEFAULT NULL,
  `CT` varchar(140) DEFAULT NULL,
  `precio_origen` varchar(140) DEFAULT NULL,
  `coste` int(10) DEFAULT NULL,
  `imagen_path` varchar(200) DEFAULT NULL,
  `estado` int(10) DEFAULT NULL,
  `socio` int(10) DEFAULT NULL,
  `beneficios` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `lotes_gasto_multiple` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gasto` int(10) DEFAULT NULL,
  `master_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `piedra_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) NOT NULL,
  `sku` varchar(140) DEFAULT NULL,
  `lote` int(10) DEFAULT NULL,
  `t_piedra` int(10) NOT NULL,
  `categoria` int(10) DEFAULT NULL,
  `imagen` varchar(200) DEFAULT NULL,
  `codigobarras` varchar(140) NOT NULL,
  `estado` int(10) DEFAULT NULL,
  `tallado` int(10) DEFAULT NULL,
  `estado_producto` int(10) DEFAULT NULL,
  `tipo_compra` int(10) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `proveedor` int(10) DEFAULT NULL,
  `GR` varchar(140) DEFAULT NULL,
  `CT` varchar(140) DEFAULT NULL,
  `precio_origen` varchar(140) DEFAULT NULL,
  `coste` int(10) DEFAULT NULL,
  `imagen_path` varchar(200) DEFAULT NULL,
  `precio_venta` varchar(140) DEFAULT NULL,
  `incremento` varchar(140) DEFAULT NULL,
  `incremento_tipo` int(10) DEFAULT NULL,
  `socio` int(10) DEFAULT NULL,
  `beneficios` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigobarras_UNIQUE` (`codigobarras`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `productos_gasto_multiple` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gasto` int(10) DEFAULT NULL,
  `master_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `proveedor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `empresa` varchar(140) DEFAULT NULL,
  `email` varchar(140) DEFAULT NULL,
  `fecha` varchar(10) DEFAULT NULL,
  `observacion` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `retiros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_retiro` int(10) DEFAULT NULL,
  `fecha` varchar(10) DEFAULT NULL,
  `notas` varchar(400) DEFAULT NULL,
  `cantidad` int(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `retiros_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `icono` varchar(140) DEFAULT NULL,
  `icono_path` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `socios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `comentario` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `statusde_cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_cliente` varchar(140) DEFAULT NULL,
  `comentario_cliente` varchar(400) DEFAULT NULL,
  `icono_cliente` varchar(140) DEFAULT NULL,
  `icono_cliente_path` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `tipo_cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(140) DEFAULT NULL,
  `comentario_cliente` varchar(400) DEFAULT NULL,
  `icono_cliente` varchar(140) DEFAULT NULL,
  `icono_cliente_path` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nombre` tinytext COLLATE latin1_general_ci,
  `nombre_usuario` varchar(200) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `pwd` varchar(220) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `pwd_re` varchar(220) COLLATE latin1_general_ci NOT NULL,
  `email` varchar(220) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `tipo_usuario` tinyint(4) NOT NULL DEFAULT '1',
  `fecha` varchar(10) COLLATE latin1_general_ci NOT NULL,
  `notas` varchar(220) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nombre_usuario_UNIQUE` (`nombre_usuario`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  FULLTEXT KEY `idx_search` (`nombre`,`email`,`nombre_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=76 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="CREATE TABLE `usuarios_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="INSERT INTO ".$database.".`usuarios` 
(`id`, `nombre`, `nombre_usuario`, `pwd`, `pwd_re`, `email`, `tipo_usuario`, `fecha`) 
VALUES ('', 'admin', 'admin', 
	'$2a$10$1qAz2wSx3eDc4rFv5tGb5esCgDuHHeECLg/wE3TcnvJliPWZGiyae', 
	'$2a$10$1qAz2wSx3eDc4rFv5tGb5esCgDuHHeECLg/wE3TcnvJliPWZGiyae', 
	'admin@localhost', '1', '2014-06-02');";

if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="INSERT INTO ".$database.".`usuarios_tipo` 
(`id`, `nombre`, `descripcion`) 
VALUES ('1', 'super-admin', 'Usuario con acceso a todo el sistema. Puede crear, editar y eliminar usuarios.');";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

$query="INSERT INTO ".$database.".`usuarios_tipo` 
(`id`, `nombre`, `descripcion`) 
VALUES ('2', 'admin', 'Usuario con acceso a todo el sistema con excepción del sistema de usuarios que no podrá borrar, editar o eliminar usuarios.');";
if (mysql_query($query, $link)) {
    echo "Tabla creada satisfactoriamente!\n";
} else {
    echo 'Error creando base de datos: ' . mysql_error() . "\n";
}

header( 'Location: index.php' ) ;

?>