<?php
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';

$conexion = abrirConexion();

$busqueda = trim($_GET['query'] ?? '');

// 🟢 Detectar tipo (si no se elige, decide automáticamente)
if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
} else {
    // Si hay texto de búsqueda, buscar en álbumes; si no, mostrar artesanos
    $tipo = ($busqueda !== '') ? 'albumes' : 'artesanos';
}

// Mostrar todos los artesanos
if ($tipo === 'artesanos' && $busqueda === '') {
    $sql = "
        SELECT u.*, 
            f.imagenPerfil AS fotoPerfil,
            (SELECT COUNT(*) FROM seguimiento s WHERE s.idSeguido = u.idUsuario) AS totalSeg,
            (SELECT COUNT(*) FROM album a WHERE a.idUsuarioAlbum = u.idUsuario) AS totalAlb
        FROM usuario u
        LEFT JOIN fotosdeperfil f ON f.idFotoPerfil = u.idFotoPerfilUsuario
    ";
    $resultado = $conexion->query($sql);
}

// Mostrar todos los álbumes
elseif ($tipo === 'albumes' && $busqueda === '') {
    $sql = "
        SELECT a.*, u.apodoUsuario, u.arrobaUsuario, f.imagenPerfil AS fotoPerfil
        FROM album a
        INNER JOIN usuario u ON u.idUsuario = a.idUsuarioAlbum
        LEFT JOIN fotosdeperfil f ON f.idFotoPerfil = u.idFotoPerfilUsuario
        ORDER BY a.idAlbum DESC
    ";
    $resultado = $conexion->query($sql);
}

// 🔍 Búsqueda con texto (busca según el tipo elegido)
elseif ($busqueda !== '') {
    if ($tipo === 'artesanos') {
        // Buscar artesanos
        $sql = "
            SELECT u.*, 
                f.imagenPerfil AS fotoPerfil,
                (SELECT COUNT(*) FROM seguimiento s WHERE s.idSeguido = u.idUsuario) AS totalSeg,
                (SELECT COUNT(*) FROM album a WHERE a.idUsuarioAlbum = u.idUsuario) AS totalAlb
            FROM usuario u
            LEFT JOIN fotosdeperfil f ON f.idFotoPerfil = u.idFotoPerfilUsuario
            WHERE (u.nombreUsuario LIKE CONCAT('%', ?, '%')
                OR u.arrobaUsuario LIKE CONCAT('%', ?, '%')
                OR u.apodoUsuario LIKE CONCAT('%', ?, '%'))
        ";
    } elseif ($tipo === 'albumes') {
        // Buscar álbumes
        $sql = "
            SELECT a.*, u.apodoUsuario, u.arrobaUsuario, f.imagenPerfil AS fotoPerfil
            FROM album a
            INNER JOIN usuario u ON u.idUsuario = a.idUsuarioAlbum
            LEFT JOIN fotosdeperfil f ON f.idFotoPerfil = u.idFotoPerfilUsuario
            WHERE a.tituloAlbum LIKE CONCAT('%', ?, '%')
               OR u.apodoUsuario LIKE CONCAT('%', ?, '%')
               OR u.arrobaUsuario LIKE CONCAT('%', ?, '%')
            ORDER BY a.idAlbum DESC
        ";
    } else {
        // Si no se marcó tipo, busca en álbumes por defecto
        $sql = "
            SELECT a.*, u.apodoUsuario, u.arrobaUsuario, f.imagenPerfil AS fotoPerfil
            FROM album a
            INNER JOIN usuario u ON u.idUsuario = a.idUsuarioAlbum
            LEFT JOIN fotosdeperfil f ON f.idFotoPerfil = u.idFotoPerfilUsuario
            WHERE a.tituloAlbum LIKE CONCAT('%', ?, '%')
               OR u.apodoUsuario LIKE CONCAT('%', ?, '%')
               OR u.arrobaUsuario LIKE CONCAT('%', ?, '%')
            ORDER BY a.idAlbum DESC
        ";
    }

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('sss', $busqueda, $busqueda, $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
}


// Sin resultado o error
else {
    $resultado = false;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de búsqueda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="../../public/assets/css/nav.css">
    <link rel="stylesheet" href="../../public/assets/css/buscar.css">
    <link rel="stylesheet" href="../../public/assets/css/home.css">
</head>
<body>

<?php include 'nav.php'; ?> 

<main class="contenedor">
<?php if ($resultado && $resultado->num_rows > 0): ?>
    <div class="grid">
        <?php if ($tipo === 'artesanos'): ?>
            <!--  Mostrar artesanos -->
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <?php
                $apodo = htmlspecialchars($row['apodoUsuario']);
                $arroba = htmlspecialchars($row['arrobaUsuario']);
                $totalSeg = (int)$row['totalSeg'];
                $totalAlb = (int)$row['totalAlb'];
                $foto = !empty($row['fotoPerfil'])
                    ? '../../public/uploads/avatars/' . htmlspecialchars($row['fotoPerfil'])
                    : '../../public/assets/images/logo.png';
                
                $colores = ['#ffeedb', '#ffe0cc', '#ffd1a3', '#ffd6cc', '#e0ffe0', '#d9e8ff', '#f0d9ff', '#fff6cc'];
                $colorRandom = $colores[array_rand($colores)];
                ?>

                <div class="tarjeta">
                    <div class="banner" style="background-color: <?=$colorRandom?>;"></div>
                    <img class="avatar" src="<?= $foto ?>" alt="Avatar">
                    <h3><?= $apodo ?></h3>
                    <p>@<?= $arroba ?></p>
                    <div class="stats">
                        <span><?= $totalSeg ?> Seguidores</span> | 
                        <span><?= $totalAlb ?> Álbumes</span>
                    </div>
                    <a class="verPerfil" href="perfil.php?id=<?= urlencode($row['idUsuario']) ?>">Ver perfil</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!--  Mostrar álbumes -->
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <?php
                $titulo = htmlspecialchars($row['tituloAlbum']);
                $apodo = htmlspecialchars($row['apodoUsuario']);
                $arroba = htmlspecialchars($row['arrobaUsuario']);
                $foto = !empty($row['fotoPerfil'])
                    ? '../../public/uploads/avatars/' . htmlspecialchars($row['fotoPerfil'])
                    : '../../public/assets/images/logo.png';
                $portada = !empty($row['urlPortadaAlbum'])
                    ? '../../public/uploads/portadas/' . htmlspecialchars($row['urlPortadaAlbum'])
                    : 'https://placehold.co/300x100?text=Sin+Portada';
                
                ?>
                <div class="tarjeta">
                    <img class="portadas" style="border-radius: 10px; width: 100%; height: 200px; object-fit: cover; object-position: center;" src="<?= htmlspecialchars($portada) ?>" alt="Portada del álbum">
                    <img class="avatar" src="<?= $foto ?>" alt="Avatar usuario">
                    <h3><?= $titulo ?></h3>
                    <p>de <?= $apodo ?> (@<?= $arroba ?>)</p>
                    <a href="#" 
                       class="abrir-modal-album verPerfil" 
                       data-id="<?= htmlspecialchars($row['idAlbum']) ?>" 
                       data-bs-toggle="modal" 
                       data-bs-target="#modalDetalleAlbum">
                       Ver álbum
                    </a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p class="sin-resultados">
        No se encontraron resultados para <b><?= htmlspecialchars($busqueda) ?></b>.
    </p>
<?php endif; ?>

<?php cerrarConexion($conexion); ?>
</main>
<!-- 🟦 Modal detalle de álbum (copiado desde home.php) -->
<div class="modal fade" id="modalDetalleAlbum" tabindex="-1" aria-labelledby="modalDetalleAlbumLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
          <img id="modalFotoPerfil" src="" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
          <h5 class="modal-title mb-0" id="modalDetalleAlbumLabel">Nombre del usuario</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button id="btnSeguir" class="btn btn-outline-primary btn-sm">Seguir</button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-lg-9" id="detalleAlbumIzquierda">
            en construcción...
          </div>
          <div class="col-lg-3" id="detalleAlbumDerecha">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/assets/js/home.js"></script>

</body>
</html>


