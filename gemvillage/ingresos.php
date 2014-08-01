<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker2.php');
?><html>
<head>
<title>Ingresos</title>
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
			$tipo_ingreso = $('#tipo_ingreso'),
			$cantidad = $('#cantidad'),
			$submit = $('#wrapper-iframe form input[name="enviar"]');

		$submit.click(function(e){
			if ($fecha.val() == "" || 
				$tipo_ingreso.val() == "" || 
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

$campos["tipo_ingreso"]=array(
	"etiqueta"=>"Tipo de ingreso",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"busqueda"=>'like',
	"editable"=>true,
	"tabla"=>"ingresos_tipo",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['ingresos_tipo']),
);

$campos["fecha"]=array(
	"etiqueta"=>"Fecha del ingreso",
	"tipo"=>"fecha",
	"busqueda"=>'like',
	"editable"=>true,
	"filtro"=>FILTRO_STRING,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : date("y-m-d H:i:s"))
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

$forms["ingresos"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
);

$core->createsqltable($forms);
print $core->createtable(array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=>"Nuevo ingreso",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar ingreso",
				"formulario"=>$forms),
			"eliminar"=>array(
				"boton"=>"eliminar.png",
				'confirmacion'=>"¿Realmente desea eliminar este ingreso?",
				"etiqueta"=>"Eliminar ingreso",
				"formulario"=>$forms)
		)
	);
	
menu_end();
?>
</body>
</html>
