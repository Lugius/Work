<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
require_once('menu-maker.php');
?><html>
<head>
<title>Amortización</title>
<script src="Chart.min.js"></script>
<?php
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
if(isset($_POST['reset'])){
	$core->db_exect("UPDATE amortizacion set enabled=0,fecha='".date("m/d/y g:i:s a")."' where enabled=1;");
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
<body onload="createChart();">
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
    $('.month-picker').datepicker( {
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm',
        showButtonPanel: true,
        closeText: 'Filtrar',
        onClose: function(dateText, inst){
            var month = parseInt($("#ui-datepicker-div .ui-datepicker-month :selected").val())+1;
            var year = parseInt($("#ui-datepicker-div .ui-datepicker-year :selected").val());
            if(month<=9){
            	month = '0' + month;
            }
            $(this).datepicker('setDate', new Date(year, month, 1));
            document.location='?mes='+month+"-"+year;
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
	$sql_load="SELECT venta_id, id FROM amortizacion";
	$ventas=$core->db_exect($sql_load);
	echo '<a name="tabla"></a><div class="title-amortizacion"><h3>Panel de control de administraci&oacute;n</div><br /><br />
	<h1 style="float:left;margin-left:5%"><b> Ganancias: </b></h1>
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
			if(mes<10) mes='0'+mes;
			if ($(this).val()=="mes")
				document.location='?'+$(this).val()+'='+mes+'-'+hoy.getFullYear();
			if ($(this).val()=="ano")
				document.location='?'+$(this).val()+'='+hoy.getFullYear();
		});
	});
</script>
<?php
	echo '<br/><br/><canvas id="grafica" width="1200" height="400"></canvas>
		<br/><br/><br/><h4 style="margin-left:10%;"><b>Ventas de los &uacute;ltimos 7 d&iacute;as:</b><h4>
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
			$fechaSemanaAnterior=date("Y-m-d",strtotime("-1 week"));
			$fechaHoy=date("Y-m-d");
			if(isset($fecha_venta[$i_p]['fecha']))
				$fecha_temp=$fecha_venta[$i_p]['fecha'];
			if($fecha_temp <= $fechaHoy && $fecha_temp >= $fechaSemanaAnterior){
				print "<tr><td>".
				$product['nombre']."</td><td>".
				$product['codigobarras']."</td><td>".
				$fecha_temp."</td><td>".
				$prec_total."</td><td>".
				$precios_venta[$i_p]['precio_venta']."</td><td>".
				$ingreso_ganancia."</td><td>".
				"</td></tr>";
				$suma_total+=$prec_total;
				$suma_total_venta+=floatval($precios_venta[$i_p]['precio_venta']);
				$ing_ganancia+=$ingreso_ganancia;
				$ing_bruto+=$prec_total;
				$ing_neto+=floatval($precios_venta[$i_p]['precio_venta']);

			}
			if (isset($fecha_venta[$i_p]['fecha'])){
				$p_fecha = explode("-", $fecha_venta[$i_p]['fecha']);
			}
			if (isset($p_fecha)){
				$temp = (explode(" ", $p_fecha[2]));
				$p_fecha[2]=$temp[0];
			}
			if (isset($_GET['mes'])){
				$partes_fecha = explode("-", $_GET['mes']);
				if(($partes_fecha[0])==$p_fecha[1] && $partes_fecha[1]==($p_fecha[0])){
					$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]+=$ingreso_ganancia;
				}
			} elseif(isset($_GET['ano'])){
				$partes_fecha = $_GET['ano'];
				if($partes_fecha==$p_fecha[0]){
					$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]+=$ingreso_ganancia;
				}
			} else {
				$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]+=$ingreso_ganancia;
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
	$gastos_extra=$core->db_exect("SELECT cantidad, fecha FROM gastos");
	foreach($gastos_extra as $gastos){
		if($gastos['fecha'] <= $fechaHoy && $gastos['fecha'] >= $fechaSemanaAnterior){
			$gastos_totales += floatval($gastos['cantidad']);
		}
		$p_fecha = explode("-", $gastos['fecha']);
		if (isset($_GET['mes'])){
				if($partes_fecha[0]==($p_fecha[1]) && $partes_fecha[1]==($p_fecha[0])){
					$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]-=floatval($gastos['cantidad']);
				}
			} elseif(isset($_GET['ano'])){
				if($partes_fecha==$p_fecha[0]){
					$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]-=floatval($gastos['cantidad']);
				}
			} else {
				$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]-=floatval($gastos['cantidad']);
			}
	}
	$ingresos_extra=$core->db_exect("SELECT cantidad, fecha FROM ingresos");
	foreach($ingresos_extra as $ingresos){
		if($ingresos['fecha'] <= $fechaHoy && $ingresos['fecha'] >= $fechaSemanaAnterior){
			$ingresos_totales += floatval($ingresos['cantidad']);
		}
		$p_fecha = explode("-", $ingresos['fecha']);
		if (isset($_GET['mes'])){
				if($partes_fecha[0]==($p_fecha[1]) && $partes_fecha[1]==($p_fecha[0])){
					$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]+=floatval($ingresos['cantidad']);
				}
			} elseif(isset($_GET['ano'])){
				if($partes_fecha==$p_fecha[0]){
					$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]+=floatval($ingresos['cantidad']);
				}
			} else {
				$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]+=floatval($ingresos['cantidad']);
			}
	}
	$retiros_extra=$core->db_exect("SELECT cantidad, fecha FROM retiros");
	foreach($retiros_extra as $retiros){
		if($retiros['fecha'] <= $fechaHoy && $retiros['fecha'] >= $fechaSemanaAnterior){
			$retiros_totales += floatval($retiros['cantidad']);
		}
		$p_fecha = explode("-", $retiros['fecha']);
		if (isset($_GET['mes'])){
				if($partes_fecha[0]==($p_fecha[1]) && $partes_fecha[1]==($p_fecha[0])){
					$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]-=floatval($retiros['cantidad']);
				}
			} elseif(isset($_GET['ano'])){
				if($partes_fecha==$p_fecha[0]){
					$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]-=floatval($retiros['cantidad']);
				}
			} else {
				$ganancias[$p_fecha[1]-1][$p_fecha[2]-1]-=floatval($retiros['cantidad']);
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
		$labels=["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
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
	print '<tr><td><b>TOTALES:</td><td></td><td></td><td>'.$suma_total.'</td><td>'.$suma_total_venta.'</td><td>'.$ing_ganancia.'</td></b>
	</tbody>
	</table></div>
	<span style="text-align:left;">';
		print "<div style=\"margin-left:10%;margin-right:20%;\"><br /><br />";
		print "<b>Ingreso bruto: </b>".money_format("%n", $ing_bruto)."<br /><br />";
		print "<b>Ingreso neto: </b>".money_format("%n", $ing_neto)."<br /><br />";
		print "<b>Ingreso ganancia: </b>".money_format("%n", $ing_ganancia)."<br /><br />";
		print "<b>Gastos adicionales: </b>".money_format("%n", $gastos_totales)."<br /><br />";
		print "<b>ingresos adicionales: </b>".money_format("%n", $ingresos_totales)."<br /><br />";
		print "<b>Retiros totales: </b>".money_format("%n", $retiros_totales)."<br /><br />";
		print "<b>Disponible general: </b>".money_format("%n", $disponible_gen)."<br /><br />";
		print "<b>Disponible ganancias: </b>".money_format("%n", $disponible_gan)."<br /><br /></span>";
	echo "<br/>";
	echo "<br/><h1 id='i'></h1>";
	echo "<br/><input type='text' id='a' value='".$ganancias1."' hidden>";
	echo "<br/><input type='text' id='b' value='".$labels."' hidden>";
	menu_end();
?>
</body>
</html>