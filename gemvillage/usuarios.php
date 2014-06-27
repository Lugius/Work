<?php
@session_start();
?><html>
<head>
<title>Usuarios</title>
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
	"etiqueta"=>"Nombre",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["nombre_usuario"]=array(
	"etiqueta"=>"Nombre de usuario",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["email"]=array(
	"etiqueta"=>"email",
	"tipo"=>"texto",
	"busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["pwd"]=array(
	"etiqueta"=>"Contraseña",
	"tipo"=>"password",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["pwd_re"]=array(
	"etiqueta"=>"Reescriba contraseña",
	"tipo"=>"password",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["tipo_usuario"]=array(
	"etiqueta"=>"Tipo de usuario",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"busqueda"=>'like',
	"editable"=>true,
	"tabla"=>"usuarios_tipo",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['usuarios_tipo']),
);

$campos["fecha"]=array(
	"etiqueta"=>"Fecha de creacion",
	"tipo"=>"fecha",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["notas"]=array(
	"etiqueta"=>"Nota",
	"tipo"=>"texto",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);

$forms["usuarios"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
);

$core->createsqltable($forms);
print $core->createtable(array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=>"Nuevo Usuario",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar Usuario",
				"formulario"=>$forms),
			"eliminar"=>array(
				"boton"=>"eliminar.png",
				'confirmacion'=>"¿Realmente desea eliminar a este usuario?",
				"etiqueta"=>"Eliminar Usuario",
				"formulario"=>$forms)
		)
	);
menu_end();
?>
<script>
	var $pass = $('#pwd'),
		$confirm = $('#pwd_re'),
		$form = $('#wrapper-iframe form');

	$form.submit(function(e){
		if ($pass.val() != $confirm.val()) {
			e.preventDefault();
			alert('Las contraseñas no coinciden.');
			return false;
		} 
	});
</script>
</body>
</html>
