<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

require_once '../../config/conexion.php';
$conexion = abrirConexion();

$userId = (int)$_SESSION['usuario']['id'];

$uploadDir = '../../public/uploads/avatars/';

$updateAvatarSql = "";
$newAvatarId = (int)($_POST['selected_history_avatar_id'] ?? 0); 
$currentAvatarId = (int)($_POST['old_avatar_id'] ?? 0); 

// -------------------------------------------------------------
// --- 1. PROCESAR CAMBIO DE AVATAR (Subida o Historial) ---
// -------------------------------------------------------------

// A. Si se subió un nuevo archivo
if (isset($_FILES['new_avatar']) && $_FILES['new_avatar']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['new_avatar'];
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid("avatar_") . '.' . $fileExt;
    $destination = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $dbPath = $destination; 

        // Insertar el nuevo registro en la tabla fotosdeperfil
        // **CORRECCIÓN 2: Usamos 'idUsuario'**
        $insertPhotoSql = $conexion->prepare("INSERT INTO fotosdeperfil (idUsuario, imagenPerfil) VALUES (?, ?)");
        
        // Si esta línea sigue fallando con error "Unknown column...", 
        // ¡debes cambiar 'idUsuario' por el nombre real de tu columna!
        
        $insertPhotoSql->bind_param("is", $userId, $dbPath);
        $insertPhotoSql->execute();
        $newAvatarId = $conexion->insert_id;
    }
}
// B. Si se seleccionó una foto del historial
elseif ($newAvatarId > 0 && $newAvatarId !== $currentAvatarId) {
    // Ya tenemos el $newAvatarId listo para usarse.
}
else {
    $newAvatarId = 0;
}

// Construir la parte de la consulta SQL para el avatar si hay un cambio
if ($newAvatarId > 0 && $newAvatarId !== $currentAvatarId) {
    $updateAvatarSql = "idFotoPerfilUsuario = ?, ";
    $_SESSION['usuario']['idFotoPerfilUsuario'] = $newAvatarId;
}


// -------------------------------------------------------------
// --- 2. PROCESAR DATOS DEL PERFIL Y CONTRASEÑA ---
// -------------------------------------------------------------

$fields = [];
$values = [];
$types = ''; 

$data = [
    'arrobaUsuario'     => $_POST['arrobaUsuario'] ?? '',
    'apodoUsuario'      => $_POST['apodoUsuario'] ?? '',
    'nombreUsuario'     => $_POST['nombreUsuario'] ?? '',
    'apellidoUsuario'   => $_POST['apellidoUsuario'] ?? '',
    'descripcionUsuario'=> $_POST['descripcionUsuario'] ?? '',
    'contactoUsuario'   => (int)($_POST['contactoUsuario'] ?? 0), 
    'correoUsuario'     => $_POST['correoUsuario'] ?? '',
];

foreach ($data as $key => $value) {
    $fields[] = "{$key} = ?";
    if ($key === 'contactoUsuario') {
        $types .= 'i';
        $values[] = $value;
    } else {
        $types .= 's';
        $values[] = $value;
    }
}

// PROCESAR CONTRASEÑA
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_new_password'] ?? '';

if (!empty($newPassword) && $newPassword === $confirmPassword) {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $fields[] = "contrasenaUsuario = ?";
    $values[] = $hashedPassword;
    $types .= 's';
}


// -------------------------------------------------------------
// --- 3. EJECUTAR LA CONSULTA FINAL ---
// -------------------------------------------------------------

if (!empty($fields) || !empty($updateAvatarSql)) {
    
    if (!empty($updateAvatarSql)) {
        array_unshift($values, $newAvatarId); 
        $types = 'i' . $types;               
    }

    $values[] = $userId;
    $types .= 'i';

    $setClauses = array_merge((!empty($updateAvatarSql) ? [substr($updateAvatarSql, 0, -2)] : []), $fields);
    
    $sql = "UPDATE usuario SET " . implode(', ', $setClauses) . " WHERE idUsuario = ?";

    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $bind_params = array_merge([$types], $values);
        $refs = [];
        foreach($bind_params as $key => $value) {
            $refs[$key] = &$bind_params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $refs);
        
        $stmt->execute();
        $stmt->close();

    } else {
        error_log("Error al preparar la consulta: " . $conexion->error);
    }
}

$conexion->close();

header("Location: perfil.php?id={$userId}");
exit;