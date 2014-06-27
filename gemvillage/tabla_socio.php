<?php
@session_start();
?><html>
<head>
<title>Socios</title>
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
<?php
menu_start();
$forms=array();
$campos=array();
$campos["nombre"]=array(
	"etiqueta"=>"Nombre del socio",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);


$campos["comentario"]=array(
	"etiqueta"=>"Nota",
	"tipo"=>"area",
	"opcional"=>true,
	"filtro"=>FILTRO_STRING
);

$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);

$forms["socios"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
);
$core->createsqltable($forms);
print $core->createtable(array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=>"Nuevo Socio",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar Socio",
				"formulario"=>$forms),
			"eliminar"=>array(
				"boton"=>"eliminar.png",
				'confirmacion'=>"¿Realmente desea eliminar a este socio?",
				"etiqueta"=>"Eliminar Socio",
				"formulario"=>$forms)
		)
	);
menu_end();
?>
</body>
</html>
