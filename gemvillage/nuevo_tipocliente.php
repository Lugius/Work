<?php
@session_start();
?><html>
<head>
<title>Nuevo tipo de cliente</title>

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
	"etiqueta"=>"Nombre del tipo de cliente",
	"tipo"=>"texto",
	"busqueda"=>'like',
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
	"filtro"=>FILTRO_IMAGEN,
	"explorador"=>'multimedia.php',
);
$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);

$forms["tipo_cliente"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
);
$core->createsqltable($forms);
print $core->createtable(array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=>"Nuevo cliente",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar cliente",
				"formulario"=>$forms),
			"eliminar"=>array(
				"boton"=>"eliminar.png",
				"etiqueta"=>"Eliminar cliente",
				"formulario"=>$forms)
		)
	);
	menu_end();
?>
</body>
</html>
