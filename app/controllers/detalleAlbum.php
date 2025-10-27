<?php
include '../models/albumModelo.php';
include '../models/imagenModelo.php';
require_once dirname(__DIR__) . '/models/usuarioHelper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'tituloAlbum' => 'Error',
        'izquierda' => '<p class="text-danger">ID de álbum no válido.</p>',
        'derecha' => ''
    ]);
    exit;
}

$id = (int)$_GET['id'];
$modeloAlbum = new AlbumModelo();
$imagenModelo = new ImagenModelo();

$album = $modeloAlbum->mostrarAlbumId($id);
$imagenes = $imagenModelo->mostrarPorAlbum($album);
$usuario = $modeloAlbum->obtenerUsuarioDe($album->idAlbum);
$cantAlbumes = $modeloAlbum->contarAlbumesDeUsuario($usuario['idUsuario']);
$cantSeguidores = $modeloAlbum->contarSeguidoresDeUsuario($usuario['idUsuario']);

// columna izquierda, las imagenes, comentarios etc
$htmlIzquierda = '<h4>'.$album->tituloAlbum.'</h4>
<div id="carouselAlbum" class="carousel slide mb-3" data-bs-ride="false">
  <div class="carousel-inner">';
foreach ($imagenes as $i => $img) {
    $active = $i === 0 ? 'active' : '';
    $htmlIzquierda .= '<div class="carousel-item ' . $active . '" 
        data-titulo="' . htmlspecialchars($img['tituloImagen']) . '" 
        data-descripcion="' . htmlspecialchars($img['descripcionImagen']) . '">
        <div style="width: 100%; max-width: 500px; aspect-ratio: 1 / 1; overflow: hidden; margin: auto;">
            <img src="/Artesanos/public/uploads/imagenes/' . $img['urlImagen'] . '" class="w-100 h-100" style="object-fit: contain;">
        </div>
    </div>';
}

$htmlIzquierda .= '</div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselAlbum" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselAlbum" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>';

$htmlIzquierda .= '<h5 id="tituloImagen"></h5>
<p id="descripcionImagen" class="text-muted"></p>';

// columna derecha, perfil
$htmlDerecha = '<div class="text-center mt-5">
  <img src="' . obtenerAvatar($usuario['idUsuario']) . '" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
  <h5>' . $usuario['apodo'] . '</h5>
  <p class="text-muted">@' . $usuario['arroba'] . '</p>
  <div class="d-flex justify-content-center gap-3 mt-2">
    <div><strong>' . $cantAlbumes . '</strong><br><small>Álbumes</small></div>
    <div><strong>' . $cantSeguidores . '</strong><br><small>Seguidores</small></div>
  </div>
</div>';

date_default_timezone_set('America/Argentina/Buenos_Aires');
echo json_encode([
  'tituloAlbum' => $album->tituloAlbum,
  'fotoPerfil' => obtenerAvatar($usuario['idUsuario']),
  'fecha' => date('c', strtotime($album->fechaCreacion)),
  'apodo' => $usuario['apodo'],
  'usuario' => $usuario['arroba'],
  'idUsuario' => $usuario['idUsuario'],
  'izquierda' => $htmlIzquierda,
  'derecha' => $htmlDerecha
]);
