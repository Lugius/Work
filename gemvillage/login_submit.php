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