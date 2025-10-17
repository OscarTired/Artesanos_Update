<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../../config/conexion.php';
$conexion = abrirConexion();

/* 1) ID del perfil a mostrar (Luismi por defecto) */
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

/* 2) Traer usuario con su foto (usa idFotoPerfilUsuario) */
$sqlUser = "
  SELECT 
    u.idUsuario,
    u.arrobaUsuario,
    u.apodoUsuario,
    u.nombreUsuario,
    u.apellidoUsuario,
    u.descripcionUsuario,
    u.idFotoPerfilUsuario,
    fp.imagenPerfil
  FROM usuario u
  LEFT JOIN fotosdeperfil fp 
         ON fp.idFotoPerfil = u.idFotoPerfilUsuario
  WHERE u.idUsuario = {$userId}
  LIMIT 1
";
if (!$resUser = $conexion->query($sqlUser)) {
  http_response_code(500);
  exit('Error al cargar usuario');
}
$u = $resUser->fetch_assoc();
$resUser->free();

if (!$u) { http_response_code(404); exit('Usuario no encontrado'); }

/* 3) Normalizar a tu array $user usado en la vista */
$user = [
  'avatar'     => $u['imagenPerfil'] ?: 'https://i.pravatar.cc/120',
  'name'       => trim($u['nombreUsuario'] . ' ' . $u['apellidoUsuario']),
  'username'   => $u['arrobaUsuario'],            // ej: luismi, chayanne, davib, rickym
  'bio'        => (string)$u['descripcionUsuario'],
  'followers'  => 999,                            // mock mientras no tengas tabla
  'following'  => 999,
  'is_private' => false,                          // ajústalo si luego agregas campo
];

/* 4) Permisos (dueño/visitante) */
$isOwner  = isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$u['idUsuario'];
$puedeVer = !$user['is_private'] || $isOwner;     // por ahora true

/* 5) Álbumes del usuario */
$soloPublicos = !$isOwner; // visitantes ven solo públicos
$sqlAlbums = "
  SELECT idAlbum, tituloAlbum, esPublicoAlbum, urlPortadaAlbum
  FROM album
  WHERE idUsuarioAlbum = {$userId}" . ($soloPublicos ? " AND esPublicoAlbum = 1" : "") . "
  ORDER BY idAlbum DESC
";
if (!$resAlbums = $conexion->query($sqlAlbums)) {
  $albums = [];
} else {
  $albums = $resAlbums->fetch_all(MYSQLI_ASSOC);
  $resAlbums->free();
}

/* 6) Helpers */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function kfmt($n){ return $n>=1000 ? number_format($n/1000, ($n%1000===0?0:1)).'K' : (string)$n; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/assets/css/nav.css">
  <title><?= e($user['name']) ?> (@<?= e($user['username']) ?>)</title>
  <style>
    body{background:#fff;}
    .profile-bar{border-bottom:1px solid #e9ecef}
    .avatar{width:64px;height:64px;border-radius:50%;object-fit:cover}
    .muted{color:#6c757d}
    .metric .num{font-weight:700}
    .tabbar{border-bottom:3px solid #22c55e}
    .private-box{border:0;text-align:center;color:#212529;font-weight:700;margin-top:48px}
    .private-box small{display:block;font-weight:600}
  </style>
</head>
<body>

<?php include 'nav.php'; ?>

<!-- HEADER PERFIL -->
<div class="container py-3 profile-bar">
  <div class="d-flex align-items-center gap-3">
    <img class="avatar" src="<?= e($user['avatar']) ?>" alt="avatar">
    <div class="flex-grow-1">
      <div class="d-flex align-items-center gap-2">
        <h5 class="mb-0"><?= e($user['name']) ?></h5>
        <span class="text-muted">@<?= e($user['username']) ?></span>
      </div>
      <?php if ($user['bio']): ?>
        <div class="small muted mt-1"><?= e($user['bio']) ?></div>
      <?php endif; ?>
    </div>

    <div class="d-flex align-items-center gap-4 me-3">
      <div class="metric text-center">
        <div class="num"><?= kfmt((int)$user['followers']) ?></div>
        <div class="small muted">Seguidores</div>
      </div>
      <div class="metric text-center">
        <div class="num"><?= kfmt((int)$user['following']) ?></div>
        <div class="small muted">Abones</div>
      </div>
    </div>

    <?php if ($isOwner): ?>
      <div class="d-flex align-items-center gap-2">
        <a href="/perfil/editar.php" class="btn btn-outline-secondary px-4 rounded-5">
          <i class="bi bi-pencil-square me-1"></i> Editar perfil
        </a>
        <form action="/logout.php" method="post" class="m-0">
          <button type="submit" class="btn btn-danger px-4 rounded-5">
            <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
          </button>
        </form>
      </div>
    <?php else: ?>
      <a href="/seguir.php?usuario=<?= (int)$u['idUsuario'] ?>" class="btn follow-btn text-white px-4 rounded-5">
        <i class="bi bi-person-plus me-1"></i> Seguir
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- TABS / ICONO CUADRÍCULA -->
<div class="container tabbar">
  <div class="d-flex align-items-center gap-3 py-2">
    <i class="bi bi-grid-3x3-gap-fill fs-4 text-success"></i>
    <div class="ms-auto"><i class="bi bi-heart text-muted"></i></div>
  </div>
</div>

<!-- CONTENIDO -->
<div class="container">
  <?php if (!$puedeVer): ?>
    <div class="private-box">
      <div>Este perfil es privado.</div>
      <small>¡Síguelo para ver sus artesanías!</small>
    </div>
  <?php else: ?>
    <?php if (empty($albums)): ?>
      <div class="text-center py-5 text-muted">Sin álbumes para este usuario.</div>
    <?php else: ?>
      <div class="row g-3 py-3">
        <?php foreach ($albums as $alb): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
            <div class="ratio ratio-1x1">
                <img src="<?= e($alb['urlPortadaAlbum']) ?>"
                    class="w-100 h-100 object-fit-cover"
                    alt="<?= e($alb['tituloAlbum']) ?>">
            </div>
            <div class="card-body py-2">
                <div class="small fw-semibold text-truncate"
                    title="<?= e($alb['tituloAlbum']) ?>">
                <?= e($alb['tituloAlbum']) ?>
                </div>
                <?php if ((int)$alb['esPublicoAlbum'] === 0): ?>
                <span class="badge bg-secondary mt-1">Privado</span>
                <?php endif; ?>
            </div>
            </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
