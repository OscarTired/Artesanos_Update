<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';

$conexion = abrirConexion();

$idUsuario = $_SESSION['usuario']['id'] ?? 0;


if ($idUsuario === 0) {
  echo "Debe iniciar sesión para ver sus notificaciones.";
  exit;
}

// Consultar notificaciones
$sql = "SELECT n.*, 
               u.nombreUsuario, 
               u.arrobaUsuario 
        FROM notificaciones n
        JOIN usuario u ON n.idUsuarioAccion = u.idUsuario
        WHERE n.idUsuarioDestino = ?
        ORDER BY n.fecha DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Notificaciones</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">

  <h3>🔔 Mis notificaciones</h3>
  <hr>
  <?php if ($result->num_rows > 0): ?>
  <?php while ($row = $result->fetch_assoc()): ?>
    <?php
      // Determinar ícono según tipo
      switch ($row['tipo']) {
        case 'seguir':
          $icon = '👤';
          break;
        case 'album_nuevo':
          $icon = '🖼️';
          break;
        case 'like':
          $icon = '❤️';
          break;
        case 'comentario':
          $icon = '💬';
          break;
        default:
          $icon = '🔔';
      }

      // Generar mensaje con link si tiene referencia
      $mensaje = htmlspecialchars($row['mensaje']);
      if (!empty($row['idReferencia'])) {
          switch ($row['tipo']) {
              case 'like':
              case 'comentario':
              case 'album_nuevo':
                  $mensaje = '<a href="ver_album.php?id=' . $row['idReferencia'] . '">' . $mensaje . '</a>';
                  break;
          }
      }
    ?>

    <div class="alert <?= $row['leida'] ? 'alert-secondary' : 'alert-primary' ?> shadow-sm">
      <?= $icon ?> <strong>@<?= htmlspecialchars($row['arrobaUsuario']) ?></strong>
      <?= $mensaje ?><br>
      <small class="text-muted"><?= htmlspecialchars($row['fecha']) ?></small>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <div class="alert alert-secondary">No tenés notificaciones aún.</div>
<?php endif; ?>

</body>
</html>

<?php
// Marcar todas como leídas
$update = $conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE idUsuarioDestino = ?");
$update->bind_param("i", $idUsuario);
$update->execute();

cerrarConexion($conexion);
?>
