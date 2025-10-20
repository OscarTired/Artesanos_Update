<?php
include '../controllers/albumControlador.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$idUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 0;

$albumes = new AlbumCont();
$albumes = $albumes->mostrarAlbumes($idUsuario); //recuperar los albumes de la bd
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
  <link rel="stylesheet" href="../../public/assets/css/re.css">
  <link rel="stylesheet" href="../../public/assets/css/footer.css">
  <link rel="stylesheet" href="../../public/assets/css/home.css">

</head>

<body>
  <?php include 'nav.php'; ?>
  <div class="container mt-4">
    <div class="row g-5">
      <?php
      if (!empty($albumes) && count($albumes) > 0) { //si hay albumes los muestra
        foreach ($albumes as $a) {
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
              <p class="card-text">' . $a->apodoUsuario . ' - @' . $a->arrobaUsuario . '</p> 
              </div>

              </div>';
        }
      } else {
        echo '<p class="text-center">Aún no hay álbumes disponibles.<br>¡Sé el primero en publicar!';
      }
      ?>
    </div>
  </div>

  <!-- boton para crear album -->
  <div id="mostrarFormulario" data-bs-toggle="modal" data-bs-target="#modalCrearAlbum"><span class="fs-5 fw-bold" style="transform: translateY(-2px); display: inline-block;">+</span></div>

  <!--formulario crear album -->
  <div id="modalCrearAlbum" class="modal fade" tabindex="-1" aria-labelledby="modalCrearAlbumLabel" aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content p-4">

        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <form action="../controllers/albumControlador.php" method="POST" enctype="multipart/form-data" onsubmit="validarCrearAlbum()">

            <div id="formParteUno">
              <div class="row align-items-center">
                <h4 mb-3>Sube la portada</h4>
                <div class="col-lg-6 col-12 mb-3">

                  <label for="inputPortada" class="imagenParaSubir"><img src="../../public/assets/images/agregarImagen.png" alt="Subir portada"></label>
                  <input type="file" name="subirPortada" id="inputPortada" accept="image/*" required>

                  <img id="previoPortada" style="max-width: 100%; margin-top: 10px; display: none;">

                </div>
                <div class="col-lg-6 col-12 ">
                  <input type="text" class="form-control mb-3" placeholder="Título del álbum">

                  <input type="text" class="form-control mb-3" placeholder="#etiqueta">

                  <select name="privacidad" id="privacidad" class="form-select mb-3">
                    <option value="publico" selected>Para todo el mundo</option>
                    <option value="privado">Para mis seguidores</option>
                  </select>

                  <div class="text-end">

                    <button id="btnSiguiente" onclick="mostrarSigForm()" class="mt-5">Siguiente</button>

                  </div>
                </div>
              </div>
            </div>
        </div>
        <!-- segunda parte del formulario -->
        <div id="formParteDos" class="d-none">
          <h4>Sube las imágenes</h4>
          <p class="mb-4">¡Recuerda que puedes subir hasta 20 imágenes!</p>
          <div class="row align-items-center">
            <div class="col-12 text-center mb-3">


              <label for="inputImagenes" class="imagenParaSubir d-inline-block"><img src="../../public/assets/images/agregarImagen.png" alt="Subir portada"></label>
              <input type="file" name="inputImagenes" id="inputImagenes" accept="image/*" required>
              <img id="previoImagen" style="max-width: 100%; margin-top: 10px; display: none;">
            </div>


            


          </div>
        </div>
        <div id="formParteTres" class="d-none">
          <div class="col-lg-6 col-12 ">
            <input type="text" class="form-control mb-3" placeholder="Título de la imagen">

            <textarea class="form-control mb-3" id="descripcionImagen" rows="4" placeholder="Descripción" style="resize: none;"></textarea>

            <input type="text" class="form-control mb-3" placeholder="#etiqueta">

            <div class="text-end">

              <button id="btnCrear" type="submit" class="mt-5">Crear álbum</button>

            </div>
          </div>
        </div>
      </div>
    </div>
    </form>
  </div>



  </div>
  </div>

  <?php
  if (!isset($_SESSION['idUsuario'])) include 'registro.php'; //si no inicio sesion se muestra el formulario de registro
  ?>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <script src="../../public/assets/js/home.js"></script>
</body>

</html>