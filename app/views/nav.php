<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="navbar navbar-expand-lg sticky-top shadow-sm" style="background-color: #FFFFF0;">
  <div class="container-fluid">
    <a class="navbar-brand" href="home.php">
      <img src="../../public/assets/images/logoConLetras.png" alt="Logo" style="height:40px;">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuNav">
      <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between w-100 gap-3 py-2">

      <!-- 🔍 Barra de búsqueda -->
    <div class="busqueda-wrapper position-relative mx-auto mt-1" style="max-width:600px;">
      <form method="GET" action="busqueda.php" class="mx-auto position-relative" id="formBusqueda" style="max-width:100%;">
    
      <!-- Campo de texto -->
      <input type="text" class="form-control buscador" name="query" placeholder="Buscar...">

      <!-- Radios -->
      <div class="btn-group position-absolute top-0 end-0 me-2 mt-1 z-1">
        <input type="radio" class="btn-check" name="tipo" id="btnArtesanos" value="artesanos" autocomplete="off">
        <label class="btn btn-blanco-negro btn-sm" for="btnArtesanos">Artesanos</label>

        <input type="radio" class="btn-check" name="tipo" id="btnAlbumes" value="albumes" autocomplete="off">
        <label class="btn btn-blanco-negro btn-sm" for="btnAlbumes">Álbumes</label>
      </div>
     </form>
    </div>
        <!-- 👤 Parte derecha (sesión / perfil) -->
        <div class="d-flex justify-content-center justify-content-lg-end align-items-center gap-3 ms-lg-3">
          <?php if (!isset($_SESSION['usuario'])): ?>
            <!-- No logueado -->
            <a href="login.php" class="btn follow-btn text-white px-4 rounded-5" role="button">
              <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
            </a>
          <?php else: ?>
            <!-- Logueado -->
            <button class="btn position-relative">
              <i class="bi bi-bell fs-5"></i>
              <span class="position-absolute top-80 start-80 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </button>

            <?php
              // 📦 Incluir helper y obtener avatar
              require_once dirname(__DIR__) . '/models/usuarioHelper.php';
              $avatarSrc = obtenerAvatar((int)$_SESSION['usuario']['id']);
            ?>

            <a href="perfil.php?id=<?= (int)$_SESSION['usuario']['id'] ?>" class="d-inline-block">
              <img src="<?php echo $avatarSrc; ?>" 
                   alt="Perfil" 
                   class="rounded-circle" 
                   style="height:40px;width:40px;object-fit:cover;">
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</nav>
<!-- Script para enviar el formulario al hacer clic en los botones -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formBusqueda");
  const radios = form.querySelectorAll('input[name="tipo"]');
  const input = form.querySelector('input[name="query"]');

  // Enviar el form cuando se cambia de opción
  radios.forEach(radio => {
    radio.addEventListener("change", () => {
      if (input.value.trim() === "") input.removeAttribute("required");
      form.submit();
    });
  });
});
  
</script>
