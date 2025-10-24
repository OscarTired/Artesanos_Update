<?php
// app/views/perfil.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión (ruta CORREGIDA y robusta)
require_once dirname(__DIR__, 2) . '/config/conexion.php';

// Helper de escape
function e($s){
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Aseguramos que exista la función abrirConexion
if (!function_exists('abrirConexion')) {
    die("Error crítico: La función abrirConexion() no fue cargada. Verifica el archivo config/conexion.php");
}

$conexion = abrirConexion();

if ($conexion === false || $conexion->connect_error) {
    $error_msg = ($conexion === false) ? "Imposible establecer la conexión." : $conexion->connect_error;
    die("Error de conexión a la base de datos. Detalles: " . $error_msg);
}

// Obtener ID del perfil
$perfilId = null;
if (isset($_GET['id'])) {
    $perfilId = (int)$_GET['id'];
} else if (isset($_SESSION['usuario']['id'])) {
    $perfilId = (int)$_SESSION['usuario']['id'];
}

if (!$perfilId) {
    header('Location: home.php');
    exit;
}

$isOwner = (isset($_SESSION['usuario']['id']) && (int)$_SESSION['usuario']['id'] === $perfilId);

// Consulta de datos del usuario
$sqlUser = "
    SELECT 
        u.idUsuario, u.arrobaUsuario, u.apodoUsuario, u.nombreUsuario, 
        u.apellidoUsuario, u.descripcionUsuario, u.contactoUsuario,
        fp.imagenPerfil
    FROM usuario u
    LEFT JOIN fotosdeperfil fp ON fp.idFotoPerfil = u.idFotoPerfilUsuario
    WHERE u.idUsuario = ? LIMIT 1
";

$userStmt = $conexion->prepare($sqlUser);
if (!$userStmt) {
    http_response_code(500);
    exit("Error al preparar la consulta de usuario: " . $conexion->error);
}

$userStmt->bind_param("i", $perfilId);
$userStmt->execute();
$resultUser = $userStmt->get_result();
$userData = $resultUser->fetch_assoc();
$userStmt->close();

if (!$userData) {
    http_response_code(404);
    exit("Usuario no encontrado.");
}

// Avatar: si imagenPerfil es URL completa la usamos, si es nombre local la apuntamos, sino imagen por defecto
$avatarRaw = $userData['imagenPerfil'] ?? '';
if ($avatarRaw) {
    if (preg_match("/^https?:\/\//i", $avatarRaw)) {
        $avatarUrl = $avatarRaw;
    } else {
        $avatarUrl = '../../public/uploads/avatars/' . e($avatarRaw);
    }
} else {
    $avatarUrl = '../../public/assets/images/imagen.png';
}

// Consulta de álbumes
$sqlAlbums = "
    SELECT 
        a.idAlbum, a.tituloAlbum AS nombreAlbum, a.urlPortadaAlbum,
        a.fechaCreacionAlbum,
        COUNT(i.idImagen) AS total_imagenes
    FROM album a
    LEFT JOIN imagen i ON i.idAlbumImagen = a.idAlbum
    WHERE a.idUsuarioAlbum = ? 
    GROUP BY a.idAlbum, a.tituloAlbum, a.urlPortadaAlbum, a.fechaCreacionAlbum 
    ORDER BY a.fechaCreacionAlbum DESC
";

$albumsStmt = $conexion->prepare($sqlAlbums);
if (!$albumsStmt) {
    http_response_code(500);
    exit("Error al preparar la consulta de álbumes: " . $conexion->error);
}

$albumsStmt->bind_param("i", $perfilId);
$albumsStmt->execute();
$resultAlbums = $albumsStmt->get_result();
$albums = $resultAlbums->fetch_all(MYSQLI_ASSOC);
$albumsStmt->close();

if (!is_array($albums)) {
    $albums = [];
}

// Conteo de seguidores
$sqlFollowers = "SELECT COUNT(*) AS seguidores FROM seguimiento WHERE idSeguido = ?";
$followersStmt = $conexion->prepare($sqlFollowers);
if ($followersStmt) {
    $followersStmt->bind_param("i", $perfilId);
    $followersStmt->execute();
    $resultFollowers = $followersStmt->get_result();
    $followersData = $resultFollowers->fetch_assoc();
    $followersCount = $followersData['seguidores'] ?? 0;
    $followersStmt->close();
} else {
    $followersCount = 0;
}

// Estado de seguimiento (si corresponde)
$isFollowing = false;
if (!$isOwner && isset($_SESSION['usuario']['id'])) {
    $currentUserId = (int)$_SESSION['usuario']['id'];
    $sqlIsFollowing = "SELECT 1 FROM seguimiento WHERE idSeguidor = ? AND idSeguido = ? LIMIT 1";
    $isFollowingStmt = $conexion->prepare($sqlIsFollowing);
    if ($isFollowingStmt) {
        $isFollowingStmt->bind_param("ii", $currentUserId, $perfilId);
        $isFollowingStmt->execute();
        $resultIsFollowing = $isFollowingStmt->get_result();
        $isFollowing = $resultIsFollowing->num_rows > 0;
        $isFollowingStmt->close();
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Artesanos</title>
    <link rel="icon" href="../../public/assets/images/logo.png" type="image/x-icon">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="../../public/assets/css/nav.css">
    <link rel="stylesheet" href="../../public/assets/css/perfil.css">

    <style>
        /* Layout de 5 cajas */
        .profile-grid {
            display: grid;
            grid-template-columns: 140px 1fr 2fr 1fr 240px;
            gap: 20px;
            align-items: center;
        }
        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .box-buttons-container { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-top:18px; }
            .action-buttons { align-items:center; }
        }
        .box { padding: 12px; }
        .box-avatar { display:flex; justify-content:center; align-items:flex-start; flex-direction:column; gap:10px; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit:cover; border: 4px solid #f7931e; display:block; margin:0 auto; }
        .apodo { font-size:1.6rem; font-weight:700; }
        .arroba { color:#6c757d; margin-top:4px; }
        .descripcion { font-size:1rem; color:#333; }
        .counters { display:flex; gap:20px; justify-content:flex-end; align-items:center; }
        .counter-item { text-align:center; }
        .counter-item strong { display:block; font-size:1.4rem; }
        .box-buttons-container { display:flex; align-items:center; gap:12px; justify-content:flex-end; }
        .action-buttons { display:flex; flex-direction:column; gap:8px; align-items:flex-end; }
        .action-buttons .btn { width:150px; }
        .btn-contact-circle { background-color: #22c55e; color: #fff; border: none; width: 48px; height: 48px; border-radius: 50%; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; margin-right: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .btn-contact-circle:hover { background-color: #1a9d4a; }
        .btn-orange-full { background-color: #f7931e; color:#fff; border:none; }
        .btn-orange-full:hover { background-color:#e58514; color:#fff; }
        .btn-success-full { background-color:#22c55e; color:#fff; border:none; }
        .btn-success-full:hover { background-color:#1a9d4a; }
        .profile-top { padding-top: 40px; padding-bottom: 20px; }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="container profile-top">
    <div class="profile-grid align-items-center">
        <div class="box box-avatar">
            <img src="<?= e($avatarUrl) ?>" alt="Avatar" class="profile-avatar">
        </div>

        <div class="box d-flex flex-column justify-content-center">
            <div class="apodo"><?= e($userData['apodoUsuario'] ?: $userData['nombreUsuario']) ?></div>
            <div class="arroba">@<?= e($userData['arrobaUsuario'] ?: $userData['apodoUsuario']) ?></div>
        </div>

        <div class="box">
            <p class="descripcion mb-0"><?= e($userData['descripcionUsuario'] ?: 'Sin descripción.') ?></p>
        </div>

        <div class="box">
            <div class="counters">
                <div class="counter-item">
                    <strong><?= count($albums) ?></strong>
                    <small class="text-muted">Álbumes</small>
                </div>
                <div class="counter-item">
                    <strong><?= e($followersCount) ?></strong>
                    <small class="text-muted">Seguidores</small>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-buttons-container">
                <button class="btn btn-contact-circle" data-bs-toggle="modal" data-bs-target="#modalContacto" title="Contactar">
                    <i class="bi bi-envelope-fill"></i>
                </button>

                <div class="action-buttons">
                    <?php if ($isOwner): ?>
                        <a href="editarPerfil.php" class="btn btn-orange-full d-flex align-items-center justify-content-center">
                            <i class="bi bi-pencil me-2"></i> Editar perfil
                        </a>

                        <form method="POST" action="cerrarSesion.php" style="margin:0;">
                            <button type="submit" class="btn btn-orange-full d-flex align-items-center justify-content-center">
                                <i class="bi bi-door-open me-2"></i> Cerrar sesión
                            </button>
                        </form>
                    <?php elseif (isset($_SESSION['usuario']['id'])): ?>
                        <?php 
                            $followBtnClass = $isFollowing ? 'btn-success-full' : 'btn-orange-full';
                            $followBtnText = $isFollowing ? '<i class="bi bi-check2 me-2"></i> Siguiendo' : '<i class="bi bi-person-plus me-2"></i> Seguir';
                        ?>
                        <button id="follow-btn" class="btn <?= $followBtnClass ?> d-flex align-items-center justify-content-center">
                            <?= $followBtnText ?>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-orange-full d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-plus me-2"></i> Seguir
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#albums-tab">Álbumes</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#likes-tab">Me gusta</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="albums-tab">
            <h3 class="mb-4">Álbumes (<?= count($albums) ?>)</h3>
            <?php if (empty($albums)): ?>
                <p class="text-muted text-center py-5">Sin álbumes para este usuario.</p>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($albums as $album): ?>
                        <?php
                            $coverUrl = $album['urlPortadaAlbum'] ? '../../public/uploads/imagenes/' . e($album['urlPortadaAlbum']) : '../../public/assets/images/imagen.png';
                            $albumDate = new DateTime($album['fechaCreacionAlbum']);
                        ?>
                        <div class="col">
                            <div class="card album-card shadow-sm h-100" onclick="window.location.href='album.php?id=<?= (int)$album['idAlbum'] ?>'">
                                <img src="<?= $coverUrl ?>" class="card-img-top album-cover" alt="Portada de álbum">
                                <div class="card-body">
                                    <h5 class="card-title"><?= e($album['nombreAlbum']) ?></h5>
                                    <p class="card-text small text-muted mb-1"><?= (int)$album['total_imagenes'] ?> imágenes</p>
                                    <p class="card-text small text-muted">Creado el <?= $albumDate->format('d/m/Y') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="likes-tab">
            <h3 class="mb-4">Imágenes que le gustan</h3>
            <p class="text-muted text-center py-5">Esta sección aún está en construcción.</p>
        </div>
    </div>
</div>

<!-- Modal Contacto -->
<div class="modal fade" id="modalContacto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contacto de <?= e($userData['nombreUsuario']) . ' ' . e($userData['apellidoUsuario']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p><strong>Nombre:</strong> <?= e($userData['nombreUsuario']) . ' ' . e($userData['apellidoUsuario']) ?></p>
                <p><strong>Email / Contacto:</strong> <?= e($userData['contactoUsuario'] ?: 'No disponible') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <?php if (!empty($userData['contactoUsuario'])): ?>
                    <a href="mailto:<?= e($userData['contactoUsuario']) ?>" class="btn btn-primary">Enviar email</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const followBtn = document.getElementById('follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', function() {
            if (this.classList.contains('btn-orange-full')) {
                this.classList.remove('btn-orange-full');
                this.classList.add('btn-success-full');
                this.innerHTML = '<i class="bi bi-check2 me-2"></i> Siguiendo';
            } else {
                this.classList.remove('btn-success-full');
                this.classList.add('btn-orange-full');
                this.innerHTML = '<i class="bi bi-person-plus me-2"></i> Seguir';
            }
        });
    }
});
</script>
</body>
</html>
