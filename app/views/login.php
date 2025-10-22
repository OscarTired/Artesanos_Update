<?php

session_start();
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';

$serverMessage = '';
$serverMessageType = '';
$redirScript = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $usuario = trim($_POST['usuario'] ?? '');
  $password = $_POST['password'] ?? '';

  if (strlen($usuario) < 3) {
    $serverMessage = 'El nombre de usuario debe tener al menos 3 caracteres.';
    $serverMessageType = 'warning';
  } elseif (strlen($password) < 6) {
    $serverMessage = 'La contraseña debe tener al menos 6 caracteres.';
    $serverMessageType = 'warning';
  } else {
    $conexion = abrirConexion();

    $stmt = $conexion->prepare("SELECT idUsuario, nombreUsuario, apellidoUsuario, apodoUsuario, arrobaUsuario, correoUsuario, contrasenaUsuario, idFotoPerfilUsuario FROM usuario WHERE arrobaUsuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
      $user = $resultado->fetch_assoc();

      // Verificamos password (asume hash BCRYPT guardado en contrasenaUsuario)
      if (password_verify($password, $user['contrasenaUsuario'])) {
        // Guardar la sesión con campos útiles
        $_SESSION['usuario'] = [
          'id'     => $user['idUsuario'],
          'nombre' => $user['nombreUsuario'],
          'apellido' => $user['apellidoUsuario'],
          'apodo'  => $user['apodoUsuario'],
          'arroba' => $user['arrobaUsuario'],
          'correo' => $user['correoUsuario'],
          'avatar' => $user['idFotoPerfilUsuario'] // puede ser NULL o filename
        ];


        $serverMessage = 'Inicio de sesión exitoso. Redirigiendo...';
        $serverMessageType = 'success';
        $redirScript = "<script>setTimeout(()=>{ window.location.href = 'home.php'; }, 800);</script>";
      } else {
        // CONTRASEÑA INCORRECTA
          session_unset();    // <-- AÑADIR ESTA LÍNEA
          session_destroy();  // <-- AÑADIR ESTA LÍNEA
          session_start();    // <-- AÑADIR (para que el mensaje de error se muestre)

          $serverMessage = 'Contraseña incorrecta.';
          $serverMessageType = 'danger';
      }
    } else {
      // USUARIO NO ENCONTRADO
      session_unset();    // <-- AÑADIR ESTA LÍNEA
      session_destroy();  // <-- AÑADIR ESTA LÍNEA
      session_start();    // <-- AÑADIR (para que el mensaje de error se muestre)

      $serverMessage = 'Usuario no encontrado.';
      $serverMessageType = 'danger';
    }

    $stmt->close();
    cerrarConexion($conexion);
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Iniciar Sesión | Artesanos</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/assets/css/re.css">
</head>

<body>
  <div class="register-container" id="registro">
    <div class="register-logo">
      <img src="../../public/assets/images/logo.png" alt="Artesanos" width="80">
    </div>

    <h5>Artesanos</h5>
    <p>¡Necesitás una cuenta para seguir viendo!</p>

    <?php if ($serverMessage): ?>
      <div class="alert alert-<?php echo htmlspecialchars($serverMessageType); ?> text-center" style="width:80%; margin:10px auto;">
        <?php echo htmlspecialchars($serverMessage); ?>
      </div>
      <?php echo $redirScript ?? ''; ?>
    <?php endif; ?>

    <div class="register-box">
      <form action="login.php" method="POST" id="registroForm" novalidate>
        <div class="form-group mb-3">
          <input type="text" class="form-control" name="usuario" placeholder="@usuario" required>
          <small class="error-text"></small>
        </div>
        <div class="form-group mb-3">
          <input type="password" class="form-control" name="password" placeholder="Contraseña" required minlength="6">
          <small class="error-text"></small>
        </div>

        <div class="button-row">
          <button type="submit" class="btn btn-main w-100 mb-2">Iniciar sesión</button>
          <button type="button" class="btn btn-outline w-100" onclick="window.location.href='home.php'">Quiero registrarme</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>