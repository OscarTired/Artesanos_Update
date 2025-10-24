<?php
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';
$conexion = abrirConexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $correo = trim($_POST['correo'] ?? '');

  // Verificamos si el correo existe
  $query = "SELECT * FROM usuario WHERE correoUsuario = '$correo'";
  $resultado = mysqli_query($conexion, $query);

  if (mysqli_num_rows($resultado) > 0) {
    echo "<script>
      alert('Se ha enviado un correo con el enlace para restablecer la contraseña (simulado).');
      window.location.href='actualizarContrasena.php?correo=" . urlencode($correo) . "';
    </script>";
  } else {
    echo "<script>alert('El correo no está registrado');</script>";
  }
}

cerrarConexion($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
  <style>
   body { 
    font-family: Arial; 
   text-align:center; 
   margin-top:80px; 
   } 
   form { 
    display:inline-block; 
    padding:20px; 
    border:none; 
    border-radius:10px; 
    } 
    input {
    padding:10px; 
    width:250px; 
    margin-bottom:10px;
    } 
    button { 
    padding:10px 20px; 
    background:orange; 
    color:white; 
    border:none; 
    border-radius:5px; 
    cursor:pointer; 
    }
  
  </style>
</head>
<body>
  <h2>Recuperar contraseña</h2>
  <form method="POST"> 
    <input type="email" name="correo" placeholder="Ingresá tu correo" required><br> 
    <button type="submit">Enviar</button> 
  </form>
    

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>




