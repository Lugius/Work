<?php
if(isset($_REQUEST['guardar_tabla'])){
	$GLOBALS['guardar_tabla']='guardar_tabla';
}

ini_set('memory_limit', '128M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

define("FILTRO_INT",1);
define("FILTRO_STRING",2);
define("FILTRO_HTML",3);
define("FILTRO_FECHA",4);
define("FILTRO_FLOAT",5);
define("FILTRO_BARCODE",6);
define("FILTRO_IMAGEN",7);
define("FILTRO_EMAIL",8);
define("SIEMPRE",2);
define("VALOR_DB",'MASTER IS USING DB VALUE');
define("PER_PAGE",(!isset($_POST['paginador_select']) || !preg_match("/^(50|100|200|500)$/",$_POST['paginador_select']) ? 500 : $_POST['paginador_select']));
define("SIZE_PAG",10);
$cache_select_valores=array();
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
		setlocale(LC_MONETARY, 'en_US');
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
	function to_float($val){
		if($this->is_int2($val)){
			return $val.".00";
		} else {
			if(preg_match("/\.\d{6}/",$val)){
				$val+=0.00001;
				$val=preg_replace("/(\.\d{5}).+$/","$1",$val);
			}
			return round($val,5);
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
			$multiples_count=0;
			$campos=$form["campos"];
			$add=0;
			$tmpcampos=array();
			foreach($campos as $name => $valor){
				if(isset($valor['add']) && $valor['add']==true){
					$add++;
					$tmpcampos[$name]=$valor;
				}
			}
			if($add>0){
				$campos=$tmpcampos;
			}
			$table_exists=$this->table_exists($table);
			if(!$table_exists || $add>0){
				if(!$table_exists){
					$add=0;
				}
				foreach($campos as $name => $valor){
					if($valor["tipo"]=='archivo' && (!isset($valor['multiple']) || $valor['multiple']!=true)){
						$campos[$name."_path"]=$valor;
					}
				}
				if($add>0){
					$sql="ALTER TABLE ".$table." ADD (";
				} else
					$sql="CREATE TABLE ".$table." (id INT NOT NULL AUTO_INCREMENT,";
				foreach($campos as $name => $valor){
					if($valor['tipo']=='callback' || ($add>0 && (!isset($valor['add']) || $valor['add']==false))){
							continue;
						}
					if(isset($valor['multiple']) && $valor['multiple']==true){
						unset($valor['multiple']);
						$multiples_count++;
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
					$sql.=$name." ".(($valor['filtro']==FILTRO_EMAIL || $valor['filtro']==FILTRO_STRING || $valor['filtro']==FILTRO_FECHA || $valor['filtro']==FILTRO_FLOAT || $valor['filtro']==FILTRO_BARCODE || $valor['filtro']==FILTRO_IMAGEN) ? " varchar (".((!isset($valor["max"]) || !$this->is_int2($valor["max"]) || $valor["max"]==0) ?
							($valor['tipo']=='area' ? $this->max_area : ($valor['tipo']=='fecha' ? $this->max_date : $this->max_string)) : (int)$valor["max"]).
												     ")," : " int(".$this->max_int."),");
				}
				if($add>0){
					$sql=preg_replace("/\,$/","",$sql);
					$sql.=");";
				} else
					$sql.="PRIMARY KEY (id));";
				if(!mysql_query($sql) && $multiples_count==0){
					$this->error("No se puede crear tabla! Error: ".$sql);
					exit;
				}
			}
		}
	}
	public function select_valor($valor,$checked){
		$columna_texto=$valor["columna_texto"];
		$columna_valor=$valor["columna_valor"];
		$condicion=(isset($valor["condicion"]) ? $valor["condicion"] : "");
		$icono=(isset($valor["icono"]) ? $valor["icono"] : "");
		$db=((isset($valor["default"]) && $valor["default"]!=VALOR_DB) ? $valor["default"] : null);
		if($db==null && !isset($valor["tabla"])){
			$this->error("El campo de tipo \"seleccionar\" necesita especificar el atributo \"tabla\" o en su lugar el atributo \"db\"");
			return;
		}
		if($db==null)
			$tabla=$valor["tabla"];
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
		$return="";
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
		if(defined('DEBUG'))
			print "Checked: ".$checked."<br />";
		global $cache_select_valores;
		if(!is_array($checked))
			$checked=array($checked);
		if($db==null){
			$sql="SELECT ".$columna_texto.",".$columna_valor." FROM ".$tabla.(($condicion!='') ? " WHERE ".$condicion : "")." order by ".$columna_texto." ASC;";
			if(!isset($cache_select_valores[$sql])){
				$res=mysql_query($sql);
				if(!$res) { $this->error("Error de base de datos (seleccionar): ".$sql); exit;}
				while($row = mysql_fetch_assoc($res)){
					$db[]=$row;
				}
				$cache_select_valores[$sql]=$db;
			} else {
				$db=$cache_select_valores[$sql];
			}
		} else {
			$tmp_sort=array();
			foreach($db as $indice => $valores){
				$tmp_sort[$valores[$columna_valor]]=$valores[$columna_texto];
			}
			asort($tmp_sort);
			$i_srt_db=0;
			foreach($tmp_sort as $tmp_asort_key=>$tmp_asort_value){
				$db[$i_srt_db]=array($columna_valor=>$tmp_asort_key,$columna_texto=>$tmp_asort_value);
				$i_srt_db++;
			}
		}
		if($multiple!=true)
			$return.="<option value=\"\">- Seleccionar -</option>\n";
		if($db!=null && is_array($db) && count($db)>0)
		foreach($db as $i => $row){
			$return.="<option value=\"".$row[$columna_valor]."\"".(((count($checked)>1 || $checked[0]!='') && in_array($row[$columna_valor],$checked)) ? " SELECTED" : "").">".$row[$columna_texto]."</option>\n";
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
    <script type="text/javascript">
        function UpdateTableHeaders() {
            $("div.divTableWithFloatingHeader").each(function() {
                var originalHeaderRow = $(".tableFloatingHeaderOriginal", this);
                var floatingHeaderRow = $(".tableFloatingHeader", this);
                var offset = $(this).offset();
                var scrollTop = $(window).scrollTop();
                if ((scrollTop > offset.top) && (scrollTop < offset.top + $(this).height())) {
                    floatingHeaderRow.css("visibility", "visible");
                    floatingHeaderRow.css("top", Math.min(scrollTop - offset.top, $(this).height() - floatingHeaderRow.height()) + "px");

                    // Copy cell widths from original header
                    $("th", floatingHeaderRow).each(function(index) {
                        var cellWidth = $("th", originalHeaderRow).eq(index).css(\'width\');
                        $(this).css(\'width\', cellWidth);
                    });

                    // Copy row width from whole table
                    floatingHeaderRow.css("width", $(this).css("width"));
                }
                else {
                    floatingHeaderRow.css("visibility", "hidden");
                    floatingHeaderRow.css("top", "0px");
                }
            });
        }

        $(document).ready(function() {
            $(".tablemaster").each(function() {
                $(this).wrap("<div class=\"divTableWithFloatingHeader\" style=\"position:relative;width:"+$("#m2").width()+"px;\"></div>");

                var originalHeaderRow = $("tr:first", this)
                originalHeaderRow.before(originalHeaderRow.clone());
                var clonedHeaderRow = $("tr:first", this)

                clonedHeaderRow.addClass("tableFloatingHeader");
                clonedHeaderRow.addClass("tablemaster");

                clonedHeaderRow.css("position", "absolute");
                clonedHeaderRow.css("top", "0px");
                clonedHeaderRow.css("left", $(this).css("margin-left"));
                clonedHeaderRow.css("visibility", "hidden");

                originalHeaderRow.addClass("tableFloatingHeaderOriginal");
            });
            UpdateTableHeaders();
            $(window).scroll(UpdateTableHeaders);
            $(window).resize(UpdateTableHeaders);
        });
    </script>
		<style>
		  .notFilled{
		    border: 2px solid #f00;
		    background: #f99;
		  }
		</style>		
		<script>
		filter_dates=function(){return [1,"cal_no_padding"];};
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
			if($(this).is(":checked")){
				if(validate_check($(this))){
					almacen(\'push\',\'id_\'+$(this).closest(\'form\').attr(\'name\'),$(this).val());
				} else
					$(this).prop(\'checked\', false);
			} else {
				unchecked($(this));
				almacen(\'del\',\'id_\'+$(this).closest(\'form\').attr(\'name\'),$(this).val());
				}
		});
		$(".toedit").click(function() {
			document.location="?master_task=editar&cid[]="+($(this).parent().attr(\'id\').substr(13));
		});
		$(\'.changable\').click(function() {
/*			valor=$(this).html();
			indice1=$(this).attr(\'id\').lastIndexOf("_");
			resto_id=$(this).attr(\'id\').substring(0,indice1);
			filtrocampo=$(this).attr(\'id\').substring(indice1+1);
			indice1=resto_id.lastIndexOf("_");
			resto_id=resto_id.substring(0,indice1);
			tipocampo=resto_id.substring(indice1+1);
			alert(\'Filtro: \'+filtrocampo+\' Tipo: \'+tipocampo);
			$(this).html(\'<input type="text" name="\'+$(this).attr(\'id\')+\'_editado" id="\'+$(this).attr(\'id\')+\'_editado" />\');
			$("#"+$(this).attr(\'id\')+\'_editado\').val(valor);
			
			*/
			$(this).unbind("click");
		});
		$(".changable").change(function() {
//			alert(\'changado\');
		});
		$(\'#editor_form > tbody > tr > td\').children(\'input:text\').keypress(function(event){
		    if (event.keyCode == 10 || event.keyCode == 13) 
			event.preventDefault();
		  });
		$(\'.editable_field\').children(\'input:text\').keypress(function(event){
		    if (event.keyCode == 10 || event.keyCode == 13) 
			event.preventDefault();

		  });
		$(".pop")
				.click(function() {
					$("#iframe-display").hide();
					$("#imagen-display").show();
					$("#imagen-display").attr("src",$(this).attr("src"));
					$("#dialog").dialog("open");
				});
		});
		validate_check=function(element){
			return true;
		}
		unchecked=function(element){
		
		}
		checkall=function(a){
			if(a.form){
				$(\'#check_master\').attr(\'checked\', a.checked);
				for(var b=0,c=a.form.elements.length;b<c;b++){
					var d=a.form.elements[b];
					if($(d).attr(\'class\')==\'checkme\'){
						d.checked=a.checked;
						if(d.checked){
							
							if(validate_check($(d))){
								almacen(\'push\',\'id_\'+$(d).closest(\'form\').attr(\'name\'),$(d).val());
							} else
								d.checked=0;
						} else {
							almacen(\'del\',\'id_\'+$(d).closest(\'form\').attr(\'name\'),$(d).val());
							unchecked($(d));
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
		Number.prototype.formatMoney = function(c, d, t){
		var n = this, 
		    c = isNaN(c = Math.abs(c)) ? 2 : c, 
		    d = d == undefined ? "." : d, 
		    t = t == undefined ? "," : t, 
		    s = n < 0 ? "-" : "", 
		    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
		    j = (j = i.length) > 3 ? j % 3 : 0;
		   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
		 };
		function round2decimal(numero) {
			var original = parseFloat(numero);
			var result = Math.round(original * 100000) / 100000;
			return result;
		}
		function truncateDecimals(numToTruncate, intDecimalPlaces) {    
			return round2decimal(numToTruncate);
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
		paginador = function(page){
			$("#master_page").val(page);
			$("#master_page").closest("form").submit();
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
		if(isset($_REQUEST['guardar_tabla'])){
			$GLOBALS['guardar_tabla']='guardar_tabla';
			}

	}
	public function safestring($string){
		return mysql_real_escape_string(htmlentities($string,ENT_QUOTES,"UTF-8"));
	}
	public function safebarcode($string){
		return preg_replace("/[^\d\w\$\/\+\%\*\.\- ]/","",$string);
	}
	public function createfield($name,$valor,$db=array(),$negritas,$tabla='',$subfijo=''){
				if(defined("DEBUG"))
					print "Creando campo: ".$name." de tipo (".$valor['tipo'].")<br />";
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
							$db[$name]=$this->to_float($_POST[$name]);
						}
					if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_FLOAT){
						if(!$this->is_float2($_POST[$name]))
							$this->error("3. Valores inválidos en el campo: ".htmlentities($valor['etiqueta'],0,"UTF-8")." (".$name.")");
						else
							$db[$name]=$this->to_float($_POST[$name]);
						}
					else if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_STRING){
						$db[$name]=$this->safestring($_POST[$name]);
						}
					else if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_EMAIL){
						if(!preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/",$_POST[$name])) $this->error("E-Mail inválido!"); else $db[$name]=$this->safestring($_POST[$name]);
						}
					else if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_BARCODE)
						$db[$name]=$this->safebarcode($_POST[$name]);
					else if(isset($valor["filtro"]) && $valor["filtro"]==FILTRO_FECHA){
						if(!$this->validatedate($_POST[$name])){
							if(isset($valor["opcional"]) && $valor["opcional"]==true)
								$_POST[$name]="";
							else
								$this->error("Formato de fecha inválido!");
						}
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
					$campo="<select".$atributos." id=\"".htmlentities(preg_replace("/\[\]$/","",$name.$subfijo),ENT_QUOTES,"UTF-8")."\" name=\"".htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8")."\"".((isset($valor['multiple']) && $valor['filtro']==FILTRO_INT && $valor['multiple']==true) ? " multiple" : "").">\n".$this->select_valores(isset($valor["tabla"]) ? $valor["tabla"] : null,$valor["columna_texto"],$valor["columna_valor"],(isset($valor["condicion"]) ? $valor["condicion"] : ""),(isset($_POST[$name]) ? $_POST[$name] : (((!isset($db[$name]) || is_array($db[$name])) && isset($valor['default2'])) ? $valor['default2'] : $db[$name])),(isset($valor["default"]) && $valor["default"]!=VALOR_DB) ? $valor["default"] : null,(isset($valor['multiple']) ? $valor['multiple'] : false))."</select>";
				}
				else if($valor["tipo"] =='area'){
					$campo='<textarea'.$atributos.' name="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'" id="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'" '.($valor['filtro']==FILTRO_INT ? "class=\"integer\"" : $valor['filtro']==FILTRO_FLOAT ? "class=\"float\"" :'').'>'.(isset($db[$name]) ? $db[$name] : "").'</textarea>';
				}
				else if($valor["tipo"] != 'enviar' && $valor["tipo"] != 'html_hidden' && $valor["tipo"]!='boton' && $valor['tipo']!='archivo'){
					$campo=($valor['tipo']=='barcode' ? '<img src="barcode.php" class="pop" id="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'_barcodedisplay" width="250" /><br />' : '').
					'<input'.$atributos.' type="'.(isset($this->tipos[$valor["tipo"]]) ? $this->tipos[$valor["tipo"]] : $valor["tipo"]).'" name="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'"  id="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'" value="'.((isset($db[$name]) && $valor["tipo"]!='archivo') ? $db[$name] : "").'" class="'.preg_replace("/\s+$/","",preg_replace("/^\s+/","",(($valor["tipo"] == 'fecha') ? 'datepicker' : "master_".$valor["tipo"]).(isset($valor['filtro']) ? ($valor['filtro']==FILTRO_INT ? " integer" : ($valor['filtro']==FILTRO_FLOAT ? " float" :'')) : '').($valor['tipo']=='barcode' ? " barcode" : ''))).'" />';
				} else if($valor['tipo']=='archivo'){
					$campo='<input type="file" name="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'" id="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'" style="display: none;" onchange="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'_seleccionar.value='.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'.value;">
<input type="text" name="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'_seleccionar" style="width:50%;"><span class="ext-img "><a href="javascript:void(0);" onClick="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'.click();">Explorar</a></span>'.(($valor["tipo"]=='archivo' && isset($valor['explorador']) && $valor['explorador']!='') ?
					'<div id="explorador_menu"><input type="hidden" name="'.
					htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'_explorador" id="'.
					htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'_explorador" value=""/><img src="" class="explorador_preview pop" id="'.
					htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'_explorador_image" style="display:none;"/><br /><span class="ext-img"><a href="javascript:explorador(\''.
					htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'_explorador\',\''.
					htmlentities($valor['explorador'],ENT_QUOTES,"UTF-8").
					'\');">Existentes</a></span></div>' : '');
				} else if($valor["tipo"] == 'enviar')
					$campo='<input'.$atributos.' class="btn" type="submit" value="'.htmlentities($valor['etiqueta'],ENT_QUOTES,"UTF-8").'" name="'.htmlentities($name.$subfijo,ENT_QUOTES,"UTF-8").'">';
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
				if($editing_form==1 && (!isset($valor['editable']) || $valor['editable']==true || $valor['editable']==SIEMPRE))
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
			$return.='<table class="master_'.$table_name.'" id="editor_form" name="master_'.$table_name.'">';
			foreach($form["campos"] as $name => $valor){
				if($valor['tipo']=='db' || ($editing_form==1 && $valor['tipo']!='callback' && $valor['tipo']!='hidden' && $valor['tipo']!='html'  && $valor['tipo']!='html_hidden'  && $valor['tipo']!='enviar'  && $valor['tipo']!='boton' && (isset($valor['editable']) && $valor['editable']==false))){
					if(defined('DEBUG'))
						print "Ignorando campo: ".$name." ".$valor['tipo']." (no editable)<br />";
					continue;
					}
				if((!isset($valor['memoria']) || $valor['memoria']!=false) && $valor['tipo']=='texto'){
					$completado=$this->db_exect("SELECT ".$name." FROM ".$table_name." group by ".$name);
					$valorcompletado="";
					foreach($completado as $vcompletado){
						$valorcompletado.="'".$vcompletado[$name]."',";
					}
					$completados_script.='var completado_'.$name.' = ['.$valorcompletado.'];';
					$completados_script2.='$(function() {$("#'.$name.'").autocomplete({source: completado_'.$name.'});});';
				}
				if(!isset($valor['no_form']) || $valor['no_form']==false){
					$datos=$this->createfield($name,$valor,$db[$table_name],$negritas);
					$return.='<tr><td>'.$datos["label"].'</td><td>'.$datos["campo"].'</td></tr>'."\n";
				}
			}
			$return.="</table></form></div></center><script>".$completados_script."$(function() {".$completados_script2."});</script>";
		}
		return $return;

	}
		public function mediaviewer($table,$db=null,$condicion=null,$selectmode=0){
		$forms=$table["tabla"];
		$usermensage="";
		$noseleccion=0;
		$completados_script="";
		$completados_script2="";
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
						$filtros.="<td class=\"filtro_label\">".$tmp['label'].': </td><td class="filtro_campo">'.$tmp['campo'].'</td></td>';
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
			$contadorinsertado=0;
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
				$db_editable=array();
		if((isset($_POST['lock_tabla']) || isset($GLOBALS['guardar_tabla'])) && isset($_POST['unlock_tabla']))
			unset($_POST['unlock_tabla']);

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
						"etiqueta"=>(defined("MASTER_REGRESAR") ? MASTER_REGRESAR : "Regresar")
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
				print "<p class='ok-txt'><b>Se elimin".(($count<2) ? "ó" : "aron")." ".$count." registro".(($count==1) ? "" : "s")." correctamente!</b></p><br />";
			break;
		}
		$forms=$table["tabla"];
		if(defined("DEBUG"))
			print "Intentando guardar (".count($_POST)." elementos en post ".(isset($GLOBALS['guardar_tabla']) ? 'guardar defined in post' : 'guardar not defined in post').")<br />";

		if(isset($_POST) && count($_POST)>0 && isset($GLOBALS['guardar_tabla'])){
			$por_guardar=array();
			
			foreach($_POST as $_key => $_val){
				if(preg_match("/^(.+)_(\d+)$/",$_key,$dis)){
					foreach($forms as $table_name=>$form){
						foreach($form["campos"] as $name => $valor){
							if($name==$dis[1]){
								$por_guardar[$dis[2]][$name]=$_val;
							}
						}
					}
				}
			}
			foreach($_FILES as $_key => $_val){
				if(preg_match("/^(.+)_(\d+)$/",$_key,$dis)){
					foreach($forms as $table_name=>$form){
						foreach($form["campos"] as $name => $valor){
							if($name==$dis[1]){
								$por_guardar[$dis[2]][$name]=$_val;
							}
						}
					}
				}
			}
			$patched_table=$table["tabla"];
			foreach($patched_table as $table_name=>$form){
				foreach($form["campos"] as $name =>$valor){
					if(isset($valor['no_tabla']) && $valor['no_tabla']==true)
						unset($patched_table[$table_name]['campos'][$name]);
				}
			}
			$t_guardados=0;
			$te_guardados=0;
			foreach($por_guardar as $id => $val_guardar){
				foreach($val_guardar as $_key => $_val) {
					foreach($forms as $table_name=>$form){
						if($form["campos"][$_key]['tipo']=='archivo'){
							if(isset($_FILES[$_key."_".$id]))
								$_FILES[$_key]=$_FILES[$_key."_".$id];
							if(isset($_POST[$_key."_".$id."_explorador"]))
							$_POST[$_key."_explorador"]=$_POST[$_key."_".$id."_explorador"];
						} else {
							$_POST[$_key]=$_val;
						}
					}
				}
				foreach($patched_table as $tname => $v){
					$patched_table[$tname]["id"]=$id;
				}
				$referencia1=array();
				if($this->saveform($patched_table,$referencia1)){
					$t_guardados++;
				}
				else{
					$te_guardados++;
				}
			}
			unset($por_guardar);
			print "<p class='ok-txt'><b>".$t_guardados." elementos guardado correctamente!</b></p><br />";
			if($te_guardados>0){
				$this->error("No se pudieron guardar ".$te_guardados." elementos de la tabla!");
			}
		}
		foreach($forms as $table_name=>$form){
			$filtros="";
			$orderby="";
			$completados_script="";
			$completados_script2="";
			$filtros_enableds=0;
			$actual_page=(isset($_POST['master_page']) ? $_POST['master_page'] : 0);
			if(!$this->is_int2($actual_page)){
				$actual_page=0;
			}
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
						$filtros.="<td class=\"filtro_label\">".$tmp['label'].': </td><td class="filtro_campo">'.$tmp['campo'].'</td></td>';
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
			/* Para el Limit */
			$conteo_tabla=array_pop($this->db_exect("SELECT COUNT(id) as contador from ".$table_name.$condicion_tabla.$orderby.";"));
			$paginador="";
//			if($conteo_tabla['contador']>PER_PAGE){
				$paginas=($conteo_tabla['contador']/PER_PAGE);
				if($conteo_tabla['contador']%PER_PAGE>0){
					$paginas=(int)$paginas;
					$paginas++;
				}
				$paginador_botones='<li'.(0==$actual_page ? " class=\"active\">Anterior" :'><a href="javascript:paginador('.($actual_page-1).');">Anterior</a>').'</li>';
				if($actual_page>$paginas-1)
					$actual_page=$paginas-1;
				if($actual_page<0)
					$actual_page=0;
				$ipag=$actual_page-(SIZE_PAG/2);
				if(($actual_page-(SIZE_PAG/2))<0)
					$ipag=0;
				else if(($actual_page+(SIZE_PAG/2))>$paginas){
					$ipag=$actual_page-(SIZE_PAG/2)-((SIZE_PAG/2)-($paginas-$actual_page));
				}
				if($ipag<0)
					$ipag=0;
				$tope=($paginas<($ipag+SIZE_PAG) ? $paginas : ($ipag+SIZE_PAG));
				for(;$ipag<$tope;$ipag++){
					$paginador_botones.='<li'.($ipag==$actual_page ? " class=\"active\">".($ipag+1) :'><a href="javascript:paginador('.$ipag.');">'.($ipag+1).'</a>').'</li>';
				}
				if($paginas==0)
					$paginador_botones.='<li class="active">1</a></li>';
				$paginador_botones.='<li'.($paginas-1==$actual_page ? " class=\"active\">Siguiente" :'><a href="javascript:paginador('.($actual_page+1).');this.form.submit();">Siguiente</a>').'</li>';
				$paginador='<div id="paginador"><ul id="pagination-clean">'.$paginador_botones.'</ul></div><br />';
//			}
			/* Fin Limit*/
			}
							// Para campos editables
			$sql_load ="SELECT * FROM ".$table_name.$condicion_tabla.$orderby." limit ".($actual_page*PER_PAGE).",".PER_PAGE.";";
			if(defined('DEBUG'))
				print "Editable:".$sql_load."<br />";
			$res=mysql_query($sql_load);
			if(!$res) { $this->error("No existe este elemento en la base de datos"); exit;}
			while($tmp=mysql_fetch_assoc($res)){
				$db[$table_name][] = $tmp;
				$db_editable[$table_name][$tmp['id']] = $tmp;
				}
			if(isset($valor['multiple']) && $valor['multiple']==true){
				$sql_load ="SELECT * FROM ".$table_name."_".$name."_multiple";
				$res=mysql_query($sql_load);
				if($res){
					while($res_v=mysql_fetch_assoc($res)){
						$id_field=$res_v['master_id'];
						if(!isset($db_editable[$table_name][$id_field]))
							$db_editable[$table_name][$id_field]=array();
						foreach($res_v as $rnam=>$vnam){
							if($rnam=='master_id')
								continue;
							if(!isset($db_editable[$table_name][$id_field][$rnam."[]"]) || !is_array($db[$table_name][$id_field][$rnam."[]"]))
								$db_editable[$table_name][$id_field][$rnam."[]"]=array();
							$db_editable[$table_name][$id_field][$rnam."[]"][]=$vnam;
						}
					}
				}
			} // Termina para campos editables
			if(!isset($table['eliminar']['confirmacion'])){
				$table['eliminar']['confirmacion']="";
			}
			$customs="";
			foreach($table as $custname=>$custdata){
				if($custname!="tabla" && $custname!="disable_link"){
					if(isset($custdata['boton']) && isset($custdata['etiqueta'])){
								$customs.='<li><a href="javascript:if($(\'.notFilled\').size()==0){'.(isset($custdata['confirmacion']) ? 'if(confirm(\''.htmlentities($custdata['confirmacion'],ENT_QUOTES,"UTF-8").'\')) {' : '').'document.getElementById(\'master_task\').value=\''.$custname.'\';document.table_'.$table_name.'.submit();'.(isset($custdata['confirmacion']) ? '}' : '').'} else { alert(\'Por favor llene los campos obligatorios\');}"><img src="'.$table[$custname]['boton'].'" />'.htmlentities($table[$custname]['etiqueta'],0,"UTF-8").'</a></li>';

					}
				}
			}
			if(!isset($db[$table_name]))
				$db[$table_name]=array();
			$opciones_paginador="";
			$desplegar=array(50,100,200,500);
			foreach($desplegar as $ipaginador){
				$opciones_paginador.="<option ".(PER_PAGE==$ipaginador ? "selected " : "")."value=\"".$ipaginador."\">".$ipaginador."</option>";
			}
			$por_pagina_select="<br/>Resultados por página:<select style=\"width:55px;\" onchange=\"this.form.submit();\" name=\"paginador_select\" >".$opciones_paginador."</select></div>";
			$form_action="";
			foreach($_GET as $getkey => $getval){
				$form_action.=htmlentities($getkey,ENT_QUOTES,'UTF-8')."=".htmlentities($getval,ENT_QUOTES,'UTF-8')."&";
			}
			$form_action=preg_replace("/&$/","",$form_action);
			$return='<div id="wrapper-content" class="wrapper-content"><div id="dialog"><img src="" style="width:100%;" id="imagen-display"/><iframe src="" style="width:100%;height:100%;display:none;" id="iframe-display"></iframe></div><form name="table_'.$table_name.'" method="POST" action="?'.$form_action.'" enctype="multipart/form-data" accept-charset="utf-8"><input type="hidden" id="master_task" name="master_task" />'."\n".'<div class="toolbar" id="toolbar"><div id="m" class="m"><div class="acciones" id="acciones"><ul class="cuadro" id="cuadro">'.$customs.'</ul></div></div></div>'.$filtros
			.'<br /><br /><div id="resultados-pag"><center><div id="resultados"><input type="hidden" name="master_page" '.((isset($_POST['master_page']) && $this->is_int2($_POST['master_page'])) ? 'value="'.$_POST['master_page'].'" ' : '').'id="master_page" />'.$por_pagina_select.$paginador.'</center></div>
			'.((isset($table['tabla'][$table_name]['unlockable']) && $table['tabla'][$table_name]['unlockable']==true) ? "<br />".(!isset($_POST['unlock_tabla']) ? "<input type=\"submit\" id=\"unlock_tabla\" name=\"unlock_tabla\" class=\"btn\" value=\"Desbloquear tabla\" />" : "<input type=\"submit\" name=\"lock_tabla\" class=\"btn float-l\" value=\"Bloquear tabla\" /><input type=\"hidden\" name=\"unlock_tabla\" value=\"1\" /><input type=\"submit\" name=\"guardar_tabla\" class=\"btn float-r\" value=\"Guardar tabla\" /><br />") : '').'
			<div id="m2" class="m" style="width:100%;"><table class="tablemaster" id="tablemaster_'.$table_name.'" style="width:100%">'."\n".'<thead>'."\n\t".'<th width="1%">'.
//			((!isset($table['multi_select']) || $table['multi_select']==true) ? '<input id="check_master" type="checkbox" onclick="checkall(this);">' : '').
			'</th>';
			$trrow=0;
			foreach($form["campos"] as $name => $valor){
				if($valor["tipo"]=='db' || $valor["tipo"]=='password' || $valor['tipo']=='html' || $valor['tipo']=='htm_hidden' || (isset($valor['no_tabla']) && $valor['no_tabla']==true) ){
					if(defined('DEBUG')){
						print "Ignorado el campo: ".$name." (tabla)<br />";
					}
					continue;
					}
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
//					$class_td="toedit";
					$class_td="changable";
				}
				$return.="\n\t<tr class=\"row".$trrow."\" id=\"tableelement_".$data['id']."\"><td style=\"text-align:center;\"><input type=\"checkbox\" name=\"cid[]\" class=\"checkme\" id=\"cid_".$data['id']."\" value=\"".$data['id']."\" ".(isset($_SESSION['master_almacen']["id_table_".$table_name]) && in_array($data['id'],$_SESSION['master_almacen']["id_table_".$table_name]) ? ' checked' : '')."/></td>";
				if($trrow==0)
					$trrow=1;
				else
					$trrow=0;
				foreach($form["campos"] as $name => $valor){
					if($valor["tipo"]=='db' || $valor["tipo"]=='password' || $valor["tipo"]=='html' || (isset($valor['no_tabla']) && $valor['no_tabla']==true)){
						if(defined('DEBUG')){
							print "Ignorado el campo: ".$name." (tabla)<br />";
						}
						continue;
						}
					$campo="";
					if(!isset($valor['filtro']) && $valor['tipo']!='callback'){
						$this->error("Filtro no definido en el campo \"".htmlentities($name)."\"");
						continue;
					}
					if(!isset($valor['filtro']) && $valor['tipo']=='callback'){
						$valor['filtro']=0;
					}
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
						} else if(isset($valor['money']) && $valor['money']==true){
							if($this->is_float2($campo) && $campo!='')
								$campo=money_format('%(n', $this->to_float($campo));
							else $campo="";
						} else if(isset($valor['percent']) && $valor['percent']==true){
							if(($this->is_int2($campo) || $this->is_float2($campo))&& $campo!='')
								$campo.="%";
							else $campo="";
						}
						$tienedatos=1;
						$tmp_td=$class_td;
						if(isset($valor['icono']) && $valor['icono']!='')
							$class_td="td_iconos";
						else if($valor["tipo"]=='archivo' && $valor['filtro']==FILTRO_IMAGEN)
								$class_td="";
						if(isset($valor['editable']) && ($valor['editable']==true || $valor['editable']==SIEMPRE)){
							$datos_field=$this->createfield($name,$valor,$db_editable[$table_name][$data['id']],0,"","_".$data['id']);
							}
						
						$return.='<td '.(($valor["tipo"]=='archivo' && $valor['filtro']==FILTRO_IMAGEN) ? ' class="img_td'.(($class_td!='') ? ' '.$class_td : '').'"': "class=\"".$class_td."\"").' id="'.$name.'_'.$data['id'].'_'.$valor["tipo"].'_'.$valor['filtro'].'"><a name="'.$name.'_'.$data['id'].'"></a>'.(((isset($valor['editable']) && ($valor['editable']==true  || $valor['editable']===SIEMPRE)) && (isset($_POST['unlock_tabla']) || $valor['editable']===SIEMPRE) && $valor['tipo']!='callback') ? '<div class="editable_field">'.$datos_field['campo'].'</div>' : '<div class="display_field">'.$campo.'</div>').'</td>';
						$class_td=$tmp_td;
					}
				}
				$return.="</tr>";
			}
			$return.="\n</tbody></table>".($tienedatos==0 ? "No hay registros" : "")."<td></tr></div><center><div style=\"text-align:center;\"><div id='paginador-foot'>".$paginador."</div></center><br />".((isset($table['tabla'][$table_name]['unlockable']) && $table['tabla'][$table_name]['unlockable']==true) ? (!isset($_POST['unlock_tabla']) ? "<input type=\"submit\" id=\"unlock_tabla\" name=\"unlock_tabla\" class=\"btn\" value=\"Desbloquear tabla\" />" : "<input type=\"submit\" name=\"lock_tabla\" class=\"btn float-l\" value=\"Bloquear tabla\" /><input type=\"hidden\" name=\"unlock_tabla\" value=\"1\" /><input type=\"submit\" name=\"guardar_tabla\" class=\"btn float-r\" value=\"Guardar tabla\" />") : '')."</form><script>".$completados_script."$(function() {".$completados_script2."});</script></div>";
		}
		return $return;

	}
	public function fin(){
		mysql_close($this->db);
	}
	public function saveform($forms,&$datos=null){
		if($this->status_error==1 || (isset($GLOBALS['status_error']) && $GLOBALS['status_error']==1)){
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
			$have_data=0;
			$condiciones="";
			$values="";
			$multiples=array();
			foreach($form["campos"] as $name =>$valor){
				if($valor['tipo']=='callback' || $valor['tipo']=='enviar' || $valor['tipo']=='html' || $valor['tipo']=='html_hidden' || ($update==1 && (isset($valor['editable']) && $valor['editable']==false)) || (!isset($_POST[$name]) && $valor['tipo']!='db' && ((isset($valor['no_tabla']) && $valor['no_tabla']==true) || (isset($valor['no_form']) && $valor['no_form']==true)))){
					if(defined('DEBUG')){
						print "Ignorado el campo: ".$name." (guardar)<br />";
					}
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
					$_POST[$name]=crypt($_POST[$name], '$2a$10$1qAz2wSx3eDc4rFv5tGb5t');
				if(!isset($_POST[$name]) && $imgexistente==0 && ($valor["tipo"] != "archivo" || !isset($_FILES[$name]['tmp_name']))){
					if((isset($valor['opcional']) && $valor['opcional']==true) || (isset($valor['editable']) && $valor['editable']==false)){
						$_POST[$name]="";
					} else {
//						print "Falta el campo: ".$name."<br />";
						return;
					}
				}
				if(isset($valor['multiple']) && $valor['multiple']==true){
					$multiples[$name]=$valor;
					continue;
				}
				if($valor["tipo"]!="enviar" && $valor["tipo"]!='html' && $valor['tipo']!='html_hidden'){
					if($update==0){
						$have_data=1;
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
							$have_data=1;
							$sql.=$name."_path,";
							$values.="'".$this->safestring($file_name)."','".$this->safestring($path)."',";
							$condiciones.=$this->safestring($file_name)."' and ".$name."_path='".$this->safestring($path)."' and ";
						}
						else{
							$_POST[$name]=$file_name;
							$sql.=$name."_path='".$this->safestring($path)."', ".$name."='".$file_name."', ";
						}
					}
					if($valor["filtro"]==FILTRO_FLOAT && $this->is_float2($_POST[$name])){
						$_POST[$name]=$this->to_float($_POST[$name]);
					}
					if($valor["filtro"]==FILTRO_INT || $valor["filtro"]==FILTRO_FLOAT){
						if((($valor["filtro"]==FILTRO_INT && !$this->is_int2($_POST[$name])) || ($valor["filtro"]==FILTRO_FLOAT && !$this->is_float2($_POST[$name])))  && (!isset($valor['opcional']) || $valor['opcional']==false)){
							$this->error("1. Valores inválidos en el campo: ".htmlentities($valor['etiqueta'],0,"UTF-8")." (".$name.")");return;}
						if($update==0){
							$values.="'".$_POST[$name]."',";
							$condiciones.=$_POST[$name]."' and ";
						}
						else
							$sql.=$name."='".$_POST[$name]."', ";
					}
					else if($valor["filtro"]==FILTRO_STRING || $valor["filtro"]==FILTRO_BARCODE || $valor["filtro"]==FILTRO_EMAIL){
						if($valor["filtro"]==FILTRO_EMAIL){
							if(!preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/",$_POST[$name]) && (!isset($valor['opcional']) || $valor['opcional']==false)) $this->error("E-Mail inválido!"); else $db[$name]=$this->safestring($_POST[$name]);		
						}
						else if($valor["filtro"]==FILTRO_STRING){
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
								$this->error(".Formato de fecha inválido!");
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
//				unset($_POST[$name]);
			}
			$sql=preg_replace("/\,\s*$/","",$sql);
			if($values=='' && $update==0){
				return false;
				}
			if($update==0){
				$condiciones=preg_replace("/ and $/","",$condiciones);
				$values=preg_replace("/\,$/","",$values);
				$sql.=") values(".$values.");";
			}
			else
				$sql.=" where id='".$form['id']."';";
			if($this->status_error==1 || (isset($GLOBALS['status_error']) && $GLOBALS['status_error']==1) || ($update==0 && $values=='')){
				return false;
			} else if(!mysql_query($sql)){
				$this->error("No se puede guardar los valores: ".mysql_error($this->db));
				return;
			}
			if($update==0){
				$sql="SELECT id FROM ".$table_name." where ".$condiciones.";";
				$resdb = mysql_query($sql);
				if (!$resdb) {
					$this->error("Error de base de datos: ".mysql_error($this->db));
					return false;
				}
				if (mysql_num_rows($resdb) == 0) {
					$this->error("Error de base de datos! 502: SELECT id FROM ".$table_name." where ".$condiciones.";");
				    return false;
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
				}
				else if($valor_multiple['tipo']=='archivo' && isset($_FILES[$name])){
					$vals_multiple=$_FILES[$name];
				}
				if(isset($valor_multiple['multiple']))
					unset($valor_multiple['multiple']);
				if(!is_array($vals_multiple)){
					$vals_multiple=array($vals_multiple);
				}
				$multiple=array();
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
			foreach($form["campos"] as $name =>$valor){
				if(isset($_POST[$name]))
					unset($_POST[$name]);
				if(isset($_FILES[$name]))
					unset($_FILES[$name]);
			}
			unset($_REQUEST['master_task']);
			return true;
		}
	}

}
?>
