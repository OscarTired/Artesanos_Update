<?php
include '../models/imagenModelo.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$idImagen = filter_input(INPUT_GET, 'idImagen', FILTER_VALIDATE_INT);

if (!$idImagen) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de imagen inválido']);
    exit;
}

try {
    $modeloImagen = new ImagenModelo(); // Ajusta si tu clase tiene otro nombre
    
    // Asume que contarLikes() devuelve el número de likes
    $totalLikes = $modeloImagen->contarLikes($idImagen);

    echo json_encode(['totalLikes' => $totalLikes]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los likes: ' . $e->getMessage()]);
}
?>