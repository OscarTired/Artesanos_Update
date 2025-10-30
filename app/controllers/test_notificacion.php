<?php
require_once '../../config/conexion.php';
require_once '../../config/cerrarConexion.php';
$conexion = abrirConexion();
session_start();

// 🟢 Usuario destino: el que está logueado
$idDestino = $_SESSION['usuario']['id'] ?? 0;

// ⚙️ Usuario que realiza la acción
$idAccion = 16; // asegurate de que este ID exista en tu tabla usuario

// 🔖 Datos de ejemplo
$tipo = 'seguir'; // puede ser 'seguir', 'me_gusta', 'comentario', etc.
$idReferencia = 0; // si quisieras vincular a una publicación o comentario
$mensaje = 'El usuario 16 te ha comenzado a seguir.';
$leida = 0;

// 🧩 Insertar la notificación
$sql = "INSERT INTO notificaciones 
        (idUsuarioDestino, idUsuarioAccion, tipo, idReferencia, mensaje, leida, fecha)
        VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("iisssi", $idDestino, $idAccion, $tipo, $idReferencia, $mensaje, $leida);

if ($stmt->execute()) {
    echo "✅ Notificación insertada correctamente.";
} else {
    echo "❌ Error al insertar: " . $stmt->error;
}

$stmt->close();
cerrarConexion($conexion);
?>
