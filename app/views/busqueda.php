<?php
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';

$conexion = abrirConexion();

$busqueda = trim($_GET['query'] ?? '');

if ($busqueda !== '') {
    // ✅ Consulta optimizada con subconsultas para contar seguidores y álbumes
    $sql = "
        SELECT u.*, 
            f.imagenPerfil AS fotoPerfil,
            (SELECT COUNT(*) FROM seguimiento s WHERE s.idSeguido = u.idUsuario) AS totalSeg,
            (SELECT COUNT(*) FROM album a WHERE a.idUsuarioAlbum = u.idUsuario) AS totalAlb
        FROM usuario u
        LEFT JOIN fotosdeperfil f ON f.idFotoPerfil = u.idFotoPerfilUsuario
        WHERE u.nombreUsuario LIKE CONCAT('%', ?, '%')
           OR u.arrobaUsuario LIKE CONCAT('%', ?, '%')
           OR u.apodoUsuario LIKE CONCAT('%', ?, '%')
    ";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        die('Error en la consulta SQL: ' . $conexion->error);
    }
    $stmt->bind_param('sss', $busqueda, $busqueda, $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    // ✅ Si no hay búsqueda, opcionalmente mostrar todos
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
</head>
<body>

<?php include 'nav.php'; ?> 

<main class="contenedor">
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="grid">
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <?php
                // ✅ Datos del usuario
                $apodo = htmlspecialchars($row['apodoUsuario']);
                $arroba = htmlspecialchars($row['arrobaUsuario']);
                $totalSeg = (int)$row['totalSeg'];
                $totalAlb = (int)$row['totalAlb'];

                // ✅ Foto de perfil (desde la base de datos)
                if (!empty($row['fotoPerfil'])) {
                    $foto = '../../public/uploads/avatars/' . htmlspecialchars($row['fotoPerfil']);
                } else {
                    $foto = '../../public/assets/images/logo.png';
                }
                ?>
                
                <div class="tarjeta">
                    <img class="banner" src="https://placehold.co/300x100" alt="Banner">
                    <img class="avatar" src="<?= $foto ?>" alt="Avatar">

                    <h3><?= $apodo ?></h3>
                    <p>@<?= $arroba ?></p>

                    <div class="stats">
                        <span><?= $totalSeg ?> Seguidores</span> | 
                        <span><?= $totalAlb ?> Álbumes</span>
                    </div>

                    <button class="seguir">Seguir</button>
                    <a class="verPerfil" href="perfil.php?id=<?= urlencode($row['idUsuario']) ?>">Ver perfil</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="sin-resultados">
            No se encontraron resultados para <b><?= htmlspecialchars($busqueda) ?></b>.
        </p>
    <?php endif; ?>

    <?php cerrarConexion($conexion); ?>
</main>


</body>
</html>


