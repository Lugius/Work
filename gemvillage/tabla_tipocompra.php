<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?><html>
<head>
<title> Tipo de compra</title>
<?php
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
?>
</head>
<body>
<?php
$forms=array();
$campos=array();
$campos["nombre"]=array(
	"etiqueta"=>"Nombre del tipo de compra",
	"busqueda"=>'like',
	"tipo"=>"texto",
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
$forms["tipo_compra"]=array(
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
?>
</body>
</html>
