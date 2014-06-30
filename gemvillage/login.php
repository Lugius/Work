<?php
@session_start();
?><html>
<head>
<title>Login</title>
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
</head>
<body>
<?php menu_start($tipo_usuario); ?>
<br/><br/>
<h1>Iniciar sesión</h1>
<?php 
	if(isset($_SESSION['id_usuario'])){
		echo "<h2>La sesión ya esta iniciada</h2>";
		echo "<form action=\"panel_control.php\"><p><input type=\"submit\" value=\"Ok\"/></p></form></h1>";
	} else {
?>
<form action="login_submit.php" method="post">
	<fieldset>
		<p>
			<label for="nombre_usuario">Nombre de usuario</label><br/>
			<input type="text" id="nombre_usuario" name="nombre_usuario" value="" maxlength="200" />
		</p>
		<p>
			<label for="pwd">Contraseña</label><br/>
			<input type="password" id="pwd" name="pwd" value="" maxlength="220" />
		</p>
		<p>
			<input type="hidden" name="form_token" value="<?php echo $form_token; ?>" />
			<input type="submit" value="&rarr; Login" />
		</p>
	</fieldset>
</form>
<?php
}
?>


<?php menu_end();?>

</body>
</html>
