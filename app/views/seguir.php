<?php
session_start();
require_once '../../config/conexion.php';
$conexion = abrirConexion();

$idSeguidor = $_SESSION['usuario']['id'];
 // quien sigue
$idSeguido = $_POST['idSeguido'];       // a quién sigue

// Insertar en la tabla seguimiento
$sql = "INSERT INTO seguimiento (idSeguidor, idSeguido, estadoSeguimiento, fechaSeguimiento)
        VALUES (?, ?, 'activo', NOW())";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $idSeguidor, $idSeguido);
$stmt->execute();

// Crear notificación
$mensaje = "te comenzó a seguir";
$tipo = "seguir";
$sqlNotif = "INSERT INTO notificaciones (idUsuarioDestino, idUsuarioAccion, tipo, mensaje, leida, fecha)
             VALUES (?, ?, ?, ?, 0, NOW())";
$stmt2 = $conexion->prepare($sqlNotif);
$stmt2->bind_param("iiss", $idSeguido, $idSeguidor, $tipo, $mensaje);
$stmt2->execute();

cerrarConexion($conexion);
?>