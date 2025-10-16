<?php
require '../../config/conexion.php';
require '../../config/cerrarConexion.php';
include 'imagen.php';

// Clase modelo: contiene la lógica de acceso a datos.
// Se encarga de consultar e insertar álbumes en la base de datos.
// No representa un álbum en sí, sino operaciones sobre ellos.

class ImagenModelo{
    public function __construct(){

    }
    
    public function crearImagen(Imagen $i){
        $conexion = abrirConexion();
        //al ser opcionales, si me los pasan vacios los dejo null
        if(empty($i->titulo)){ 
            $titulo = 'NULL';
        }else{
            $titulo = "'" . mysqli_real_escape_string($conexion, $i->titulo) ."'";
        }
        if(empty($i->descripcion)){
            $descripcion = 'NULL';
        }else{
            $descripcion = "'" . mysqli_real_escape_string($conexion, $i->descripcion) . "'";
        }
        if(empty($i->etiqueta)){
            $etiqueta = 'NULL';
        }else{
            $etiqueta = "'" . mysqli_real_escape_string($conexion, $i->etiqueta) . "'";
        }
        $url = mysqli_real_escape_string($conexion, $i->url); //hace que no se rompa la consulta sql si meten algun caracter especial
        $idAlbum = (int)$i->idAlbum;

        $fecha = date("Y-m-d");

        $consulta = "INSERT INTO imagen (tituloImagen, descripcionImagen, etiquetaImagen, enRevision, fechaImagen, urlImagen, idAlbumImagen) VALUES ($titulo, $descripcion, $etiqueta, 0, '$fecha', '$url', $idAlbum);";
        $resultado = mysqli_query($conexion, $consulta);

        cerrarConexion($conexion);
        return $resultado ? true : false; //si pudo crear la imagen retorna true
        
    }    

    public function mostrarPorAlbum(Album $a){
        $conexion = abrirConexion();

        $idAlbum = (int)$a->idAlbum;

        $consulta = "SELECT * FROM imagen WHERE idAlbumImagen = $idAlbum ORDER BY idImagen DESC;";
        $resultado = mysqli_query($conexion,$consulta);

        $imagenes = [];
        if(mysqli_num_rows($resultado) > 0){
            while($fila = mysqli_fetch_assoc($resultado)){
                $imagen = new Imagen($fila['tituloImagen'], $fila['descripcionImagen'], $fila['etiquetaImagen'], $fila['fechaImagen'], $fila['urlImagen']);
                $imagen->idAlbum = $fila['idAlbumImagen'];
                $imagen->idImagen = $fila['idImagen'];
                
                $imagenes[] = $imagen;
            }
            cerrarConexion($conexion);
            return $imagenes;
        }else{
            cerrarConexion($conexion); //si hubo errores o no hay imagenes devuelve falso
            return false;
        }

    }    
}