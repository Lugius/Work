<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?><html>
<head>
<title>Status de cliente</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
menu_header();
?>
</head>
<body>
<?php
menu_start($tipo_usuario);
$forms=array();
$campos=array();
$campos["status_cliente"]=array(
	"etiqueta"=>"Status del cliente",
	"busqueda"=>'like',
	"tipo"=>"texto",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["comentario_cliente"]=array(
	"etiqueta"=>"Comentario del cliente",
	"tipo"=>"area",
	"filtro"=>FILTRO_STRING
);
$campos["icono_cliente"]=array(
	"etiqueta"=>"Icono del cliente",
	"tipo"=>"archivo",
	"explorador"=>'multimedia.php',
	"filtro"=>FILTRO_IMAGEN
);
$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);

$forms["statusde_cliente"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
);
$core->createsqltable($forms);
print $core->createtable(array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=>"Nuevo",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar",
				"formulario"=>$forms),
			"eliminar"=>array(
				"boton"=>"eliminar.png",
				"etiqueta"=>"Eliminar",
				"formulario"=>$forms)
		)
	);
menu_end();
?>
</body>
</html>
