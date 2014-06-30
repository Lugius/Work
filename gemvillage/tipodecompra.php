<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?><html>
<head>
<title>Nueva categoría</title>
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
$campos["tipo"]=array(
	"etiqueta"=>"Tipo de compra",
	"busqueda"=>'=',
	"icono"=>"icono",
	"tipo"=>"seleccionar",
	"tabla"=>"tipo_compra",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["cat_tipo_compra"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
);
$core->createsqltable($forms);
if($core->saveform($forms)){
	print "<span class='ok-txt'><b>Guardado correctamente!</b></span>";
} else {
	print $core->createform($forms);
}
?>
</body>
</html>
