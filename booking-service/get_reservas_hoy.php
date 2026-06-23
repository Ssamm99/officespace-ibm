<?php
// /booking-service/get_reservas_hoy.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
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

if (!$payload || !isset($payload['id_usuario'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Token malformado."]);
    exit();
}

// Solo administradores
if (($payload['rol'] ?? '') !== 'ADMINISTRADOR') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Acceso restringido a administradores."]);
    exit();
}

require_once '../shared-infra/db.php';

$hoy = date('Y-m-d');

// Reservas activas de hoy con datos de espacio y usuario
$query_reservas = "
    SELECT r.id_reserva, r.hora_inicio, r.hora_fin, r.asistentes,
           e.nombre AS nombre_espacio, e.tipo,
           u.email AS email_usuario
    FROM reservas r
    JOIN espacios e ON r.id_espacio = e.id_espacio
    JOIN usuarios u ON r.id_usuario = u.id_usuario
    WHERE r.fecha = '$hoy'
      AND r.estatus = 'Activa'
    ORDER BY r.hora_inicio ASC
";

// Total de espacios activos
$query_total = "SELECT COUNT(*) as total FROM espacios WHERE activo = 1";

$res_reservas = mysqli_query($conn, $query_reservas);
$res_total    = mysqli_query($conn, $query_total);

$reservas = [];
if ($res_reservas) {
    while ($row = mysqli_fetch_assoc($res_reservas)) {
        $reservas[] = $row;
    }
}

$total_espacios = 0;
if ($res_total) {
    $row_total      = mysqli_fetch_assoc($res_total);
    $total_espacios = (int) $row_total['total'];
}

http_response_code(200);
echo json_encode([
    "status"         => "success",
    "data"           => $reservas,
    "total_espacios" => $total_espacios
]);
?>