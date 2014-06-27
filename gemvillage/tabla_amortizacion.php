<?php
@session_start();
?><html>
<head>
<title>Amortización</title>
<?php
require_once('framework-master.php');
require_once('db.php');
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
if(isset($_POST['reset'])){
	$core->db_exect("UPDATE amortizacion set enabled=0,fecha='".date("m/d/y g:i:s a")."' where enabled=1;");
}
$fechas_activas=$core->db_exect("SELECT fecha FROM amortizacion where enabled=0 group by fecha;");
$matriz_fechas="";
foreach($fechas_activas as $fecha_activa){
	$matriz_fechas.="'".substr($fecha_activa['fecha'],0,8)."',";
}
?>
</head>
<body>
<script>
function imprimir(mi){
	document.getElementById('toolbar').style.display='none';
	if(typeof(document.getElementById('reset')) !='undefined' && document.getElementById('reset')!=null)
		document.getElementById('reset').style.visibility='hidden';
	document.getElementById('buscador_amortizacion').style.display='none';
	mi.style.visibility='hidden';
	window.print();
	mi.style.visibility='visible';
	if(typeof(document.getElementById('reset')) !='undefined' && document.getElementById('reset')!=null)
		document.getElementById('reset').style.visibility='visible';
	document.getElementById('toolbar').style.display='block';
	document.getElementById('buscador_amortizacion').style.display='block';
}
$(function() {
$("#filtro_amortizaciones").change(function() {
	document.location='?fecha_amortizacion='+$(this).val();
});
}
);

natDays = [
  <?php echo $matriz_fechas; ?>
];
filter_dates=function(date){
var dat = $.datepicker.formatDate("mm/dd/y", date);
for (var i=0, c=natDays.length; i<c; i++)
	if(dat==natDays[i])
		return[1,""];
return [0,""];
};

</script>
<style>
	.acciones2{
		text-align:center;
	}
	.acciones2 .cuadro2 li {
		float:left;
		list-style: none outside none;
		margin-right:29px;
		text-align:center;
	}
	.acciones2 .cuadro2 li a img {
		height:50px;
	}
</style>
<div id="toolbar" class="toolbar" style="width:70%;margin-left:14%;">
<div class="m m1">
<div id="acciones2" class="acciones2">
<ul id="cuadro2" class="cuadro2">
<li><a href="tabla_productos.php?master_task=nuevo"><img src="producto.png"/><br />Nuevo producto</a></li>
<li><a href="categorias.php?master_task=nuevo"><img src="category.png"/><br />Nueva categoría</a></li>
<li><a href="tabla_cliente.php?master_task=nuevo"><img src="cliente.png"/><br />Nuevo cliente</a></li>
<li><a href="tabla_proveedor.php?master_task=nuevo"><img src="proveedor.png"/><br />Nuevo proveedor</a></li>
<li><a href="tabla_nuevaventa.php?master_task=nuevo"><img src="car.png" /><br />Nueva venta</a></li>

</ul>
</div>
</div>
</div>
<?php
	$subtotal=array();
	$gastos=array();
	setlocale(LC_MONETARY, 'en_US');
	$buscador=$core->createfield("filtro_amortizaciones",array("tipo"=>"fecha","filtro"=>FILTRO_FECHA,"etiqueta"=>"Ver amortizaciones pasadas"),null,1);
	$condicion="enabled='1'";
	$display_adicional="";
	if(isset($_GET['fecha_amortizacion']) && $core->validatedate($_GET['fecha_amortizacion'])){
		$partesfecha=explode("/",$_GET['fecha_amortizacion']);
		$condicion="enabled=0 and fecha like '".($partesfecha[1]."/".$partesfecha[0]."/".substr($partesfecha[2],2,2))."%'";
		$display_adicional=" (".($partesfecha[1]."/".$partesfecha[0]."/".substr($partesfecha[2],2,2)).")";
	}
	$sql_load="SELECT venta_id, id FROM amortizacion where ".$condicion;
	$ventas=$core->db_exect($sql_load);
	echo '<div class="title-amortizacion"><h3>Tabla de amortización'.$display_adicional.'</h3><br /><div class="buscador_amortizacion" id="buscador_amortizacion">'.$buscador['label'].': '.$buscador['campo'].'</div><br /><br /><div style="width:70%;margin-left:10%;margin-right:20%;" class="m" id="m2"><table class="tablemaster" style="width:100%;"><thead><th>ID Venta</th><th>Subtotal</th><th>Total</th></thead>
			<tbody>';
			$amortizacionsubtotal=0;
			$amortizacionsumar=0;
	foreach($ventas as $venta){
		$subtotal=0;
		$sumar=0;
		$productos=$core->db_exect("SELECT id,precio_origen FROM productos where id in(SELECT cid FROM amortizacion_cid_multiple where master_id='".$venta['id']."')");
		foreach($productos as $product){
			$gastos=$core->db_exect("select nombre,aumento from gastosadicionales where id in (select gasto from productos_gasto_multiple where master_id='".$product['id']."');");
			$product['precio_origen']=round($product['precio_origen'],2);
			$subtotal+=$product['precio_origen'];
			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$adicional=round($product['precio_origen']*($gasto['aumento'])/100,2);
				$sumar+=$adicional;
			}
		}
		$amortizacionsubtotal+=$subtotal;
		$amortizacionsumar+=$sumar;
		print "<tr><td>".$venta['venta_id']."</td><td>".money_format('%(n', $subtotal)."</td><td>".money_format('%(n', $subtotal+$sumar)."</td></tr>";
	}
	?>
					</tbody>
			</table></div>
			<?php
				print "<div style=\"margin-left:10%;margin-right:20%;\"><br /><br /><b>Subtotal:</b>".money_format('%(n', $amortizacionsubtotal)."<br />";
	print "<b>Total:</b>".money_format('%(n', ($amortizacionsubtotal+$amortizacionsumar))."<br /><br />";
	?>
<input type="button" class="btn float-l" value="Imprimir" onClick="imprimir(this);" /><form method="POST" action="?" <?php if(!isset($_GET['fecha_amortizacion']) || !$core->validatedate($_GET['fecha_amortizacion'])){?>onsubmit="return confirm('Realmente desea reiniciar la tabla?');"<?php } ?>><?php if(!isset($_GET['fecha_amortizacion']) || !$core->validatedate($_GET['fecha_amortizacion'])){?><input type="submit" class="btn float-r" name="reset" id="reset" value="Reiniciar tabla"/><?php } else {?><input type="submit" class="btn float-r" name="regresar" value="Regresar"/><?php } ?></form></div></div>
</body>
</html>
