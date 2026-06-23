<?php
// /catalog-service/delete_space.php

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
    echo json_encode(["status" => "error", "message" => "Solo administradores pueden eliminar espacios."]);
    exit();
}

require_once '../shared-infra/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id_espacio)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta el id_espacio."]);
    exit();
}

$id_espacio = (int) $data->id_espacio;

// Verificar que el espacio existe
$check = mysqli_query($conn, "SELECT id_espacio FROM espacios WHERE id_espacio = $id_espacio");
if (!$check || mysqli_num_rows($check) === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Espacio no encontrado."]);
    exit();
}

// Verificar que no tiene reservas activas futuras
$hoy = date('Y-m-d');
$check_reservas = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM reservas
    WHERE id_espacio = $id_espacio
      AND estatus = 'Activa'
      AND fecha >= '$hoy'
");
$row_check = mysqli_fetch_assoc($check_reservas);
if ((int)$row_check['total'] > 0) {
    http_response_code(409);
    echo json_encode([
        "status"  => "error",
        "message" => "No puedes eliminar este espacio, tiene reservas activas futuras. Cancélalas primero o desactívalo."
    ]);
    exit();
}

// Eliminación física
if (mysqli_query($conn, "DELETE FROM espacios WHERE id_espacio = $id_espacio")) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Espacio eliminado exitosamente."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno al eliminar el espacio."]);
}
?>