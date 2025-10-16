<?php

// Clase controlador: coordina las acciones entre la vista y el modelo.
// Recibe solicitudes, llama al modelo correspondiente y devuelve los datos.
// No contiene lógica de negocio ni acceso directo a la base de datos.

include '../models/albumModelo.php';

class AlbumCont{
    private $albumModelo;

    public function __construct(){
        $this->albumModelo = new AlbumModelo();
    }

    public function mostrarTodos(){
        return $this->albumModelo->mostrarTodos();
    }
}
?>