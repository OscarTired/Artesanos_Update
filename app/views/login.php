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

    // 🔥 Traemos también la imagen de perfil del usuario
    $stmt = $conexion->prepare("
      SELECT 
        u.idUsuario,
        u.nombreUsuario,
        u.apellidoUsuario,
        u.apodoUsuario,
        u.arrobaUsuario,
        u.correoUsuario,
        u.contrasenaUsuario,
        f.imagenPerfil AS avatar
      FROM usuario u
      LEFT JOIN fotosdeperfil f ON u.idFotoPerfilUsuario = f.idFotoPerfil
      WHERE u.arrobaUsuario = ?
    ");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
      $user = $resultado->fetch_assoc();

      if (password_verify($password, $user['contrasenaUsuario'])) {
        // ✅ Guardamos la sesión con el nombre real del archivo de imagen
        $_SESSION['usuario'] = [
          'id'       => $user['idUsuario'],
          'nombre'   => $user['nombreUsuario'],
          'apellido' => $user['apellidoUsuario'],
          'apodo'    => $user['apodoUsuario'],
          'arroba'   => $user['arrobaUsuario'],
          'correo'   => $user['correoUsuario'],
          'avatar'   => $user['avatar'] // ← ahora contiene el nombre del archivo real
        ];

        $serverMessage = 'Inicio de sesión exitoso. Redirigiendo...';
        $serverMessageType = 'success';
        $redirScript = "<script>setTimeout(()=>{ window.location.href = 'home.php'; }, 800);</script>";
      } else {
        session_unset();
        session_destroy();
        session_start();
        $serverMessage = 'Contraseña incorrecta.';
        $serverMessageType = 'danger';
      }
    } else {
      session_unset();
      session_destroy();
      session_start();
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../public/assets/css/re.css">
  <link rel="stylesheet" href="../../public/assets/css/modalRecContra.css">
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
        <div class="form-group password-wrapper">
          <input type="password" class="form-control" name="password" id="password" placeholder="Contraseña" required minlength="6">
          <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
          <small class="error-text"></small>
        </div>

        <div class="text-start mb-3">
          <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#modalRecuperar">
            ¿Olvidaste tu contraseña?
          </a>
        </div>


        <div class="button-row">
          <button type="submit" class="btn btn-main w-100 mb-2">Iniciar sesión</button>
          <button type="button" class="btn btn-outline w-100" onclick="window.location.href='home.php#registro'">Quiero registrarme</button>
        </div>
      </form>
    </div>
  </div>
  <!-- MODAL Recuperar Contraseña -->
<div class="modal fade" id="modalRecuperar" tabindex="-1" aria-labelledby="recuperarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formRecuperar" method="POST" action="recuperar.php">
        <div class="modal-header">
          <h5 class="modal-title" id="recuperarLabel">Recuperar contraseña</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Ingresa tu correo electrónico para restablecer tu contraseña.</p>
          <input type="email" name="correo" id="correoRecuperar" class="form-control" placeholder="Tu correo" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Enviar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const togglePassword = document.getElementById("togglePassword");
      const passwordField = document.getElementById("password");
      togglePassword.addEventListener("click", () => {
        const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
        passwordField.setAttribute("type", type);
        togglePassword.classList.toggle("bi-eye");
        togglePassword.classList.toggle("bi-eye-slash");
      });
    });
  </script>

  <script>
document.getElementById("formRecuperar").addEventListener("submit", async (e) => {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);

  const response = await fetch("recuperar.php", { method: "POST", body: data });
  const result = await response.json();

  alert(result.message);

  if (result.status === "success") {
    // Redirigir al formulario para cambiar contraseña
    window.location.href = result.redirect;
  }
});
</script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
