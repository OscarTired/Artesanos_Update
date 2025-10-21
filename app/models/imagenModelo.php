<?php
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';
include 'imagen.php';

// Clase modelo: contiene la lógica de acceso a datos.
// Se encarga de consultar e insertar álbumes en la base de datos.
// No representa un álbum en sí, sino operaciones sobre ellos.

class ImagenModelo
{
    public function __construct() {}

    public function crearImagen($idAlbum, $titulo, $descripcion, $etiqueta, $url)
    {
        $conexion = abrirConexion();
        $titulo_sql = empty($titulo) ? "NULL" : "'" . mysqli_real_escape_string($conexion, $titulo) . "'";
        $descripcion_sql = empty($descripcion) ? "NULL" : "'" . mysqli_real_escape_string($conexion, $descripcion) . "'";
        $etiqueta_sql = empty($etiqueta) ? "NULL" : "'" . mysqli_real_escape_string($conexion, $etiqueta) . "'";
        $url_sql = mysqli_real_escape_string($conexion, $url);
        $idAlbum = (int)$idAlbum;
        $fecha = date("Y-m-d");

        $consulta = "INSERT INTO imagen (tituloImagen, descripcionImagen, etiquetaImagen, enRevision, fechaImagen, urlImagen, idAlbumImagen) 
        VALUES ($titulo_sql, $descripcion_sql, $etiqueta_sql, 0, '$fecha', '$url_sql', $idAlbum);";

        $resultado = mysqli_query($conexion, $consulta);

        cerrarConexion($conexion);
        return $resultado ? true : false;
    }

    public function mostrarPorAlbum(Album $a)
    {
        $conexion = abrirConexion();

        $idAlbum = (int)$a->idAlbum;

        $consulta = "SELECT * FROM imagen WHERE idAlbumImagen = $idAlbum ORDER BY idImagen DESC;";
        $resultado = mysqli_query($conexion, $consulta);

        $imagenes = [];
        if (mysqli_num_rows($resultado) > 0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $imagen = new Imagen($fila['tituloImagen'], $fila['descripcionImagen'], $fila['etiquetaImagen'], $fila['fechaImagen'], $fila['urlImagen']);
                $imagen->idAlbum = $fila['idAlbumImagen'];
                $imagen->idImagen = $fila['idImagen'];

                $imagenes[] = $imagen;
            }
            cerrarConexion($conexion);
            return $imagenes;
        } else {
            cerrarConexion($conexion); //si hubo errores o no hay imagenes devuelve falso
            return false;
        }
    }
}
