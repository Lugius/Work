<?php
@session_start();
?><html>
<head>
<title>Gastos adicionales</title>
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
	"etiqueta"=>"Nombre del gasto adicional",
	"busqueda"=>'like',
	"tipo"=>"texto",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["aumento"]=array(
	"etiqueta"=>"Porcentaje de aumento",
	"tipo"=>"texto",
        "busqueda"=>'=',
	"filtro"=>FILTRO_FLOAT,
	"default"=>VALOR_DB,
);

$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["gastosadicionales"]=array(
		"titulo"=>"Gastos adicionales",
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
