<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/models/albumModelo.php';
require_once dirname(__DIR__) . '/models/imagenModelo.php';
require_once dirname(__DIR__) . '/models/usuarioHelper.php';

// --- Validar ID ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID de álbum no válido.']);
    exit;
}

$id = (int) $_GET['id'];

$modeloAlbum = new AlbumModelo();
$imagenModelo = new ImagenModelo();

// --- Obtener álbum ---
$album = $modeloAlbum->mostrarAlbumId($id);

// Si no se encontró el álbum, devolvemos error limpio
if (!$album) {
    http_response_code(404);
    echo json_encode(['error' => 'Álbum no encontrado o no existe.']);
    exit;
}

// --- Obtener imágenes ---
$imagenes = $imagenModelo->mostrarPorAlbum($album);

// --- Obtener usuario del álbum ---
$usuario = $modeloAlbum->obtenerUsuarioDe($album->idAlbum);

// Validar usuario
if (!$usuario || !isset($usuario['idUsuario'])) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario del álbum no encontrado.']);
    exit;
}

// --- Contadores ---
$cantAlbumes = $modeloAlbum->contarAlbumesDeUsuario($usuario['idUsuario']);
$cantSeguidores = $modeloAlbum->contarSeguidoresDeUsuario($usuario['idUsuario']);

// --- HTML IZQUIERDA (carrusel e info de imágenes) ---
$htmlIzquierda = '<h4>' . htmlspecialchars($album->tituloAlbum) . '</h4>
<div id="carouselAlbum" class="carousel slide mb-3" data-bs-ride="false">
  <div class="carousel-inner">';

if (!empty($imagenes)) {
    foreach ($imagenes as $i => $img) {
        $active = $i === 0 ? 'active' : '';
        $htmlIzquierda .= '
        <div class="carousel-item ' . $active . '" 
             data-titulo="' . htmlspecialchars($img['tituloImagen'] ?? '') . '" 
             data-descripcion="' . htmlspecialchars($img['descripcionImagen'] ?? '') . '">
          <div style="width: 100%; max-width: 500px; aspect-ratio: 1 / 1; overflow: hidden; margin: auto;">
              <img src="../../public/uploads/imagenes/' . htmlspecialchars($img['urlImagen'] ?? 'sin-imagen.png') . '" 
                   class="w-100 h-100" style="object-fit: contain;">
          </div>
        </div>';
    }
} else {
    $htmlIzquierda .= '
    <div class="carousel-item active">
        <div class="text-center text-muted py-5">Sin imágenes en este álbum.</div>
    </div>';
}

$htmlIzquierda .= '
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselAlbum" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselAlbum" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<h5 id="tituloImagen"></h5>
<p id="descripcionImagen" class="text-muted"></p>';

// --- HTML DERECHA (perfil lateral) ---
$htmlDerecha = '
<div class="text-center mt-5">
  <img src="' . obtenerAvatar($usuario['idUsuario']) . '" 
       class="rounded-circle mb-3" 
       style="width: 100px; height: 100px; object-fit: cover;">
  <h5>' . htmlspecialchars($usuario['apodo'] ?? 'Sin apodo') . '</h5>
  <p class="text-muted">@' . htmlspecialchars($usuario['arroba'] ?? '') . '</p>
  <div class="d-flex justify-content-center gap-3 mt-2">
    <div><strong>' . (int)$cantAlbumes . '</strong><br><small>Álbumes</small></div>
    <div><strong>' . (int)$cantSeguidores . '</strong><br><small>Seguidores</small></div>
  </div>
</div>';

// --- Enviar respuesta final ---
date_default_timezone_set('America/Argentina/Buenos_Aires');

echo json_encode([
    'tituloAlbum' => $album->tituloAlbum,
    'fotoPerfil' => obtenerAvatar($usuario['idUsuario']),
    'fecha' => date('c', strtotime($album->fechaCreacion)),
    'apodo' => $usuario['apodo'] ?? 'Usuario',
    'usuario' => $usuario['arroba'] ?? '',
    'idUsuario' => $usuario['idUsuario'],
    'izquierda' => $htmlIzquierda,
    'derecha' => $htmlDerecha
]);
