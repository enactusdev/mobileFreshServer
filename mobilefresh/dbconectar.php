<?php
function conectar(){
	$conexion = mysql_connect("localhost", "root","");
	mysql_select_db("mobile_fresh",$conexion);
	return $conexion;
}
?>