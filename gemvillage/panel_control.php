<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?><html>
<head>
<title>Amortización</title>
<?php
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
menu_header();
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
<?php
menu_start($tipo_usuario);
?>
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
	echo '<a name="tabla"></a><div class="title-amortizacion"><h3>Tabla de amortización'.$display_adicional.'</h3><br /><div class="buscador_amortizacion" id="buscador_amortizacion">'.$buscador['label'].': '.$buscador['campo'].'</div><br /><br /><div style="width:70%;margin-left:10%;margin-right:20%;" class="m" id="m2"><table class="tablemaster" style="width:100%;"><thead><th>Nombre del producto</th><th>Código de Barras</th><th>Precio total</th><th>Precio de Venta</th><th>Socio</th><th>%</th><th>B. Socio</th><th>Amortización</th></thead>
			<tbody>';
//	$subtotal=0;
	$subtotal_1=0;
	$suma_amortizacion=0;
	$suma_amortizacion_socios=0;
	$suma_total=0;
	$suma_total_venta=0;
	foreach($ventas as $venta){
		$productos=$core->db_exect("SELECT amortizacion_cid_multiple.id,nombre,codigobarras,precio_origen,lote,IFNULL((select nombre from socios where id=productos.socio),'') as socio_nombre,IFNULL(beneficios,0) as beneficios FROM productos,amortizacion_cid_multiple where productos.id=cid and master_id='".$venta['id']."' order by amortizacion_cid_multiple.id;");
		$precios_venta=$core->db_exect("SELECT precio_venta FROM amortizacion_precio_venta_multiple where master_id='".$venta['id']."';");
		foreach($productos as $i_p=>$product){
			$gastos=$core->db_exect("select nombre,aumento from gastosadicionales where id in (select gasto from productos_gasto_multiple where master_id='".$product['id']."');");
			$product['precio_origen']=round($product['precio_origen'],2);
			$sumar=0;
			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$sumar+=round((floatval($product['precio_origen'])/100)*floatval($gasto['aumento']),2);
			}
//			$vals_socios=array_pop($core->db_exect("SELECT (select nombre from socios where id=productos.socio) as socio_nombre,beneficios FROM lotes where id='".$product['lote']."';"));
			$amortizacion_socio=round((floatval($precios_venta[$i_p]['precio_venta'])-(floatval($product['precio_origen'])+floatval($sumar))) *($product['beneficios']/100),5);
			$prec_total=floatval(floatval($product['precio_origen'])+$sumar);
			$amortizacion=round(floatval($prec_total*2),5);
			$suma_amortizacion+=floatval($amortizacion);
			$suma_amortizacion_socios+=floatval($amortizacion_socio);
			$suma_total+=$prec_total;
			$suma_total_venta+=floatval($precios_venta[$i_p]['precio_venta']);
			print "<tr><td>".
			$product['nombre']."</td><td>".
			$product['codigobarras']."</td><td>".
			$prec_total."</td><td>".
			$precios_venta[$i_p]['precio_venta']."</td><td>".$product['socio_nombre']."</td><td>".$product['beneficios']."</td><td>".$amortizacion_socio."</td><td>".$amortizacion
			.
			"</td></tr>";
			
//			$subtotal+=(floatval($precios_venta[$i_p]['precio_venta'])-(floatval($product['precio_origen'])+floatval($sumar)));
			$subtotal_1+=floatval($precios_venta[$i_p]['precio_venta']);
		}
	}
	print '<tr><td><b>TOTALES:</b></td><td></td><td>'.$suma_total.'</td><td>'.$suma_total_venta.'</td><td></td><td></td><td>'.$suma_amortizacion_socios.'</td><td>'.$suma_amortizacion.'</td></tr>'
	?>
					</tbody>
			</table></div>
			<?php
				print "<div style=\"margin-left:10%;margin-right:20%;\"><br /><br />";
	print "<b>Total (amortización):</b>".money_format('%(n', $suma_amortizacion)."<br />";
	print "<b>Total (venta):</b>".money_format('%(n', $subtotal_1)."<br /><br />";
	?>
<input type="button" class="btn float-l" value="Imprimir" onClick="imprimir(this);" /><form method="POST" action="?" <?php if(!isset($_GET['fecha_amortizacion']) || !$core->validatedate($_GET['fecha_amortizacion'])){?>onsubmit="return confirm('Realmente desea reiniciar la tabla?');"<?php } ?>><?php if(!isset($_GET['fecha_amortizacion']) || !$core->validatedate($_GET['fecha_amortizacion'])){?><input type="submit" class="btn float-r" name="reset" id="reset" value="Reiniciar tabla"/><?php } else {?><input type="submit" class="btn float-r" name="regresar" value="Regresar"/><?php } ?></form></div></div>
<?php
menu_end();
?>
</body>
</html>
