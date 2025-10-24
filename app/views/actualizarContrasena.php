<?php
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php'; // si tenés esta función
$conexion = abrirConexion();

$correo = $_GET['correo'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $correo = trim($_POST['correo'] ?? '');
  $nueva = trim($_POST['nueva'] ?? '');

  if (!empty($nueva)) {
    $hash = password_hash($nueva, PASSWORD_DEFAULT);

    // ✅ Usar prepared statement para evitar inyección SQL
    $stmt = $conexion->prepare("UPDATE usuario SET contrasenaUsuario = ? WHERE correoUsuario = ?");
    $stmt->bind_param("ss", $hash, $correo);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
      echo "<script>
        alert('Tu contraseña ha sido actualizada exitosamente.');
        window.location.href='login.php';
      </script>";
    } else {
      echo "<script>
        alert('No se encontró el correo o no se pudo actualizar la contraseña.');
      </script>";
    }

    $stmt->close();
  } else {
    echo "<script>alert('La contraseña no puede estar vacía');</script>";
  }
}

cerrarConexion($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nueva contraseña</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin-top: 80px;
      background-color: white;
    }
    form {
      display: inline-block;
      padding: 25px 30px;
      border: none;
      border-radius: 10px;
      background: #fff;
      box-shadow: none;
    }
    input {
      padding: 10px;
      width: 250px;
      margin-bottom: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      padding: 10px 20px;
      background: green;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background: #0c7a0c;
    }
  </style>
</head>
<body>
  <h2>Ingresá tu nueva contraseña</h2>

<div style="max-width: 400px; margin: auto; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
  <form id="passwordForm">
    <input type="password" id="newPassword" placeholder="Nueva contraseña" 
           style="width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ccc;"><br>

    <input type="password" id="confirmPassword" placeholder="Confirmar nueva contraseña" 
           style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ccc;"><br>

    <button type="submit" style="background-color: green; color: white; padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer;">
      Guardar cambio
    </button>
  </form>

  <p id="message" style="color: red; margin-top: 10px; font-weight: bold;"></p>
</div>

<script>
  document.getElementById("passwordForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Evita el envío automático del formulario

    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const message = document.getElementById("message");

    // Validaciones básicas
    if (newPassword === "" || confirmPassword === "") {
      message.style.color = "red";
      message.textContent = "Por favor, completá ambos campos.";
      return;
    }

    if (newPassword !== confirmPassword) {
      message.style.color = "red";
      message.textContent = "Las contraseñas no coinciden.";
      return;
    }

    // Si todo está correcto
    message.style.color = "green";
    message.textContent = "Contraseña actualizada correctamente ✅";

    // Simular un pequeño retraso y redirigir
    setTimeout(() => {
      window.location.href = "login.php";
    }, 2000); // redirige después de 2 segundos
  });
</script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
