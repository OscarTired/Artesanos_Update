<?php
session_start();
include '../models/comentarioModelo.php';
include '../models/usuarioHelper.php';
include_once '../../config/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$idImagen = (int)$data['idImagen'];
$mensaje = trim($data['mensaje']);

if ($idUsuario && $idImagen && $mensaje) {
  $modelo = new ComentarioModelo();
  $modelo->agregarComentario($idImagen, $idUsuario, $mensaje);

  $usuario = $modelo->obtenerUsuario($idUsuario);
  echo json_encode([
    'ok' => true,
    'apodo' => $usuario['apodoUsuario'],
    'avatar' => obtenerAvatar($idUsuario),
    'mensaje' => $mensaje
  ]);
} else {
  echo json_encode(['ok' => false]);
}