<?php
require_once('framework-master.php');
require_once('db.php');
session_start();
	if(isset($_SESSION['id_usuario'])){
		$message = "Esta sesión ya esta iniciada.";
		} else {

		if(!isset($_POST['nombre_usuario'], $_POST['pwd'])){
			$message = "Pofravor introduzca informacion valida.";
		} else {

			$nombre_usuario = filter_var($_POST['nombre_usuario'], FILTER_SANITIZE_STRING);
			$pwd = filter_var($_POST['pwd'], FILTER_SANITIZE_STRING);
			mysql_connect($server,$user,$password);
			@mysql_select_db($database) or die("Error eligiendo database");
			$query="SELECT id, nombre, nombre_usuario, pwd FROM usuarios WHERE nombre_usuario = '$nombre_usuario' LIMIT 1";
			$result=mysql_query($query);
			if (!$result) {
				$link=mysql_connect($server,$user,$password);
				if (!$link) {
				    die('No se ha podido conectar: ' . mysql_error());
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
				if (!mysql_query($query, $link)) {
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
				if (!mysql_query($query, $link)) {
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
				if (!mysql_query($query, $link)) {
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
				if (!mysql_query($query, $link)) {
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
				if (!mysql_query($query, $link)) {
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
				if (!mysql_query($query, $link)) {
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
				if (!mysql_query($query, $link)) {
				    echo 'Error creando base de datos: ' . mysql_error() . "\n";
				}

				$query="CREATE TABLE `usuarios_tipo` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `nombre` varchar(120) DEFAULT NULL,
				  `descripcion` varchar(250) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;";
				if (!mysql_query($query, $link)) {
				    echo 'Error creando base de datos: ' . mysql_error() . "\n";
				}

				$query="INSERT INTO ".$database.".`usuarios` 
				(`id`, `nombre`, `nombre_usuario`, `pwd`, `pwd_re`, `email`, `tipo_usuario`, `fecha`) 
				VALUES ('', 'admin', 'admin', 
					'$2a$10$1qAz2wSx3eDc4rFv5tGb5esCgDuHHeECLg/wE3TcnvJliPWZGiyae', 
					'$2a$10$1qAz2wSx3eDc4rFv5tGb5esCgDuHHeECLg/wE3TcnvJliPWZGiyae', 
					'admin@localhost', '1', '2014-06-02');";

				if (!mysql_query($query, $link)) {
				    echo 'Error creando base de datos: ' . mysql_error() . "\n";
				}

				$query="INSERT INTO ".$database.".`usuarios_tipo` 
				(`id`, `nombre`, `descripcion`) 
				VALUES ('1', 'super-admin', 'Usuario con acceso a todo el sistema. Puede crear, editar y eliminar usuarios.');";
				if (!mysql_query($query, $link)) {
				    echo 'Error creando base de datos: ' . mysql_error() . "\n";
				}

				$query="INSERT INTO ".$database.".`usuarios_tipo` 
				(`id`, `nombre`, `descripcion`) 
				VALUES ('2', 'admin', 'Usuario con acceso a todo el sistema con excepción del sistema de usuarios que no podrá borrar, editar o eliminar usuarios.');";
				if (!mysql_query($query, $link)) {
				    echo 'Error creando base de datos: ' . mysql_error() . "\n";
				}

				$query="SELECT id, nombre, nombre_usuario, pwd FROM usuarios WHERE nombre_usuario = '$nombre_usuario' LIMIT 1";
				$result=mysql_query($query);
			}

			if($usuario=mysql_fetch_array($result)){
				$hash = $usuario['pwd'];
				if (crypt($pwd, '$2a$10$1qAz2wSx3eDc4rFv5tGb5t') === $hash){
					$_SESSION['id_usuario'] = $usuario['id'];
					$nombre = $usuario['nombre'];
					header( 'Location: panel_control.php' ) ;
				} else {
					$message = "El usuario no existe o la contraseña es incorrecta";
				}
			} else {
				$message = "El usuario no existe o la contraseña es incorrecta";
			}
			mysql_close();
		} 
	}
?>
<html>
<head>
<?php
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
require_once('menu-maker.php');


?>
<title>Error al iniciar session</title>
</head>
<body>
<div class="wrapper" id="wrapper">
<div class="wrapper-header" id="wrapper-header">
	<div style="margin-top:5%;margin-bottom:2%;">
		<a href="index.php"><img class="logo" src="logo.png" style="float:left;height:183px;margin-left:30%;"/></a>
		<?php
		if($tipo_usuario == 1 || $tipo_usuario == 2) {
			?> 
			<a href="logout.php"><p style="float:left;margin-left:20px;">Cerrar sesión</p></a>
			<?php
		}
		?>
		<br/><br/><br/><br/><br/><br/><br/><br/>
		<h1><div id="titulo" style="text-align:right;margin-right:2%;"></div></h1>
	</div>
</div>
</div>
<br/><br/><br/><br/><br/><br/>
<?php 
	echo "<p style=\"margin-left:30%;font-size:30px;\">$message</p>";
?>
<form action="index.php"><p style="margin-left:30%;font-size:30px;"><input type="submit" value="Ok"/></p></form></h1>
<?php menu_end();?>
</body>
</html>