<?php
@session_start();
?><html>
<head>
<title>Multimedia</title>
<?php
require_once('framework-master.php');
require_once('db.php');
$core= new master($server,$database,$user,$password);
$core->set_upload_dir("./images");
$core->semilla="xianur0";
echo $core->header();
$data=$core->db_exect("select id,nombre,aumento from gastosadicionales;");
$selectmode=0;
if(isset($_GET['seleccionar']))
	$selectmode=1;
require_once('menu-maker.php');
if($selectmode!=1)
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
	$('#gasto').change(cotizar);
	$('#precio_origen').change(cotizar);
});
</script>
</head>
<body>
<?php
if($selectmode!=1)
	menu_start();
$forms=array();
$campos=array();
$campos["filtro_elemento"]=array(
        "etiqueta"=>"Sección",
        "busqueda"=>'none',
        "tipo"=>"seleccionar",
        "columna_texto"=>"nombre",
        "columna_valor"=>"id",
        "filtro"=>FILTRO_STRING,
        "default"=>array(
		array('nombre'=>"Productos",'id'=>'productos'),
		array('nombre'=>"Categorías",'id'=>'categorias'),
		array('nombre'=>"Lotes",'id'=>'lotes'),
		array('nombre'=>"Tipos de clientes",'id'=>'tipo_cliente'),
		array('nombre'=>"Status de cliente",'id'=>'statusde_cliente'),
		array('nombre'=>"Estado de venta",'id'=>'cat_tipo_estado_venta'),
		array('nombre'=>"Tipo de compra",'id'=>'cat_tipo_compra'),
		array('nombre'=>"Estado producto",'id'=>'estado_producto')
        ),
);
if(isset($_POST['masterfiltro_filtro_elemento'])){
	switch($_POST['masterfiltro_filtro_elemento']){
		case 'productos':
		case 'categorias':
		case 'lotes':
			$campos["imagen"]=array("tipo"=>"texto","busqueda"=>'like',"etiqueta"=>"Nombre", "filtro"=>FILTRO_STRING);
		break;
		case 'tipo_cliente':
		case 'statusde_cliente':
		$campos["icono_cliente"]=array("tipo"=>"texto","busqueda"=>'like',"etiqueta"=>"Nombre", "filtro"=>FILTRO_STRING);
		break;
		case 'cat_tipo_estado_venta':
		case 'cat_tipo_compra':
		case 'estado_producto':
		$campos["icono"]=array("tipo"=>"texto","busqueda"=>'like',"etiqueta"=>"Nombre", "filtro"=>FILTRO_STRING);
		break;
		default:
			exit();
		break;
	}
	$campos["enviar"]=array(
	"etiqueta"=>"Guardar",
	"tipo"=>"enviar",
	"filtro"=>FILTRO_STRING
	);
}
$forms[isset($_POST['masterfiltro_filtro_elemento']) ? $_POST['masterfiltro_filtro_elemento'] : '']=array(
			"campos"=>$campos,
);
$tabla=array(
		"tabla"=>$forms,
);
$guardado=0;
$condicion=null;
$lista="";
print $core->mediaviewer($tabla,null,$condicion,$selectmode);
if($selectmode!=1)
	menu_end();
?>
</body>
</html>
