<?php
@session_start();
ob_start();
//define("DEBUG",1);
?><html>
<head>
<title>Lote</title>
<?php
unset($_SESSION['master_almacen']['id_table_lotes']);
require_once('framework-master.php');
require_once('db.php');
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
if(isset($_POST['master_task']) && $_POST['master_task']=='eliminar_2'){
	foreach($_POST['cid'] as $ia => $idc){
		if(!$core->is_int2($idc) || $idc==0){
			$core->error("Ataque detectado!");
			exit;
		}
		$core->db_exect("UPDATE lotes set estado=1 where id='".$idc."';");
	}
}
$data=$core->db_exect("select id,nombre,aumento from gastosadicionales;");
require_once('menu-maker.php');
menu_header();
?>
<script>
var gastos=<?php echo $core->array2json($data);?>;
$(function() {
	cotizar=function() {
			gasto_valores=$('#gasto').val();
			aumento_total=0;
			if(gasto_valores!=null && typeof gasto_valores !='undefined'){
				for(var i=0;i<gasto_valores.length;i++){
					rest=dbquery(gastos,'id',gasto_valores[i]);
					aumento_total+=parseFloat(rest['aumento']);
				}
			}
			$('#display_real').val((parseFloat($('#precio_origen').val())+(parseFloat($('#precio_origen').val())*(aumento_total/100))).toFixed(2));
		}
	pesos=function() {
		valor=$(this).val();
		name=$(this).attr('id');
		name2='';
		por=0;
		if(name=='GR'){
			name2='CT';
			por=5;
		}
		else{
			name2='GR';
			por=0.2;
		}
		$('#'+name2).val(round2decimal(valor*por));
	}
	$('#GR').change(pesos);
	$('#CT').change(pesos);
	$('#gasto').change(cotizar);
	$('#precio_origen').change(cotizar);
	cotizar();
});
</script>
</head>
<body>
<?php
$forms=array();
$campos=array();
$campos["nombre"]=array(
	"etiqueta"=>"Referencia de lote",
	"tipo"=>"texto",
        "busqueda"=>'like',
	"filtro"=>FILTRO_STRING,
	"editable"=>true,
	"default"=>VALOR_DB,
);
$campos["tipo_piedra"]=array(
	"etiqueta"=>"Tipo de piedra",
	"tipo"=>"seleccionar",
        "busqueda"=>'=',
	"tabla"=>"piedra_tipo",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"editable"=>true,
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);

$campos["categoria"]=array(
	"etiqueta"=>"Categoría",
	"tipo"=>"seleccionar",
        "busqueda"=>'=',
	"tabla"=>"categorias",
	"icono"=>"imagen",
	"editable"=>true,
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["imagen"]=array(
	"etiqueta"=>"Imagen",
	"tipo"=>"archivo",
	"filtro"=>FILTRO_IMAGEN,
	"editable"=>true,
	"explorador"=>'multimedia.php',
	"max"=>200,
);

$campos["tallado"]=array(
        "etiqueta"=>"Talle de piedra",
        "tipo"=>"seleccionar",
        "columna_texto"=>"nombre",
        "columna_valor"=>"id",
        "editable"=>true,
        "filtro"=>FILTRO_INT,
        "busqueda"=>'=',
        "default"=>array(
                array("nombre"=>"Tallado",
                      "id"=>1),
                array("nombre"=>"Bruto",
                      "id"=>0),
        ),
);

$campos["tipo_compra"]=array(
	"etiqueta"=>"Tipo de compra",
	"tipo"=>"seleccionar",
        "busqueda"=>'=',
	"tabla"=>"cat_tipo_compra",
	"columna_texto"=>"nombre",
	"editable"=>true,
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);

$campos["estado"]=array(
	"tipo"=>"db",
	"no_tabla"=>true,
	"editable"=>false,
	"filtro"=>FILTRO_INT,
//	"add"=>true,
	"default"=>0,
);

$campos["estado_producto"]=array(
	"etiqueta"=>"Estado del producto",
	"tipo"=>"seleccionar",
	"icono"=>"icono",
	"tabla"=>"estado_producto",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
        "busqueda"=>'=',
        "editable"=>true,
	"default"=>VALOR_DB,
);
$campos["descripcion"]=array(
	"atributos"=>array(
		"rows"=>4,
		"cols"=>50,
	),
	"etiqueta"=>"Descripción de lote",
	"tipo"=>"area",
	"editable"=>true,
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

$campos["proveedor"]=array(
	"etiqueta"=>"Proveedor",
	"tipo"=>"seleccionar",
	"no_tabla"=>true,
	"tabla"=>"proveedor",
        "busqueda"=>'=',
        "editable"=>true,
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["socio"]=array(
	"etiqueta"=>"Socio",
	"tipo"=>"seleccionar",
	"editable"=>true,
	"tabla"=>"socios",
	"opcional"=>true,
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
//	"add"=>true,
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);

$campos["beneficios"]=array(
	"etiqueta"=>"% Beneficios (Socio)",
	"tipo"=>"texto",
	"opcional"=>true,
	"editable"=>true,
	"percent"=>true,
//	"add"=>true,
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);
$campos["raw_cotizacion"]=array(
	"tipo"=>"html",
	"editable"=>true,
	"filtro"=>FILTRO_FLOAT,
	"default"=>"<h3>Cotización:</h3></td><td>",
);

$campos["GR"]=array(
	"etiqueta"=>"GR (Gramos)",
	"tipo"=>"texto",
	 "busqueda"=>'=',
	"filtro"=>FILTRO_FLOAT,
	"default"=>VALOR_DB,
	"editable"=>true,
);

class total {
	function get_no_money($id,$core){
		$productos=$core->db_exect("select precio_origen,id from lotes where id='".$id."' and estado!=1;");
		$sumar=0;
		$subtotal=0;
		foreach($productos as $product){
			$gastos=$core->db_exect("select nombre,aumento from gastosadicionales where id in (select gasto from lotes_gasto_multiple where master_id='".$id."');");
			$product['precio_origen']=round($product['precio_origen'],5);
			$subtotal+=$product['precio_origen'];
			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$adicional=round($product['precio_origen']*($gasto['aumento'])/100,5);
				$sumar+=$adicional;
			}
		}
		return $subtotal+$sumar;
	}
	function get($id,$core){
			return money_format('%(n', $this->get_no_money($id,$core));
	}
}
$total_gen=new total();
$total_val=array();
$lote_pesos_val=array();
class preciopor {
	function get_gr($id,$core){
//		global $total_val;
		global $lote_pesos_val;
//		global $total_gen;
//		if(!isset($total_val[$id]))
//			$total_val[$id]=$total_gen->get_no_money($id,$core);
		if(!isset($lote_pesos_val[$id]))
			$lote_pesos_val[$id]=array_pop($core->db_exect("select id,GR,CT,precio_origen from lotes where id='".$id."'"));
		return money_format('%(n', $lote_pesos_val[$id]['precio_origen']/$lote_pesos_val[$id]['GR']);
	}
	function get_ct($id,$core){
//		global $total_val;
		global $lote_pesos_val;
//		global $total_gen;
//		if(!isset($total_val[$id]))
//			$total_val[$id]=$total_gen->get_no_money($id,$core);
		if(!isset($lote_pesos_val[$id]))
			$lote_pesos_val[$id]=array_pop($core->db_exect("select id,GR,CT,precio_origen from lotes where id='".$id."'"));
		return money_format('%(n', $lote_pesos_val[$id]['precio_origen']/$lote_pesos_val[$id]['CT']);
	}
}

$campos["precio_gr"]=array(
	"tipo"=>"callback",
	"etiqueta"=>"Precio/GR en origen",
	"callback"=>array(new preciopor(),"get_gr"),
);

$campos["CT"]=array(
	"etiqueta"=>"CT (Kilates)",
	"tipo"=>"texto",
	"busqueda"=>'=',
	"filtro"=>FILTRO_FLOAT,
	"default"=>VALOR_DB,
	"editable"=>true,
);
$campos["precio_ct"]=array(
	"tipo"=>"callback",
	"no_form"=>true,
	"etiqueta"=>"Precio/ CT en origen",
	"callback"=>array(new preciopor(),"get_ct"),
);
$campos["precio_origen"]=array(
	"etiqueta"=>"Precio en origen",
	"tipo"=>"texto",
	"money"=>true,
	"editable"=>true,
	"filtro"=>FILTRO_FLOAT,
	"default"=>VALOR_DB,
);

class gastos_callback{
	function get($id,$core){
		$valor_retorno=0;
		$gastos=$core->db_exect("select aumento from gastosadicionales where id in (select gasto from lotes_gasto_multiple where master_id='".$id."');");
		if(count($gastos)>0)
		foreach($gastos as $gasto){
			$valor_retorno+=$gasto['aumento'];
		}
		return $valor_retorno."%";
	}
}
$campos["gastos_callback"]=array(
"tipo"=>"callback",
"etiqueta"=>"Gasto adicional",
"editable"=>true,
"callback"=>array(new gastos_callback(),"get"),
);
$campos["gasto"]=array(
	"etiqueta"=>"Gasto adicional",
	"tipo"=>"seleccionar",
	"no_tabla"=>true,
	"multiple"=>true,
	"editable"=>true,
	"tabla"=>"gastosadicionales",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>VALOR_DB,
);

$campos["precio_total"]=array(
	"etiqueta"=>"Precio total",
	"tipo"=>"callback",
	"callback"=>array(new total(),"get"),
);
class dividir {
	function dividir_lote($id,$core){
		$productos=$core->db_exect("SELECT id FROM productos where lote='".$id."'");
		if(count($productos)==0){
			return "<a href=\"tabla_productos.php?master_task=nuevo&lote=".$id."\">Dividir</a>";
		} else{
			return count($productos);
		}
	}
}

$campos["coste"]=array(
        "etiqueta"=>"Coste",
        "tipo"=>"seleccionar",
        "columna_texto"=>"nombre",
        "columna_valor"=>"id",
        "no_tabla"=>true,
        "filtro"=>FILTRO_INT,
        "default"=>array(
                array("nombre"=>"CT (Kilates)",
                      "id"=>1),
                array("nombre"=>"GR (Gramos)",
                      "id"=>0),
        ),
        "default2"=>1,
);

$campos["display_real"]=array(
	"tipo"=>"html",
	"etiqueta"=>"Precio total:",
	"default"=>"<input type=\"text\" id=\"display_real\" readonly />",
);
$campos["dividir"]=array(
	"tipo"=>"callback",
	"etiqueta"=>"Productos",
	"callback"=>array(new dividir(),"dividir_lote"),
);

$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["lotes"]=array(
		"titulo"=>"Nueva categoría",
		"negritas"=>TRUE,
		"unlockable"=>true,
		"campos"=>$campos,
);
$core->mensajeguardado="<p class='ok-txt'>Lote guardado correctamente. <a href=\"tabla_productos.php?master_task=nuevo&lote={master-guardado}\">Dividir en productos.</a></p>";
$core->createsqltable($forms);
menu_start();
print $core->createtable(array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=>"Nuevo lote",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar lote",
				"formulario"=>$forms),
			"eliminar_2"=>array(
				'confirmacion'=>"Esta acción eliminará también todos los productos asociados a este lote. ¿Realmente desea continuar?",
				"boton"=>"eliminar.png",
				"etiqueta"=>"Eliminar lote",
				"formulario"=>$forms)
		)
	,null,"estado=0",$datosguardado,true);
menu_end();
?>
</body>
</html>
<?php
$salida_master=ob_get_contents();
ob_end_clean();
print $salida_master;
?>
