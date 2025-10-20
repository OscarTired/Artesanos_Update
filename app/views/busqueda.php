<?php
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';

$conexion = abrirConexion();

$busqueda = trim($_GET['query'] ?? '');

if ($busqueda !== '') {
    // Consulta con filtro
    $sql = "SELECT * FROM usuario 
            WHERE nombreUsuario LIKE CONCAT('%', ?, '%') 
            OR arrobaUsuario LIKE CONCAT('%', ?, '%')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('ss', $busqueda, $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    // Si no hay búsqueda, traer todos o ninguno (según lo que prefieras)
    $sql = "SELECT * FROM usuario";
    $resultado = $conexion->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de búsqueda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- ENLACE AL CSS -->
    <link rel="stylesheet" href="../../public/assets/css/nav.css">
    <link rel="stylesheet" href="../../public/assets/css/buscar.css">
</head>
<body>


<?php include 'nav.php'; ?> 

<main class="contenedor">
    <?php if ($resultado->num_rows > 0): ?>
    <div class="grid">
        <?php while ($row = $resultado->fetch_assoc()): ?>
            <div class="tarjeta">
                <img class="banner" src="https://placehold.co/300x100" alt="Banner">
                <img class="avatar" src="https://placehold.co/80x80" alt="Avatar">

                <h3><?php echo htmlspecialchars($row['nombreUsuario']); ?></h3>
                <p>@<?php echo htmlspecialchars($row['arrobaUsuario']); ?></p>

                <div class="stats">
                    <span><?php echo htmlspecialchars($row['seguidores'] ?? '999'); ?> Seguidores</span> | 
                    <span><?php echo htmlspecialchars($row['albumes'] ?? '999'); ?> Álbumes</span>
                </div>

                <button class="seguir">Seguir</button>

                <a class="verPerfil" href="perfil.php?id=<?= urlencode($row['idUsuario']) ?>">Ver perfil</a>

            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p class="sin-resultados">
        No se encontraron resultados para <b><?php echo htmlspecialchars($busqueda); ?></b>.
    </p>
<?php endif; ?>


</main>

</body>
</html>
