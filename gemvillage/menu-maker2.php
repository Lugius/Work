<?php
$tipo_usuario = check_tipo_usuario($server, $database, $user, $password);
if ($tipo_usuario != 1 && $tipo_usuario != 2){
	header( 'Location: index.php' );
}
function check_tipo_usuario($server,$database,$user,$password){
	if (isset($_SESSION['id_usuario'])){
		$id_usuario = $_SESSION['id_usuario'];
		mysql_connect($server,$user,$password);
		@mysql_select_db($database) or die("Error eligiendo base de datos");
		$query="SELECT tipo_usuario FROM usuarios WHERE id = '$id_usuario' LIMIT 1";
		$result=mysql_query($query);
		if ($usuario=mysql_fetch_array($result)){
			$tipo_usuario = $usuario['tipo_usuario'];
		} else {
			session_destroy();
			header( 'Location: login.php');
		}
		return $tipo_usuario;
	}
	return false;
}

	function menu_header(){
?>
		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.0/build/reset/reset-min.css">
		<link rel="stylesheet" href="css/style.css" type="text/css" media="all" charset="utf-8" />
		<link rel="stylesheet" href="css/MenuMatic.css" type="text/css" media="screen" charset="utf-8" />
		<script>
        $(function() {
        	show_menu=function() {
	        	$(this).children('ul').css('margin-left','186px').css('margin-top','-2.5em');
      			$(this).parent().css('margin-left','0px');
      			$(this).children('li').css('margin-left','0px');
      			$(this).parent().parent().css('margin-left','0px');
      			$(this).css('margin-left','0px');
	        	};
	        $('#nav').children('li').mouseover(show_menu).click(show_menu).mouseout(function(){
	        			$(this).children('ul').css('margin-left','-1000em').css('margin-top','0em');
	        			});
	        $(document).ready(function(){
	        	 settitle=document.title;
	        	 header_image=window.location.href;
               		 header_image=header_image.replace(/\?.*$/,"");
               		 header_image=header_image.replace(/\.php$/,"");
               		 header_image=header_image.replace(/.+\//,"");
               		 if(header_image=='')
				header_image='index';
               		 settitle+=' <img src="'+header_image+'.png" />';
                         $('#titulo').html(settitle);
	        });
       });
               </script>
<?php
}
function menu_start($tipo_usuario){
?>

<div class="wrapper" id="wrapper">
<div class="wrapper-header" id="wrapper-header">
	<div class="m" style="margin-bottom:2%;">
		<a href="index.php"><img class="logo" src="logo.png" style="float:left;height:73px;margin-left:20px;"/></a>
		<h1><div id="titulo" style="text-align:right;margin-right:2%;"></div></h1>
	</div>
</div>
<div id="wrapper-menu">
<div width="14%" style="float:left !important;">
   <div id="container" >
   
	    
	
	    <ul id="nav">

			<li><a class="radiustop" href="ganancias.php">PANEL DE CONTROL</a></li>
			<li><a href="informes.php">INFORMES</a></li>
			<li><a href="javascript:void(0);">GASTOS</a>
				<ul>
					<li><a class="radiustop" href="gastos.php">Administrar gastos</a></li>
					<li><a class="radiusbottom" href="gastos_tipo.php">Tipos de gastos</a></li>
				</ul>
			</li>
			<li><a href="javascript:void(0);">INGRESOS</a>
				<ul>
					<li><a class="radiustop" href="ingresos.php">Administrar ingresos</a></li>
					<li><a class="radiusbottom" href="ingresos_tipo.php">Tipos de ingresos</a></li>
				</ul>
			</li>
			<li><a href="javascript:void(0);">RETIROS</a>
				<ul>
					<li><a class="radiustop" href="retiros.php">Administrar retiros</a></li>
					<li><a class="radiusbottom" href="retiros_tipo.php">Tipos de retiros</a></li>
				</ul>
			</li>
			<?php
			if($tipo_usuario == 1) {
				?>
				<li><a href="javascript:void(0);">USUARIOS</a>
				<ul>
					<li><a class="radiustop radiusbottom" href="usuarios.php">Administrar usuarios</a></li>			
				</ul>
				<?php 
			} 
			?>
			<li><a href="panel_control.php">PUNTO DE VENTA</a></li>
			<li><a class="radiusbottom" href="logout.php">CERRAR SESSIÓN</a></li>
    </div>
</div>
</div>
<div class="wrapper-iframe" id="wrapper-iframe">
<?php
}
function menu_end(){
?>
<div id="copy">
Copyright &copy; Punto de Venta Kinui 2013. Todos los derechos Reservados.  Powered by <a href="http://www.kinui.com" title="KINUI" target="_blank"> KINUI Simple Web &amp; Digital Solutions</a> | <a href="http://www.pixel07.com" title="Pixel07" target="_blank"> Pixel07 Design Solutions </a><br/>
<a href="puntodeventa.html" target="_blank" title="masInfo"> Más información</a>
</div>
</div>
</div>
<?php
}

?>