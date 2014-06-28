<?php
session_start();
?>
<html>
<head>
<?php
require_once('framework-master.php');
require_once('db.php');
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
require_once('menu-maker.php');
menu_header();


?>
<title>Members Only Page</title>
</head>
<body>
<?php menu_start();?>
<h1><br/><br/><?php 
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

			if($usuario=mysql_fetch_array($result)){
				$hash = $usuario['pwd'];
				if (crypt($pwd, '$2a$10$1qAz2wSx3eDc4rFv5tGb5t') === $hash){
					$_SESSION['id_usuario'] = $usuario['id'];
					$nombre = $usuario['nombre'];
					echo "<p align='center'>Sesión iniciada. Bienvenido, $nombre.<p>";
				} else {
					$message = "Contraseña incorrecta";
				}
			} else {
				$message = "No se pudo iniciar sesión, $query";
			}
			mysql_close();
		} 
	}
?></h1>
<?php menu_end();?>
</body>
</html>