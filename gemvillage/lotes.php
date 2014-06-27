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
?>
</head>
<body>
<?php
$forms=array();
$campos=array();
$campos["nombre"]=array(
	"etiqueta"=>"Nombre del lote",
        "busqueda"=>"like",
	"tipo"=>"texto",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["tipo_piedra"]=array(
	"etiqueta"=>"Tipo de piedra",
	"tipo"=>"seleccionar",
        "busqueda"=>"=",
	"tabla"=>"piedra_tipo",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["tallado"]=array(
        "etiqueta"=>"Talle de piedra",
        "busqueda"=>"=",
        "tipo"=>"seleccionar",
        "columna_texto"=>"nombre",
        "columna_valor"=>"id",
        "filtro"=>FILTRO_INT,
        "default"=>array(
                array("nombre"=>"Tallado",
                      "id"=>1),
                array("nombre"=>"Bruto",
                      "id"=>0),
        ),
);
$campos["categoria"]=array(
	"etiqueta"=>"Categoría",
	"tipo"=>"seleccionar",
	"tabla"=>"categorias",
	"icono"=>"imagen",
        "busqueda"=>"=",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["imagen"]=array(
	"atributos"=>array(
		"name"=>"archivo",
	),
	"etiqueta"=>"Imagen",
	"tipo"=>"archivo",
	"filtro"=>FILTRO_IMAGEN,
	"explorador"=>'multimedia.php',
	"max"=>200,
);
$campos["barcode"]=array(
	"etiqueta"=>"Código de barras",
	"tipo"=>"texto",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["numero_lote"]=array(
	"etiqueta"=>"Número de lote",
	"tipo"=>"texto",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["lotes"]=array(
		"titulo"=>"Nueva categoría",
		"negritas"=>TRUE,
		"campos"=>$campos,
);
$core->createsqltable($forms);
if($core->saveform($forms)){
	print "<p class='ok-txt'><b>Guardado correctamente!</b></p>";
} else {
	print $core->createform($forms);
}
?>
</body>
</html>
