<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?><html>
<head>
<title>Retiros</title>
<?php
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
menu_header();
?>


<script>
	$(function(){
		var $fecha = $('#fecha'),
			$tipo_retiro = $('#tipo_retiro'),
			$cantidad = $('#cantidad'),
			$submit = $('#wrapper-iframe form input[name="enviar"]');

		$submit.click(function(e){
			if ($fecha.val() == "" || 
				$tipo_retiro.val() == "" || 
				$cantidad.val() == "" ){
				e.preventDefault();
				alert('Porfavor, introduzca todos los campos');
				return false;
			}

		});
	})
</script>


</head>
<body>
<?php
menu_start($tipo_usuario);
$forms=array();
$campos=array();

$campos["tipo_retiro"]=array(
	"etiqueta"=>"Tipo de retiro",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"busqueda"=>'like',
	"editable"=>true,
	"tabla"=>"retiros_tipo",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['retiros_tipo']),
);

$campos["fecha"]=array(
	"etiqueta"=>"Fecha del retiro",
	"tipo"=>"fecha",
	"busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["notas"]=array(
	"etiqueta"=>"Nota",
	"tipo"=>"area",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["cantidad"]=array(
	"etiqueta"=>"Cantidad",
	"tipo"=>"texto",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);

$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);

$forms["retiros"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
);

$core->createsqltable($forms);
print $core->createtable(array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=>"Nuevo retiro",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar retiro",
				"formulario"=>$forms),
			"eliminar"=>array(
				"boton"=>"eliminar.png",
				'confirmacion'=>"¿Realmente desea eliminar este retiro?",
				"etiqueta"=>"Eliminar retiro",
				"formulario"=>$forms)
		)
	);
	
menu_end();
?>
</body>
</html>
