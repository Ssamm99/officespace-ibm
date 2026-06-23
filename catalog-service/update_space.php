<?php
// /catalog-service/update_space.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit(0); }

// Validación JWT
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if (!str_starts_with($auth, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "No autorizado."]);
    exit();
}

$token  = substr($auth, 7);
$partes = explode('.', $token);

if (count($partes) !== 3) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Token inválido."]);
    exit();
}

$payload = json_decode(base64_decode(str_pad(
    strtr($partes[1], '-_', '+/'),
    strlen($partes[1]) % 4, '=', STR_PAD_RIGHT
)), true);

if (!$payload || ($payload['rol'] ?? '') !== 'ADMINISTRADOR') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Solo administradores pueden editar espacios."]);
    exit();
}

require_once '../shared-infra/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id_espacio) || empty($data->nombre) || empty($data->capacidad) || empty($data->piso)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan campos obligatorios."]);
    exit();
}

$id_espacio = (int) $data->id_espacio;
$nombre     = mysqli_real_escape_string($conn, $data->nombre);
$tipo       = in_array($data->tipo, ['SALA', 'DESK']) ? $data->tipo : 'SALA';
$capacidad  = (int) $data->capacidad;
$piso       = mysqli_real_escape_string($conn, $data->piso);
$recursos   = mysqli_real_escape_string($conn, $data->recursos ?? '');
$activo     = isset($data->activo) ? (int) $data->activo : 1;

// Verificar que el espacio existe
$check = mysqli_query($conn, "SELECT id_espacio FROM espacios WHERE id_espacio = $id_espacio");
if (!$check || mysqli_num_rows($check) === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Espacio no encontrado."]);
    exit();
}

$query = "
    UPDATE espacios
    SET nombre     = '$nombre',
        tipo       = '$tipo',
        capacidad  = $capacidad,
        piso       = '$piso',
        recursos   = '$recursos',
        activo     = $activo
    WHERE id_espacio = $id_espacio
";

if (mysqli_query($conn, $query)) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Espacio actualizado exitosamente."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno al actualizar el espacio."]);
}
?>