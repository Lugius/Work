<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?><html>
<head>
<title>Informe de ventas y ganancias</title>
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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/themes/base/jquery-ui.css">
<script type="text/javascript">
$(function() {
    $('.month-picker').datepicker( {
        changeMonth: true,
        changeYear: true,
        dateFormat: 'mm/yy',
        showButtonPanel: true,
        closeText: 'Filtrar',
        onClose: function(dateText, inst){
            var month = parseInt($("#ui-datepicker-div .ui-datepicker-month :selected").val())+1;
            var year = parseInt($("#ui-datepicker-div .ui-datepicker-year :selected").val());
            if(month<=9){
            	month = '0' + month;
            }
            $(this).datepicker('setDate', new Date(year, month, 1));
            document.location='?'+"<?php echo $_POST['filtro'] ?>"+'='+month+"/"+year;
        }
    });
});
$(function() {
    $('.year-picker').datepicker( {
        changeYear: true,
        dateFormat: 'yy',
        showButtonPanel: true,
        closeText: 'Filtrar',
        onClose: function(dateText, inst){
            var year = parseInt($("#ui-datepicker-div .ui-datepicker-year :selected").val());
            $(this).datepicker('setDate', new Date(year, 1, 1));
            document.location='?'+"<?php echo $_POST['filtro'] ?>"+'='+year;
        }
    });
});
$(function() {
    var startDate;
    var endDate;
    
    var selectCurrentWeek = function() {
        window.setTimeout(function () {
            $('.week-picker').find('.ui-datepicker-current-day a').addClass('ui-state-active')
        }, 1);
    }
    
    $('.week-picker').datepicker( {
    	dateFormat: 'dd/mm/yy',
        showOtherMonths: true,
        selectOtherMonths: true,
        onSelect: function(dateText, inst) { 
            var date = $(this).datepicker('getDate');
            startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay());
            endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 6);
            var dateFormat = inst.settings.dateFormat || $.datepicker._defaults.dateFormat;
            $('#startDate').text($.datepicker.formatDate( dateFormat, startDate, inst.settings ));
            $('#endDate').text($.datepicker.formatDate( dateFormat, endDate, inst.settings ));
            
            selectCurrentWeek();

			document.location='?'+"<?php echo $_POST['filtro'] ?>"+'='+$.datepicker.formatDate( dateFormat, startDate, inst.settings )+" to "+$.datepicker.formatDate( dateFormat, endDate, inst.settings );
        },
        beforeShowDay: function(date) {
            var cssClass = '';
            if(date >= startDate && date <= endDate)
                cssClass = 'ui-datepicker-current-day';
            return [true, cssClass];
        },
        onChangeMonthYear: function(year, month, inst) {
            selectCurrentWeek();
        }
    });
    
    $('.week-picker .ui-datepicker-calendar tr').live('mousemove', function() { $(this).find('td a').addClass('ui-state-hover'); });
    $('.week-picker .ui-datepicker-calendar tr').live('mouseleave', function() { $(this).find('td a').removeClass('ui-state-hover'); });
});
</script>
</head>


</head>
<body>
<script>
function imprimir(mi){
	document.getElementById('toolbar').style.display='none';
	if(typeof(document.getElementById('reset')) !='undefined' && document.getElementById('reset')!=null)
		document.getElementById('reset').style.visibility='hidden';
	document.getElementById('buscador_ventas').style.display='none';
	mi.style.visibility='hidden';
	window.print();
	mi.style.visibility='visible';
	if(typeof(document.getElementById('reset')) !='undefined' && document.getElementById('reset')!=null)
		document.getElementById('reset').style.visibility='visible';
	document.getElementById('toolbar').style.display='block';
	document.getElementById('buscador_ventas').style.display='block';
}

$(function() {
	$('[name="filtro"]').change(function() {
  		$(this).closest('form').submit();
	});
});

$('[name="filtro"]').change(function() {
  $(this).closest('form').submit();
});

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
<?php
	$subtotal=array();
	$gastos=array();
	setlocale(LC_MONETARY, 'en_US');
	$condicion="";
	$display_adicional=" ";
	$sql_load="SELECT venta_id, id FROM amortizacion";
	if(isset($_GET['none'])){
		$condicion="";
		$sql_load="SELECT venta_id, id FROM amortizacion";
	} elseif(isset($_GET['codigo'])){
		$extra="and codigobarras='".$_GET['codigo']."'";
		$extra2=" INNER JOIN amortizacion_cid_multiple
				   	  ON master_id = amortizacion.id
				   INNER JOIN productos
				   	  ON productos.id = cid
				   WHERE codigobarras='".$_GET['codigo']."'";
		$extra3="";
	} elseif(isset($_GET['nombre'])){
		$extra="and nombre='".$_GET['nombre']."'";
		$extra2=" INNER JOIN amortizacion_cid_multiple
				   	  ON master_id = amortizacion.id
				   INNER JOIN productos
				   	  ON productos.id = cid
				   WHERE nombre='".$_GET['nombre']."'";
		$extra3="";
	} elseif(isset($_GET['lote'])){
		$extra="and lote='".$_GET['lote']."'";
		$extra2=" INNER JOIN amortizacion_cid_multiple
				   	  ON master_id = amortizacion.id
				   INNER JOIN productos
				   	  ON productos.id = cid
				   WHERE lote='".$_GET['lote']."'";
		$extra3="";
	} elseif(isset($_GET['diario'])){
		$partes_fecha=explode("/",$_GET['diario']);
		$extra="";
		$extra2="WHERE fecha LIKE '".$partes_fecha[2]."-".$partes_fecha[1]."-".$partes_fecha[0]." %'";
		$extra3=" WHERE fecha LIKE '".$partes_fecha[0]."/".$partes_fecha[1]."/".$partes_fecha[2]."%'";
	} elseif(isset($_GET['semanal'])){
		$partes_semana=explode(" to ",$_GET['semanal']);
		$partes_fechas1=explode("/",$partes_semana[0]);
		$partes_fechas2=explode("/",$partes_semana[1]);
		$extra="";
		$extra2="WHERE (fecha BETWEEN '".$partes_fechas1[2]."-".$partes_fechas1[1]."-".$partes_fechas1[0]." %' AND '".$partes_fechas2[2]."-".$partes_fechas2[1]."-".$partes_fechas2[0]." %')";
		$extra3="WHERE (fecha between '".$partes_fechas1[0]."/".$partes_fechas1[1]."/".$partes_fechas1[2]." %' AND '".$partes_fechas2[0]."/".$partes_fechas2[1]."/".$partes_fechas2[2]."%')";
	} elseif(isset($_GET['mensual'])){
		$partes_fecha=explode("/",$_GET['mensual']);
		$extra="";
		$extra2="WHERE fecha LIKE '".$partes_fecha[1]."-".$partes_fecha[0]."-%'";
		$extra3=" WHERE fecha LIKE '%/".$partes_fecha[0]."/".$partes_fecha[1]."%'";
	} elseif(isset($_GET['anual'])){
		$partes_fecha=$_GET['anual'];
		$extra="";
		$extra2="WHERE fecha LIKE '".$partes_fecha."-%'";
		$extra3=" WHERE fecha LIKE '%/".$partes_fecha."'";
	}
	$sql_load="SELECT venta_id, amortizacion.id
			   FROM amortizacion ".
			   $extra2;
	$ventas=$core->db_exect($sql_load);
	echo '<a name="tabla"></a><div class="title-amortizacion"><h3>INFORME DE GANANCIAS Y VENTAS'.
		$display_adicional.'</h3><br />'.
		'<p style="text-align:left;margin-left:20%;">Filtrar por:</p>
		<form id="filter" action="" method="post">
			<select name="filtro" style="width: 300px;float:left;margin-left:20%;">
			  <option value="none" selected>Seleccionar filtro</option>
			  <option value="codigo">Código de barras</option>
			  <option value="nombre">Nombre de producto</option>
			  <option value="lote">Lote</option>
			  <option value="diario">Diario</option>
			  <option value="semanal">Semanal</option>
			  <option value="mensual">Mensual</option>
			  <option value="anual">Anual</option>
			</select>
		<br/><br/>
		</form><br/><div class="buscador_ventas" id="buscador_ventas" 
		style="clear:left;float:left;margin-left:20%;">';
	switch($_POST["filtro"]){
		case "codigo":
			$buscador=$core->createfield("filtro_amortizaciones",array("tipo"=>"barcode","filtro"=>FILTRO_BARCODE,"etiqueta"=>"Introdusca código de barras"),null,1);
			break;
		case "nombre":
			$buscador=$core->createfield("filtro_amortizaciones",array("tipo"=>"texto","filtro"=>FILTRO_STRING,"etiqueta"=>"Introdusca el nombre del producto"),null,1);
			break;
		case "lote":
			$buscador=$core->createfield("filtro_amortizaciones",array("tipo"=>"seleccionar","tabla"=>"lotes","columna_texto"=>"nombre","columna_valor"=>"id","filtro"=>FILTRO_INT,"etiqueta"=>"Introdusca nomber de lote"),null,1);
			break;
		case "diario":
			$buscador=$core->createfield("filtro_amortizaciones",array("tipo"=>"fecha","filtro"=>FILTRO_STRING,"etiqueta"=>"Ver dias anteriores"),null,1);
			break;
		case "semanal":
			echo "<span class='week-picker'><b>Ver una semana anterior</b></span><br/>";
    		break;
		case "mensual":
			?>
			<style>
			.ui-datepicker-calendar {
			    display: none;
			    }
			button.ui-datepicker-current { 
				display: none; 
			}
			</style>
			<?php
			echo "<label for='startDate'>Date :</label><input type='button' value='Select date' id='startDate' class='month-picker' /><br/>";
			break;
		case "anual":
			?>
			<style>
			.ui-datepicker-calendar {
			    display: none;
			    }
			button.ui-datepicker-current { 
				display: none; 
			}
			</style>
			<?php
			echo "<label for='startDate'>Date :</label><input type='button' value='Select date' id='startDate' class='year-picker' /><br/>";
			break;
		case "none":
			$buscador=$core->createfield("",array("tipo"=>"","filtro"=>"","etiqueta"=>""),null,1);
			break;
	}
?>

<script>
	$(function() {
		$("#filtro_amortizaciones").change(function() {
			document.location='?'+"<?php echo $_POST['filtro'] ?>"+'='+$(this).val();
		});
	});
</script>

<?php
	echo $buscador['label'].' '.$buscador['campo'].'</div><br/><br/>
		<div style="width:70%;margin-left:10%;clear:both;margin-right:20%;margin-top:40px;" class="m" id="m2">
		<table class="tablemaster" style="width:100%;"><thead>
		<th>Nombre del producto</th>
		<th>Código de Barras</th>
		<th>Fecha de venta</th>
		<th>Precio original</th>
		<th>Precio de Venta</th>
		<th>Ingreso ganancia</th>
		<tbody>';
//	$subtotal=0;
	$subtotal_1=0;
	$suma_amortizacion=0;
	$suma_amortizacion_socios=0;
	$suma_total=0;
	$suma_total_venta=0;
	foreach($ventas as $venta){
		$productos=$core->db_exect("SELECT amortizacion_cid_multiple.id,nombre,codigobarras,precio_origen,lote,
			IFNULL((select nombre from socios where id=productos.socio),'') 
			as socio_nombre,IFNULL(beneficios,0) as beneficios 
			FROM productos
			INNER JOIN amortizacion_cid_multiple
				ON productos.id=cid
			where master_id='".$venta['id']."'".
			$extra.
			"order by amortizacion_cid_multiple.id;");
		$fecha_venta=$core->db_exect("SELECT fecha 
			FROM amortizacion 
			where id='".$venta['id']."';");
		$precios_venta=$core->db_exect("SELECT precio_venta 
			FROM amortizacion_precio_venta_multiple 
			where master_id='".$venta['id']."';");
		foreach($productos as $i_p=>$product){
			$gastos=$core->db_exect("select nombre,aumento from gastosadicionales where id in (select gasto from productos_gasto_multiple where master_id='".$product['id']."');");
			$product['precio_origen']=round($product['precio_origen'],2);
			$sumar=0;
			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$sumar+=round((floatval($product['precio_origen'])/100)*floatval($gasto['aumento']),2);
			}
//			$vals_socios=array_pop($core->db_exect("SELECT (select nombre from socios where id=productos.socio) as socio_nombre,beneficios FROM lotes where id='".$product['lote']."';"));
			$prec_total=floatval(floatval($product['precio_origen'])+$sumar);
			$suma_amortizacion+=floatval($amortizacion);
			$suma_amortizacion_socios+=floatval($amortizacion_socio);
			$suma_total+=$prec_total;
			$suma_total_venta+=floatval($precios_venta[$i_p]['precio_venta']);
			$ingreso_ganancia = floatval($precios_venta[$i_p]['precio_venta']) - $prec_total;
			print "<tr><td>".
			$product['nombre']."</td><td>".
			$product['codigobarras']."</td><td>".
			$fecha_venta[$i_p]['fecha']."</td><td>".
			$prec_total."</td><td>".
			$precios_venta[$i_p]['precio_venta']."</td><td>".
			$ingreso_ganancia."</td><td>".
			"</td></tr>";
			
//			$subtotal+=(floatval($precios_venta[$i_p]['precio_venta'])-(floatval($product['precio_origen'])+floatval($sumar)));
			$ing_bruto+=$prec_total;
			$ing_neto+=floatval($precios_venta[$i_p]['precio_venta']);
			$ing_ganancia+=$ingreso_ganancia;
		}
	}
	print_r($extra3);
	$gastos_extra=$core->db_exect("SELECT cantidad FROM gastos".$extra3);
	foreach($gastos_extra as $ig=>$gastos){
		foreach($gastos as $iig){
			$gastos_totales += floatval($iig);
		}
	}
	$ingresos_extra=$core->db_exect("SELECT cantidad FROM ingresos".$extra3);
	foreach($ingresos_extra as $ig=>$ingresos){
		foreach($ingresos as $iig){
			$ingresos_totales += floatval($iig);
		}
	}
	$retiros_extra=$core->db_exect("SELECT cantidad FROM retiros".$extra3);
	foreach($retiros_extra as $ig=>$retiros){
		foreach($retiros as $iig){
			$retiros_totales += floatval($iig);
		}
	}
	$disponible_gen=($ing_neto+$ingresos_totales)-$gastos_totales;
	$disponible_gan=($ing_ganancia+$ingresos_totales)-$gastos_totales;
	print '<tr><td><b>TOTALES:</td><td></td><td></td><td>'.$suma_total.'</td><td>'.$suma_total_venta.'</td><td>'.$ing_ganancia.'</td></b>'
	?>
	</tbody>
	</table></div>
	<span style="text-align:left;">
	<?php
		print "<div style=\"margin-left:10%;margin-right:20%;\"><br /><br />";
		print "<b>Ingreso bruto: </b>".money_format('%n', $ing_bruto)."<br /><br />";
		print "<b>Ingreso neto: </b>".money_format('%n', $ing_neto)."<br /><br />";
		print "<b>Ingreso ganancia: </b>".money_format('%n', $ing_ganancia)."<br /><br />";
		print "<b>Gastos adicionales: </b>".money_format('%n', $gastos_totales)."<br /><br />";
		print "<b>ingresos adicionales: </b>".money_format('%n', $ingresos_totales)."<br /><br />";
		print "<b>Disponible general: </b>".money_format('%n', $disponible_gen)."<br /><br />";
		print "<b>Disponible ganancias: </b>".money_format('%n', $disponible_gan)."<br /><br />";
		print "<b>Retiros totales: </b>".money_format('%n', $retiros_totales)."<br /><br />";
	?>
	</span>
<input type="button" class="btn float-l" value="Imprimir" onClick="imprimir(this);" />
<form method="POST" action="?" <?php if(!isset($_GET['fecha_amortizacion']) || !$core->validatedate($_GET['fecha_amortizacion'])){
	?>onsubmit="return confirm('Realmente desea reiniciar la tabla?');"<?php 
} ?>>
<?php if(!isset($_GET['fecha_amortizacion']) || !$core->validatedate($_GET['fecha_amortizacion'])){
	?><input type="submit" class="btn float-r" name="reset" id="reset" value="Reiniciar tabla"/><?php 
} else {
	?><input type="submit" class="btn float-r" name="regresar" value="Regresar"/><?php 
} ?>
</form></div></div>
<?php
menu_end();
?>
</body>
</html>
