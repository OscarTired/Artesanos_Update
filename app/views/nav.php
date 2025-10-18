<nav class="navbar navbar-expand-lg sticky-top shadow-sm" style="background-color: #FFFFF0; ">
  <div class="container-fluid">

    <a class="navbar-brand" href="home.php">
      <img src="../../public/assets/images/logoConLetras.png" alt="Logo" style="height: 40px;">
    </a>

    <!-- Botón hamburguesa -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuNav">
      <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between w-100 gap-3 py-2">

        <!-- Buscador -->
        <div class="busqueda-wrapper position-relative mx-auto mt-1" style="max-width:600px;">
          <div class="btn-group position-absolute top-0 end-0 me-2 mt-1 z-1">
            <input type="radio" class="btn-check" name="tipo" id="btnArtesanos" value="artesanos" autocomplete="off" checked>
            <label class="btn btn-blanco-negro btn-sm" for="btnArtesanos">Artesanos</label>

            <input type="radio" class="btn-check" name="tipo" id="btnAlbumes" value="albumes" autocomplete="off">
            <label class="btn btn-blanco-negro btn-sm" for="btnAlbumes">Álbumes</label>
          </div>

          <form method="GET" action="buscar.php" class="mx-auto" style="max-width:100%;">
            <input type="text" class="form-control buscador" name="query" placeholder="Buscar..." required>
          </form>
        </div>
        <div class="d-flex justify-content-center justify-content-lg-end align-items-center gap-3 ms-lg-3">

        <!-- iconos de notificacion y perfil -->
         <?php
         if(!isset($_SESSION['usuario'])) {
          echo'
          <a href="#registro" class="btn follow-btn text-white px-4 rounded-5" role="button"><i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión</a>
            ';
         }else{
           echo'
              <button class="btn position-relative">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-80 start-80 translate-middle p-1 bg-danger border border-light rounded-circle">
                  <span class="visually-hidden">Notificación</span>
                </span>
              </button>
              <a href="perfil.php">
                <img src="../../public/assets/images/imagen.png" alt="Perfil" class="rounded-circle" style="height: 40px; width: 40px; object-fit: cover;">
              </a>
           ';
         }
         ?>



        </div>

      </div>
    </div>

  </div>
</nav>
