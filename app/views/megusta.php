<?php
// Asegúrate de que tu modelo tenga una función llamada toggleLike($idImagen, $idUsuario)
include '../models/imagenModelo.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$idUsuario = (int)$_SESSION['usuario']['id'];
$idImagen = filter_input(INPUT_POST, 'idImagen', FILTER_VALIDATE_INT);

if (!$idImagen) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de imagen inválido']);
    exit;
}

try {
    $modeloImagen = new ImagenModelo(); // Ajusta si tu clase tiene otro nombre
    
    // Asume que toggleLike() devuelve un array con:
    // ['accion' => 'like'/'dislike', 'totalLikes' => 5]
    $resultado = $modeloImagen->toggleLike($idImagen, $idUsuario);

    echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar el like: ' . $e->getMessage()]);
}
?>