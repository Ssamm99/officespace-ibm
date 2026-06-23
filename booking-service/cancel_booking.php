<?php
// /booking-service/cancel_booking.php

// 1. Cabeceras CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// 2. Validación JWT
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if (!str_starts_with($auth, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "No autorizado. Token requerido."]);
    exit();
}

$token = substr($auth, 7);
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

if (!$payload || !isset($payload['id_usuario'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Token malformado."]);
    exit();
}

$id_usuario_token = (int) $payload['id_usuario'];

// 3. Conexión a la base de datos
require_once '../shared-infra/db.php';

// 4. Recibir payload JSON
$data = json_decode(file_get_contents("php://input"));

if (empty($data->id_reserva)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta el id_reserva."]);
    exit();
}

$id_reserva = (int) $data->id_reserva;

// 5. Verificar que la reserva existe y pertenece al usuario del token
$query_check = "
    SELECT id_reserva, id_usuario, fecha, hora_inicio, estatus 
    FROM reservas 
    WHERE id_reserva = $id_reserva
";
$res_check = mysqli_query($conn, $query_check);
$reserva = mysqli_fetch_assoc($res_check);

if (!$reserva) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Reserva no encontrada."]);
    exit();
}

// 6. Solo el dueño o un ADMINISTRADOR puede cancelar
$rol_token = $payload['rol'] ?? '';
if ((int)$reserva['id_usuario'] !== $id_usuario_token && $rol_token !== 'ADMINISTRADOR') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "No tienes permiso para cancelar esta reserva."]);
    exit();
}

// 7. Verificar que la reserva ya no está cancelada
if ($reserva['estatus'] === 'Cancelada') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Esta reserva ya fue cancelada."]);
    exit();
}

// 8. Verificar que la reserva es futura (no se pueden cancelar reservas pasadas)
$fecha_hora_reserva = strtotime($reserva['fecha'] . ' ' . $reserva['hora_inicio']);
if ($fecha_hora_reserva <= time()) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No puedes cancelar una reserva que ya comenzó o ya pasó."]);
    exit();
}

// 9. Cancelar: cambiar estatus a 'Cancelada'
$query_cancel = "
    UPDATE reservas 
    SET estatus = 'Cancelada' 
    WHERE id_reserva = $id_reserva
";

if (mysqli_query($conn, $query_cancel)) {
    http_response_code(200);
    echo json_encode([
        "status"  => "success",
        "message" => "Reserva cancelada exitosamente."
    ]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno al cancelar la reserva."]);
}
?>