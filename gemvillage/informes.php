<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker2.php');
if(!isset($_GET['todo'])&&!isset($_GET['bruto'])&&!isset($_GET['neto'])&&!isset($_GET['ganancia'])&&
	!isset($_GET['gastos'])&&!isset($_GET['ingresos'])&&!isset($_GET['general'])&&!isset($_GET['ganancias'])){
	echo "<script> document.location='?todo=1';</script>";
}
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
        dateFormat: 'y-mm',
        showButtonPanel: true,
        closeText: 'Filtrar',
        onClose: function(dateText, inst){
            var month = parseInt($("#ui-datepicker-div .ui-datepicker-month :selected").val());
            var year = parseInt($("#ui-datepicker-div .ui-datepicker-year :selected").val());
            if(month<=9){
            	month = '0' + month;
            }
            year-=2000;
            if(year < 10){
            	year = '0' + year;
            }
            $(this).datepicker('setDate', new Date(year, month, 1));
        }
    });
});

$(function() {
    $('.year-picker').datepicker( {
        changeYear: true,
        dateFormat: 'y',
        showButtonPanel: true,
        closeText: 'Filtrar',
        onClose: function(dateText, inst){
            var year = parseInt($("#ui-datepicker-div .ui-datepicker-year :selected").val());
            year-=2000;
            if(year < 10){
            	year = '0' + year;
            }
            $(this).datepicker('setDate', new Date(year, 1, 1));
        }
    });
});

$(function() {
    $('.date-picker').datepicker( {
        changeMonth: true,
        changeYear: true,
        dateFormat: 'y-mm-dd',
        showButtonPanel: true,
        closeText: 'Filtrar',
        onClose: function(dateText, inst){
            var date = $(this).datepicker('getDate');
            var day = date.getDate();
            var month = date.getMonth();
            var year = date.getFullYear();
            if(month<=9){
            	month = '0' + month;
            }     
            if(day<=9){
            	day = '0' + day;
            }

            year-=2000;
            if(year < 10){
            	year = '0' + year;
            }
            $(this).datepicker('setDate', new Date(year, month, day));
        }
    });
});

$(function() {
    $( "#from" ).datepicker({
      	defaultDate: "+1w",
      	changeMonth: true,
        changeYear: true,
        dateFormat: 'y-mm-dd',
      	numberOfMonths: 1,
      	onClose: function( selectedDate ) {
        	$( "#to" ).datepicker( "option", "minDate", selectedDate );
      	}
    });
    $( "#to" ).datepicker({
      	defaultDate: "+1w",
      	changeMonth: true,
        changeYear: true,
        dateFormat: 'y-mm-dd',
      	numberOfMonths: 1,
      	onClose: function( selectedDate ) {
        	$( "#from" ).datepicker( "option", "maxDate", selectedDate );
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
    	dateFormat: 'y-mm-dd',
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
	$subtotal=array();
	$gastos=array();
	setlocale(LC_MONETARY, 'en_US');
	$condicion="";
	$display_adicional=" ";
	$sql_load="SELECT venta_id, id FROM amortizacion";
	if(isset($_GET['codigo']) || isset($_GET['nombre']) || isset($_GET['lote'])){
		$extra2=" INNER JOIN amortizacion_cid_multiple
				   	  ON master_id = amortizacion.id
				   INNER JOIN productos
				   	  ON productos.id = cid
				   WHERE ";
	} else if(isset($_GET['diario']) || isset($_GET['semanal']) || isset($_GET['mensual']) || isset($_GET['anual'])){
		$extra2.=" WHERE ";
	}
	if(isset($_GET['codigo'])){
		$extra="and codigobarras='".$_GET['codigo']."'";
		$extra2.="codigobarras='".$_GET['codigo']."'";
		if(isset($_GET['nombre']) || isset($_GET['lote']) || isset($_GET['diario']) || isset($_GET['semanal']) || isset($_GET['mensual']) || isset($_GET['anual'])){
			$extra2.=" AND ";
		}
		$extra3="";
	} 
	if(isset($_GET['nombre'])){
		$extra="and nombre='".$_GET['nombre']."'";
		$extra2.="nombre='".$_GET['nombre']."'";
		if(isset($_GET['lote']) || isset($_GET['diario']) || isset($_GET['semanal']) || isset($_GET['mensual']) || isset($_GET['anual'])){
			$extra2.=" AND ";
		}
		$extra3="";
	} 
	if(isset($_GET['lote'])){
		$extra="and lote='".$_GET['lote']."'";
		$extra2.="lote='".$_GET['lote']."'";
		if(isset($_GET['diario']) || isset($_GET['semanal']) || isset($_GET['mensual']) || isset($_GET['anual'])){
			$extra2.=" AND ";
		}
		$extra3="";
	} 
	if(isset($_GET['diario'])){
		$partes_fecha=explode("-",$_GET['diario']);
		$extra="";
		$extra2.="STR_TO_DATE(fecha, '%m/%d/%y')
  			LIKE STR_TO_DATE('".$partes_fecha[1]."/".$partes_fecha[2]."/".$partes_fecha[0]."', '%m/%d/%y')";
		$extra3=" WHERE fecha LIKE '".$partes_fecha[0]."-".$partes_fecha[1]."-".$partes_fecha[2]."%'";
	} 
	if(isset($_GET['semanal'])){
		$partes_semana=explode(" to ",$_GET['semanal']);
		$partes_fechas1=explode("-",$partes_semana[0]);
		$partes_fechas2=explode("-",$partes_semana[1]);
		$extra="";
		$extra2.="(STR_TO_DATE(fecha, '%m/%d/%y') BETWEEN STR_TO_DATE('".$partes_fechas1[1]."/".($partes_fechas1[2]-1)."/".$partes_fechas1[0]."', '%m/%d/%y')
			AND STR_TO_DATE('".$partes_fechas2[1]."/".$partes_fechas2[2]."/".$partes_fechas2[0]."', '%m/%d/%y'))";
		$extra3=" WHERE (STR_TO_DATE(fecha, '%y-%m-%d') BETWEEN STR_TO_DATE('".$partes_fechas1[0]."-".$partes_fechas1[1]."-".($partes_fechas1[2]-1)."', '%y-%m-%d') 
			AND STR_TO_DATE('".($partes_fechas2[0])."-".$partes_fechas2[1]."-".$partes_fechas2[2]."', '%y-%m-%d'))";
	} 
	if(isset($_GET['mensual'])){
		$partes_fecha=explode("-",$_GET['mensual']);
		$extra="";
		$extra2.="fecha LIKE '".$partes_fecha[1]."/__/".$partes_fecha[0]."%'";
		$extra3=" WHERE fecha LIKE '".$partes_fecha[0]."-".$partes_fecha[1]."-%'";
	} 
	if(isset($_GET['anual'])){
		$partes_fecha=$_GET['anual'];
		$extra="";
		$extra2.="fecha LIKE '__/__/".$partes_fecha."%'";
		$extra3=" WHERE fecha LIKE '".$partes_fecha."-__-__%'";
	}
	$sql_load="SELECT venta_id, amortizacion.id
			   FROM amortizacion ".
			   $extra2;
	$ventas=$core->db_exect($sql_load);
	echo '<a name="tabla"></a><div class="title-amortizacion"><h3>INFORME DE GANANCIAS Y VENTAS'.
		$display_adicional.'</h3><br /><div class="" style="width:100%"><div class="m buscador_ventas float-l" id="buscador_ventas" 
		style="font-size:14px; width:42%; margin-left:1%;padding:5px;"><h2 style="float:left;color:#dc6800;width:100%;"><b>Filtros: </b></h2><br/>';
			$buscador=$core->createfield("filtro_codigo",array("tipo"=>"texto","filtro"=>FILTRO_BARCODE,"etiqueta"=>"Código de barras"),null,1);
			echo '<div class="w-30">'.$buscador['label'].' <br/>'.$buscador['campo']."</div>";

			$buscador=$core->createfield("filtro_nombre",array("tipo"=>"texto","filtro"=>FILTRO_STRING,"etiqueta"=>"Nombre del producto"),null,1);
			echo '<div class="w-30">'.$buscador['label'].'<br/> '.$buscador['campo']."</div>";

			$buscador=$core->createfield("filtro_lote",array("tipo"=>"seleccionar","tabla"=>"lotes","columna_texto"=>"nombre","columna_valor"=>"id","filtro"=>FILTRO_INT,"etiqueta"=>"Nombre de lote"),null,1);
			echo '<div class="w-30">'.$buscador['label'].' '.$buscador['campo']."</div>";


	echo '</div><div class="m float-r" style="font-size:14px;width:42%; margin-left:1%;padding:5px;"><h2 style="float:left;color:#dc6800;width:100%;"><b>Periodo: </b></h2>
		<div class="w-30">
		<br/>
		<form id="filter" action="" method="post">
			<select name="filtro" style="width: 100%;float:left;">
			  <option value="none" selected>Seleccionar filtro</option>
			  <option value="diario">Diario</option>
			  <option value="semanal">Semanal</option>
			  <option value="mensual">Mensual</option>
			  <option value="anual">Anual</option>
			  <option value="range">Rango de fechas</option>
			</select>
		</form></div>';
	switch($_POST["filtro"]){
		case "diario":
			echo "<label for='startDate'>Fecha :</label><input type='button' value='Seleccionar fecha' id='startDate' class='date-picker' /><br/><br/><br/>";
			break;
		case "semanal":
			echo "<span class='week-picker'><b>Ver una semana anterior</b></span>
				<span id='startDate'></span> a <span id='endDate'></span><br/><br/><br/>";
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
			echo "<label for='startDate'>Fecha :</label><input type='button' value='Seleccionar fecha' id='startDate' class='month-picker' /><br/><br/><br/>";
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
			echo "<label for='startDate'>Fecha :</label><input type='button' value='Seleccionar fecha' id='startDate' class='year-picker' /><br/><br/><br/>";
			break;
		default:
			echo '<div class="w-30"><label for="from"><b>Desde</b></label><br/>
				<input type="text" id="from" name="from"></div><div class="w-30">
				<label for="to"><b>Hasta</b></label>
				<input type="text" id="to" name="to"></div>';
			break;
	}
?>
</div>
<div class="float-l" style="width:100%; margin-top:30px; margin-bottom:30px;"><h2 style="color:#dc6800;"><b>Informe a generar:</b></h2>
	<form class="informe" action="" method="post">
			<select name="informe" id="informe" style="width: 300px;">
			  <option value="todo" selected>Ver todo</option>
			  <option value="bruto">Ingresos brutos</option>
			  <option value="neto">Ingresos netos</option>
			  <option value="ganancia">Ingresos ganancia</option>
			  <option value="gastos">Gastos</option>
			  <option value="ingresos">Ingresos</option>
			  <option value="general">Disponible general</option>
			  <option value="ganancias">Disponible ganancias</option>
			</select>
		</form>
<input type="button" class="btn" value="Buscar" onClick="buscar();" /></div>
<script>
	function buscar() {
		var filtro = "?";
		if ($("#filtro_lote").val() !== ""){
			filtro += "lote="+$("#filtro_lote").val()+"&";
		}
		if ($("#filtro_nombre").val() !== ""){
			filtro += "nombre="+$("#filtro_nombre").val()+"&";
		}
		if ($("#filtro_codigo").val() !== ""){
			filtro += "codigo="+$("#filtro_codigo").val()+"&";
		}
		if ($(".year-picker").val() !== undefined){
			filtro += "anual="+$(".year-picker").val()+"&";
		}
		if ($(".month-picker").val() !== undefined){
			filtro += "mensual="+$(".month-picker").val()+"&";
		}
		if ($(".date-picker").val() !== undefined){
			filtro += "diario="+$(".date-picker").val()+"&";
		}
		if ($("#endDate").val() !== undefined){
			filtro += "semanal="+$("#startDate").text()+" to "+$("#endDate").text()+"&";
		}
		if ($("#from").val() !== undefined && $("#from").val()!==""){
			filtro += "semanal="+$("#from").val()+" to "+$("#to").val()+"&";
		}
		filtro += $("#informe").val()+"=1";
		document.location=filtro;
	};
</script>
</div></div>
<div style="width:70%;margin-left:10%;clear:both;margin-right:20%;margin-top:40px;" class="m" id="m2">
<?php
	echo '<table class="tablemaster" style="width:100%;"><thead>';
	if(isset($_GET['todo'])||isset($_GET['bruto'])||isset($_GET['neto'])||isset($_GET['ganancia'])||isset($_GET['general'])||isset($_GET['ganancias'])){
		echo '<th>Nombre del producto</th>
			<th>Código de Barras</th>
			<th>Fecha de venta</th>';
	}
	if(isset($_GET['bruto'])){
		echo '<th>Precio original</th>';
	}
	if(isset($_GET['neto'])){
		echo '<th>Precio de Venta</th>';
	}
	if(isset($_GET['ganancia'])||isset($_GET['general'])||isset($_GET['ganancias'])||isset($_GET['todo'])){
		echo '<th>Precio original</th>';
		echo '<th>Precio de Venta</th>';
		echo '<th>Ingreso ganancia</th>';
	}
	if(isset($_GET['gastos'])){
		echo '<th>Fecha del gasto</th>';
		echo '<th>Cantidad</th>';
	}
	if(isset($_GET['ingresos'])){
		echo '<th>Fecha del ingreso</th>';
		echo '<th>Cantidad</th>';	
	}
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
			if(isset($_GET['codigo'])){
				if($product['codigobarras']!=$_GET['codigo']){
					continue;
				}
			}
			if(isset($_GET['nombre'])){
				if($product['nombre']!=$_GET['nombre']){
					continue;
				}
			}
			if(isset($_GET['lote'])){
				if($product['lote']!=$_GET['lote']){
					continue;
				}
			}
			$gastos=$core->db_exect("select nombre,aumento from gastosadicionales where id in (select gasto from productos_gasto_multiple where master_id='".$product['id']."');");
			$product['precio_origen']=round($product['precio_origen'],2);
			$sumar=0;
			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$sumar+=round((floatval($product['precio_origen'])/100)*floatval($gasto['aumento']),2);
			}
			if(isset($fecha_venta[$i_p]['fecha']))
				$fecha_temp=$fecha_venta[$i_p]['fecha'];
//			$vals_socios=array_pop($core->db_exect("SELECT (select nombre from socios where id=productos.socio) as socio_nombre,beneficios FROM lotes where id='".$product['lote']."';"));
			$prec_total=floatval(floatval($product['precio_origen'])+$sumar);
			$suma_amortizacion+=floatval($amortizacion);
			$suma_amortizacion_socios+=floatval($amortizacion_socio);
			$suma_total+=$prec_total;
			$suma_total_venta+=floatval($precios_venta[$i_p]['precio_venta']);
			$ingreso_ganancia = floatval($precios_venta[$i_p]['precio_venta']) - $prec_total;
			
			if(isset($_GET['todo'])||isset($_GET['bruto'])||isset($_GET['neto'])||isset($_GET['ganancia'])||isset($_GET['general'])||isset($_GET['ganancias'])){
				print "<tr><td>".
					$product['nombre']."</td><td>".
					$product['codigobarras']."</td><td>".
					$fecha_temp."</td><td>";
			}
			if(isset($_GET['bruto'])){
				print $prec_total."</td><td>";
				print "</td></tr>";
			}
			if(isset($_GET['neto'])){
				print $precios_venta[$i_p]['precio_venta']."</td><td>";
				print "</td></tr>";
			}
			if(isset($_GET['ganancia'])||isset($_GET['general'])||isset($_GET['ganancias'])||isset($_GET['todo'])){
				print $prec_total."</td><td>";
				print $precios_venta[$i_p]['precio_venta']."</td><td>";
				print $ingreso_ganancia."</td><td>";
				print "</td></tr>";
			}
				print "</td></tr>";

			
//			$subtotal+=(floatval($precios_venta[$i_p]['precio_venta'])-(floatval($product['precio_origen'])+floatval($sumar)));
			$ing_bruto+=$prec_total;
			$ing_neto+=floatval($precios_venta[$i_p]['precio_venta']);
			$ing_ganancia+=$ingreso_ganancia;
		}
	}
	$gastos_extra=$core->db_exect("SELECT cantidad, fecha FROM gastos".$extra3);
	foreach($gastos_extra as $gastos['cantidad']){
		foreach($gastos as $iig){
			$gastos_totales += $iig['cantidad'];
			if(isset($_GET['gastos'])){
				print "<tr><td>";
				print $iig['fecha']."</td><td>";
				print $iig['cantidad']."</td></tr>";
			}
		}
	}
	$ingresos_extra=$core->db_exect("SELECT cantidad, fecha FROM ingresos".$extra3);
	foreach($ingresos_extra as $ingresos['cantidad']){
		foreach($ingresos as $iig){
			$ingresos_totales += $iig['cantidad'];
			if(isset($_GET['ingresos'])){
				print "<tr><td>";
				print $iig['fecha']."</td><td>";
				print $iig['cantidad']."</td></tr>";
			}
		}
	}
	$retiros_extra=$core->db_exect("SELECT cantidad, fecha FROM retiros".$extra3);
	foreach($retiros_extra as $retiros['cantidad']){
		foreach($retiros as $iig){
			$retiros_totales += $iig['cantidad'];
		}
	}
	$disponible_gen=($ing_neto+$ingresos_totales)-$gastos_totales;
	$disponible_gan=($ing_ganancia+$ingresos_totales)-$gastos_totales;

	if(isset($_GET['bruto'])){
		print '<tr><td><b>TOTALES:</td><td></td><td></td><td>'.$suma_total.'</td></b>';
	}
	if(isset($_GET['neto'])){
		print '<tr><td><b>TOTALES:</td><td></td><td></td><td>'.$suma_total_venta.'</td></b>';
	}
	if(isset($_GET['ganancia'])||isset($_GET['general'])||isset($_GET['ganancias'])||isset($_GET['todo'])){
		print '<tr><td><b>TOTALES:</td><td></td><td></td><td>'.$suma_total.'</td><td>'.$suma_total_venta.'</td><td>'.$ing_ganancia.'</td></b>';
	}
	?>
	</tbody>
	</table></div>
	<br/><br/><div class="m" style="width:40%;margin:auto;margin-right:35%;"><span style="text-align:center;">
	<?php
		print "<div style=\"margin-left:10%;margin-right:20%;\"><br /><br />";

		if(isset($_GET['bruto'])){
			print "<b style='color:#dc6800;'>Ingreso bruto: </b>".money_format('%n', $ing_bruto)."<br /><br />";
		}
		if(isset($_GET['neto'])){
		print "<b style='color:#dc6800;'>Ingreso neto: </b>".money_format('%n', $ing_neto)."<br /><br />";
		}
		if(isset($_GET['ganancia'])){
			print "<b style='color:#dc6800;'>Ingreso ganancia: </b>".money_format('%n', $ing_ganancia)."<br /><br />";
		}
		if(isset($_GET['general'])){
			print "<b>Ingreso neto: </b>".money_format('%n', $ing_neto)."<br />+<br />";
			print "<b>ingresos adicionales: </b>".money_format('%n', $ingresos_totales)."<br />-<br />";
			print "<b>Gastos adicionales: </b>".money_format('%n', $gastos_totales)."<br /><br />=<br /><br />";
			print "<b style='color:#dc6800;'>Disponible general: </b>".money_format('%n', $disponible_gen)."<br /><br />";
		}
		if(isset($_GET['ganancias'])){
			print "<b>Ingreso ganancia: </b>".money_format('%n', $ing_ganancia)."<br />+<br />";
			print "<b>ingresos adicionales: </b>".money_format('%n', $ingresos_totales)."<br />-<br />";
			print "<b>Gastos adicionales: </b>".money_format('%n', $gastos_totales)."<br /><br />=<br /><br />";
			print "<b style='color:#dc6800;'>Disponible ganancias: </b>".money_format('%n', $disponible_gan)."<br /><br />";
		}
		if(isset($_GET['todo'])){
			print "<b style='color:#dc6800;'>Ingreso bruto: </b>".money_format('%n', $ing_bruto)."<br /><br />";
			print "<b style='color:#dc6800;'>Ingreso neto: </b>".money_format('%n', $ing_neto)."<br /><br />";
			print "<b style='color:#dc6800;'>Ingreso ganancia: </b>".money_format('%n', $ing_ganancia)."<br /><br />";
			print "<b style='color:#dc6800;'>Gastos adicionales: </b>".money_format('%n', $gastos_totales)."<br /><br />";
			print "<b style='color:#dc6800;'>ingresos adicionales: </b>".money_format('%n', $ingresos_totales)."<br /><br />";
			print "<b style='color:#dc6800;'>Disponible general: </b>".money_format('%n', $disponible_gen)."<br /><br />";
			print "<b style='color:#dc6800;'>Disponible ganancias: </b>".money_format('%n', $disponible_gan)."<br /><br />";
			print "<b style='color:#dc6800;'>Retiros totales: </b>".money_format('%n', $retiros_totales)."<br /><br />";
		}
		if(isset($_GET['gastos'])){
			print "<b style='color:#dc6800;'>Gastos adicionales: </b>".money_format('%n', $gastos_totales)."<br /><br />";
		}
		if(isset($_GET['ingresos'])){
			print "<b style='color:#dc6800;'>ingresos adicionales: </b>".money_format('%n', $ingresos_totales)."<br /><br />";
		}
	?>
	</span></div>
<input type="button" class="btn float-l" value="Imprimir" onClick="imprimir(this);" style="margin-top:40px;"/>
<form method="POST" action="?" <?php if(!isset($_GET['fecha_amortizacion']) || !$core->validatedate($_GET['fecha_amortizacion'])){
	?>onsubmit="return confirm('Realmente desea reiniciar la tabla?');"<?php 
} ?>>
<?php if(!isset($_GET['fecha_amortizacion']) || !$core->validatedate($_GET['fecha_amortizacion'])){
	?><input type="submit" class="btn float-r" name="reset" id="reset" style="margin-top:40px;" value="Reiniciar tabla"/><?php 
} else {
	?><input type="submit" class="btn float-r" name="regresar" style="margin-top:40px;" value="Regresar"/><?php 
} ?>
</form></div></div>
<?php
menu_end();
?>
</body>
</html>
