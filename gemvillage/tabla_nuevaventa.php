<?php
@session_start();
ob_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?>
<html>
<head>
<title>Ventas</title>
<?php
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
$core->db_exect("UPDATE productos set estado=0 where estado IS NULL;");
$core->db_exect("UPDATE productos set estado=2 where id in (SELECT cid FROM amortizacion_cid_multiple where master_id in(select id from amortizacion where venta_id in(select id from cat_tipo_nueva_venta where estado=1)));");
$core->db_exect("UPDATE productos set estado=0 where id not in (SELECT cid FROM amortizacion_cid_multiple where master_id in(select id from amortizacion where venta_id in(select id from cat_tipo_nueva_venta where estado=1))) and estado!=1;");
?>
<script>
function display_productos(id) {
	$("#imagen-display").hide();
	$("#iframe-display").attr("src","tabla_productos.php?venta_id="+id);
	$("#iframe-display").show();
	$("#dialog").dialog("option", "width", 1200);
	$("#dialog").dialog("open");
}
</script>
<?php
menu_header();
?>
</head>
<body>
<?php
menu_start($tipo_usuario);
$forms=array();
$campos=array();
$campos["fecha"]=array(
        "tipo"=>"db",
        "filtro"=>FILTRO_STRING,
	"busqueda"=>'=',
        "default"=>date("Y-m-d H:i:s"),
        "max"=>21
);
$campos["cliente"]=array(
	"busqueda"=>'=',
	"etiqueta"=>"Cliente",
	"tipo"=>"seleccionar",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"editable"=>true,
	"tabla"=>"clientes",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["venta"]=array(
	"busqueda"=>'=',
	"etiqueta"=>"Tipo de venta",
	"tipo"=>"seleccionar",
	"editable"=>true,
	"tabla"=>"cat_tipo_venta",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["estado"]=array(
	"etiqueta"=>"Estado de la venta",
	"tipo"=>"seleccionar",
	"busqueda"=>'=',
	"editable"=>true,
	"icono"=>"icono",
	"tabla"=>"cat_tipo_estado_venta",
	"columna_texto"=>"nombre",
	"columna_valor"=>'id',
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
class monto {
	function monto_venta($id,$core){
		$productos=$core->db_exect("SELECT sum(precio_venta) as monto FROM amortizacion_precio_venta_multiple where  master_id in (SELECT id FROM amortizacion where venta_id='".$id."');");
		$sum=array_pop($productos);
		return money_format('%(n', $sum['monto']);
	}
}
$campos["monto_venta"]=array(
	"etiqueta"=>"Monto de la venta",
	"tipo"=>"callback",
	"callback"=>array(new monto(),"monto_venta"),
);
class detalles_venta{
	function show($id,$core){
		return "<a href=\"javascript:display_productos(".$id.");\">Detalles</a>";
	}

}
$campos["show_productos"]=array(
	"etiqueta"=>"Detalles",
	"tipo"=>"callback",
	"callback"=>array(new detalles_venta(),"show"),
);
$campos["enviar"]=array(
	"etiqueta"=>"Continuar con la venta",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["cat_tipo_nueva_venta"]=array(
		"unlockable"=>true,
		"negritas"=>TRUE,
		"campos"=>$campos,
);
$core->createsqltable($forms);
$datos_pendientes=array();
if(isset($_REQUEST['master_task']) && $_REQUEST['master_task']=='nuevo'){
	unset($_SESSION['compra_pendiente']);
}
$editando=((isset($_POST['master_task']) && $_POST['master_task']=='editar') || (isset($_POST['editando']) && $_POST['editando']==1));
if($editando){
	$forms["cat_tipo_nueva_venta"]['id']=((isset($_SESSION['compra_pendiente']) && $_SESSION['compra_pendiente']!='') ? $_SESSION['compra_pendiente'] : array_shift($_REQUEST['cid']));
	$_SESSION['compra_pendiente']=$forms["cat_tipo_nueva_venta"]['id'];
}
if($core->saveform($forms,$datos_pendientes)){
	$_SESSION['compra_pendiente']=$datos_pendientes['id'];
}
if(isset($_POST['master_task']) && $_POST['master_task']=='cancelar'){
	unset($_SESSION['compra_pendiente']);
	$_SESSION['master_almacen']['id_table_productos']=array();
}
class detalles_venta_prod_2 {
	function get($id_venta,$core){
		global $cache_detalles_venta;
		if(!isset($cache_detalles_venta[$id_venta])){
			$datos=$core->db_exect("select cid,amortizacion_precio_venta_multiple.precio_venta as precio_venta from amortizacion_cid_multiple,amortizacion_precio_venta_multiple where amortizacion_cid_multiple.master_id in (select id from amortizacion where venta_id='".$id_venta."') and amortizacion_cid_multiple.master_id=amortizacion_precio_venta_multiple.master_id;");
			foreach($datos as $dato){
				$cache_detalles_venta[$id_venta][$dato['cid']]=$dato['precio_venta'];
			}
		}
		return $cache_detalles_venta[$id_venta];
	}
}
if(isset($_SESSION['compra_pendiente'])){
	ob_end_clean();
	if($editando && !isset($_POST['editando'])){
		$control_precio_venta_core=new detalles_venta_prod_2();
		$control_precio_venta=$control_precio_venta_core->get($_SESSION['compra_pendiente'],$core);
		foreach($control_precio_venta as $cid_rm=>$control_precioventa){
			$_SESSION['master_almacen']['id_table_productos'][]=$cid_rm;
			$_SESSION['master_almacen']['precio_'.$cid_rm.'_table_productos']=$control_precioventa;
		}
	}

	define("COMPRA",1);
	ob_start();
	require_once('tabla_productos.php');
	$salida1_productos = ob_get_contents();
	ob_end_clean();
	if($editando)
		$salida1_productos=preg_replace("/<\/form>/","<input type=\"hidden\" name=\"editando\" value=\"1\" /></form>",$salida1_productos);
	print $salida1_productos;
} else {
	unset($forms["cat_tipo_nueva_venta"]["campos"]["fecha"]);
	$tabla=array(
		"tabla"=>$forms,
		"nuevo"=>array(
			"boton"=>"nuevo.png",
			"etiqueta"=>"Nueva venta",
			"formulario"=>$forms),
		"editar"=>array(
			"boton"=>"editar.png",
			"etiqueta"=>"Editar venta",
			"formulario"=>$forms),
		"eliminar"=>array(
			'confirmacion'=>"¿Realmente desea eliminar esta venta?",
			"boton"=>"eliminar.png",
			"etiqueta"=>"Eliminar venta",
			"formulario"=>$forms),
		);
print $core->createtable($tabla);
menu_end();
?>
</body>
</html>
<?php
}
$salida = ob_get_contents();
ob_end_clean();
echo $salida;
?>
