<?php

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Artesanos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/registro.css">
    <link rel="stylesheet" href="../../public/assets/css/footer.css">
    <link rel="stylesheet" href="../../public/assets/css/nav.css">
</head>
<body>
<?php

?>


<div class="register-container">
    <div class="register-logo">
      <img src="../../public/assets/images/logo.png" alt="Artesanos" width="80">
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

?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

