<?php
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';

$conexion = abrirConexion();

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
    $stmt = $conexion->prepare("SELECT idUsuario FROM usuario WHERE arrobaUsuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $serverMessage = 'Inicio de sesión exitoso. Redirigiendo al home...';
      $serverMessageType = 'success';
      $redirScript = "<script>setTimeout(()=>{ window.location.href = 'home.php'; }, 2000);</script>";
    } else {
      $serverMessage = 'Usuario no encontrado o contraseña incorrecta.';
      $serverMessageType = 'danger';
    }
    $stmt->close();
  }
  cerrarConexion($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión | Artesanos</title>

  <!-- ENLACE AL CSS -->
  <link rel="stylesheet" href="../../public/assets/css/registro.css">
</head>
<body>


  <div class="register-container" id="registro">
    <div class="register-logo">
      <img src="../../public/assets/images/logo.png" alt="Artesanos" width="80">
    </div>

    <h5>Artesanos</h5>
    <p>¡Necesitás una cuenta para seguir viendo!</p>

    <?php if ($serverMessage): ?>
      <div class="alert alert-<?php echo $serverMessageType; ?>">
        <?php echo $serverMessage; ?>
      </div>
      <?php echo $redirScript ?? ''; ?>
    <?php endif; ?>

    <div class="register-box">
      <form action="login.php" method="POST" id="registroForm" novalidate>

        <div class="form-group">
          <input type="text" class="form-control" name="usuario" placeholder="@usuario" required>
          <small class="error-text"></small>
        </div>

        <div class="form-group">
          <input type="password" class="form-control" name="password" placeholder="Contraseña" required minlength="6">
          <small class="error-text"></small>
        </div>

        <div class="button-row">
          <button type="submit" class="btn btn-main">Iniciar sesión</button>
          <button type="button" class="btn btn-outline" onclick="window.location.href='registro.php'">Quiero registrarme</button>
        </div>
      </form>
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
</body>
</html>  


