
<div class="register-container" id="registro">
    <div class="register-logo">
      <img src="../../public/assets/images/logo.png" alt="Artesanos" width="80">
    </div>

    <h5>Artesanos</h5>
    <p>¡Necesitás una cuenta para seguir viendo!</p>

    <div class="register-box">
      <form action="registro.php" method="POST">

        <div class="form-row">
          <input type="text" class="form-control" name="nombre" placeholder="Nombre" required>
          <input type="text" class="form-control" name="apellido" placeholder="Apellido" required>
        </div>

        <div class="form-row">
          <input type="text" class="form-control" name="usuario" placeholder="@usuario" required>
          <input type="text" class="form-control" name="apodo" placeholder="Apodo" required>
        </div>

        <div class="form-row">
          <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
          <input type="password" class="form-control" name="confirmar" placeholder="Confirmar contraseña" required>
        </div>

        <div class="form-single">
          <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
        </div>

        <button type="button" class="btn btn-outline">Ya tengo una cuenta</button>
        <button type="submit" class="btn btn-main">Registrarse</button>

      </form>
    </div>
  </div>

<?php
  require '../../config/conexion.php';
  require '../../config/cerrarConexion.php';
  
  $conexion = abrirConexion();
  
  if($_SERVER["REQUEST_METHOD"] === "POST"){
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $usuario = $_POST["usuario"];
    $apodo = $_POST["apodo"];
    $password = $_POST["password"];
    $confirmar = $_POST["confirmar"];
    $email = $_POST["email"];

    if($password != $confirmar){
      echo "Las contraseñas no coinciden";
      exit;
    }
    $password = password_hash($password, PASSWORD_DEFAULT);

    $insert = "INSERT INTO usuario (nombreUsuario, apellidoUsuario, arrobaUsuario, apodoUsuario, contrasenaUsuario, correoUsuario)
        VALUES ('$nombre', '$apellido', '$usuario', '$apodo', '$password', '$email')";

    if(!mysqli_query($conexion,"$insert")){
      echo "Error al registrar el usuario: ".mysqli_error($conexion);
    }  

    $consulta = "SELECT * FROM usuario";
    $resultado = mysqli_query($conexion, $consulta);
    if(mysqli_num_rows($resultado)>0){
       echo "<h2>Los usuarios registrados son: </h2>";
        echo "<style>
                table {
                border-collapse: collapse;
                width: 80%;
                margin: 20px auto;
                }
                th, td {
                border: 1px solid #999;
                padding: 8px 12px;
                text-align: center;
                }
                th {
                background-color: #f2f2f2;
                }
              </style>";

        echo "<table>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Nombre</th>";
        echo "<th>Apellido</th>";
        echo "<th>Usuario</th>";
        echo "<th>Apodo</th>";
        echo "<th>Correo Electronico</th>";
        echo "<th>Contraseña</th>";
        echo "</tr>";

        while($fila = $resultado->fetch_assoc()){
            echo "<tr>";
            echo "<td>" . $fila["idUsuario"] . "</td>";
            echo "<td>" . $fila["arrobaUsuario"] . "</td>";
            echo "<td>" . $fila["apodoUsuario"] . "</td>";
            echo "<td>" . $fila["nombreUsuario"] . "</td>";
            echo "<td>" . $fila["apellidoUsuario"] . "</td>";
            echo "<td>" . $fila["correoUsuario"] . "</td>";
            echo "<td>" . $fila["contrasenaUsuario"] . "</td>";
            echo "</tr>";
        }

        echo "</table>";

    } else {
        echo "No hay vuelos registrados para esa fecha";
    }
    cerrarConexion($conexion);

  }  
  
?>

