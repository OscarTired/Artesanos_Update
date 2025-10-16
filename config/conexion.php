<?php
function abrirConexion(){
    $conexion = new mysqli("localhost", "root", "", "artesanos");
    if($conexion->connect_error){
        die("Error de conexion: " . $conexion->connect_error);
    }
    return $conexion;
}
?>
