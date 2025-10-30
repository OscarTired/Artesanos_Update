<?php
session_start();
require_once '../../config/conexion.php';
$conexion = abrirConexion();

$idUsuarioLike = $_SESSION['usuario']['id'];
$idImagen = $_POST['idImagen'];

// Insertar el "me gusta"
$sql = "INSERT INTO megusta (idImagenLike, idUsuarioLike, fechaLike)
        VALUES (?, ?, NOW())";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $idImagen, $idUsuarioLike);
$stmt->execute();

// Obtener el dueño del álbum al que pertenece la imagen
$sqlAutor = "SELECT a.idUsuarioAlbum, a.tituloAlbum
             FROM album a
             JOIN imagen i ON i.idAlbumImagen = a.idAlbum
             WHERE i.idImagen = ?";
$stmt2 = $conexion->prepare($sqlAutor);
$stmt2->bind_param("i", $idImagen);
$stmt2->execute();
$result = $stmt2->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $idUsuarioDestino = $row['idUsuarioAlbum'];
    $tituloAlbum = $row['tituloAlbum'];

    if ($idUsuarioDestino != $idUsuarioLike) { // no notificar si es su propio álbum
        $mensaje = "le dio me gusta a tu álbum '$tituloAlbum'";
        $tipo = "like";

        $sqlNotif = "INSERT INTO notificaciones (idUsuarioDestino, idUsuarioAccion, tipo, idReferencia, mensaje, leida, fecha)
                     VALUES (?, ?, ?, ?, ?, 0, NOW())";
        $stmt3 = $conexion->prepare($sqlNotif);
        $stmt3->bind_param("iisis", $idUsuarioDestino, $idUsuarioLike, $tipo, $idImagen, $mensaje);
        $stmt3->execute();
    }
}

cerrarConexion($conexion);
?>