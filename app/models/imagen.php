<?php

class Imagen{
    private $idImagen, $titulo, $descripcion, $etiqueta, $enRevision, $fecha, $url, $idAlbum;
    public function __construct($titulo,$descripcion,$etiqueta,$fecha,$url){
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
        $this->etiqueta = $etiqueta;
        $this->fecha = $fecha;
        $this->url = $url;

    }
    public function __get($variable){
        return $this->$variable;
    }
    public function __set($variable, $valor){
        $this->$variable = $valor;
    }

}

?>