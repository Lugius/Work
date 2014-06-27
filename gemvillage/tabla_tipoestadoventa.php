<?php
@session_start();
?><html>
<head>
<title>Nuevo tipo de estado de venta</title>
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
	"etiqueta"=>"Nombre del estado de venta",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["descripcion"]=array(
	"atributos"=>array(
		"rows"=>4,
		"cols"=>50,
	),
	"etiqueta"=>"Descripción",
	"tipo"=>"area",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
 $campos["icono"]=array(
       "etiqueta"=>"Icono",
       "tipo"=>"archivo",
	"explorador"=>'multimedia.php',
       "filtro"=>FILTRO_IMAGEN,
       "default"=>VALOR_DB,
);
$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["cat_tipo_estado_venta"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
);
$core->createsqltable($forms);
print $core->createtable(array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=> "Nuevo ",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar",
				"formulario"=>$forms),
				"eliminar"=>array(
				"boton"=>"eliminar.png",
				"etiqueta"=>"Eliminar",
				"confirmacion"=>"¿Está seguro que desea eliminar estos elementos?",
				"formulario"=>$forms)
		)
	);
	menu_end();
?>
</body>
</html>
