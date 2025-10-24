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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

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
        <div class="form-group password-wrapper">
          <input type="password" 
            class="form-control" 
            name="password" 
            id="password" 
            placeholder="Contraseña" 
            required 
            minlength="6">
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
          <button type="button" class="btn btn-outline w-100" onclick="window.location.href='registro.php'">Quiero registrarme</button>
        </div>
      </form>
    </div>
  </div>

 <!-- Modal Recuperar Contraseña -->
<div class="modal" id="modalRecuperar" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4" style="pointer-events: auto;">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center">Recuperar contraseña</h5>
      </div>
      <div class="modal-body">
        <form method="POST" action="recuperar.php">
          <input id="correoRecuperar" type="email" class="form-control mb-3" name="correo" placeholder="Ingresá tu correo" required>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning">Enviar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("registroForm");
    const inputs = form.querySelectorAll(".form-control");

    inputs.forEach(input => {
      input.addEventListener("input", () => validarCampo(input));
    });

    form.addEventListener("submit", e => {
      let valido = true;
      inputs.forEach(input => {
        if (!validarCampo(input)) valido = false;
      });
      if (!valido) e.preventDefault();
    });

    function validarCampo(input) {
      const errorText = input.parentElement.querySelector(".error-text");
      let valido = true;
      let mensaje = "";

      if (input.name === "usuario" && input.value.trim().length < 3) {
        valido = false;
        mensaje = "El usuario debe tener al menos 3 caracteres.";
      } else if (input.name === "password" && input.value.length < 6) {
        valido = false;
        mensaje = "La contraseña debe tener al menos 6 caracteres.";
      }

      if (!valido) {
        input.classList.remove("is-valid");
        input.classList.add("is-invalid");
        errorText.textContent = mensaje;
      } else {
        input.classList.remove("is-invalid");
        input.classList.add("is-valid");
        errorText.textContent = "";
      }
      return valido;
    }
  });
  </script>

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
  

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>