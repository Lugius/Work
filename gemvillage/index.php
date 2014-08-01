<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
if(isset($_SESSION['id_usuario'])){
	header( 'Location: panel_control.php' ) ;
}
?>

<?php
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
require_once('menu-maker.php');
$tipo_usuario = check_tipo_usuario($server, $database, $user, $password);
?>
<html>
<head>
<title>Index</title>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.0/build/reset/reset-min.css">
<link rel="stylesheet" href="css/style.css" type="text/css" media="all" charset="utf-8" />
<link rel="stylesheet" href="css/MenuMatic.css" type="text/css" media="screen" charset="utf-8" />
</head>
<body>
<style>
	.acciones2{
		text-align:center;
	}
	.acciones2 .cuadro2 li {
		float:left;
		list-style: none outside none;
		margin-right:29px;
		text-align:center;
	}
	.acciones2 .cuadro2 li a img {
		height:50px;
	}
</style>
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
<div id="wrapper-menu">
<br/><br/><br/><br/><br/><br/>
<div width="40%" style="float:left !important;margin-top:30px;margin-left:33%">
<?php 
	if(!isset($_SESSION['id_usuario'])){
?>
   <form id="login" action="login_submit.php" method="post">
	<fieldset>
		<h1>
			<label for="nombre_usuario">Nombre de usuario</label><br/>
			<input type="text" id="nombre_usuario" name="nombre_usuario" value="" maxlength="200" />
		</h1>
		<h1>
			<label for="pwd">Contraseña</label><br/>
			<input type="password" id="pwd" name="pwd" value="" maxlength="220" />
		</h1>
		<h1>
			<input type="hidden" name="form_token" value="<?php echo $form_token; ?>" />
			<input type="submit" value="&rarr; Login" />
		</h1>
		<br/>
	</fieldset>
</form>
<?php
}
?>
</div>
</div>
<div id="copy">
Copyright &copy; Punto de Venta Kinui 2013. Todos los derechos Reservados.  Powered by <a href="http://www.kinui.com" title="KINUI" target="_blank"> KINUI Simple Web &amp; Digital Solutions</a> | <a href="http://www.pixel07.com" title="Pixel07" target="_blank"> Pixel07 Design Solutions </a><br/>
<a href="puntodeventa.html" target="_blank" title="masInfo"> Más información</a>
</div>
</div>
</div>
</body>
</html>
