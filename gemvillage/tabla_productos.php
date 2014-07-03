<?php
@session_start();
ob_start();
//define('DEBUG',1);
require_once('framework-master.php');
require_once('db.php');
require_once("menu-maker.php");
?><html>
<head>
<title>Productos</title>
<?php
if(!defined("COMPRA")){
	unset($_SESSION['master_almacen']['id_table_productos']);
}
$cache_detalles_venta=array();
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
$adicional_condicion="";
echo $core->header();
if(isset($_GET['venta_id']) && $core->is_int2($_GET['venta_id'])){
	if(isset($_POST['borrar_de_venta']) && $_POST['borrar_de_venta']==1){
		$base_borrar=new detalles_venta_prod();
		$db_borrarme=$base_borrar->get($_GET['venta_id'],$core);
		$core->db_exect("DELETE FROM amortizacion_cid_multiple where master_id in(select id from amortizacion where venta_id=".$_GET['venta_id'].");");
		$core->db_exect("DELETE FROM amortizacion_precio_venta_multiple where master_id in(select id from amortizacion where venta_id=".$_GET['venta_id'].");");
		if(is_array($db_borrarme) && count($db_borrarme)>0)
		foreach($db_borrarme as $id_noborrado => $val_noborrado){
			if(!in_array($id_noborrado,$_SESSION['master_almacen']['id_table_ver_detalles_venta'])){
				$core->db_exect("INSERT INTO amortizacion_cid_multiple (master_id,cid) values ((select id from amortizacion where venta_id=".$_GET['venta_id']."),'".$id_noborrado."');");
				$core->db_exect("INSERT INTO amortizacion_precio_venta_multiple (master_id,precio_venta) values ((select id from amortizacion where venta_id=".$_GET['venta_id']."),'".$val_noborrado."');");
			}
		}
		unset($_SESSION['master_almacen']['id_table_ver_detalles_venta']);
		print '<script>parent.location.reload();</script>';
	}
	$adicional_condicion="id in (select cid FROM amortizacion_cid_multiple where master_id in(select id from amortizacion where venta_id=".$_GET['venta_id']."))";
	print '
	<script>$(function(){
	$("#toolbar").hide();
	});
</script>';

}

if((isset($_POST['unlock_tabla']) && !isset($_POST['lock_tabla'])) && !defined("COMPRA")){

?>
<script>
$(function(){
	$('[id^=codigobarras_]').keypress(function(event){
		  var intRegex = /^codigobarras_\d+$/;
		  if(intRegex.test($(this).attr('id'))) {
			  if (event.keyCode == 10 || event.keyCode == 13){
				event.preventDefault();
				$(this).parent().parent().parent().next().children('[id^=codigobarras_]').children('.editable_field').children('.master_barcode').focus();
			  }
		  }
	});
	});
</script>
<?php

}
$core->db_exect("UPDATE productos set estado=0 where estado IS NULL;");
$core->db_exect("UPDATE productos set estado=1 where lote in (select id from lotes where estado=1);");
$core->db_exect("UPDATE productos set estado=0 where id not in (SELECT cid FROM amortizacion_cid_multiple where master_id in(select id from amortizacion where venta_id in(select id from cat_tipo_nueva_venta where estado=1))) and estado!=1;");
$core->db_exect("UPDATE productos set estado=2 where id in (SELECT cid FROM amortizacion_cid_multiple where master_id in(select id from amortizacion where venta_id in(select id from cat_tipo_nueva_venta where estado=1)));");
?>

<script>
$(function(){
	$('[id^=precio_origen_]').each(function(){
		id_element=this.id.substring(14);
		dbres4=dbquery(lotes_db,'id',id_element);
		if(typeof dbres4 == 'undefined' || dbres4==null || dbres4['lote']==0){
			$(this).removeAttr('readonly');
		}
	});
    if($('#lote').length){
    	if($('#lote').val()==''){
    		$('#precio_origen').removeAttr('readonly');
    	}
	$('#lote').change(function() {
		if($(this).val()==''){
			$('#precio_origen').removeAttr('readonly');
		}
	});
    }
});
</script>
<?php
if(defined("COMPRA")){
?>
<script>
$(function(){
	<?php
	$count_results=-1;
	if(isset($_POST['masterfiltro_codigobarras']) && $_POST['masterfiltro_codigobarras']!=''){
		$codebarras_string=$core->safestring($_POST['masterfiltro_codigobarras']);
		$code_barras_vals=$core->db_exect("SELECT id from productos where codigobarras='".$codebarras_string."';");
		$count_results=count($code_barras_vals);
		$code_barras_vals=array_pop($code_barras_vals);
		$codebarras=$code_barras_vals['id'];
		if(!isset($_SESSION['master_almacen'])){
			$_SESSION['master_almacen']=array();
		}
		if(!isset($_SESSION['master_almacen']['id_table_productos'])){
			$_SESSION['master_almacen']['id_table_productos']=array();
		}
		if(!in_array($codebarras,$_SESSION['master_almacen']['id_table_productos'])){
			array_push($_SESSION['master_almacen']['id_table_productos'],$codebarras);
		}
	}
	if($count_results!=0){
	?>
    document.getElementById('masterfiltro_codigobarras').value='';
    <?php
    	}
    ?>
    document.getElementById('masterfiltro_codigobarras').focus();
});
</script>
<?php
}
if(isset($_POST['master_task']) && $_POST['master_task']=='eliminar_2'){
	foreach($_POST['cid'] as $ia => $idc){
		if(!$core->is_int2($idc) || $idc==0){
			$core->error("Ataque detectado!");
			exit;
		}
		$core->db_exect("UPDATE productos set estado=1 where id='".$idc."';");
	}
}
if($adicional_condicion=='')
	menu_header();
$loteseleccionado=0;
$rdlotes=array();
$rddlotes=array();
$errores_pendientes='';
function validatelote($lote,$gr=0.00001,$ct=0.00001){
	if($lote==0)
		return true;
	if($GLOBALS['core']->is_int2($lote)){
		$GLOBALS['rdlotes']=$GLOBALS['core']->db_exect("select * from lotes where id='".$lote."' and estado!=1;");
		$GLOBALS['rddlotes']=$GLOBALS['core']->db_exect("select id,SUM(GR) as sum_gr,SUM(CT) as sum_ct from productos where lote='".$lote."' and estado!=1;");
		if(count($GLOBALS['rdlotes'])==0){
			return false;
		}
		if((floatval($gr)+floatval($GLOBALS['rddlotes'][0]['sum_gr']))>floatval($GLOBALS['rdlotes'][0]['GR']) || (floatval($ct)+floatval($GLOBALS['rddlotes'][0]['sum_ct']))>floatval($GLOBALS['rdlotes'][0]['CT'])){
			return false;
		}
		return true;
	}
	return false;
}
if(isset($_GET['lote']) && validatelote($_GET['lote']) && isset($_GET['master_task']) && $_GET['master_task']=='nuevo'){
		$loteseleccionado=1;
} else if(isset($_GET['lote'])){
	$errores_pendientes.="* Lote inválido o ya divido en su totalidad.<br />";
}
if(isset($_POST['GR']) && isset($_POST['CT']) && ($_REQUEST['master_task']!='editar' && isset($_POST['numero']))){
	if(!$core->is_float2($_POST['GR']) || !$core->is_float2($_POST['CT']) || ($_REQUEST['master_task']!='editar' && !$core->is_int2($_POST['numero']))){
		$errores_pendientes.="* Valores inválidos!<br/>";
	} else {
		if($_REQUEST['master_task']=='editar' && isset($_GET['cid']) && $core->is_int2($_GET['cid'][0])){
			$tmp_base=$GLOBALS['core']->db_exect("select id,lote,GR,CT from productos where id='".$_GET['cid'][0]."' and estado!=1;");
			if($_POST['GR']>$tmp_base[0]['GR']){
				if(!validatelote($tmp_base[0]['lote'],$_POST['GR']-$tmp_base[0]['GR'],$_POST['CT']-$tmp_base[0]['CT'])){
					$errores_pendientes.="* No hay suficiente en este lote para este productos<br />";
				}
			}
		
		} else
		if(!validatelote((($loteseleccionado==1) ? $_GET['lote'] : $_POST['lote']),round(floatval($_POST['GR'])*floatval($_POST['numero']),5),$_POST['CT']*$_POST['numero'])){
			$errores_pendientes.="* No hay suficiente en este lote para crear estos productos<br />";
		}
	}
}
?>
<script>
 $(function() {
 $(".checkme").each(function(){
		if($(this).is(":checked")){
			if($("#precio_venta_"+$(this).val()).val() == ''){
				pre_total=truncateDecimals(parseFloat($("#total_"+$(this).val()+"_callback_0").children('div').html().replace(/^[^\d]/mg,"")),2);
				$("#precio_venta_"+$(this).val()).val(pre_total);
				almacen('set','precio_'+$(this).val()+'_'+$(this).closest('form').attr('name'),pre_total);
			}
		}
	});
	 cotizar=function() {
			gasto_valores=$('#gasto').val();
			aumento_total=0;
			if(gasto_valores!=null && typeof gasto_valores !='undefined'){
				for(var i=0;i<gasto_valores.length;i++){
					rest=dbquery(gastos,'id',gasto_valores[i]);
					aumento_total+=parseFloat(rest['aumento']);
				}
			}
			$('#display_real').val(truncateDecimals((parseFloat($('#precio_origen').val())+(parseFloat($('#precio_origen').val())*(aumento_total/100))).toFixed(2),2));
		}
		pesos=function() {
			valor=$(this).val();
			if(valor=='')
				return;
			name=$(this).attr('id');
			<?php
			if(isset($_POST['master_task']) && $_POST['master_task']=='editar' && isset($_POST['cid']) && $core->is_int2($_POST['cid'][0])){
			
			?>
			var idpeso=<?php echo $_POST['cid'][0]; ?>;
			var etiquetaorigen="#precio_origen";
			<?php
			} else {
			?>
			ptm=name.split("_");
			var idpeso=null;
			if(ptm.length>1){
				idpeso=ptm[1];
				name=ptm[0];
			}
			var etiquetaorigen="#precio_origen_"+idpeso;
			name2='';
			por=0;
			<?php } ?>
			var dbres;
			var dbres2;
			var dbres3;
			var dbres4;
			var valor_gramo=0;
			var intentar_cotizar=0;
			if(idpeso!=null){
				dbres4=dbquery(lotes_db,'id',idpeso);
				if(dbres4['lote']!=0){
					dbres=dbquery(disponibles,'id',dbres4['lote']);
					dbres3=dbquery(relacion_pesos,'id',dbres4['lote']);
					dbres2=dbquery(pesos_actuales,'id',idpeso);
					valor_gramo=dbres3['valorgramo'];
				} else {
					valor_gramo=1;
					dbres2={"GR":"0","CT":"0"};
				}
			} else {
				dbres2={"GR":"0","CT":"0"};
				dbres=dbquery(disponibles,'id',$("#lote").val());
				dbres4={"id":"0","lote":$("#lote").val()};
				dbres3=dbquery(relacion_pesos,'id',dbres4['lote']);
				if(typeof dbres3 =='undefined' || dbres3==null){
					valor_gramo=1;
				} else {
					valor_gramo=parseFloat(dbres3['valorgramo']);
				}
				etiquetaorigen="#precio_origen";
				intentar_cotizar=1;
			}
			if(name=='GR'){
				name2='CT';
				por=5;
				<?php
			 	if($loteseleccionado==1){
			 		$libre=($rdlotes[0]['GR']-$rddlotes[0]['sum_gr']);
					print 'if(parseFloat(valor)>parseFloat(\''.$libre.'\')){valor=\''.$libre.'\';$(this).val(\''.$libre.'\');}';
			 	} else {
			 		?>
			 		if(dbres4['lote']!=0){
			 		if(parseFloat(valor)>(parseFloat(dbres['disponible_gr'])+parseFloat(dbres2['GR']))){
			 			valor=truncateDecimals(parseFloat(dbres['disponible_gr'])+parseFloat(dbres2['GR']),2);
			 			$(this).val(valor);
			 		}
			 		}
			 		<?php
			 	}
			 	?>
			 	$(etiquetaorigen).val(truncateDecimals(valor_gramo*parseFloat(valor),2));

			}
				
			else{
				name2='GR';
				por=0.2;
				<?php
				 	if($loteseleccionado==1){
			 		$libre=($rdlotes[0]['CT']-$rddlotes[0]['sum_ct']);
					print 'if(parseFloat(valor)>parseFloat(\''.$libre.'\')){valor=\''.$libre.'\';$(this).val(\''.$libre.'\');}';
				 	} else {
			 		?>
			 		if(dbres4['lote']!=0){
			 		if(parseFloat(valor)>(parseFloat(dbres['disponible_ct'])+parseFloat(dbres2['CT']))){
			 			valor=truncateDecimals(parseFloat(dbres['disponible_ct'])+parseFloat(dbres2['CT']),2);
			 			$(this).val(valor	$("#toolbar").hide(););
			 		}
			 		}
			 		<?php
			 	}
			 	?>
			 	$(etiquetaorigen).val(truncateDecimals(valor_gramo*parseFloat(parseFloat(valor)/5)));
			}
			<?php
			if(!(isset($_POST['master_task']) && $_POST['master_task']=='editar' && isset($_POST['cid']) && $core->is_int2($_POST['cid'][0]))){
			
			?>
			if(idpeso!=null)
				name2+="_"+idpeso;
			$('#'+name2).val(truncateDecimals(valor*por,2));
			if(intentar_cotizar==1)
				cotizar();
			<?php } else { ?>
			$('#'+name2).val(truncateDecimals(valor*por,2));
			cotizar();
			<?php
			}
			?>
		}
	numpeso=function(){
		valor=$(this).val();
		<?php
			if($loteseleccionado==1){
		?>
		libregr=<?php echo '\''.($rdlotes[0]['GR']-$rddlotes[0]['sum_gr']).'\''; ?>;
		librect=<?php echo '\''.($rdlotes[0]['CT']-$rddlotes[0]['sum_ct']).'\''; ?>;
		<?php
			} else {
			?>
		if($('#lote').val()!='' && parseInt($('#lote').val())>0){
			dbres=dbquery(disponibles,'id',$('#lote').val());
			libregr=dbres['disponible_gr'];
			librect=dbres['disponible_ct'];
		}
			<?php
			}
		?>
		roundgr=truncateDecimals(parseFloat(libregr)/parseFloat(valor),5);
		roundct=truncateDecimals(parseFloat(librect)/parseFloat(valor),5);
		if((roundgr*parseFloat(valor))>libregr)
			roundgr=roundgr-0.01;
		if((roundct*parseFloat(valor))>librect)
			roundct=roundct-0.01;

		$('#GR').val(roundgr);
		$('#CT').val(roundct);
	}
	$('#gasto').change(cotizar);
	$('#precio_origen').change(cotizar);
	$('[id^=GR]').change(pesos);
	$('[id^=CT]').change(pesos);
	$('#numero').change(numpeso);
	cotizar();
	setsubtotal=function(){
		newsubtotal=0;
		$("[id^=precio_venta_]").each(function(){
			if($(this).is("input"))
				newsubtotal+=parseFloat($(this).val());
		});
		val_to_set="$"+Number(newsubtotal).formatMoney(2, '.', ',');
		$("#subtotal").html(val_to_set);
		$("#total").html(val_to_set);
	}
	$('[id^=precio_venta_]').change(function() {
		id_element=this.id.substring(13);
		if($("#cid_"+id_element).is(":checked")){
			if($(this).val()==''){
				$(this).focus();
				$(this).addClass('notFilled');
			} else {
				almacen('set','precio_'+id_element+'_'+$(this).closest('form').attr('name'),$(this).val());
				$('#desglose_'+id_element).html("$"+Number($(this).val()).formatMoney(2, '.', ','));
				$(this).removeClass('notFilled');
				setsubtotal();
			}
		}
	});
	unchecked=function(element){
		$('#precio_venta_'+element.val()).removeClass('notFilled');
	}
	validate_check=function(element){
		if($('#precio_venta_'+element.val()).val()==''){
			$('#precio_venta_'+element.val()).focus();
			$('#precio_venta_'+element.val()).addClass('notFilled');
			return true;
		}
		almacen('set','precio_'+element.val()+'_'+$('#precio_venta_'+element.val()).closest('form').attr('name'),$('#precio_venta_'+element.val()).val());
		$('#precio_venta_'+element.val()).removeClass('notFilled');
		return true;
	}
	$('[id^=incremento_]').change(function() {
			if($(this).is("td"))
				return;
			id_element=this.id.substring(11);
			pre_total=0;
			incremento=0;
			if(id_element.match(/^\d+$/)){
			} else {
				id_element=id_element.replace(/^[^\d]+/mg,"").replace(/[^\d]+$/mg,"");
			}
			if($("#incremento_"+id_element).val()=='')
				return;
			incremento=parseFloat($("#incremento_"+id_element).val());
			pre_total=parseFloat($("#total_"+id_element+"_callback_0").children('div').html().replace(/^[^\d]/mg,""));
			
			if($("#incremento_tipo_"+id_element).val()==='1'){
				$("#precio_venta_"+id_element).val(truncateDecimals(parseFloat(pre_total*incremento),2));
				$("#precio_venta_"+id_element).removeClass('notFilled');
			} else if($("#incremento_tipo_"+id_element).val()==='0'){
				$("#precio_venta_"+id_element).val(truncateDecimals(parseFloat(pre_total+((pre_total/100)*incremento)),2));
				$("#precio_venta_"+id_element).removeClass('notFilled');
			}
			almacen('set','precio_'+id_element+'_'+$(this).closest('form').attr('name'),$("#precio_venta_"+id_element).val());
			almacen('set','incremento_'+id_element+'_'+$(this).closest('form').attr('name'),incremento);
			almacen('set','incremento_tipo_'+id_element+'_'+$(this).closest('form').attr('name'),$("#incremento_tipo_"+id_element).val());
	});
});
</script>
<?php
$forms=array();
$campos=array();
$campos["codigobarras"]=array(
	"etiqueta"=>"Código de barras",
	"tipo"=>"barcode",
	"busqueda"=>"=",
	"editable"=>true,
	"filtro"=>FILTRO_BARCODE,
	"default"=>VALOR_DB,
);
$campos["tipo_compra"]=array(
	"etiqueta"=>"Tipo de compra",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"editable"=>true,
	"tabla"=>"cat_tipo_compra",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['tipo_compra']),
);

$campos["lote"]=array(
	"etiqueta"=>"Nombre de Lote",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"tabla"=>"lotes",
	"editable"=>true,
//	"no_tabla"=>true,
	"busqueda"=>"=",
//	"icono"=>"imagen",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"opcional"=>true,
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $_GET['lote']),
);

$campos["nombre"]=array(
	"etiqueta"=>"Nombre del producto",
	"tipo"=>"texto",
	"editable"=>true,
	"busqueda"=>"like",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);
$campos["sku"]=array(
	"etiqueta"=>"SKU del producto",
	"tipo"=>"texto",
	"busqueda"=>"=",
	"no_tabla"=>true,
	"editable"=>false,
	"no_form"=>true,
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
);

if(isset($_REQUEST['master_task']) && $_REQUEST['master_task']!='editar' && isset($_GET['lote'])){
	$campos["numero"]=array(
		"etiqueta"=>"Número de productos a dividir",
		"tipo"=>"html",
		"default"=>'<input type="text" class="master_texto" value="" id="numero" name="numero" />',
	);
}
$campos["t_piedra"]=array(
	"etiqueta"=>"Tipo de piedra",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"editable"=>true,
	"tabla"=>"piedra_tipo",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['tipo_piedra']),
);

$campos["categoria"]=array(
	"etiqueta"=>"Categoría",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"busqueda"=>"=",
	"editable"=>true,
	"no_tabla"=>true,
	"icono"=>"imagen",
	"tabla"=>"categorias",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['categoria']),
);
$campos["imagen"]=array(
	"etiqueta"=>"Imagen",
	"tipo"=>"archivo",
	"editable"=>true,
	"no_tabla"=>true,
	"explorador"=>'multimedia.php',
	"filtro"=>FILTRO_IMAGEN,
	"max"=>200,
);

$campos["estado"]=array(
	"tipo"=>"db",
	"no_tabla"=>true,
	"editable"=>false,
	"filtro"=>FILTRO_INT,
	//"add"=>true,
	"default"=>0,
);

$campos["tallado"]=array(
        "etiqueta"=>"Talle de piedra",
        "tipo"=>"seleccionar",
        "columna_texto"=>"nombre",
        "columna_valor"=>"id",
        "no_tabla"=>true,
       	"editable"=>true,
        "filtro"=>FILTRO_INT,
        "default"=>array(
                array("nombre"=>"Tallado",
                      "id"=>1),
                array("nombre"=>"Bruto",
                      "id"=>0),
        ),
);
if($loteseleccionado!=0) {
	$campos["tallado"]['tipo']='db';
	$campos["tallado"]['default']=$rdlotes[0]['tallado'];
}

$campos["estado_producto"]=array(
	"etiqueta"=>"Estado del producto",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"tabla"=>"estado_producto",
	"columna_texto"=>"nombre",
	"no_tabla"=>true,
	"editable"=>true,
	"icono"=>"icono",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['estado_producto']),
);



$campos["descripcion"]=array(
	"atributos"=>array(
		"rows"=>4,
		"cols"=>50,
	),
	"editable"=>true,
	"etiqueta"=>"Descripción de lote",
	"tipo"=>(($loteseleccionado==0) ? "area" : 'db'),
	"filtro"=>FILTRO_STRING,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['descripcion'])
);

$campos["proveedor"]=array(
	"etiqueta"=>"Proveedor",
	"tipo"=>(($loteseleccionado==0) ? "seleccionar" : 'db'),
	"no_tabla"=>true,
	"editable"=>true,
	"tabla"=>"proveedor",
	"columna_texto"=>"nombre",
	"columna_valor"=>"id",
	"filtro"=>FILTRO_INT,
	"default"=>(($loteseleccionado==0) ? VALOR_DB : $rdlotes[0]['proveedor'])
);

if(isset($_GET['lote']) && $core->is_int2($_GET['lote']) && $_GET['lote']>0){
	$datos_socio=array_pop($core->db_exect("SELECT IFNULL(socio,'') as socio,IFNULL(beneficios,0) as beneficios FROM lotes where id='".$_GET['lote']."' and estado!=1;"));
	$_POST['socio']=$datos_socio['socio'];
	$_POST['beneficios']=$datos_socio['beneficios'];
}
$campos["socio"]=array(
	"etiqueta"=>"Socio",
	"tipo"=>"seleccionar",
	"editable"=>true,
	"tabla"=>"socios",
	"opcional"=>"true",
//	"add"=>true,
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

class total_lote {
	function get_no_money($id,$core){
		$productos=$core->db_exect("select lotes.precio_origen,lotes.id from productos,lotes where lotes.id=productos.lote and productos.id='".$id."' and lotes.estado!=1;");
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
		return round($subtotal+$sumar,2);
	}
	function get($id,$core){
			return money_format('%(n', $this->get_no_money($id,$core));
	}
}
$total_gen=new total_lote();
$lote_pesos_val=array();
class preciopor {
	function get_gr($id,$core){
		global $lote_pesos_val;
		if(!isset($lote_pesos_val[$id])){
			$lote_pesos_val[$id]=array_pop($core->db_exect("select lotes.id,lotes.GR,lotes.precio_origen as lote_precio_origen,productos.GR as producto_GR,lotes.CT,productos.CT as producto_CT from productos,lotes where lotes.id=productos.lote and productos.id='".$id."'"));
			if($lote_pesos_val[$id]['id']==0){
				$lote_pesos_val[$id]['GR']=$lote_pesos_val[$id]['producto_GR'];
				$lote_pesos_val[$id]['CT']=$lote_pesos_val[$id]['producto_CT'];
			}
		}
		return money_format('%(n', round((($lote_pesos_val[$id]['GR']!=0) ? $lote_pesos_val[$id]['lote_precio_origen']/$lote_pesos_val[$id]['GR'] : 0),2));
	}
	function get_ct($id,$core){
		global $lote_pesos_val;
		if(!isset($lote_pesos_val[$id])){
			$lote_pesos_val[$id]=array_pop($core->db_exect("select lotes.id,lotes.GR,lotes.CT from productos,lotes where lotes.id=productos.lote and productos.id='".$id."'"));
			if($lote_pesos_val[$id]['id']==0){
				$lote_pesos_val[$id]['GR']=$lote_pesos_val[$id]['producto_GR'];
				$lote_pesos_val[$id]['CT']=$lote_pesos_val[$id]['producto_CT'];
			}
		}
		return money_format('%(n', round((($lote_pesos_val[$id]['CT']!=0) ? $lote_pesos_val[$id]['lote_precio_origen']/$lote_pesos_val[$id]['CT'] : 0),2));
	}
}

$campos["GR"]=array(
	"etiqueta"=>"GR (Gramos)",
	"tipo"=>"texto",
	"editable"=>true,
	"filtro"=>FILTRO_FLOAT,
	"default"=>VALOR_DB,
);

$campos["CT"]=array(
	"etiqueta"=>"CT (Kilates)",
	"tipo"=>"texto",
	"editable"=>true,
	"filtro"=>FILTRO_FLOAT,
	"default"=>VALOR_DB,
);
$campos["precio_gr"]=array(
	"tipo"=>"callback",
	"etiqueta"=>"Precio/ GR en origen",
	"callback"=>array(new preciopor(),"get_gr"),
);
$campos["precio_ct"]=array(
	"tipo"=>"callback",
	"no_form"=>true,
	"etiqueta"=>"Precio/ CT en origen",
	"callback"=>array(new preciopor(),"get_ct"),
);
$campos["precio_origen"]=array(
	"etiqueta"=>"Precio en origen",
	"tipo"=>(($loteseleccionado==0) ? "texto" : 'db'),
	"filtro"=>FILTRO_FLOAT,
	"money"=>true,
	"editable"=>true,
	"default"=>(($loteseleccionado==0 || !$core->is_int2($_POST['numero']) || $_POST['numero']==0) ? VALOR_DB : round($rdlotes[0]['precio_origen']/$_POST['numero'],5))
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
        "default2"=>1,
);
if(!defined("COMPRA")){
	$campos["coste"]['no_tabla']=true;
	class gastos_callback{
		function get($id,$core){
			$valor_retorno=0;
			$gastos=$core->db_exect("select aumento from gastosadicionales where id in (select gasto from productos_gasto_multiple where master_id='".$id."');");
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
}
class total {
	function get_no_money($id,$core){
		$productos=$core->db_exect("select nombre,precio_origen,id from productos where id='".$id."' and estado!=1;");
		$sumar=0;
		$subtotal=0;
		foreach($productos as $product){
			$gastos=$core->db_exect("select nombre,aumento from gastosadicionales where id in (select gasto from productos_gasto_multiple where master_id='".$id."');");
			$product['precio_origen']=round($product['precio_origen'],5);
			$subtotal+=$product['precio_origen'];
			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$adicional=round($product['precio_origen']*($gasto['aumento'])/100,5);
				$sumar+=$adicional;
			}
		}
		return round($subtotal+$sumar,2);
	}
	function get($id,$core){
		return money_format('%(n', $this->get_no_money($id,$core));
	}
}
$total_makerme=new total();
$cache_total_makerme=array();
class multiplicar {
	public $precio_total=null;
	function x2($id,$core){
		global $total_makerme;
		global $cache_total_makerme;
		if(!isset($cache_total_makerme[$id]))
			$cache_total_makerme[$id]=round($total_makerme->get_no_money($id,$core),2);
		return money_format('%(n', $cache_total_makerme[$id]*2);
	}
	function x3($id,$core){
		global $total_makerme;
		global $cache_total_makerme;
		return money_format('%(n', $cache_total_makerme[$id]*3);
	}
	function x4($id,$core){
		global $total_makerme;
		global $cache_total_makerme;
		return money_format('%(n', $cache_total_makerme[$id]*4);
	}
}


$campos["total"]=array(
	"tipo"=>"callback",
	"etiqueta"=>"Total con gastos",
	"editable"=>true,
	"callback"=>array(new total(),"get"),
);

if(!isset($_GET['venta_id'])){
	$nm=new multiplicar();
	$campos["x2"]=array(
		"tipo"=>"callback",
		"etiqueta"=>"x2",
		"editable"=>true,
		"callback"=>array($nm,"x2"),
	);
	$campos["x3"]=array(
		"tipo"=>"callback",
		"etiqueta"=>"x3",
		"editable"=>true,
		"callback"=>array($nm,"x3"),
	);
	$campos["x4"]=array(
		"tipo"=>"callback",
		"etiqueta"=>"x4",
		"editable"=>true,
		"callback"=>array($nm,"x4"),
	);
}

if($loteseleccionado!=0) {
	$campos["coste"]['tipo']='db';
	$campos["coste"]['default']=$rdlotes[0]['coste'];
}
$cache_detalles_venta=array();
class detalles_venta_prod {
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

class amortizar {
	function get($id,$core){
		$tot=new total();
		$det=new detalles_venta_prod();
		$det_2=$det->get($_GET['venta_id'],$core);
		$tota=$tot->get($id,$core);
		$total=preg_replace("/^[^\d]+/","",$tota);
		return money_format('%(n', round($det_2[$id]-$total,2));
	}
}

$campos["amortizar"]=array(
	"tipo"=>"callback",
	"etiqueta"=>"Amortizar",
	"no_tabla"=>true,
	"editable"=>true,
	"callback"=>array(new amortizar(),"get"),
);
$campos["incremento"]=array(
	"tipo"=>"texto",
	"etiqueta"=>"Incremento",
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
	"no_tabla"=>true,
	"editable"=>true,
	"no_form"=>true,
//	"add"=>true,
);

$campos["incremento_tipo"]=array(
        "tipo"=>"seleccionar",
        "etiqueta"=>"%/x",
        "filtro"=>FILTRO_INT,
        "default"=>array(array("nombre"=>'%',"id"=>0),array("nombre"=>'x',"id"=>1)),
	"no_tabla"=>true,
	"editable"=>SIEMPRE,
        "no_form"=>true,
        "columna_texto"=>"nombre",
        "columna_valor"=>'id',
//        "add"=>true,
);


if(isset($_GET['venta_id'])){
	unset($campos["precio_venta"]);
	class me_precio_venta {
		function get($id,$core){
			$det=new detalles_venta_prod();
			$det_2=$det->get($_GET['venta_id'],$core);
			return money_format('%(n', round($det_2[$id],2));
		}
	}
	$campos["precio_venta"]=array(
		"tipo"=>"callback",
		"etiqueta"=>"Precio de venta",
		"editable"=>true,
		"callback"=>array(new me_precio_venta(),"get"),
	);
}

$campos["precio_venta"]=array(
	"etiqueta"=>"Precio de venta",
	"tipo"=>"texto",
	"editable"=>true,
	"filtro"=>FILTRO_STRING,
	"default"=>VALOR_DB,
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

$lista="0,";
if($adicional_condicion!=''){
	$tabla["editar"]=array();
	$tabla["nuevo"]=array();
	$tabla["eliminar"]=array();
	$tabla["disable_link"]=TRUE;
	$tabla['unlockable']=false;
	unset($campos["nombre"]['busqueda']);
	unset($campos["sku"]['busqueda']);
	unset($campos["lote"]['busqueda']);
	unset($campos["categoria"]['busqueda']);
	unset($campos["codigobarras"]['busqueda']);
	unset($campos["estado"]);
	$campos["amortizar"]['no_tabla']=false;
}
if(!isset($editando))
	$editando=false;
if(defined("COMPRA")){
if($editando && $lista!='')
	unset($campos["estado"]);
$campos["incremento_tipo"]['no_tabla']=true;
$campos["incremento"]['no_tabla']=true;
$campos["incremento"]['editable']=true;
$campos["incremento_tipo"]['editable']=true;
$campos["amortizar"]['no_tabla']=true;
$campos["codigobarras"]["editable"]=true;
$campos["codigobarras"]["atributos"]=array("size"=>"40");
unset($campos["nombre"]['busqueda']);
unset($campos["sku"]['busqueda']);
unset($campos["lote"]['busqueda']);
unset($campos["categoria"]['busqueda']);
if(!isset($_GET['venta_id']))
	$campos["precio_venta"]['no_tabla']=false;
$campos["t_piedra"]['no_tabla']=true;
$campos["categoria"]['no_tabla']=true;
$campos["imagen"]['no_tabla']=true;
$campos["tallado"]['no_tabla']=true;
$campos["x2"]['no_tabla']=true;
$campos["x3"]['no_tabla']=true;
$campos["x4"]['no_tabla']=true;
$campos["estado_producto"]['no_tabla']=true;
$campos["descripcion"]['no_tabla']=true;
$campos["proveedor"]['no_tabla']=true;
//$campos["GR"]['no_tabla']=true;
//$campos["CT"]['no_tabla']=true;
$campos["tipo_compra"]['no_tabla']=true;
$campos["precio_origen"]['no_tabla']=true;
$campos["coste"]['no_tabla']=true;
}
if($loteseleccionado!=0){
	$data3=$core->db_exect("select gasto from lotes_gasto_multiple where master_id='".$_GET['lote']."';");
	$gastosguardar=array();
	foreach($data3 as $dg){
		$gastosguardar[]=$dg['gasto'];
	}
	$campos["gasto"]['tipo']='db';
	$campos["gasto"]['default']=$gastosguardar;
	$_POST["gasto"]=$gastosguardar;
}
$campos["display_real"]=array(
	"tipo"=>"html",
	"etiqueta"=>"Precio total:",
	"default"=>"<input type=\"text\" id=\"display_real\" readonly />",
);
if($loteseleccionado!=0){
	unset($campos["display_real"]);
}

$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
);
$forms["productos"]=array(
		"negritas"=>TRUE,
		"campos"=>$campos,
		"unlockable"=>(defined("COMPRA") ? false : true),
);
$forms2["amortizacion"]=array(
		"campos"=>array(
				"cid"=>array(
					"tipo"=>"texto",
					"filtro"=>FILTRO_INT,
					"multiple"=>true,
					"default"=>VALOR_DB,
					),
				"precio_venta"=>array(
					"tipo"=>"texto",
					"filtro"=>FILTRO_FLOAT,
					"default"=>VALOR_DB,
					"multiple"=>true,
//					"add"=>true
					),
				"fecha"=>array(
					"tipo"=>"db",
					"filtro"=>FILTRO_STRING,
					"default"=>date("Y-m-d H:i:s")
					),
				"enabled"=>array(
					"tipo"=>"db",
					"filtro"=>FILTRO_INT,
					"default"=>1
					),
				"venta_id"=>array(
					"tipo"=>"db",
					"filtro"=>FILTRO_INT,
					"default"=>isset($_SESSION['compra_pendiente']) ? $_SESSION['compra_pendiente'] : null
					),

			),
);
$guardado=0;
if(defined("COMPRA") && isset($_POST['master_task']) && $_POST['master_task']=='carrito'){
	$_POST['cid']=$_SESSION['master_almacen']['id_table_productos'];
	$_POST['precio_venta']=array();
	foreach($_POST['cid'] as $ic_cid => $cid_a){
		if(isset($_SESSION['master_almacen']['precio_'.$cid_a.'_table_productos']))
			$_POST['precio_venta'][$ic_cid]=$_SESSION['master_almacen']['precio_'.$cid_a.'_table_productos'];
	}
	if(isset($_SESSION['compra_pendiente'])){
		$core->db_exect("DELETE FROM amortizacion_cid_multiple where master_id in (SELECT id FROM amortizacion where venta_id='".$_SESSION['compra_pendiente']."');");
		$core->db_exect("DELETE FROM amortizacion_precio_venta_multiple where master_id in (SELECT id FROM amortizacion where venta_id='".$_SESSION['compra_pendiente']."');");
		$core->db_exect("DELETE FROM amortizacion where venta_id='".$_SESSION['compra_pendiente']."';");
		}
	if($core->saveform($forms2)){
		$guardado=1;
		unset($_SESSION['compra_pendiente']);
	}
}
$condicion=null;
if(isset($_POST['master_task']) && $_POST['master_task']=='ver_carrito')
	$condicion='id=0';
if((defined("COMPRA") && (isset($_SESSION['master_almacen']['id_table_productos']) && is_array($_SESSION['master_almacen']['id_table_productos']) && count($_SESSION['master_almacen']['id_table_productos'])>0)) || $guardado==1){

	foreach($_SESSION['master_almacen'] as $key_almacen => $val_almacen){
		if(preg_match("/^precio_(\d+)_table_productos/",$key_almacen,$match_almacen)){
			if($core->is_float2($val_almacen)){
				$core->db_exect("UPDATE productos SET precio_venta='".$val_almacen."' where id='".$match_almacen[1]."';");
			} else {
				$core->error("Precio de venta inválido!");
			}
		}
		if(preg_match("/^incremento_(\d+)_table_productos/",$key_almacen,$match_almacen)){
			if($core->is_float2($val_almacen)){
				$core->db_exect("UPDATE productos SET incremento='".$val_almacen."' where id='".$match_almacen[1]."';");
			} else {
				$core->error("Incremento inválido!");
			}
		}
		if(preg_match("/^incremento_tipo_(\d+)_table_productos/",$key_almacen,$match_almacen)){
			if($core->is_float2($val_almacen)){
				$core->db_exect("UPDATE productos SET incremento_tipo='".$val_almacen."' where id='".$match_almacen[1]."';");
			} else {
				$core->error("Tipo de incremento inválido!");
			}
		}
	}

}
/*if((defined("COMPRA") && ((isset($_POST['master_task']) && ($_POST['master_task']=='ver_carrito' || $_POST['master_task']=='regresar' || $_POST['master_task']=='editar')) || !isset($_POST['master_task'])) && (isset($_SESSION['master_almacen']['id_table_productos']) && is_array($_SESSION['master_almacen']['id_table_productos']) && count($_SESSION['master_almacen']['id_table_productos'])>0)) || $guardado==1){
*/
if((defined("COMPRA") && (isset($_SESSION['master_almacen']['id_table_productos']) && is_array($_SESSION['master_almacen']['id_table_productos']) && count($_SESSION['master_almacen']['id_table_productos'])>0)) || $guardado==1){
	foreach($_SESSION['master_almacen']['id_table_productos'] as $i=>$cid){
		if($core->is_int2($cid) && $cid>0)
		$lista.="'".$cid."',";
	}
}
	$lista=preg_replace("/\,$/","",$lista);
	if($lista!='' && defined("COMPRA")){
		$condicion="";
		if(isset($_POST['masterfiltro_codigobarras']) && $_POST['masterfiltro_codigobarras']!='')
			$condicion.="1=1 or ";
		$condicion.="id in (".$lista.")";
	}
$data=$core->db_exect("select id,nombre,aumento from gastosadicionales;");
$data2=$core->db_exect("select id,GR-(SELECT IFNULL(SUM(GR),0) FROM productos where lote=lotes.id and estado!=1) as disponible_gr,CT-(SELECT IFNULL(SUM(CT),0) FROM productos where lote=lotes.id and estado!=1) as disponible_ct from lotes where estado=0 group by id;");
$data5=$core->db_exect("select id,lote,precio_venta from productos where estado!=1 ".(defined("COMPRA") ? 'and id in ('.$lista.') ' : '')."group by id;");
$data3=$core->db_exect("select id,GR,CT from productos where estado!=1 ".(defined("COMPRA") ? 'and id in ('.$lista.') ' : '')."group by id;");

function get_lote_valorgramo(){
		global $core;
		$returnar=array();
		$lotes=$core->db_exect("select precio_origen,id,GR from lotes;");
		foreach($lotes as $lott){
//			$sumar=0;
			$subtotal=0;
//			$gastos=$core->db_exect("select aumento from gastosadicionales where id in (select gasto from lotes_gasto_multiple where master_id='".$lott['id']."');");
			$lott['precio_origen']=round($lott['precio_origen'],5);
			$subtotal=$lott['precio_origen'];
/*			if(count($gastos)>0)
			foreach($gastos as $gasto){
				$adicional=round($lott['precio_origen']*($gasto['aumento'])/100,5);
				$sumar+=$adicional;
			}
			*/
			$returnar[]=array("id"=>$lott['id'],"valorgramo"=>round(($subtotal)/$lott['GR'],2));
		}
		return $returnar;
	}
$data4=get_lote_valorgramo();
?>
<script>
var gastos=<?php echo $core->array2json($data);?>;
var disponibles=<?php echo $core->array2json($data2);?>;
var pesos_actuales=<?php echo $core->array2json($data3);?>;
var relacion_pesos=<?php echo $core->array2json($data4);?>;
var lotes_db=<?php echo $core->array2json($data5);?>;
</script>
</head>
<body>
<?php
if($adicional_condicion=='')
	menu_start($tipo_usuario);
if($errores_pendientes!=''){
	$core->error($errores_pendientes);
}
$core->createsqltable($forms);
$core->createsqltable($forms2);
$tabla=array(
			"tabla"=>$forms,
			"nuevo"=>array(
				"boton"=>"nuevo.png",
				"etiqueta"=>"Nuevo producto",
				"formulario"=>$forms),
			"editar"=>array(
				"boton"=>"editar.png",
				"etiqueta"=>"Editar producto",
				"formulario"=>$forms),
			"eliminar_2"=>array(
				"boton"=>"eliminar.png",
				'confirmacion'=>"¿Realmente desea eliminar este producto?",
				"etiqueta"=>"Eliminar producto",
				"formulario"=>$forms),
			"unlockable"=>(defined("COMPRA") ? false : true),
		);
if($adicional_condicion!=''){
	$tabla["editar"]=array();
	$tabla["nuevo"]=array();
	$tabla["eliminar"]=array();
	$tabla["eliminar_2"]=array();
	$tabla["disable_link"]=TRUE;
	$tabla['unlockable']=false;
	unset($campos["nombre"]['busqueda']);
	unset($campos["sku"]['busqueda']);
	unset($campos["lote"]['busqueda']);
	unset($campos["categoria"]['busqueda']);
	unset($campos["codigobarras"]['busqueda']);
	unset($campos["estado"]);
}
if(defined("COMPRA")){
	$tabla["multi_select"]=false;
	$tabla["editar"]=array();
	$tabla["nuevo"]=array();
	$tabla["eliminar"]=array();
	$tabla["disable_link"]=TRUE;
/*
	if(!isset($_POST['master_task']) || $_POST['master_task']!='ver_carrito'){
		$tabla["ver_carrito"]=array(
			"boton"=>"car.png",
			"etiqueta"=>"Ver carrito",
			"formulario"=>$forms);
	}
	else{
		$tabla["regresar"]=array(
			"boton"=>"car.png",
			"etiqueta"=>"Seguir comprando",
			"formulario"=>$forms);
	}
	*/
	$tabla["eliminar_2"]=array();
	$tabla["carrito"]=array(
			"boton"=>"car.png",
			"etiqueta"=>"Finalizar Venta",
			"formulario"=>$forms);
	$tabla["cancelar"]=array(
			"boton"=>"eliminar.png",
			"confirmacion"=>"Realmente desea cancelar esta venta?",
			"etiqueta"=>"Cancelar Venta",
			"formulario"=>$forms);
	if($condicion==null){
		$condicion="";
	}else if($lista=='') {
		$condicion.=" and ";
	}
	if($editando && $lista=='')
		$condicion.="(estado=0";
	else if($lista=='') $condicion.="estado=0";
}
if($guardado==0){
	if($loteseleccionado!=0 && isset($_POST['numero']) && $core->is_int2($_POST['numero']) && $_POST['numero']>0){
		$corguardado=0;
		$por_guardar=$_POST['numero'];
		for($inum=0;$inum<$por_guardar;$inum++){
			$tmp_post=$_POST;
			if($core->saveform($tabla["nuevo"]["formulario"],$referencia)){
				$_SESSION['guardado_mensaje']="<span class='ok-txt'><b>Guardado correctamente!</b></span><br />";
				$core->db_exect("UPDATE productos set estado=0 where estado IS NULL;");
				$corguardado=1;
			}
			if($inum+1<$por_guardar)
				$_POST=$tmp_post;
		}
		$core->db_exect("UPDATE productos SET sku=concat(lote,'-',id) where sku='' or sku IS NULL;");
		if($corguardado!=0){
			if(isset($_GET['master_task'])) unset($_GET['master_task']);
			header("HTTP/1.1 302");
			header("Location: ?");
			exit;
		}
	}
	$referencia=array();
	if($condicion!='' && $lista=='')
		$condicion.=" and ";
	if($lista=='')
		$condicion.="lote in (select id from lotes where estado=0)";
	if($editando && $lista=='')
		$condicion.=") or id in (select cid from amortizacion_cid_multiple where master_id in (select id from amortizacion where venta_id='".$_SESSION['compra_pendiente']."'))";
	if($adicional_condicion!=''){
		$condicion=$adicional_condicion;
	}
	$tabla_def=$core->createtable($tabla,null,$condicion,$referencia,true);
	if((isset($_POST['editando']) && $_POST['editando']==1) || $guardado==1 || defined("COMPRA")){
		print preg_replace("/<\/div>$/","",$tabla_def);
	} else print $tabla_def;
	if(!isset($_POST['sku']) && isset($referencia['id'])){
		$retorno_db=array_pop($core->db_exect("SELECT lote FROM productos where id='".$referencia['id']."';"));
		$core->db_exect("UPDATE productos SET sku='".$retorno_db['lote']."-".$referencia['id']."' where id='".$referencia['id']."';");
	}
}
if((isset($_POST['editando']) && $_POST['editando']==1) || $guardado==1 || defined("COMPRA")){
	$subtotal=0;
	$gastos=array();
	if($lista!=''){
		$productos=$core->db_exect("select nombre,id from productos where id in (".$lista.");");
		$subtotal=0;
		echo '<div style="width:90%;display:block;text-align: center;margin-left:5%;margin-right:5%;">
		<p class=\'cart-txt\'><b>Detalles del pedido realizado:</b></p><br />
<br /><div style="width:70%;margin:0 auto;" class="m" id="m2"><table class="tablemaster_2" style="width:100%;"><caption>DESGLOSE</caption><thead><th>Producto</th><th>Precio</th></thead>
			<tbody>';
			$row=0;
		foreach($productos as $product){
			if(!isset($_SESSION['master_almacen']['precio_'.$product['id'].'_table_productos'])){
				foreach($data5 as $dat5){
					if($dat5['id']==$product['id']){
						$_SESSION['master_almacen']['precio_'.$product['id'].'_table_productos']=round($dat5['precio_venta'],5);
						break 1;
						}
				}
			}
			$product['precio_venta']=round($_SESSION['master_almacen']['precio_'.$product['id'].'_table_productos'],5);
?>
				<tr class='row<?php echo ($row==0 ? $row=1 : $row=0);?>'><td><?php echo $product['nombre']; ?></td><td id="desglose_<?php echo $product['id']; ?>"><?php echo money_format('%(n', round($product['precio_venta'],2)); ?></td></tr>
<?php
			$subtotal+=$product['precio_venta'];

		}
		?>
					</tbody>
			</table></div></div>
		<?php
	}
	print "<div class='venta-rest'><table class='venta-tot'><tr><td>Subtotal: </td><td><a id=\"subtotal\">".money_format('%(n', round($subtotal,2))."</a></td></tr><tr><td>Total: </td><td><a id=\"total\">".money_format('%(n', round($subtotal,2))."</a></td></tr></table></div>";
	if($guardado==1) {
		print '<input type="button" value="Imprimir" onclick="this.style.visibility=\'hidden\';document.getElementById(\'regresar\').style.visibility=\'hidden\';window.print();this.style.visibility=\'visible\';document.getElementById(\'regresar\').style.visibility=\'visible\';" class="btn" /><input type="button" value="Ir a administrador de ventas" onclick="document.location=\'tabla_nuevaventa.php\';" id="regresar" class="btn" /><input type="button" value="Ir a Amortizaciones" onclick="document.location=\'panel_control.php\';" id="regresar2" class="btn" />';
		$_SESSION['master_almacen']['id_table_productos']=array();
	}
	print '</div>';
}
if($adicional_condicion=='')
	menu_end();
?>
</body>
</html>
<?php
$salida_master=ob_get_contents();
ob_end_clean();
if(isset($_GET['venta_id']) && $core->is_int2($_GET['venta_id'])){
$salida_master=preg_replace("/<form name=\"table_productos\"/","<form name=\"table_ver_detalles_venta\"",$salida_master);
$salida_master=preg_replace("/<\/form>/","<input type=\"hidden\" name=\"borrar_de_venta\" id=\"borrar_de_venta\" value=\"0\"><input type=\"button\" value=\"Eliminar de la venta\" onclick=\"if(confirm('Realmente desea eliminar estos productos de esta venta?')) {document.getElementById('borrar_de_venta').value=1;this.form.submit();}\" id=\"regresar\" class=\"btn\" /></form>",$salida_master);
}
print $salida_master;
?>
