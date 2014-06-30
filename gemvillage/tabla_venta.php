<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?><html>
<head>
<title>Nueva venta</title>
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
	"etiqueta"=>"Nombre del nuevo producto",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["datos"]=array(
	"atributos"=>array(
		"rows"=>4,
		"cols"=>50,
	),
	"etiqueta"=>"Datos del producto",
	"tipo"=>"area",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["cliente"]=array(
	"atributos"=>array(
		"rows"=>4,
		"cols"=>50,
	),
	"etiqueta"=>"Datos del cliente",
	"tipo"=>"area",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["venta"]=array(
	
	"etiqueta"=>"Tipo de venta",
	"tipo"=>"text",
	"busqueda"=>'=',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["precio_unidad"]=array(
	
	"etiqueta"=>"Precio de unidad",
	"tipo"=>"text",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["precio_venta"]=array(
	
	"etiqueta"=>"Precio de venta recomendado",
	"tipo"=>"text",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["precio_total"]=array(
	
	"etiqueta"=>"Precio total a la venta",
	"tipo"=>"text",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["estado"]=array(
	
	"etiqueta"=>"Estado de la venta",
	"tipo"=>"text",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["cat_tipo_nueva_venta"]=array(
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
