<?php
// Asegúrate de que el usuario esté logueado para acceder a esta página
if (session_status() === PHP_SESSION_NONE) session_start();
// Se mantiene la consistencia con $_SESSION['usuario']['id']
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: login.php'); 
    exit;
}

require_once '../../config/conexion.php';
$conexion = abrirConexion();

// ID del usuario logueado
$userId = (int)$_SESSION['usuario']['id'];

// 1. Traer datos del usuario para precargar el formulario
$sqlUser = "
    SELECT 
        arrobaUsuario, apodoUsuario, nombreUsuario, apellidoUsuario, 
        descripcionUsuario, contactoUsuario, idFotoPerfilUsuario, correoUsuario 
    FROM usuario
    WHERE idUsuario = {$userId}
    LIMIT 1
";
if (!$resUser = $conexion->query($sqlUser)) {
    http_response_code(500);
    exit('Error al cargar datos de edición');
}
$data = $resUser->fetch_assoc();
$resUser->free();
$conexion->close();

if (!$data) { http_response_code(404); exit('Error: Datos de usuario no encontrados.'); }

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Lógica para obtener el avatar actual (Placeholder)
// Reemplaza 'URL_AVATAR_ACTUAL' con la lógica real para obtener la URL de la imagen.
$avatarUrl = 'URL_AVATAR_ACTUAL'; 

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/assets/css/nav.css">
  <title>Editar Perfil</title>
  <style>
    /* Estilo para el botón naranja personalizado */
    .btn-custom-orange {
      background-color: #f7931e;
      border-color: #f7931e;
      color: white;
    }
    .btn-custom-orange:hover {
      background-color: #e58514;
      border-color: #e58514;
      color: white;
    }
    .avatar-edit{width:72px; height:72px; border-radius: 50%; object-fit: cover;}
  </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <h2 class="mb-4">Editar Perfil</h2>
            
            <form action="/procesar_edicion.php" method="POST" enctype="multipart/form-data">

                <div class="card mb-4 p-4 border-0 shadow-sm">
                    <h5>Foto de perfil</h5>
                    <div class="d-flex align-items-center flex-wrap gap-2 pt-3">
                        <img src="<?= e($avatarUrl) ?>" alt="Avatar" class="avatar-edit">
                        </div>
                    <p class="small text-muted mt-3 mb-1">Cambiar foto:</p>
                    <input type="file" name="new_avatar" class="form-control" accept="image/*">
                </div>

                <div class="card mb-4 p-4 border-0 shadow-sm">
                    <h5>Datos Personales</h5>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Sobre mí (Descripción)</label>
                        <textarea class="form-control" id="bio" name="descripcionUsuario" rows="3"><?= e($data['descripcionUsuario']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombreUsuario" value="<?= e($data['nombreUsuario']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellidoUsuario" value="<?= e($data['apellidoUsuario']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" id="username" name="arrobaUsuario" value="<?= e($data['arrobaUsuario']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apodo" class="form-label">Apodo</label>
                            <input type="text" class="form-control" id="apodo" name="apodoUsuario" value="<?= e($data['apodoUsuario']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="privacidad" class="form-label">Privacidad de la cuenta</label>
                        <select class="form-select" id="privacidad" name="contactoUsuario">
                            <option value="0" <?= (int)$data['contactoUsuario'] === 0 ? 'selected' : '' ?>>Pública</option>
                            <option value="1" <?= (int)$data['contactoUsuario'] === 1 ? 'selected' : '' ?>>Privada</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico (Contacto)</label>
                        <input type="email" class="form-control" id="email" name="correoUsuario" value="<?= e($data['correoUsuario']) ?>" required>
                    </div>
                </div>

                <div class="card mb-4 p-4 border-0 shadow-sm">
                    <h5>Cambiar Contraseña</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password" name="new_password">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_new_password">
                        </div>
                    </div>
                </div>


                <div class="d-flex justify-content-end gap-3 pt-3">
                    <a href="perfil.php?id=<?= $userId ?>" class="btn btn-outline-secondary px-4 rounded-5">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-custom-orange px-4 rounded-5">
                        Guardar cambios
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>