<?php
@session_start();
?><html>
<head>
<title>Nueva categoría</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<?php
include('framework-master.php');
require_once('db.php');
$zapato=new master($server,$database,$user,$password);
$zapato->set_upload_dir("./images");
$agujetas=array();
$agujetas["nombre"]=array(
	"etiqueta"=>"Nombre de la categoría",
	"tipo"=>"texto",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$agujetas['subir']=array(
	"tipo"=>"archivo",
	"filtro"=>FILTRO_STRING,
	"etiqueta"=>"Subir archivo",

);
$agujetas['dedo']=array(
	"tipo"=>"enviar",
	"etiqueta"=>"Enviar archivo",

);
$popo["popo_table"]=array(
//uii90'	"id"=>1,
	"titulo"=>":3",
	"campos"=>$agujetas,
	"negritas"=>true
);
$zapato->createsqltable($popo);
print $zapato->createform($popo);
$zapato->saveform($popo);
?>
</body>
</html>
