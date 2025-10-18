<?php
include '../controllers/albumControlador.php';
$albumes = new AlbumCont();
$albumes = $albumes->mostrarTodos(); //recuperar los albumes de la bd
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artesanos</title>
    <link rel="icon" href="../../public/assets/images/logo.png" type="image/x-icon">
    
    <!--bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../../public/assets/css/nav.css">
    <link rel="stylesheet" href="../../public/assets/css/registro.css">
    <link rel="stylesheet" href="../../public/assets/css/footer.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-4">
        <div class="row g-5">
          <?php
          if(!empty($albumes) && count($albumes) >0 ){ //si hay albumes los muestra
            foreach ($albumes as $a){
              echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3 ">
              
              <div class="card-body">
              <img class="card-img-top " style="border-radius: 10px; height: 200px; object-fit: cover;" src="' . $a->urlPortada . '"/>

              <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">' . $a->tituloAlbum . '</h5>
              <div class="d-flex gap-2">
              <img src="../../public/assets/images/like.png" alt="Me gusta" class="img-fluid me-4 me-sm-1" style="max-height: 25px; cursor: pointer; margin-top: 5px">
              <img src="../../public/assets/images/comentario.png" alt="Comentario" class="img-fluid me-4 me-sm-1" style="max-height: 23px; cursor: pointer; margin-top: 5px">
              </div>

              </div>
              <p class="card-text">' . $a->apodoUsuario .' - @' . $a->arrobaUsuario . '</p> 
              </div>

              </div>';
            }
          }else{
            echo '<p class="text-center">Aún no hay álbumes disponibles.<br>¡Sé el primero en publicar!';
          }
            
          ?>
        </div>
    </div>

    <?php
    if (!isset($_SESSION['idUsuario'])) include 'registro.php'; //si no inicio sesion se muestra el formulario de registro
    ?>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>