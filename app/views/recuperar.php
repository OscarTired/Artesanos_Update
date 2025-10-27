<?php
header('Content-Type: application/json');
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';

$conexion = abrirConexion();

$response = ["status" => "error", "message" => "Error inesperado"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $correo = trim($_POST['correo'] ?? '');

  if (!empty($correo)) {
    $stmt = $conexion->prepare("SELECT idUsuario FROM usuario WHERE correoUsuario = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
      $response = [
        "status" => "success",
        "message" => "Se ha enviado un correo con el enlace para restablecer la contraseña (simulado).",
        "redirect" => "actualizarContrasena.php?correo=" . urlencode($correo)
      ];
    } else {
      $response = [
        "status" => "warning",
        "message" => "El correo no está registrado."
      ];
    }

    $stmt->close();
  } else {
    $response = ["status" => "warning", "message" => "Por favor ingresá un correo válido."];
  }
}

cerrarConexion($conexion);

echo json_encode($response);
exit;
?>
