<?php
define("FILTRO_INT",1);
define("FILTRO_STRING",2);
define("FILTRO_HTML",3);
define("FILTRO_FECHA",4);
define("FILTRO_FLOAT",5);
define("FILTRO_BARCODE",6);
define("FILTRO_IMAGEN",7);
define("VALOR_DB",'MASTER IS USING DB VALUE');
class master{
	public $db;
	public $uploads;
	public $max_string=140;
	public $max_int=10;
	public $max_float=10;
	public $max_date=10;
	public $max_area=400;
	public $semilla="-core";
	public $status_error=0;
	public $mensajeguardado='';
	public $tipos=array("texto"=>"text","archivo"=>"file","enviar"=>"submit","fecha"=>"text","barcode"=>"text");
	public function __construct($server,$database,$user,$password) {
		if(!isset($_SESSION)){
			$this->error("Por favor agrega session_start() al inicio de tu archivo.");
			exit;
		}
		$this->db=mysql_connect($server, $user,$password);
		if(!$this->db){
			$this->error("Error conectando con base de datos!");
			exit;
		}
		if (!mysql_select_db($database)) {
			$this->error("Error seleccionando base de datos: ".mysql_error());
			exit;
		}
	}
	function db_exect($sql){
		$resultado = mysql_query($sql);
		if (!$resultado) {
			$this->error("Error de base de datos: ".$sql);
			exit;
		}
		if ($resultado===TRUE || mysql_num_rows($resultado) == 0) {
			return array();
		}
		$filas=array();
		while($fila = mysql_fetch_assoc($resultado)){
			$filas[]=$fila;
		}
		mysql_free_result($resultado);
		return $filas;
	}
	
	function array2json($arr) {
    if(function_exists('json_encode')) return json_encode($arr);
    $parts = array();
    $is_list = false;
    $keys = array_keys($arr);
    $max_length = count($arr)-1;
    if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {
        $is_list = true;
        for($i=0; $i<count($keys); $i++) {
            if($i != $keys[$i]) {
                $is_list = false;
                break;
            }
        }
    }
    foreach($arr as $key=>$value) {
        if(is_array($value)) {
            if($is_list) $parts[] = array2json($value);
            else $parts[] = '"' . $key . '":' . array2json($value);
        } else {
            $str = '';
            if(!$is_list) $str = '"' . $key . '":';
            if(is_numeric($value)) $str .= $value;
            elseif($value === false) $str .= 'false';
            elseif($value === true) $str .= 'true';
            else $str .= '"' . addslashes($value) . '"';
            $parts[] = $str;
        }
    }
    $json = implode(',',$parts);
    if($is_list) return '[' . $json . ']';
    return '{' . $json . '}';
} 

	function is_int2(&$val){
		$val=$val."";
		$val=preg_replace("/^\s+/","",$val);
		$val=preg_replace("/\s+$/","",$val);
		if(preg_match("/^\d+$/",$val))
			return true;
		return false;
	}
	function is_float2(&$val){
		$val=$val."";
		$val=preg_replace("/^\s+/","",$val);
		$val=preg_replace("/\s+$/","",$val);
		if(preg_match("/^\d*(\.\d+)?$/",$val)){
			if(preg_match("/^\./",$val)){
				$val="0".$val;
			}
			return true;
		}
		return false;
	}
	public function set_upload_dir($path){
		$this->uploads=$path;
	}
	public function table_exists($table){
		$sql="Select count(id) as count from ".$table." limit 0,1;";
		if(!mysql_query($sql))
			return false;
		return true;
		
	}
	public function error($error){
		print "<p class='error-txt'>".$error."</p>";
		$this->status_error=1;
		$GLOBALS['status_error']=1;
	}
	public function validatedate($valor){
		if(preg_match("/^(\d{2})\/(\d{2})\/(\d{4})$/",$valor,$matches)){
			$mes=$matches[1];
			$dia=$matches[2];
			$ano=$matches[3];
			return checkdate ($dia,$mes,$ano);
		}
		return false;
	}
	public function createsqltable($forms){
		foreach($forms as $table=>$form){
			$campos=$form["campos"];
			if(!$this->table_exists($table)){
				foreach($campos as $name => $valor){
					if($valor["tipo"]=='archivo' && (!isset($valor['multiple']) || $valor['multiple']!=true)){
						$campos[$name."_path"]=$valor;
					}
				}
				$sql="CREATE TABLE ".$table." (id INT NOT NULL AUTO_INCREMENT,";
				foreach($campos as $name => $valor){
					if($valor['tipo']=='callback'){
							continue;
						}
					if(isset($valor['multiple']) && $valor['multiple']==true){
						unset($valor['multiple']);
						$multiple=array();
						$multiple_campos=array();
						$multiple_campos[$name]=$valor;
						if($valor['tipo']=='archivo'){
							$valor['tipo']='db';
							$multiple_campos[$name."_path"]=$valor;
						}
						$multiple_campos["master_id"]=array(
										"tipo"=>"db",
										"filtro"=>FILTRO_INT,
										);
						$multiple[$table."_".$name."_multiple"]=array(
							"campos"=>$multiple_campos,
						);
						$this->createsqltable($multiple);
						continue;
					}
					if($valor["tipo"]!='enviar' && $valor['tipo']!='html' && $valor['tipo']!='html_hidden')
					$sql.=$name." ".(($valor['filtro']==FILTRO_STRING || $valor['filtro']==FILTRO_FECHA || $valor['filtro']==FILTRO_FLOAT || $valor['filtro']==FILTRO_BARCODE || $valor['filtro']==FILTRO_IMAGEN) ? " varchar (".((!isset($valor["max"]) || !$this->is_int2($valor["max"]) || $valor["max"]==0) ?
							($valor['tipo']=='area' ? $this->max_area : ($valor['tipo']=='fecha' ? $this->max_date : $this->max_string)) : (int)$valor["max"]).
												     ")," : " int(".$this->max_int."),");
				}
				$sql.="PRIMARY KEY (id));";
				if(!mysql_query($sql)){
					$this->error("No se puede crear tabla!");
					exit;
				}
			}
		}
	}
	public function select_valor($valor,$checked){
		$tabla=$valor["tabla"];
		$columna_texto=$valor["columna_texto"];
		$columna_valor=$valor["columna_valor"];
		$condicion=(isset($valor["condicion"]) ? $valor["condicion"] : "");
		$icono=(isset($valor["icono"]) ? $valor["icono"] : "");
		$db=((isset($valor["default"]) && $valor["default"]!=VALOR_DB) ? $valor["default"] : null);
		$multiple=((isset($valor['multiple']) && $valor['multiple']==true) ? true : false);
		if(!is_array($checked))
			$checked=array($checked);
		$checkeds="";
		foreach($checked as $check){
			$checkeds.="'".$check."',";
		}
		$checkeds=preg_replace("/,$/","",$checkeds);
		if($db==null){
			$sql="SELECT ".$columna_texto.",".$columna_valor.(($icono!='') ? ",".$icono.",".$icono."_path" : "")." FROM ".$tabla." WHERE ".$columna_valor." in (".$checkeds.")".(($condicion!='') ? " and ".$condicion : "").";";
			$res=mysql_query($sql);
			if(!$res) { $this->error("Error de base de datos (seleccionar): ".$sql); exit;}
			while($row = mysql_fetch_assoc($res)){
				$db[]=$row;
			}
		}
		if($db!=null && is_array($db) && count($db)>0)
		foreach($db as $i => $row){
			if(in_array($row[$columna_valor],$checked)){
				if($icono!='' && file_exists($row[$icono."_path"])){
					$md5=preg_replace("/^.+?\/([^\/]+)$/","$1",$row[$icono."_path"]);
					$_SESSION['imagenes'][$md5]=array('path'=>$row[$icono."_path"],'name'=>$row[$icono]);
					$return.="<img src=\"image.php?f=".$md5."\" class=\"icono_seleccionar pop\" /><br />";
				}
				$return.=$row[$columna_texto]."<br />";
			}
		}
		return $return;
	}
	public function select_valores($tabla,$columna_texto,$columna_valor,$condicion,$checked,$db=null,$multiple=false){
		$return="";
		if(!is_array($checked))
			$checked=array($checked);
		if($db==null){
			$sql="SELECT ".$columna_texto.",".$columna_valor." FROM ".$tabla.(($condicion!='') ? " WHERE ".$condicion : "").";";
			$res=mysql_query($sql);
			if(!$res) { $this->error("Error de base de datos (seleccionar): ".$sql); exit;}
			while($row = mysql_fetch_assoc($res)){
				$db[]=$row;
			}
		}
		if($multiple!=true)
			$return.="<option value=\"\">- Seleccionar -</option>\n";
		if($db!=null && is_array($db) && count($db)>0)
		foreach($db as $i => $row){
			$return.="<option value=\"".$row[$columna_valor]."\"".((in_array($row[$columna_valor],$checked)) ? " SELECTED" : "").">".$row[$columna_texto]."</option>\n";
		}
		return $return;
	}
	public function header(){
		return '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
		<link rel="stylesheet" href="master.css" />
		<link rel="stylesheet" href="adicional.css" />
		<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
		<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
		<script type="text/javascript" src="jquery.numeric.js"></script>
		<script type="text/javascript" src="jquery.barcode.js"></script>
		<script>
		filter_dates=function(){};
		 $(function() {
		$( ".datepicker" ).datepicker({ dateFormat: "dd/mm/yy", beforeShowDay: filter_dates});
		$( "#dialog" ).dialog({
			autoOpen: false,
			modal: true,
			width:550,
			height:500,
			position: [\'middle\',jQuery(document).scrollTop()]
		});
		$(".checkme").change(function() {
			if($(this).is(":checked"))
				almacen(\'push\',\'id_\'+$(this).closest(\'form\').attr(\'name\'),$(this).val());
			else
				almacen(\'del\',\'id_\'+$(this).closest(\'form\').attr(\'name\'),$(this).val());
		});
		$(".toedit").click(function() {
			document.location="?master_task=editar&cid[]="+($(this).parent().attr(\'id\').substr(13));
		});
		$(".pop")
				.click(function() {
					$("#iframe-display").hide();
					$("#imagen-display").show();
					$("#imagen-display").attr("src",$(this).attr("src"));
					$("#dialog").dialog("open");
				});
		});
		checkall=function(a){
			if(a.form){
				for(var b=0,c=a.form.elements.length;b<c;b++){
					var d=a.form.elements[b];
					if($(d).attr(\'class\')==\'checkme\'){
						d.checked=a.checked;
						if(d.checked){
							almacen(\'push\',\'id_\'+$(d).closest(\'form\').attr(\'name\'),$(d).val());
						} else {
							almacen(\'del\',\'id_\'+$(d).closest(\'form\').attr(\'name\'),$(d).val());
						}
					}
				}
			}
		}
		$(document).ready(function(){
				$(\'.integer\').numeric(false);
				$(\'.float\').numeric();
				$(\'.barcode\').barcode();
			});
		function dbquery(db,column,value){
			for (var i=0;i<db.length;i++){
				if(db[i][column]==value){
					return db[i];
				}
			}
		}
		function rellenar(fuente,db,relaciones,columna){
			resultados=dbquery(db,columna,fuente.value);
			for (elemento in relaciones){
				if(typeof resultados !=\'undefined\')
					nval=resultados[elemento];
				else
					nval=\'\';
				document.getElementById(relaciones[elemento]).value=nval;
			}
		}
		HTTPfactories = [
		    function() { return new XMLHttpRequest(); },
		    function() { return new ActiveXObject("Msxml2.XMLHTTP"); },
		    function() { return new ActiveXObject("Microsoft.XMLHTTP"); }
		];
		TArray = [\'push\',\'set\',\'unshift\',\'del\'];
		function inArray(needle, haystack) {
		    var length = haystack.length;
		    for(var i = 0; i < length; i++) {
			if(haystack[i] == needle) return true;
		    }
		    return false;
		}
		function makexmlhttp(){
			for(var i = 0; i < HTTPfactories.length; i++) {
				try {
					var factory = HTTPfactories[i];
					var request = factory();
					if (request != null) {
						return request;
					}
				}
				catch(e) {
				    continue;
				}
			}
			return null;
		}
		function round2decimal(numero) {
			var original = parseFloat(numero);
			var result = Math.round(original * 100) / 100;
			return result;
		}
		function almacen(action,var_name,val){
			var xhr = makexmlhttp();
			xhr.open(\'POST\', \'almacen.php\', false);
			xhr.setRequestHeader(\'Content-type\', \'application/x-www-form-urlencoded\');
			xhr.send(\'action=\'+encodeURI(action)+\'&var=\'+encodeURI(var_name)+\'&val=\'+encodeURI(val));
			if(xhr.responseText!=\'\' && !inArray(action,TArray)){
				return JSON.parse(xhr.responseText);
			}
			return xhr.responseText;
		}
/*		$(function() {
			$(\'#iframe-display\').load(function() {
		                   innerDoc = (this.contentDocument) ? this.contentDocument : this.contentWindow.document;
		                   if (innerDoc.body.offsetHeight)
		                   {
		                           this.style.height = (innerDoc.body.offsetHeight + 32+32) +\'px\';
		                           this.style.width = (innerDoc.body.offsetWidth + 32+128) +\'px\';
		                   }
		                   else if (this.Document && this.Document.body.scrollHeight){
		                           this.style.height = (this.Document.body.scrollHeight+32)+\'px\';
		                           this.style.width = (this.Document.body.scrollWidth+128)+\'px\';
		                   }
		                   if($("#iframe-display").attr("src")!=\'\')
					$("#dialog").dialog("open");
		       });
               });
               */
               function closedialog(){
	               $("#dialog").dialog("close");
               }
		function explorador(salida,explorador){
			$("#imagen-display").hide();
			$("#iframe-display").show();
			$("#iframe-display").attr("src",explorador+\'?seleccionar&field=\'+salida);
			$("#dialog").dialog("open");
		}
		function seleccionado(md5){
		window.parent.document.getElementById(\''.(isset($_GET['field']) ? htmlentities($_GET['field'],ENT_QUOTES,"UTF-8") : '').'\').value=md5;
		window.parent.document.getElementById(\''.(isset($_GET['field']) ? htmlentities($_GET['field'],ENT_QUOTES,"UTF-8")."_image" : '').'\').src=\'image.php?f=\'+md5;
		window.parent.document.getElementById(\''.(isset($_GET['field']) ? htmlentities($_GET['field'],ENT_QUOTES,"UTF-8")."_image" : '').'\').style.display=\'block\';
		window.parent.closedialog();
		}
		</script>
		';

	}
	public function safestring($string){
		return mysql_real_escape_string(htmlentities($string,ENT_QUOTES,"UTF-8"));
	}
	public function safebarcode($string){
		return preg_replace("/[^\d\w\$\/\+\%\*\.\- ]/","",$string);
	}
	public function createfield($name,$valor,$db=array(),$negritas,$tabla=''){
				if($valor['tipo']=='db' || $valor['tipo']=='callback')
					return;
				if(!isset($db[$name]) && isset($valor['default']) && $valor['default']!=VALOR_DB){
					$db[$name]=$valor['default'];
				}
				if($valor['tipo']=='password')
					$db[$name]='';
				if(isset($valor['multiple']) && $valor['multiple']==true){
					$name.="[]";
				}
				if(isset($_POST[$name]) && $_POST[$name]!=''){
					if(isset($valor["filtro"]) && ($valor["filtro"]==FILTRO_INT || $valor["filtro"]==FILTRO_FLOAT)){
						if(($valor["filtro"]==FILTRO_INT && !$this->is_int2($_POST[$name])) || ($valor["filtro"]==FILTRO_FLOAT && !$this->is_float2($_POST[$name]))){
							$this->error("2. Valores inválidos en el campo: ".htmlentities($valor['etiqueta'],0,"UTF-8")." (".$name.")");
							}
						else
							$db[$name]=$_POST[$name];
						}
					if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_FLOAT){
						if(!$this->is_float2($_POST[$name]))
							$this->error("3. Valores inválidos en el campo: ".htmlentities($valor['etiqueta'],0,"UTF-8")." (".$name.")");
						else
							$db[$name]=$_POST[$name];
						}
					else if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_STRING){
						$db[$name]=$this->safestring($_POST[$name]);
						}
					else if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_BARCODE)
						$db[$name]=$this->safebarcode($_POST[$name]);
					else if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_FECHA){
						if(!$this->validatedate($_POST[$name])){
							if(isset($valor["opcional"]) && $valor["opcional"]==1)
								$_POST[$name]="";
							else
								$this->error("Formato de fecha inválido!");
						}
					}
					else if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_SQL){
					// Validación inecesaria :D
					}
				}
				$negritas_form=((isset($valor["negritas"]) && $valor["negritas"]==true) || !isset($valor["negritas"]) && $negritas);
				$atributos="";
				if(isset($valor["atributos"]) && is_array($valor["atributos"])) foreach($valor["atributos"] as $nt => $val){
					$atributos.=" ".preg_replace("/[^\d\w\_\-]/","",$nt)."=\"".htmlentities($val,ENT_COMPAT,"UTF-8")."\"";
				}
				$campo="";
				if(isset($valor['etiqueta']) && ($valor['tipo']!='submit' && $valor['tipo']!='button'))
					$label=($negritas_form ? "<b>" :"").htmlentities($valor['etiqueta'],0,"UTF-8").($negritas_form ? "</b>" :"");
				else
					$label="";

				if($valor["tipo"]=='html'){
					$campo=$valor["default"];
				}
				else if($valor["tipo"]=='seleccionar'){
					if(!isset($valor["columna_valor"]))
						$valor["columna_valor"]='id';
						if($valor["default"]==VALOR_DB && (!isset($valor["columna_texto"]) || !isset($valor["tabla"])))
						die("El campo de tipo \"seleccionar\" requiere especificar los valores \"tabla\", \"columna_texto\" y \"columna_valor\"");
					if(!isset($db[$name])) $db[$name]="";
					$campo="<select".$atributos." id=\"".htmlentities(preg_replace("/\[\]$/","",$name),ENT_QUOTES,"UTF-8")."\" name=\"".htmlentities($name,ENT_QUOTES,"UTF-8")."\"".((isset($valor['multiple']) && $valor['filtro']==FILTRO_INT && $valor['multiple']==true) ? " multiple" : "").">\n".$this->select_valores(isset($valor["tabla"]) ? $valor["tabla"] : null,$valor["columna_texto"],$valor["columna_valor"],(isset($valor["condicion"]) ? $valor["condicion"] : ""),$db[$name],(isset($valor["default"]) && $valor["default"]!=VALOR_DB) ? $valor["default"] : null,(isset($valor['multiple']) ? $valor['multiple'] : false))."</select>";
				}
				else if($valor["tipo"] =='area'){
					$campo='<textarea'.$atributos.' name="'.htmlentities($name,ENT_QUOTES,"UTF-8").'" id="'.htmlentities($name,ENT_QUOTES,"UTF-8").'" '.($valor['filtro']==FILTRO_INT ? "class=\"integer\"" : $valor['filtro']==FILTRO_FLOAT ? "class=\"float\"" :'').'>'.(isset($db[$name]) ? $db[$name] : "").'</textarea>';
				}
				else if($valor["tipo"] != 'enviar' && $valor["tipo"] != 'html_hidden' && $valor["tipo"]!='boton' && $valor['tipo']!='archivo'){
					$campo=($valor['tipo']=='barcode' ? '<img src="barcode.php" class="pop" id="'.htmlentities($name,ENT_QUOTES,"UTF-8").'_barcodedisplay" width="250" /><br />' : '').
					'<input'.$atributos.' type="'.(isset($this->tipos[$valor["tipo"]]) ? $this->tipos[$valor["tipo"]] : $valor["tipo"]).'" name="'.htmlentities($name,ENT_QUOTES,"UTF-8").'"  id="'.htmlentities($name,ENT_QUOTES,"UTF-8").'" value="'.((isset($db[$name]) && $valor["tipo"]!='archivo') ? $db[$name] : "").'" class="'.preg_replace("/\s+$/","",preg_replace("/^\s+/","",(($valor["tipo"] == 'fecha') ? 'datepicker' : "master_".$valor["tipo"]).(isset($valor['filtro']) ? ($valor['filtro']==FILTRO_INT ? " integer" : ($valor['filtro']==FILTRO_FLOAT ? " float" :'')) : '').($valor['tipo']=='barcode' ? " barcode" : ''))).'" />';
				} else if($valor['tipo']=='archivo'){
					$campo='<input type="file" name="'.htmlentities($name,ENT_QUOTES,"UTF-8").'" id="'.htmlentities($name,ENT_QUOTES,"UTF-8").'" style="display: none;" onchange="'.htmlentities($name,ENT_QUOTES,"UTF-8").'_seleccionar.value='.htmlentities($name,ENT_QUOTES,"UTF-8").'.value;">
<input type="text" name="'.htmlentities($name,ENT_QUOTES,"UTF-8").'_seleccionar" style="width:50%;"><span class="ext-img "><a href="javascript:void(0);" onClick="'.htmlentities($name,ENT_QUOTES,"UTF-8").'.click();">Explorar</a></span>'.(($valor["tipo"]=='archivo' && isset($valor['explorador']) && $valor['explorador']!='') ?
					'<div id="explorador_menu"><input type="hidden" name="'.
					htmlentities($name,ENT_QUOTES,"UTF-8").'_explorador" id="'.
					htmlentities($name,ENT_QUOTES,"UTF-8").'_explorador" value=""/><img src="" class="explorador_preview pop" id="'.
					htmlentities($name,ENT_QUOTES,"UTF-8").'_explorador_image" style="display:none;"/><br /><span class="ext-img"><a href="javascript:explorador(\''.
					htmlentities($name,ENT_QUOTES,"UTF-8").'_explorador\',\''.
					htmlentities($valor['explorador'],ENT_QUOTES,"UTF-8").
					'\');">Usar imagen existente</a></span></div>' : '');
				} else if($valor["tipo"] == 'enviar')
					$campo='<input'.$atributos.' class="btn" type="submit" value="'.htmlentities($valor['etiqueta'],ENT_QUOTES,"UTF-8").'" name="'.htmlentities($name,ENT_QUOTES,"UTF-8").'">';
 				else if($valor["tipo"] == 'boton')
					$campo='<button'.$atributos.'>'.htmlentities($valor['etiqueta'],0,"UTF-8").'</button>';
				return array("campo"=>$campo,"label"=>$label);
	}
	
	
	
	
	
	
	public function createform($forms,$db=null){
		foreach($forms as $table_name=>$form){
			$completados_script="";
			$completados_script2="";
			$editing_form=0;
			if(isset($form["id"]) && $this->is_int2($form['id']) && $form["id"]>0 && ($db==null || !isset($db[$table_name]))){
				$sql_load ="SELECT * FROM ".$table_name." where id='".$form['id']."' limit 0,1";
				$res=mysql_query($sql_load);
				if(!$res) { $this->error("No existe este elemento en la base de datos"); exit;}
				$db[$table_name] = mysql_fetch_assoc($res);
				$editing_form=1;
			}
			if(!isset($db[$table_name]))
				$db[$table_name]=array();
			if(isset($form["negritas"]) && $form["negritas"]==true)
				$negritas=true;
			else
				$negritas=false;
			if(isset($form['id']))
			foreach($form["campos"] as $name => $valor){
				if($editing_form==1 && (!isset($valor['editable']) || $valor['editable']==true))
				if(isset($valor['multiple']) && $valor['multiple']==true){
					$sql_load ="SELECT * FROM ".$table_name."_".$name."_multiple where master_id='".$form['id']."'";
					$res=mysql_query($sql_load);
					if($res){
						while($res_v=mysql_fetch_assoc($res)){
							foreach($res_v as $rnam=>$vnam){
								if($rnam=='master_id')
									continue;
								if(!isset($db[$table_name][$rnam."[]"]) || !is_array($db[$table_name][$rnam."[]"]))
									$db[$table_name][$rnam."[]"]=array();
								$db[$table_name][$rnam."[]"][]=$vnam;
							}
						}
					}
				}
			}
			$return='<div id="dialog"><img src="" style="width:100%;" id="imagen-display"/><iframe src="" style="width:100%;height:100%;display:none;" id="iframe-display"></iframe></div><center><div class="m m1"><form name="form_'.$table_name.'" method="POST" enctype="multipart/form-data" accept-charset="utf-8">';
			foreach($form["campos"] as $name => $valor){
				if($valor["tipo"]=='html_hidden')
					$return.=$valor['default'];
			}	
			$return.='<table class="master_'.$table_name.'" name="master_'.$table_name.'">';
			foreach($form["campos"] as $name => $valor){
				if($valor['tipo']=='db' || ($editing_form==1 && isset($valor['editable']) && $valor['editable']==false))
					continue;
				if((!isset($valor['memoria']) || $valor['memoria']!=false) && $valor['tipo']=='texto'){
					$completado=$this->db_exect("SELECT ".$name." FROM ".$table_name." group by ".$name);
					$valorcompletado="";
					foreach($completado as $vcompletado){
						$valorcompletado.="'".$vcompletado[$name]."',";
					}
					$completados_script.='var completado_'.$name.' = ['.$valorcompletado.'];';
					$completados_script2.='$(function() {$("#'.$name.'").autocomplete({source: completado_'.$name.'});});';
				}

				$datos=$this->createfield($name,$valor,$db[$table_name],$negritas);
				$return.='<tr><td>'.$datos["label"].'</td><td>'.$datos["campo"].'</td></tr>'."\n";
			}
			$return.="</table></form></div></center><script>".$completados_script."$(function() {".$completados_script2."});</script>";
		}
		return $return;

	}
		public function mediaviewer($table,$db=null,$condicion=null,$selectmode=0){
		$forms=$table["tabla"];
		$usermensage="";
		$noseleccion=0;
		if(isset($_REQUEST['master_task']))
		switch($_REQUEST['master_task']){
			case 'editar':
				foreach($forms as $table_name=>$form){
					if($table_name==''){
						$noseleccion=1;
						return;
					}
					foreach($form["campos"] as $name => $valor){
						if(!isset($valor['busqueda']) || $valor['busqueda']!='like' || !isset($_POST['control_'.$table_name.'_'.$name]))
							continue;
						foreach($_POST['control_'.$table_name.'_'.$name] as $ic => $control_id){
							$path="framework-master.php";
							$file_name="";
							if(isset($_FILES["mastermedia_".$table_name.'_'.$name]['tmp_name'][$ic]) && file_exists($_FILES["mastermedia_".$table_name.'_'.$name]['tmp_name'][$ic])){
								if(!preg_match("/\.(jpg|jpeg|png|bmp|gif)$/i",$_FILES["mastermedia_".$table_name.'_'.$name]['name'][$ic])){
									$this->error("Archivo no es imagen!");
									return;
								} else {
									while(file_exists($path)){
										$path=$this->safestring($this->uploads."/".md5($_FILES["mastermedia_".$table_name.'_'.$name]['tmp_name'][$ic].rand(0,1000000).$this->semilla));
									}
									copy($_FILES["mastermedia_".$table_name.'_'.$name]['tmp_name'][$ic],$path);
									$file_name=$this->safestring($_FILES["mastermedia_".$table_name.'_'.$name]['name'][$ic]);
								}
								$this->db_exect("UPDATE ".$table_name." set ".$name."='".$file_name."',".$name."_path='".$path."' where id='".$control_id."';");
							}
						}
					}								
				}
			break;
		}
		foreach($forms as $table_name=>$form){
			if($table_name==''){
				$noseleccion=1;
			}
			$contadorfields=0;
			$filtros="";
			$filtros_enableds=0;
			if($db==null || !isset($db[$table_name])){
				$condicion_tabla=" where ";
				foreach($form["campos"] as $name => $valor){
					if($valor["tipo"]=='db' && isset($valor["default"])){
						$condicion_tabla.=$name."='".$this->safestring($valor['default'])."' and ";
					}
					if(isset($valor['busqueda'])){
						if($valor['tipo']=='seleccionar'){
							$valor['atributos']['onchange']='document.table_'.$table_name.'.submit();';
						} else if($valor['tipo']=='barcode'){
								$valor['tipo']='texto';
						}
					if((!isset($valor['memoria']) || $valor['memoria']!=false) && $valor['tipo']!='seleccionar' && $valor['tipo']!='archivo' && $table_name!=''){
						$completado=$this->db_exect("SELECT ".$name." FROM ".$table_name." group by ".$name);
						$valorcompletado="";
						foreach($completado as $vcompletado){
							$valorcompletado.="'".$vcompletado[$name]."',";
						}
						$completados_script.='var completado_'.$name.' = ['.$valorcompletado.'];';
						$completados_script2.='$(function() {$("#masterfiltro_'.$name.'").autocomplete({source: completado_'.$name.'});});';
					}
						$tmp=$this->createfield("masterfiltro_".$name,$valor,null,1);
						if($filtros_enableds==0){
							$filtros.="<tr>";
						}
						else if($filtros_enableds>=3){
							$filtros.="</tr>";
							$filtros_enableds=0;
						}
						$filtros_enableds++;
						$filtros.="<td class=\"filtro_label\">".$tmp['label'].': </td><td class=\"filtro_campo\">'.$tmp['campo'].'</td></td>';
						if(isset($_POST["masterfiltro_".$name]) && $_POST["masterfiltro_".$name]!='')
						switch($valor['busqueda']){
							case '=':
								$condicion_tabla.=" ".$name."='".$this->safestring($_POST["masterfiltro_".$name])."' and ";
							break;
							case 'like':
								$condicion_tabla.=" ".$name." like '%".$this->safestring($_POST["masterfiltro_".$name])."%' and ";
							break;
						}
					}
				}
				if($condicion!=null)
					$condicion_tabla.=" ".$condicion;
				if($filtros!='')
					$filtros="<table class=\"filtros_busqueda\"><tbody>".$filtros.'</tbody></table> <br /><input type="submit" class="btn" name="buscar" value="Buscar" />';
				$condicion_tabla=preg_replace("/ and $/","",$condicion_tabla);
				if($condicion_tabla==' where ')
					$condicion_tabla="";
				if($table_name!=''){
					$sql_load ="SELECT * FROM ".$table_name.$condicion_tabla.((isset($form["limit"]) && $this->is_int2($form['limit']) && $form["limit"]>0) ? " limit 0,".$form["limit"] : "");
					$res=mysql_query($sql_load);
					if(!$res) { $this->error("No existe este elemento en la base de datos"); exit;}
					while($tmp=mysql_fetch_assoc($res))
					$db[$table_name][] = $tmp;
				}
			}

			if($table_name=='' || !isset($db[$table_name]))
				$db[$table_name]=array();
			$return='<div id="multimedia" class="multimedia"><div id="wrapper-content" class="wrapper-content"><div id="dialog"><img src="" style="width:100%;" id="imagen-display"/><iframe src="" style="width:100%;height:100%;display:none;" id="iframe-display"></iframe></div><form enctype="multipart/form-data" name="table_'.$table_name.'" method="POST" accept-charset="utf-8">'.$filtros.(($table_name!='')
 ? '<br /><br /><div id="m2" class="m" style="width:100%;"><table class="tablemaster" id="tablemaster_mediaviewer" style="width:100%">'."\n".'<tbody>' : '');
			$trrow=0;
			$tienedatos=0;
			$contadorinsertados=0;
			if($table_name!=''){
			foreach($db[$table_name] as $id => $data){
				$class_td="";
				if($trrow==0)
					$trrow=1;
				else
					$trrow=0;
				foreach($form["campos"] as $name => $valor){
						if(!isset($valor['busqueda']) || $valor['busqueda']!='like')
							continue;
						if($contadorinsertado>=3){
							$contadorinsertado=0;
							$return.="</tr>";
						}
						if($contadorinsertado==0){
							$return.="<tr class=\"row".$trrow."\" id=\"tableelement_".$data['id']."\">";
						}
						$md5=preg_replace("/^.+?\/([^\/]+)$/","$1",$data[$name."_path"]);
						if(file_exists($data[$name."_path"])){
							$_SESSION['imagenes'][$md5]=array('path'=>$data[$name."_path"],'name'=>$data[$name]);
							$campo="<img class=\"pop\" src=\"image.php?f=".$md5."\" />";
							$tienedatos=1;
							$return.="\n\t<td style=\"text-align:center;\">";
							$return.='<div class="img_td">'.$campo.'</div><br /><b>Nombre: </b>'.$data[$name]."<br />".($selectmode==0 ? "<b>Reemplazar: </b><input type=\"file\" name=\"mastermedia_".$table_name."_".$name."[".$contadorfields."]\" /><input type=\"hidden\" name=\"control_".$table_name."_".$name."[".$contadorfields."]\" value=\"".$data['id']."\" />" : '<a href="javascript:seleccionado(\''.$md5.'\');">Seleccionar</a>');
							$return.="</td>";
							$contadorinsertado++;
							$contadorfields++;
						}
					}
				}
				if(!preg_match("/<\/tr>$/",$return))
					$return.="</tr>";
				} else {
					$noseleccion=1;
				}
			}
			$return.=(($noseleccion!=1)
 ? (($tienedatos==0 ? "<tr><td>No hay registros</td></tr>" : "")."\n</tbody></table>".(($selectmode==0) ? "<input type=\"submit\" value=\"Guardar todo\" /><input type=\"hidden\" value=\"editar\" name=\"master_task\" id=\"master_task\" />" : '')) : '')."</div></form></div></div><script>".$completados_script."$(function() {".$completados_script2."});</script>";
		return $return;
	}
	public function createtable($table,$db=null,$condicion=null,&$referencia=null,$redirect=false){
		$usermensage="";
		if(isset($_SESSION['guardado_mensaje'])){
			print $_SESSION['guardado_mensaje'];
			unset($_SESSION['guardado_mensaje']);
		}
		if(isset($_REQUEST['master_task']))
		switch($_REQUEST['master_task']){
			case 'nuevo':
				if($this->saveform($table["nuevo"]["formulario"],$referencia)){
					$mensaje_desplegar=(($this->mensajeguardado!='') ? str_replace("{master-guardado}",$referencia['id'],$this->mensajeguardado) : "<p class='ok-txt'><b>Guardado correctamente!</b></p><br />");
					if($redirect==true){
						$_SESSION['guardado_mensaje']=$mensaje_desplegar;
						if(isset($_GET['master_task'])) unset($_GET['master_task']);
						header("HTTP/1.1 302");
						header("Location: ?");
						exit;
					} else {
						echo $mensaje_desplegar;
					}
				} else {
					
					$referer=(isset($_SERVER['HTTP_REFERER']) ? preg_replace("/\?.+$/","",$_SERVER['HTTP_REFERER']) : '');
					if($referer!='' && !preg_match("/\/".preg_replace("/^.+\/([^\/]+)$/","$1",__FILE__)."$/",$referer))
						$regresar="history.back();return false;";
					else
						$regresar="document.getElementById('master_task').value='';";
					foreach($table["nuevo"]["formulario"] as $tname => $v){
						$table["nuevo"]["formulario"][$tname]["campos"]["cancelar"]=array(
						"atributos"=>array("onclick"=>$regresar),
						"tipo"=>"enviar",
						"etiqueta"=>"Regresar"
						);
						$table["nuevo"]["formulario"][$tname]["campos"]["master_task"]=array(
						"tipo"=>"hidden",
						"default"=>$_REQUEST['master_task']
						);
					}
					echo $this->createform($table["nuevo"]["formulario"]);
					return;
				}
			break;
			case 'editar':
				if(!isset($_REQUEST['cid']) || count($_REQUEST['cid'])==0)
					break;
				$cid=$_REQUEST['cid'];
				foreach($cid as $ia => $idc){
					if(!$this->is_int2($idc) || $idc==0){
						$this->error("Ataque detectado!");
						exit;
						}
				}
				$id=$cid[0];
				foreach($table["editar"]["formulario"] as $tname => $v){
					$table["editar"]["formulario"][$tname]["id"]=$id;
				}
				if($this->saveform($table["editar"]["formulario"],$referencia)){
					$t=array_shift($cid);
					if(isset($cid[0]))
						$id=$cid[0];
					print "<p class='ok-txt'><b>Guardado correctamente!</b></p><br />";
					if(isset($_GET['master_task'])) unset($_GET['master_task']);
				}
				if(count($cid)>0){
					$raw="";
					foreach($cid as $io=>$tid){
						$raw.='<input type="hidden" name="cid[]" value="'.$tid.'" />';
					}
					foreach($table["editar"]["formulario"] as $tname => $v){
						$table["editar"]["formulario"][$tname]["campos"]["cancelar"]=array(
						"atributos"=>array("onclick"=>"document.getElementById('master_task').value='';"),
						"tipo"=>"boton",
						"etiqueta"=>"Regresar"
						);
						$table["editar"]["formulario"][$tname]["id"]=$id;
						$table["editar"]["formulario"][$tname]["campos"]["master_task"]=array(
						"tipo"=>"hidden",
						"default"=>$_REQUEST['master_task']
						);
						$table["editar"]["formulario"][$tname]["campos"]["raw"]=array(
						"tipo"=>"html_hidden",
						"default"=>$raw
						);
					}
					echo $this->createform($table["editar"]["formulario"]);
					exit;
				}

			break;
			case 'eliminar':
				if(!isset($_REQUEST['cid']) || count($_REQUEST['cid'])==0)
					break;
				$cid=$_REQUEST['cid'];
				$count=0;
				foreach($table["nuevo"]["formulario"] as $tname => $v)
					foreach($cid as $ia => $idc){
						if(!$this->is_int2($idc) || $idc==0){
							$this->error("Ataque detectado!");
							exit;
							}
						if(!mysql_query("DELETE FROM ".$tname." where id='".$idc."';")){
							$this->error("Error de base de datos! 306");
							exit;
						}
						$count++;
					}
				print "<span class='ok-txt'><b>Se elimin".(($count<2) ? "ó" : "aron")." ".$count." registro".(($count==1) ? "" : "s")." correctamente!</b></span><br />";
			break;
		}
		$forms=$table["tabla"];
		foreach($forms as $table_name=>$form){
			$filtros="";
			$orderby="";
			$filtros_enableds=0;
			foreach($form["campos"] as $name => $valor){
				if(isset($_GET['orderby']) && (isset($_GET['asc']) || isset($_GET['desc']))){
					if($_GET['orderby']==$name){
						$orderby=" order by ".$name." ".(isset($_GET['asc']) ? 'asc' : 'desc');
					}
				}
			}
			if($db==null || !isset($db[$table_name])){
				$condicion_tabla=" where ";
				foreach($form["campos"] as $name => $valor){
					if($valor["tipo"]=='db' && isset($valor["default"])){
						$condicion_tabla.=$name."='".$this->safestring($valor['default'])."' and ";
					}
					if(isset($valor['busqueda'])){
						if($valor['tipo']=='seleccionar'){
							$valor['atributos']['onchange']='document.table_'.$table_name.'.submit();';
						} else if($valor['tipo']=='barcode'){
								$valor['tipo']='texto';
						}
					if((!isset($valor['memoria']) || $valor['memoria']!=false) && $valor['tipo']!='seleccionar' && $valor['tipo']!='archivo'){
						$completado=$this->db_exect("SELECT ".$name." FROM ".$table_name." group by ".$name);
						$valorcompletado="";
						foreach($completado as $vcompletado){
							$valorcompletado.="'".$vcompletado[$name]."',";
						}
						$completados_script.='var completado_'.$name.' = ['.$valorcompletado.'];';
						$completados_script2.='$(function() {$("#masterfiltro_'.$name.'").autocomplete({source: completado_'.$name.'});});';
					}
						$tmp=$this->createfield("masterfiltro_".$name,$valor,null,1);
						if($filtros_enableds==0){
							$filtros.="<tr>";
						}
						else if($filtros_enableds>=4){
							$filtros.="</tr>";
							$filtros_enableds=0;
						}
						$filtros_enableds++;
						$filtros.="<td class=\"filtro_label\">".$tmp['label'].': </td><td class=\"filtro_campo\">'.$tmp['campo'].'</td></td>';
						if(isset($_POST["masterfiltro_".$name]) && $_POST["masterfiltro_".$name]!='')
						switch($valor['busqueda']){
							case '=':
								$condicion_tabla.=" ".$name."='".$this->safestring($_POST["masterfiltro_".$name])."' and ";
							break;
							case 'like':
								$condicion_tabla.=" ".$name." like '%".$this->safestring($_POST["masterfiltro_".$name])."%' and ";
							break;
						}
					}
				}
				if($condicion!=null)
					$condicion_tabla.=" ".$condicion;
				if($filtros!='')
					$filtros="<table class=\"filtros_busqueda\"><tbody>".$filtros.'</tbody></table> <br /><input type="submit" name="buscar" class="btn" value="Buscar" />';
				$condicion_tabla=preg_replace("/ and $/","",$condicion_tabla);
				if($condicion_tabla==' where ')
					$condicion_tabla="";
				$sql_load ="SELECT * FROM ".$table_name.$condicion_tabla.$orderby.((isset($form["limit"]) && $this->is_int2($form['limit']) && $form["limit"]>0) ? " limit 0,".$form["limit"] : "");
				$res=mysql_query($sql_load);
				if(!$res) { $this->error("No existe este elemento en la base de datos"); exit;}
				while($tmp=mysql_fetch_assoc($res))
				$db[$table_name][] = $tmp;
			}
			if(!isset($table['eliminar']['confirmacion'])){
				$table['eliminar']['confirmacion']="";
			}
			$customs="";
			foreach($table as $custname=>$custdata){
				if($custname!="tabla" && $custname!="disable_link"){
					if(isset($custdata['boton']) && isset($custdata['etiqueta'])){
								$customs.='<li><a href="javascript:'.(isset($custdata['confirmacion']) ? 'if(confirm(\''.htmlentities($custdata['confirmacion'],ENT_QUOTES,"UTF-8").'\')) {' : '').'document.getElementById(\'master_task\').value=\''.$custname.'\';document.table_'.$table_name.'.submit();'.(isset($custdata['confirmacion']) ? '}' : '').'"><img src="'.$table[$custname]['boton'].'" />'.htmlentities($table[$custname]['etiqueta'],0,"UTF-8").'</a></li>';

					}
				}
			}
			if(!isset($db[$table_name]))
				$db[$table_name]=array();
			$return='<div id="wrapper-content" class="wrapper-content"><div id="dialog"><img src="" style="width:100%;" id="imagen-display"/><iframe src="" style="width:100%;height:100%;display:none;" id="iframe-display"></iframe></div><form name="table_'.$table_name.'" method="POST" action="?" accept-charset="utf-8"><input type="hidden" id="master_task" name="master_task" />'."\n".'<div class="toolbar" id="toolbar"><div id="m" class="m"><div class="acciones" id="acciones"><ul class="cuadro" id="cuadro">'.$customs.'</ul></div></div></div>'.$filtros
			.'<br /><br /><div id="m2" class="m" style="width:100%;"><table class="tablemaster" id="tablemaster_'.$table_name.'" style="width:100%">'."\n".'<thead>'."\n\t".'<th width="1%">'.((!isset($table['multi_select']) || $table['multi_select']==true) ? '<input type="checkbox" onclick="checkall(this);">' : '').'</th>';
			$trrow=0;
			foreach($form["campos"] as $name => $valor){
				if($valor["tipo"]=='db' || $valor["tipo"]=='password' || $valor['tipo']=='html' || $valor['tipo']=='htm_hidden' || (isset($valor['no_tabla']) && $valor['no_tabla']==true) )
					continue;
				if($valor["tipo"]!='enviar' && $valor['tipo']!='callback'){
					$return.="<th><a class=\"headerlink\" href=\"?orderby=".$name."&".(isset($_GET['desc']) ? 'asc' : "desc")."\">".$valor["etiqueta"]." ".(((isset($_GET['asc']) || isset($_GET['desc'])) && $_GET['orderby']==$name) ? "<img src=\"".(isset($_GET['asc']) ? 'up' : "down").".png\" style=\"width:10px;\" />" : "")."</a></th>";
				} else if($valor['tipo']=='callback'){
					$return.="<th><a class=\"headerlink\" href=\"javascript:void(0);\">".$valor["etiqueta"]."</a></th>";
				}
			}
			$return.="\n</thead>\n<tbody>";
			$tienedatos=0;
			foreach($db[$table_name] as $id => $data){
				$class_td="";
				if(((!isset($table['disable_link']) || $table['disable_link']!=true))){
					$class_td="toedit";
				}
				$return.="\n\t<tr class=\"row".$trrow."\" id=\"tableelement_".$data['id']."\"><td style=\"text-align:center;\"><input type=\"checkbox\" name=\"cid[]\" class=\"checkme\" value=\"".$data['id']."\" ".(isset($_SESSION['master_almacen']["id_table_".$table_name]) && in_array($data['id'],$_SESSION['master_almacen']["id_table_".$table_name]) ? ' checked' : '')."/></td>";
				if($trrow==0)
					$trrow=1;
				else
					$trrow=0;

				foreach($form["campos"] as $name => $valor){
					if($valor["tipo"]=='db' || $valor["tipo"]=='password' || $valor["tipo"]=='html' || (isset($valor['no_tabla']) && $valor['no_tabla']==true))
						continue;
					$campo="";
					if(isset($valor['default']) && $valor['default']!=VALOR_DB && ($valor['tipo']!='seleccionar' || !is_array($valor['default']))){
						$data[$name]=$valor['default'];
					}
					if($valor['tipo']=='callback'){
						if (is_callable($valor['callback'])) {
						    $campo=call_user_func($valor['callback'], $data['id'],$this);
						}
					}
					else if($valor["tipo"]=='seleccionar'){
						if(!isset($valor["columna_valor"]))
							$valor["columna_valor"]='id';
						if($valor["default"]==VALOR_DB && (!isset($valor["columna_texto"]) || !isset($valor["tabla"])))
							die("El campo de tipo \"seleccionar\" requiere especificar los valores \"tabla\", \"columna_texto\" y \"columna_valor\"");
							
						if(!isset($data[$name])) $data[$name]="";
						$campo=$this->select_valor($valor,$data[$name]);
					}
					else if($valor["tipo"] =='area'){
						$campo=(isset($data[$name]) ? $data[$name] : "");
					}
					else if($valor["tipo"] != 'enviar'){
						$campo=((isset($data[$name]) && $valor["tipo"]!='archivo') ? $data[$name]: "");
					}
					if($valor["tipo"] != 'enviar'){
						if($valor["tipo"]=='archivo' && $valor['filtro']==FILTRO_IMAGEN){
							$md5=preg_replace("/^.+?\/([^\/]+)$/","$1",$data[$name."_path"]);
							$_SESSION['imagenes'][$md5]=array('path'=>$data[$name."_path"],'name'=>$data[$name]);
							$campo="<img class=\"pop\" src=\"image.php?f=".$md5."\" />";
						}
						$tienedatos=1;
						$tmp_td=$class_td;
						if(isset($valor['icono']) && $valor['icono']!='')
							$class_td="td_iconos";
						else if($valor["tipo"]=='archivo' && $valor['filtro']==FILTRO_IMAGEN)
								$class_td="";

						$return.='<td '.(($valor["tipo"]=='archivo' && $valor['filtro']==FILTRO_IMAGEN) ? ' class="img_td'.(($class_td!='') ? ' '.$class_td : '').'"': "class=\"".$class_td."\"").'>'.$campo.'</td>';
						$class_td=$tmp_td;
					}
				}
				$return.="</tr>";
			}
			$return.="\n</tbody></table>".($tienedatos==0 ? "No hay registros" : "")."<td></tr></div></form></div><script>".$completados_script."$(function() {".$completados_script2."});</script>";
		}
		return $return;

	}
	public function fin(){
		mysql_close($this->db);
	}
	public function saveform($forms,&$datos=null){
		if($this->status_error==1 || $GLOBALS['status_error']==1){
			$this->error("No puede guardar debido a los errores anteriores");
			return false;
		}
		foreach($forms as $table_name=>$form){
			if(isset($form['id']) && $this->is_int2($form['id']) && $form['id']!=0){
				if(count($this->db_exect("SELECT id FROM ".$table_name." where id='".$form['id']."'"))==0){
					unset($form['id']);
				}
			}
			$update=0;
			if(!isset($form['id']) || !$this->is_int2($form['id']) || $form['id']==0){
				$sql="INSERT into ".$table_name." (";
			} else {
				$update=1;
				$sql="UPDATE ".$table_name." SET ";
			}
			$condiciones="";
			$values="";
			$multiples=array();
			foreach($form["campos"] as $name =>$valor){
				if($valor['tipo']=='callback' || $valor['tipo']=='html' || $valor['tipo']=='html_hidden' || ($update==1 && isset($valor['editable']) && $valor['editable']==false)){
					continue;
				}
				$imgexistente=0;
				if($valor['tipo']=='archivo' && $valor['filtro']==FILTRO_IMAGEN && isset($valor['explorador']) && $valor['explorador']!='' && isset($_POST[$name.'_explorador'])){
					if(!isset($_FILES[$name]['name']) || $_FILES[$name]['name']=='' || !file_exists($_FILES[$name]['tmp_name'])){
						if(preg_match("/^[\w\d]{32}$/",$_POST[$name.'_explorador']) && isset($_SESSION['imagenes'][$_POST[$name.'_explorador']])){
							$imgexistente=$_POST[$name.'_explorador'];
						} else continue;
					}
				}
				if($valor["tipo"]=='db')
					$_POST[$name]=$valor['default'];
				if($valor["tipo"]=='password')
					$_POST[$name]=md5($this->semilla.$_POST[$name]);
				if(!isset($_POST[$name]) && $imgexistente==0 && ($valor["tipo"] != "archivo" || !isset($_FILES[$name]['tmp_name']))){
//					$this->error("Falta el campo: ".$name);
					return;
				}
				if(isset($valor['multiple']) && $valor['multiple']==true){
					$multiples[$name]=$valor;
					continue;
				}
				if($valor["tipo"]!="enviar" && $valor["tipo"]!='html' && $valor['tipo']!='html_hidden'){
					if($update==0){
						$sql.=$name.",";
						$condiciones.=" ".$name."='";
					}
					if($valor["tipo"]=="archivo"){
						$path="framework-master.php";
						$file_name="";
						if(0!==$imgexistente){
							$path=$_SESSION['imagenes'][$imgexistente]['path'];
							$file_name=$_SESSION['imagenes'][$imgexistente]['name'];
						}
						else {
							if(isset($_FILES[$name]['tmp_name']) && file_exists($_FILES[$name]['tmp_name'])){
								if($valor['filtro']== FILTRO_IMAGEN && !preg_match("/\.(jpg|jpeg|png|bmp|gif)$/i",$_FILES[$name]['name'])){
									$this->error("Archivo no es imagen!");
									return;
								} else {
									while(file_exists($path)){
										$path=$this->uploads."/".md5($_FILES[$name]['tmp_name'].rand(0,1000000).$this->semilla);
									}
									copy($_FILES[$name]['tmp_name'],$path);
									$file_name=$_FILES[$name]['name'];
								}
							} else {
								$path="";
							}
						}
						$_POST[$name]=$path;
						if($update==0){
							$sql.=$name."_path,";
							$values.="'".$this->safestring($file_name)."','".$this->safestring($path)."',";
							$condiciones.=$this->safestring($file_name)."' and ".$name."_path='".$this->safestring($path)."' and ";
						}
						else{
							$_POST[$name]=$file_name;
							$sql.=$name."_path='".$this->safestring($path)."', ";
						}
					}
					if($valor["filtro"]==FILTRO_INT || $valor["filtro"]==FILTRO_FLOAT){
						if(($valor["filtro"]==FILTRO_INT && !$this->is_int2($_POST[$name])) || ($valor["filtro"]==FILTRO_FLOAT && !$this->is_float2($_POST[$name]))){
							$this->error("1. Valores inválidos en el campo: ".htmlentities($valor['etiqueta'],0,"UTF-8")." (".$name.")");return;}
						if($update==0){
							$values.="'".$_POST[$name]."',";
							$condiciones.=$_POST[$name]."' and ";
						}
						else
							$sql.=$name."='".$_POST[$name]."', ";
					}
					else if($valor["filtro"]==FILTRO_STRING || $valor["filtro"]==FILTRO_BARCODE){
						if($valor["filtro"]==FILTRO_STRING){
							$_POST[$name]=$this->safestring($_POST[$name]);
						} else
							$_POST[$name]=$this->safebarcode($_POST[$name]);
						if($update==0){
							$condiciones.=$_POST[$name]."' and ";
							$values.="'".$_POST[$name]."',";
						}
						else
							$sql.=$name."='".$_POST[$name]."', ";
					}
					else if($valor["filtro"]==FILTRO_FECHA){
						if(!$this->validatedate($_POST[$name])){
							if(isset($valor["opcional"]) && $valor["opcional"]==1)
								$_POST[$name]="";
							else {
								$this->error("Formato de fecha inválido!");
								return;
								}
						}
						if($update==0){
							$condiciones.=$_POST[$name]."' and ";
							$values.="'".$_POST[$name]."',";
						}
						else
							$sql.=$name."='".$_POST[$name]."', ";
					}
				}
				unset($_POST[$name]);
			}
			$sql=preg_replace("/\,\s*$/","",$sql);
			if($update==0){
				$condiciones=preg_replace("/ and $/","",$condiciones);
				$values=preg_replace("/\,$/","",$values);
				$sql.=") values(".$values.");";
			}
			else
				$sql.=" where id='".$form['id']."';";
			if($this->status_error==1 || $GLOBALS['status_error']==1){
				return false;
			} else if(!mysql_query($sql)){
				$this->error("No se puede guardar los valores: ".mysql_error($this->db)." ".$sql);
				return;
			}
			if($update==0){
				$sql="SELECT id FROM ".$table_name." where ".$condiciones.";";
				$resdb = mysql_query($sql);
				if (!$resdb) {
					$this->error("Error de base de datos: ".mysql_error($this->db));
					return;
				}
				if (mysql_num_rows($resdb) == 0) {
					$this->error("Error de base de datos! 502: SELECT id FROM ".$table_name." where ".$condiciones.";");
				    return;
				}
				while ($fila = mysql_fetch_assoc($resdb)) {
					$datos[]=$fila;
				}
				$datos=array_pop($datos);
			} else {
				$datos=array("id"=>$form['id']);
			}
			foreach($multiples as $name =>$valor_multiple){
				if($valor_multiple['tipo']!='archivo' && isset($_POST[$name])){
					$vals_multiple=$_POST[$name];
					unset($_POST[$name]);
				}
				else if($valor_multiple['tipo']=='archivo' && isset($_FILES[$name])){
					$vals_multiple=$_FILES[$name];
					unset($_FILES[$name]);
				}
				if(isset($valor_multiple['multiple']))
					unset($valor_multiple['multiple']);
				if(!is_array($vals_multiple)){
					$vals_multiple=array($vals_multiple);
				}
				$multipe=array();
				$multiple_campos=array();
				$multiple_campos[$name]=$valor_multiple;
				$multiple_campos['master_id']=array(
					"tipo"=>"db",
					"filtro"=>FILTRO_INT,
					"default"=>$datos['id']
				);
				$multiple[$table_name."_".$name."_multiple"]=array(
					"campos"=>$multiple_campos,
				);
				$resdb = mysql_query("DELETE FROM ".$table_name."_".$name."_multiple where master_id='".$datos['id']."';");
				foreach($vals_multiple as $null =>$val_multiple){
					if($valor_multiple['tipo']=='archivo'){
						$_FILES[$name]=$val_multiple;
					} else {
						$multiple[$table_name."_".$name."_multiple"]['campos'][$name]['default']=$val_multiple;
						$_POST[$name]=$val_multiple;
					}
					$this->saveform($multiple);
				}

			}
			unset($_REQUEST['master_task']);
			return true;
		}
	}

}
?>
