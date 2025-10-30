<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';

$conexion = abrirConexion();

$idUsuario = $_SESSION['usuario']['id'] ?? 0;
if ($idUsuario === 0) {
  echo '<div class="alert alert-warning">Iniciá sesión para ver tus notificaciones.</div>';
  exit;
}

$sql = "SELECT n.*, u.nombreUsuario, u.arrobaUsuario, u.idUsuario
        FROM notificaciones n
        JOIN usuario u ON n.idUsuarioAccion = u.idUsuario
        WHERE n.idUsuarioDestino = ?
        ORDER BY n.fecha DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  echo '<div class="list-group list-group-flush">';

  while ($row = $result->fetch_assoc()) {
    // Íconos según tipo
    switch ($row['tipo']) {
      case 'seguir': 
        $icon = '<i class="bi bi-person-plus-fill text-warning fs-5"></i>'; 
        break;
      case 'album_nuevo': 
        $icon = '<i class="bi bi-images text-success fs-5"></i>'; 
        break;
      case 'like': 
        $icon = '<i class="bi bi-heart-fill text-danger fs-5"></i>'; 
        break;
      case 'comentario': 
        $icon = '<i class="bi bi-chat-left-text-fill text-warning fs-5"></i>'; 
        break;
      default: 
        $icon = '<i class="bi bi-bell-fill text-secondary fs-5"></i>';
    }

    // Enlaces dinámicos
    $link = '#';
    if ($row['tipo'] === 'seguir') {
      $link = 'perfil.php?id=' . $row['idUsuarioAccion'];
    } elseif (in_array($row['tipo'], ['like', 'comentario', 'album_nuevo']) && !empty($row['idReferencia'])) {
      $link = 'ver_album.php?id=' . $row['idReferencia'];
    }

    echo '
      <a href="' . htmlspecialchars($link) . '" 
         class="list-group-item list-group-item-action d-flex align-items-start gap-3 border-0 ' . 
         ($row['leida'] ? 'bg-white shadow-sm' : 'bg-light') . ' rounded-3 mb-1 p-3" 
         style="transition: all 0.2s;">
        ' . $icon . '
        <div class="flex-grow-1">
          <div class="fw-semibold">
            @' . htmlspecialchars($row['arrobaUsuario']) . '
            <span class="text-muted small d-block mt-1">'
              . htmlspecialchars($row['mensaje']) . '
            </span>
          </div>
          <div class="text-muted small mt-1">
            ' . date("d/m/Y H:i", strtotime($row['fecha'])) . '
          </div>
        </div>
      </a>
    ';
  }

  echo '</div>';

  // Marcar todas como leídas
  $update = $conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE idUsuarioDestino = ?");
  $update->bind_param("i", $idUsuario);
  $update->execute();

} else {
  echo '<div class="alert alert-secondary text-center">No tenés notificaciones aún 😊</div>';
}

cerrarConexion($conexion);
?>

<style>
.list-group-item {
  transition: background-color 0.2s ease, transform 0.1s ease;
  border: none;
  border-radius: 8px;
  margin-bottom: 6px;
}

.list-group-item:hover {
  background-color: #f0f4ff !important;
  transform: translateY(-1px);
}

.list-group-item.bg-light {
  background-color: #fff3cd  !important; /* notificación nueva */
  border-left: 4px solid #ff9800;
}

.list-group-item.bg-white {
  background-color: #fff !important; /* leída */
}

.list-group-item-action {
  text-decoration: none;
  color: #333;
}
.list-group-item-action strong {
  color: #ff9800;
}
</style>
