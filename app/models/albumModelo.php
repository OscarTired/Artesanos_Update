<?php
require '../../config/conexion.php';
require '../../config/cerrarConexion.php';
include 'album.php';

// Clase modelo: contiene la lógica de acceso a datos.
// Se encarga de consultar e insertar álbumes en la base de datos.
// No representa un álbum en sí, sino operaciones sobre ellos.

class AlbumModelo{
    public function __construct(){
    }

    public function crearAlbum(Album $album){ //inserta el album a la bd
        $conexion = abrirConexion();

        $titulo = mysqli_real_escape_string($conexion, $album->tituloAlbum); //hace que no se rompa la consulta sql si meten algun caracter especial
        $urlPortada = mysqli_real_escape_string($conexion, $album->urlPortada);
        $esPublico = $album->esPublico ? 1 : 0;
        $idUsuario = (int)$album->idUsuario;

        $consulta = "INSERT INTO album (tituloAlbum, esPublicoAlbum, urlPortadaAlbum, idUsuarioAlbum) VALUES ('$titulo', $esPublico, '$urlPortada', $idUsuario)";

        $resultado = mysqli_query($conexion,$consulta);

        if($resultado){
            $id = mysqli_insert_id($conexion);
            $album->idAlbum = $id;
            $album->agregarDatosUsuario();
        }

        cerrarConexion($conexion);

        return $resultado ? mysqli_insert_id($conexion) : false; //si pudo crear el album retorna el id para poder usarlo en imagen
    }
    
    public function agregarDatosUsuario(Album $a){ //agrega los datos del usuario al album q le pasen
        $conexion = abrirConexion();

        $idAlbum = (int)$a->idAlbum;

        $consulta = "SELECT u.apodoUsuario, u.arrobaUsuario FROM album a JOIN usuario u ON a.idUsuarioAlbum = u.idUsuario WHERE a.idAlbum = $idAlbum";

        $resultado = mysqli_query($conexion, $consulta);

        $nfilas = mysqli_num_rows($resultado);

        if($nfilas > 0){
            $fila = mysqli_fetch_assoc($resultado);
            $a->setUsuario($fila['apodoUsuario'], $fila['arrobaUsuario']);
        }

        return $a;
        cerrarConexion($conexion);
    }
    
    public function mostrarTodos(){ //devuelve todos los albumes publicos y privados
        $conexion = abrirConexion();

        $consulta = "SELECT a.idAlbum, a.tituloAlbum, a.urlPortadaAlbum, a.esPublicoAlbum, u.apodoUsuario, u.arrobaUsuario, u.idUsuario FROM album a JOIN usuario u ON a.idUsuarioAlbum = u.idUsuario ORDER BY a.idAlbum DESC";
        $resultado = mysqli_query($conexion,$consulta);

        $albumes = [];
        $nfilas = mysqli_num_rows($resultado);
        if($nfilas > 0){
            while($fila = mysqli_fetch_assoc($resultado)){
                $album = new Album($fila['tituloAlbum'], (bool)$fila['esPublicoAlbum'], $fila['urlPortadaAlbum'], $fila['idUsuario']);
                $album->idAlbum = $fila['idAlbum'];
                $album->setUsuario($fila['apodoUsuario'], $fila['arrobaUsuario']);

                $albumes[] = $album;
            }

            cerrarConexion($conexion);
            return $albumes;
        }else{ //si no hay albumes creados retorna falso
            cerrarConexion($conexion);
            return false;
        }
    }

    public function mostrarPublicos(){ //devuelve todos los albumes publicos
        $conexion = abrirConexion();

        $consulta = 'SELECT a.idAlbum, a.tituloAlbum, a.esPublicoAlbum, a.urlPortadaAlbum, u.idUsuario, u.apodoUsuario, u.arrobaUsuario FROM album a JOIN usuario u ON a.idUsuarioAlbum = u.idUsuario WHERE a.esPublicoAlbum = 1';

        $resultado = mysqli_query($conexion,$consulta);
        $nfilas = mysqli_num_rows($resultado);

        $albumes = [];
        if($nfilas > 0){
            while($fila = mysqli_fetch_assoc($resultado)){
                $album = new Album ($fila['tituloAlbum'], (bool)$fila['esPublicoAlbum'], $fila['urlPortadaAlbum'], $fila['idUsuario']);
                $album->idAlbum = $fila['idAlbum'];
                $album->setUsuario($fila['apodoUsuario'], $fila['arrobaUsuario']);

                $albumes[] = $album;
            }

            cerrarConexion($conexion);
            return $albumes;
        }else{
            cerrarConexion($conexion);
            return false;
        }
    }

    public function mostrarPorUsuario(Usuario $us){ //va a andar cuando este lista la clase usuario
        $conexion = abrirConexion();

        $idUs = (int) $us->idUsuario;

        $consulta = "SELECT * FROM album WHERE idUsuarioAlbum = $idUs ORDER BY idAlbum DESC";
        $resultado = mysqli_query($conexion,$consulta);

        $albumes = [];
        $nfilas = mysqli_num_rows($resultado);
        if($nfilas > 0){
            while($fila = mysqli_fetch_assoc($resultado)){
                $album = new Album($fila['tituloAlbum'], (bool)$fila['esPublicoAlbum'], $fila['urlPortadaAlbum'], $idUs);
                $album->idAlbum = $fila['idAlbum'];
                $album->setUsuario($us->apodoUsuario, $us->arrobaUsuario);

                $albumes[] = $album;
            }

            cerrarConexion($conexion);
            return $albumes;

        }else{ //si no tiene albumes retorna falso
            cerrarConexion($conexion);
            return false;
        }
    }
}