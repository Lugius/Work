<?php
@session_start();
?><html>
<head>
<title>Nueva categoría</title>
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

$forms=array();
$campos=array();
$campos["nombre"]=array(
	"etiqueta"=>"Nombre de la categoría",
	"busqueda"=>"like",
	"tipo"=>"texto",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["descripcion"]=array(
	"atributos"=>array(
		"rows"=>4,
		"cols"=>50,
	),
	"etiqueta"=>"Descripción de la categoría",
	"tipo"=>"area",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["tipo"]=array(
	"etiqueta"=>"Tipo de categoría",
	"busqueda"=>"=",
	"tipo"=>"seleccionar",
	"tabla"=>"cat_tipo",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["imagen"]=array(
	"etiqueta"=>"Imagen de la categoría",
	"tipo"=>"archivo",
	"filtro"=>FILTRO_IMAGEN,
	"explorador"=>'multimedia.php',
	"max"=>200,
);
$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["categorias"]=array(
		"titulo"=>"Nueva categoría",
		"negritas"=>TRUE,
		"campos"=>$campos,
);
$core->createsqltable($forms);
menu_start();
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
