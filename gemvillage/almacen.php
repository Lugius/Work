<?php
@session_start();
require_once('framework-master.php');
require_once('db.php');
$core=new master($server,$database,$user,$password);
if(!isset($_POST['var']) || !isset($_POST['action'])|| !preg_match("/^[\w\d\-\_]+$/",$_POST['var'])){
	exit;
	}
if(!isset($_SESSION['master_almacen'])){
	$_SESSION['master_almacen']=array();
}
$tarray=array('push','shift','pop','unshift');
if(in_array($_POST['action'],$tarray)){
	if(!isset($_SESSION['master_almacen'][$_POST['var']]) || !is_array($_SESSION['master_almacen'][$_POST['var']])){
		$_SESSION['master_almacen'][$_POST['var']]=array();
		}
}
function del(){
	if(isset($_SESSION['master_almacen'][$_POST['var']]) && is_array($_SESSION['master_almacen'][$_POST['var']])){
		foreach($_SESSION['master_almacen'][$_POST['var']] as $i => $v){
			if($v==$_POST['val']){
				array_splice($_SESSION['master_almacen'][$_POST['var']],$i,1);
			}
		}
	}
}
switch($_POST['action']){
	case 'append':
		if(!isset($_SESSION['master_almacen'][$_POST['var']]))
			$_SESSION['master_almacen'][$_POST['var']]="";
		$_SESSION['master_almacen'][$_POST['var']].=$_POST['val'];
	break;
	case 'push':
		del();
	 array_push($_SESSION['master_almacen'][$_POST['var']],$_POST['val']);
	break;
	case 'shift':
		print array2json(array_shift($_SESSION['master_almacen'][$_POST['var']]));
	break;
	case 'pop':
		print array2json(array_pop($_SESSION['master_almacen'][$_POST['var']]));
	break;
	case 'unshift':
		array_unshift($_SESSION['master_almacen'][$_POST['var']]);
	break;
	case 'set':
		$_SESSION['master_almacen'][$_POST['var']]=$_POST['val'];
	break;
	case 'del':
		del();
	break;
	case 'get':
		print (is_array($_SESSION['master_almacen'][$_POST['var']]) ? $core->array2json($_SESSION['master_almacen'][$_POST['var']]) : "\"".htmlentities($_SESSION['master_almacen'][$_POST['var']],ENT_COMPAT,"UTF-8")."\"");
	break;
	default:
		exit;
	break;
}
?> 
