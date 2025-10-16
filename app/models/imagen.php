<?php
require '../../config/conexion.php';
require '../../config/cerrarConexion.php';

class Imagen{
    private $idImagen, $titulo, $descripcion, $etiqueta, $enRevision, $fecha, $url, $idAlbum;
    public function __construct(){
        $this->idImagen = 0;
        $this->titulo = "";
        $this->descripcion = "";
        $this->etiqueta= "";
        $this->enRevision = 0; 
        $this->url = "";
        $this->idAlbum = 0;
    }
    public function __get($variable){
        return $this->$variable;
    }
    public function __set($variable, $valor){
        $this->$variable = $valor;
    }

    public function crearImagen($titulo,$descripcion,$etiqueta,$url,$idAlbum){
        $conexion = abrirConexion();
        
        $url = mysqli_real_escape_string($conexion, $url); //hace que no se rompa la consulta sql si meten algun caracter especial
        $idAlbum = (int)$idAlbum;

        //como son opcionales, si me lo pasan vacios los declaro null
        if(empty($titulo)){ 
            $titulo = NULL;
        }else{
            $titulo = "'" . mysqli_real_escape_string($conexion, $titulo) . "'";
        }
        if(empty($descripcion)){
            $descripcion = NULL;
        }else{
            $descripcion = "'" . mysqli_real_escape_string($conexion, $descripcion) . "'";
        }
        if(empty($etiqueta)){
            $etiqueta = NULL;
        }else{
            $etiqueta = "'" . mysqli_real_escape_string($conexion, $etiqueta) . "'";
        }

        $fecha = date("Y-m-d");

        $consulta = "INSERT INTO imagen (tituloImagen, descripcionImagen, etiquetaImagen, enRevision, fechaImagen, urlImagen, idAlbumImagen) VALUES ($titulo, $descripcion, $etiqueta, 0, '$fecha', '$url', $idAlbum);";
        $resultado = mysqli_query($conexion, $consulta);

        cerrarConexion($conexion);
        return $resultado ? true : false; //si pudo crear la imagen retorna true
        
    }

    public function mostrarPorAlbum($idAlbum){
        $conexion = abrirConexion();

        $idAlbum = (int)$idAlbum;

        $consulta = "SELECT * FROM imagen WHERE idAlbumImagen = $idAlbum ORDER BY idImagen DESC;";
        $resultado = mysqli_query($conexion,$consulta);

        $imagenes = [];
        if(mysqli_num_rows($resultado) > 0){
            while($fila = mysqli_fetch_array($resultado)){
                $imagenes[] = $fila;
            }
            cerrarConexion($conexion);
            return $imagenes;
        }else{
            cerrarConexion($conexion); //si hubo errores o no hay imagenes devuelve falso
            return false;
        }

    }
}

?>