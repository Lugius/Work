<?php
@session_start();
if(!isset($_GET['f']) || !preg_match("/^[a-fA-F0-9]{32}$/",$_GET['f'])){
	die('Valor inválido!');
}
if(isset($_SESSION['imagenes']) && isset($_SESSION['imagenes'][$_GET['f']]) && isset($_SESSION['imagenes'][$_GET['f']]['path']) && isset($_SESSION['imagenes'][$_GET['f']]['name'])){
	$mime="application/octet-stream";
	$ext=preg_replace("/^.+?\.(\w+)$/","$1",$_SESSION['imagenes'][$_GET['f']]['name']);
	switch(strtolower($ext)){
		case 'jpg':
		case 'jpeg':
			$mime='image/jpeg';
		break;
		case 'png':
			$mime='image/png';
		break;
		case 'png':
			$mime='image/gif';
		break;
		case 'bmp':
			$mime='image/x-ms-bmp';
		break;
	}
	header('Content-Disposition: attachment; filename="'.$_SESSION['imagenes'][$_GET['f']]['name'].'"');
	header('Content-Type: '.$mime);
	echo file_get_contents($_SESSION['imagenes'][$_GET['f']]['path']);
}
?> 
