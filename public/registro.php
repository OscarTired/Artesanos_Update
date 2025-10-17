<?php
include '../includes/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Artesanos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/registro.css">
    <link rel="stylesheet" href="./css/footer.css">
    <link rel="stylesheet" href="./css/nav.css">
</head>
<body>
<?php
include '../includes/nav.php';
?>

<div class="container mt-4">
    
    <div class="row row-cols-1 row-cols-md-4 g-3">
        <?php
        // Consultar los datos
        $consulta = "SELECT * FROM tarjetas";
        $resultado = $conexion->query($consulta);

        // Verificar si hay registros
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                echo '
                <div class="col">
                    <div class="card h-100">
                        <img src="'.$fila['Imagenes'].'" class="card-img-top" alt="'.$fila['Titulo'].'">
                        <div class="card-body">
                            <h5 class="card-title">'.$fila['Titulo'].'</h5>
                            <p class="card-text">'.$fila['Autor'].' - '.$fila['Usuario'].'</p>
                        </div>
                    </div>
                </div>
                ';
            }
        } else {
            echo '<p class="text-center">No hay artesanos cargados todavía.</p>';
        }

        $conexion->close();
        ?>
    </div>
</div>

<div class="register-container">
    <div class="register-logo">
      <img src="../images/logo.png" alt="Artesanos" width="80">
    </div>

    <h5>Artesanos</h5>
    <p>Necesitás una cuenta para seguir viéndolo</p>

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
include '../includes/footer.php';
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

