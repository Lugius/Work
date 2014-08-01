<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker2.php');
?><html>
<head>
<title>Panel de control</title>
<script src="Chart.min.js"></script>
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
<script>
function createChart(){
	var ctx = document.getElementById("grafica").getContext("2d");


	var ventas = document.getElementById('a').value;
	var label = document.getElementById('b').value;
	ventas = ventas.split(",");
	label = label.split(",");
	var data = {
		labels: label,
	    datasets: [
	        {
	            label: "Ganancias",
	            fillColor: "rgba(0,0,0,0.2)",
	            strokeColor: "rgba(0,0,0,1)",
	            pointColor: "rgba(0,0,0,1)",
	            pointStrokeColor: "#fff",
	            pointHighlightFill: "#fff",
	            pointHighlightStroke: "rgba(0,0,0,1)",
	            data: ventas
	        }
	    ]
	};

    options = {
        animation: false,
        bezierCurve: true,
    	bezierCurveTension : 0.2,
    	datasetFill : false
    };

	new Chart(ctx).Line(data, options);
}
</script>
</head>


</head>
<body onload="createChart();">
<script>
$(function() {
    $('.month-picker').datepicker( {
        changeMonth: true,
        changeYear: true,
        dateFormat: 'y-mm',
        showButtonPanel: true,
        closeText: 'Filtrar',
        onClose: function(dateText, inst){
            var month = parseInt($("#ui-datepicker-div .ui-datepicker-month :selected").val())+1;
            var year = parseInt($("#ui-datepicker-div .ui-datepicker-year :selected").val());
            if(month<=9){
            	month = '0' + month;
            }
            year-=2000;
            if(year < 10){
            	year = '0' + year;
            }
            $(this).datepicker('setDate', new Date(year, month, 1));
            document.location='?mes='+month+"-"+year;
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
            document.location='?ano='+year;
        }
    });
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
<div id="toolbar" class="toolbar" style="width:70%;margin-left:14%;text-align:center;">
<div class="m m1">
<div id="acciones2" class="acciones2">
<ul id="cuadro2" class="cuadro2">
<li><a href="informes.php"><img src="tabla_lotes.png"/><br />Informes</a></li>
<li><a href="gastos.php"><img src="gastos.png"/><br />Gastos</a></li>
<li><a href="ingresos.php"><img src="ingresos.png"/><br />Ingresos</a></li>
<li><a href="retiros.php"><img src="retiros.png"/><br />Retiros</a></li>
<li><a href="usuarios.php"><img src="usuarios.png" /><br />Usuarios</a></li>
<li><a href="panel_control.php"><img src="panel_control.png" /><br />Punto de venta</a></li>

</ul>
</div>
</div>
</div>
<?php
	$subtotal=array();
	$gastos=array();
	setlocale(LC_MONETARY, 'en_US');
	$sql_load="SELECT venta_id, id FROM amortizacion";
	$ventas=$core->db_exect($sql_load);
	echo '<a name="tabla"></a><div class="title-amortizacion"><h3>Panel de control de administraci&oacute;n</div><br /><br />
	<div style="float:right;width:70%;"><h1 style="float:left;margin-left:5%;"><b> Ganancias: </b></h1>
	<form id="filter" action="" method="post">
		<div class="buscador_ventas" id="buscador_ventas" 
		style="clear:left;float:left;margin-left:5%;">';
		if(isset($_GET['ano'])){
			echo '<input class="filter" type="radio" name="tipo_graf" value="ano" checked> Del A&ntilde;o <br/>
				  <input class="filter" type="radio" name="tipo_graf" value="mes"> Del mes <br/>';
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
		} else {
			echo '<input class="filter" type="radio" name="tipo_graf" value="ano"> Del A&ntilde;o <br/>
				  <input class="filter" type="radio" name="tipo_graf" value="mes" checked> Del mes <br/>';
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
		}
	echo '</form>';
?>
<script>
	$(function() {
		$(".filter").change(function() {
			var hoy = new Date();
			var mes = hoy.getMonth()+1;
			var ano = hoy.getFullYear()-2000;
			if (ano<10) ano='0'+ano;
			if(mes<10) mes='0'+mes;
			if ($(this).val()=="mes")
				document.location='?'+$(this).val()+'='+mes+'-'+ano;
			if ($(this).val()=="ano")
				document.location='?'+$(this).val()+'='+ano;
		});
	});
</script>
<?php
	echo '<br/><br/><canvas id="grafica" width="800" height="400"></canvas></div></div>
		<br/><br/><br/>
		<div style="float:left;width:30%;"><h4 style="margin-left:10%;"><b>Ventas de los &uacute;ltimos 7 d&iacute;as:</b><h4>
		<div style="width:70%;margin-left:10%;clear:both;margin-right:20%;margin-top:40px;" class="m" id="m2">
		<table class="tablemaster" style="width:100%;"><thead>
		<th></th>
		<th></th>';
//	$subtotal=0;
	$subtotal_1=0;
	$suma_amortizacion=0;
	$suma_amortizacion_socios=0;
	$suma_total=0;
	$suma_total_venta=0;
	$ganancias = array(array());

	for($u=0; $u<=$ganancias.length; $u++){
		for($i=0; $i<=11; $i++){
			for($ii=0;$ii<=30;$ii++){
				if (!isset($ganancias[$i][$ii]))
					$ganancias[$i][$ii] = 0;
			}
		}
	}

	foreach($ventas as $venta){
		$productos=$core->db_exect("SELECT amortizacion_cid_multiple.id,nombre,codigobarras,precio_origen,lote,
			IFNULL((select nombre from socios where id=productos.socio),'') 
			as socio_nombre,IFNULL(beneficios,0) as beneficios 
			FROM productos
			INNER JOIN amortizacion_cid_multiple
				ON productos.id=cid
			where master_id='".$venta['id']."'".
			"order by amortizacion_cid_multiple.id;");
		$fecha_venta=$core->db_exect("SELECT fecha 
			FROM amortizacion 
			where id='".$venta['id']."';");
		$precios_venta=$core->db_exect(
			"SELECT precio_venta 
			FROM amortizacion_precio_venta_multiple 
			WHERE master_id='".$venta['id']."';");
		foreach($productos as $i_p=>$product){
			$gastos=$core->db_exect(
				"SELECT nombre,aumento 
				FROM gastosadicionales 
				WHERE id in (select gasto 
						from productos_gasto_multiple 
						where master_id='".$product['id']."');");
			$product['precio_origen']=round($product['precio_origen'],2);
			$sumar=0;
			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$sumar+=round((floatval($product['precio_origen'])/100)*floatval($gasto['aumento']),2);
			}
//			$vals_socios=array_pop($core->db_exect("SELECT (select nombre from socios where id=productos.socio) as socio_nombre,beneficios FROM lotes where id='".$product['lote']."';"));
			$prec_total=floatval(floatval($product['precio_origen'])+$sumar);
			$ingreso_ganancia = floatval($precios_venta[$i_p]['precio_venta']) - $prec_total;
			$fechaSemanaAnterior=date("m/d/y",strtotime("-1 week"));
			$fechaHoy=date("m/d/y",strtotime("+1 day"));
			if(isset($fecha_venta[$i_p]['fecha']))
				$fecha_temp=$fecha_venta[$i_p]['fecha'];
			if($fecha_temp <= $fechaHoy && $fecha_temp >= $fechaSemanaAnterior){
				$suma_total+=$prec_total;
				$suma_total_venta+=floatval($precios_venta[$i_p]['precio_venta']);
				$ing_ganancia+=$ingreso_ganancia;
				$ing_bruto+=$prec_total;
				$ing_neto+=floatval($precios_venta[$i_p]['precio_venta']);

			}
			if (isset($fecha_venta[$i_p]['fecha'])){
				$p_fecha = explode("/", $fecha_venta[$i_p]['fecha']);
			}
			if (isset($p_fecha)){
				$temp = (explode(" ", $p_fecha[2]));
				$p_fecha[2]=$temp[0];
				$p_ano = $p_fecha[2];
				$p_mes = $p_fecha[0];
				$p_dia = $p_fecha[1];
			}
			if (isset($_GET['mes'])){
				$partes_fecha = explode("-", $_GET['mes']);
				if(($partes_fecha[0])==$p_mes && $partes_fecha[1]==($p_ano)){
					$ganancias[$p_mes-1][$p_dia-1]+=$ingreso_ganancia;
				}
			} elseif(isset($_GET['ano'])){
				$partes_fecha = $_GET['ano'];
				if($partes_fecha==$p_ano){
					$ganancias[$p_mes-1][$p_dia-1]+=$ingreso_ganancia;
				}
			} else {
				$ganancias[$p_mes-1][$p_dia-1]+=$ingreso_ganancia;
			}
		}
	}
	if(isset($_GET['mes'])){
		$partes_fecha = explode("-", $_GET['mes']);
	} elseif(isset($_GET['ano'])){
		$partes_fecha = $_GET['ano'];
	} else {
		$fecha = date("m-Y");
		$partes_fecha = explode("-", $fecha);
	}
	$fechaSemanaAnterior=date("y-m-d",strtotime("-1 week"));
	$fechaHoy=date("y-m-d",strtotime("+1 day"));
	$gastos_extra=$core->db_exect("SELECT cantidad, fecha FROM gastos");
	foreach($gastos_extra as $gastos){
		if($gastos['fecha'] <= $fechaHoy && $gastos['fecha'] >= $fechaSemanaAnterior){
			$gastos_totales += floatval($gastos['cantidad']);
		}
		$p_fecha = explode("-", $gastos['fecha']);
		$gas_ano = $p_fecha[0];
		$gas_mes = $p_fecha[1];
		$gas_dia = $p_fecha[2];
		if (isset($_GET['mes'])){
				if($partes_fecha[0]==($gas_mes) && $partes_fecha[1]==($gas_ano)){
					$ganancias[$gas_mes-1][$gas_dia-1]-=floatval($gastos['cantidad']);
				}
			} elseif(isset($_GET['ano'])){
				if($partes_fecha==$gas_ano){
					$ganancias[$gas_mes-1][$gas_dia-1]-=floatval($gastos['cantidad']);
				}
			} else {
				$ganancias[$gas_mes-1][$gas_dia-1]-=floatval($gastos['cantidad']);
			}
	}
	$ingresos_extra=$core->db_exect("SELECT cantidad, fecha FROM ingresos");
	foreach($ingresos_extra as $ingresos){
		if($ingresos['fecha'] <= $fechaHoy && $ingresos['fecha'] >= $fechaSemanaAnterior){
			$ingresos_totales += floatval($ingresos['cantidad']);
		}
		$p_fecha = explode("-", $ingresos['fecha']);
		$ing_ano = $p_fecha[0];
		$ing_mes = $p_fecha[1];
		$ing_dia = $p_fecha[2];
		if (isset($_GET['mes'])){
				if($partes_fecha[0]==($ing_mes) && $partes_fecha[1]==($ing_ano)){
					$ganancias[$ing_mes-1][$ing_dia-1]+=floatval($ingresos['cantidad']);
				}
			} elseif(isset($_GET['ano'])){
				if($partes_fecha==$ing_ano){
					$ganancias[$ing_mes-1][$ing_dia-1]+=floatval($ingresos['cantidad']);
				}
			} else {
				$ganancias[$ing_mes-1][$ing_dia-1]+=floatval($ingresos['cantidad']);
			}
	}
	$retiros_extra=$core->db_exect("SELECT cantidad, fecha FROM retiros");
	foreach($retiros_extra as $retiros){
		if($retiros['fecha'] <= $fechaHoy && $retiros['fecha'] >= $fechaSemanaAnterior){
			$retiros_totales += floatval($retiros['cantidad']);
		}
		$p_fecha = explode("-", $retiros['fecha']);
		$gas_ano = $p_fecha[0];
		$gas_mes = $p_fecha[1];
		$gas_dia = $p_fecha[2];
		if (isset($_GET['mes'])){
				if($partes_fecha[0]==($gas_mes) && $partes_fecha[1]==($gas_ano)){
					$ganancias[$gas_mes-1][$gas_dia-1]-=floatval($retiros['cantidad']);
				}
			} elseif(isset($_GET['ano'])){
				if($partes_fecha==$gas_ano){
					$ganancias[$gas_mes-1][$gas_dia-1]-=floatval($retiros['cantidad']);
				}
			} else {
				$ganancias[$gas_mes-1][$gas_dia-1]-=floatval($retiros['cantidad']);
			}
	}
	$i=0;
	foreach($ganancias as $g_mes){
		$ganancias_mes[$i] += array_sum($g_mes);
		$i++;
	}
	$labels=array();
	if(isset($_GET['mes'])){
		$ganancias1 = implode(",", $ganancias[($partes_fecha[0]-1)]);
		for($i=0;$i<=30;$i++){
			$labels[$i] = strval($i+1);
		}
		$labels = implode(",", $labels);
	} elseif(isset($_GET['ano'])) {
		$ganancias1 = implode(",", $ganancias_mes);
		$labels=array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		$labels = implode(",", $labels);
	} else {
		$ganancias1 = implode(",", $ganancias[($partes_fecha[0]-1)]);
		for($i=0;$i<=30;$i++){
			$labels[$i] = strval($i+1);
		}
		$labels = implode(",", $labels);
	}
	$disponible_gen=($ing_neto+$ingresos_totales)-$gastos_totales-$retiros_totales;
	$disponible_gan=($ing_ganancia+$ingresos_totales)-$gastos_totales-$retiros_totales;
	print '<tbody></div><tr><td><b><h2>GANANCIAS:</h2></td><td> <h2>$ '.$ing_ganancia.'</h2></td></b>
	<tr><td><b><h2>GASTOS:</h2></td><td><h2>-$ '.$gastos_totales.'</h2></td></b>
	<tr><td><b><h2>INGRESOS:</h2></td><td><h2>$ '.$ingresos_totales.'</h2></td></b>
	<tr><td><b><h2>RETIROS:</h2></td><td><h2>-$ '.$retiros_totales.'</h2></td></b></div>
	</tbody>
	</table></div>
	<span style="text-align:left;">';
		print "<br/><h1 style='color:#dc6800;text-align:center;'>TOTAL: $ ".$disponible_gan."</h1></div>";
		echo "<br/>";
	echo "<br/><h1 id='i'></h1>";
	echo "<br/><input type='text' id='a' value='".$ganancias1."' hidden>";
	echo "<br/><input type='text' id='b' value='".$labels."' hidden><div style='float:none;clear:both;text-align:center;width:100%;'";
	menu_end();
?>
</body>
</html>