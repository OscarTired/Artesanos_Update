<?php
require '../../config/conexion.php';
require '../../config/cerrarConexion.php';

class Album{
    private $idAlbum, $tituloAlbum, $esPublico, $urlPortada, $idUsuario;
    public function __construct(){
        $this->idAlbum = 0;
        $this->tituloAlbum = "";
        $this->esPublico = 0;
        $this->urlPortada = "";
        $this->idUsuario = 0;
    }
    public function __get($variable){
        return $this->$variable;
    }
    public function __set($variable,$valor){
        $this->$variable = $valor;
    }

    public function crearAlbum($titulo,$esPublico,$urlPortada,$idUsuario){
        $conexion = abrirConexion();

        $consulta = "INSERT INTO album (tituloAlbum, esPublicoAlbum, urlPortadaAlbum, idUsuarioAlbum) VALUES ('$titulo', $esPublico, '$urlPortada', $idUsuario)";

        $resultado = mysqli_query($conexion,$consulta);

        cerrarConexion($conexion);

        return $resultado ? true : false;
    }

    public function mostrarTodos(){
        $conexion = abrirConexion();

        $consulta = "SELECT * FROM album ORDER BY idAlbum DESC";
        $resultado = mysqli_query($conexion,$consulta);

        $albumes = [];
        $nfilas = mysqli_num_rows($resultado);
        if($nfilas > 0){
            for($i=0; $i < $nfilas; $i++){
                $fila = mysqli_fetch_array($resultado);
                $albumes[] = $fila;
            }
            cerrarConexion($conexion);
            return $albumes;
        }else{ //si no hay albumes creados retorna falso
            cerrarConexion($conexion);
            return false;
        }
    }

    public function mostrarPorUsuario($idUs){
        $conexion = abrirConexion();

        $consulta = "SELECT * FROM album WHERE idUsuarioAlbum = $idUs ORDER BY idAlbum DESC";
        $resultado = mysqli_query($conexion,$consulta);

        $albumes = [];
        $nfilas = mysqli_num_rows($resultado);
        if($nfilas > 0){
            for($i=0; $i < $nfilas; $i++){
                $fila = mysqli_fetch_array($resultado);
                $albumes[] = $fila;
            }
            cerrarConexion($conexion);
            return $albumes;
            
        }else{ //si no tiene albumes retorna falso
            cerrarConexion($conexion);
            return false;
        }
    }
}


?>