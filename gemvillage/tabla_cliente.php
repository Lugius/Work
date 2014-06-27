<?php
@session_start();
?><html>
<head>
<title>Clientes</title>
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
	"etiqueta"=>"Nickname",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["usuario"]=array(
	"etiqueta"=>"Usuario",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"opcional"=>true,
	"filtro"=>FILTRO_STRING
);
$campos["email"]=array(
	"etiqueta"=>"E-Mail",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"opcional"=>true,
	"filtro"=>FILTRO_EMAIL
);
$campos["telefono"]=array(
	"etiqueta"=>"Telefono del cliente",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"opcional"=>true,
	"filtro"=>FILTRO_STRING
);
$campos["tipo_cliente"]=array(
	"etiqueta"=>"Tipo de cliente",
	"tipo"=>"seleccionar",
	"icono"=>"icono_cliente",
	"tabla"=>"tipo_cliente",
	"columna_valor"=>'id',
	"busqueda"=>'=',
	"opcional"=>true,
	"columna_texto"=>'nombre',
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["status"]=array(
	"etiqueta"=>"Status del cliente",
	"tipo"=>"seleccionar",
	"tabla"=>"statusde_cliente",
	"icono"=>"icono_cliente",
	"columna_texto"=>"status_cliente",
	"busqueda"=>'=',
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"opcional"=>true,
	"default"=>VALOR_DB,
);


$campos["fecha_comentario"]=array(
       "etiqueta"=>"Fecha comentario",
       "tipo"=>"fecha",
       "opcional"=>true,
       "filtro"=>FILTRO_FECHA
);

$campos["comentario"]=array(
	"etiqueta"=>"Comentarios del cliente",
	"tipo"=>"area",
	"opcional"=>true,
	"filtro"=>FILTRO_STRING
);

$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);

$forms["clientes"]=array(
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
