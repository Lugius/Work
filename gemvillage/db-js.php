<?php
@session_start();
?><html>
<head>
<title>Lote</title>
<?php
require_once('framework-master.php');
require_once('db.php');
$core= new master($server,$database,$user,$password);
echo $core->header();
$data=$core->db_exect("select * from productos");
?>

<script>
var db=<?php echo $core->array2json($data);?>;
var relaciones={'descripcion':'codigobarras','imagen':'descripcion'};
</script> 

		</head>
<body>
<center><div class="m m1"><form name="form_lotes" method="POST" enctype="multipart/form-data" accept-charset="utf-8"><table class="master_lotes" name="master_lotes"><tr><td><b>Nombre del lote</b></td><td><input type="text" name="nombre"  id="nombre" value="" class="master_texto" /></td></tr>
<tr><td><b>Tipo de piedra</b></td><td><select id="tipo_piedra" name="tipo_piedra">
<option value="">- Seleccionar -</option>
<option value="1">Oro</option>
<option value="2">Fierro</option>
</select></td></tr>
<tr><td><b>Categor&iacute;a</b></td><td><select id="categoria" name="categoria">
<option value="">- Seleccionar -</option>
<option value="1">n cat</option>
</select></td></tr>
<tr><td><b>Imagen</b></td><td><input type="file" name="imagen"  id="imagen" value="" class="master_archivo" /></td></tr>
<tr><td><b>C&oacute;digo de barras</b></td><td><img src="barcode.php" id="codigobarras_barcodedisplay" width="250" /><br /><input type="text" name="codigobarras"  id="codigobarras" value="" class="master_barcode barcode" /></td></tr>
<tr><td><b>N&uacute;mero de lote</b></td><td><input type="text" name="numero_lote"  id="numero_lote" value="" class="master_texto integer" /></td></tr>
<tr><td><b>Talle de piedra</b></td><td><select id="tallado" name="tallado" onchange="rellenar(this,db,relaciones,'id');">
<option value="">- Seleccionar -</option>
<option value="1">Tallado</option>
<option value="0">Bruto</option>
</select></td></tr>
<tr><td><b>Tipo de compra</b></td><td><select id="tipo_compra" name="tipo_compra">
<option value="">- Seleccionar -</option>
<option value="1">tipo 1</option>
</select></td></tr>
<tr><td><b>Estado del producto</b></td><td><select id="estado_producto" name="estado_producto">
<option value="">- Seleccionar -</option>
<option value="1">asas</option>
</select></td></tr>
<tr><td><b>Descripci&oacute;n de lote</b></td><td><textarea rows="4" cols="50" name="descripcion" id="descripcion" ></textarea></td></tr>
<tr><td><b>Proveedor</b></td><td><select id="proveedor" name="proveedor">
<option value="">- Seleccionar -</option>
<option value="1">xianuro</option>
</select></td></tr>
<tr><td><h3>Cotización:</h3></td><td></td></tr>
<tr><td><b>Peso</b></td><td><select id="peso" name="peso">
<option value="">- Seleccionar -</option>
<option value="1">CT (Kilates)</option>
<option value="0">GR (Gramos)</option>
</select></td></tr>
<tr><td><b>Gasto adicional</b></td><td><select id="gasto[]" name="gasto[]" multiple>
<option value="1">asdasd</option>
<option value="2">a a a</option>
<option value="3">bbbbb</option>
</select></td></tr>
<tr><td></td><td><input type="submit" value="Guardar" name="enviar"></td></tr>
<tr><td></td><td><button onclick="document.getElementById('master_task').value='';">Regresar</button></td></tr>
<tr><td></td><td><input type="hidden" name="master_task"  id="master_task" value="nuevo" class="master_hidden" /></td></tr>
</table></form></div></center></body>
</html>

