 <?php
@session_start();
ob_start();
require_once('framework-master.php');
require_once('db.php');
require_once("menu-maker.php");
//define('DEBUG',1);
?><html>
<head>
<title>Productos vendidos</title>
<?php
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
$adicional_condicion="";
echo $core->header();
menu_header();
$by=(isset($_GET['desc']) ? 'asc' : "desc");
$iconoby=(isset($_GET['desc']) ? '<img style="width:3px;" src="down.png">' : ((isset($_GET['asc'])) ? '<img style="width:3px;" src="up.png">' : ''));
$cols=array('venta_id'=>"ID Venta",'codigobarras'=>"Código de barras",'nombre'=>'Nombre','t_piedra'=>'Tipo de piedra','tipo_compra'=>'Tipo de compra','descripcion'=>'Descripción del lote','GR'=>'GR','CT'=>'CT','precio_origen'=>'Precio en origen','x2'=>'x2','x3'=>'x3','x4'=>'x4','coste'=>'Coste');
$header_tabla="";
foreach($cols as $name_col => $etiqueta){
	$header_tabla.='<th><a href="?orderby='.$name_col.'&amp;'.$by.'" class="headerlink">'.$etiqueta.' '.((isset($_GET['orderby']) && $_GET['orderby']==$name_col) ? $iconoby : '').'</a></th>';
}

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
</header>
<body>
<?php
menu_start($tipo_usuario);
?>
<div id="dialog"><img src="" style="width:100%;" id="imagen-display"/><iframe src="" style="width:100%;height:100%;display:none;" id="iframe-display"></iframe></div><center>
<div style="width:100%;" class="m" id="m2"><table style="width:100%" id="tablemaster_productos" class="tablemaster">
<thead>
	<tr><?php echo $header_tabla; ?><th><a href="javascript:void(0);" class="headerlink">Total</a><th><a href="javascript:void(0);" class="headerlink">Precio Venta</a></th>
</tr></thead>
<tbody>
<?php
class total {
	function get($id,$core){
		$productos=$core->db_exect("select nombre,precio_origen,id from productos where id='".$id."' and estado!=1;");
		$sumar=0;
		$subtotal=0;
		foreach($productos as $product){
			$gastos=$core->db_exect("select nombre,aumento from gastosadicionales where id in (select gasto from productos_gasto_multiple where master_id='".$id."');");
			$product['precio_origen']=round($product['precio_origen'],2);
			$subtotal+=$product['precio_origen'];
			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$adicional=round($product['precio_origen']*($gasto['aumento'])/100,2);
				$sumar+=$adicional;
			}
		}
		return money_format('%(n', $subtotal+$sumar);
	}
}
$campos["t_piedra"]=array(
	"etiqueta"=>"Tipo de piedra",
	"tipo"=>"seleccionar",
	"editable"=>true,
	"tabla"=>"piedra_tipo",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["tipo_compra"]=array(
	"etiqueta"=>"Tipo de compra",
	"tipo"=>"seleccionar",
	"editable"=>true,
	"tabla"=>"cat_tipo_compra",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["coste"]=array(
        "etiqueta"=>"Coste",
        "tipo"=>"seleccionar",
        "columna_texto"=>"nombre",
        "columna_valor"=>"id",
        "editable"=>true,
        "filtro"=>FILTRO_INT,
        "default"=>array(
                array("nombre"=>"CT (Kilates)",
                      "id"=>1),
                array("nombre"=>"GR (Gramos)",
                      "id"=>0),
        ),
);
$total=new total();
$sql_load="select amortizacion.venta_id,productos.codigobarras,productos.nombre,productos.t_piedra,productos.tipo_compra,productos.descripcion,productos.GR,productos.CT,productos.precio_origen,productos.precio_origen*2 as x2,productos.precio_origen*3 as x3,productos.precio_origen*4 as x4,productos.coste,productos.id from amortizacion,cat_tipo_nueva_venta,amortizacion_cid_multiple,productos where cat_tipo_nueva_venta.id=amortizacion.venta_id and productos.id=amortizacion_cid_multiple.cid and amortizacion_cid_multiple.master_id=amortizacion.id";
if(isset($_GET['orderby']) && isset($cols[$_GET['orderby']])){
	$orderby=$_GET['orderby'];
	$by=(isset($_GET['desc']) ? 'desc' : 'asc');
	$sql_load.=" order by ".$orderby." ".$by;
} else {
	$sql_load.=" order by venta_id desc";
}
$sql_load.=";";
$datos=$core->db_exect($sql_load);
$row=0;
$as_money=array("x2","x3","x4","precio_origen");
$as_seleccionar=array("t_piedra","tipo_compra","coste");
$cache_detalles_venta=array();
class detalles_venta_prod {
	function get($id_venta,$core,$id_prod){
		global $cache_detalles_venta;
		if(!isset($cache_detalles_venta[$id_venta])){
			$sql="select cid,amortizacion_precio_venta_multiple.precio_venta as precio_venta from amortizacion_cid_multiple,amortizacion_precio_venta_multiple where amortizacion_cid_multiple.master_id in (select id from amortizacion where venta_id='".$id_venta."') and cid='".$id_prod."' and amortizacion_cid_multiple.master_id=amortizacion_precio_venta_multiple.master_id;";
			$datos=$core->db_exect($sql);
			foreach($datos as $dato){
				$cache_detalles_venta[$id_venta][$dato['cid']]=$dato['precio_venta'];
			}
		}
		return $cache_detalles_venta[$id_venta];
	}
}
class me_precio_venta {
	function get($id,$core,$venta){
		$det=new detalles_venta_prod();
		$det_2=$det->get($venta,$core,$id);
		return money_format('%(n', round($det_2[$id],2));
	}
}
$me_precio_venta=new me_precio_venta();
foreach($datos as $dato){
	print '<tr id="tableelement_'.$dato['venta_id'].'_'.$dato['id'].'" class="row0">';
	$id_prod=$dato['id'];
	unset($dato['id']);
	$row=($row==0 ? 1 : 0);
	foreach($dato as $datname=>$dat){
		if(in_array($datname,$as_money))
			$dat=money_format('%(n', $dat);
		if(in_array($datname,$as_seleccionar))
			$dat=$core->select_valor($campos[$datname],$dat);
		if($datname=='venta_id')
			$dat="<a href=\"javascript:display_productos(".$dat.");\">".$dat."</a>";
		print "<td id=\"".$datname."\">".$dat."</td>";
			
	}
	$precio_venta=$me_precio_venta->get($id_prod,$core,$dato['venta_id']);
	print "<td id=\"total\">".$total->get($id_prod,$core)."</td>";
	print "<td id=\"precio_venta\">".$precio_venta."</td>";
	print '</tr>';
}
?>
</tbody></table></div>
<?php
menu_end();
?>
</body>
</html>
