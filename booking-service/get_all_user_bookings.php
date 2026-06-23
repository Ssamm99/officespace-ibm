<?php
// /booking-service/get_all_user_bookings.php
// A diferencia de get_user_bookings.php, este devuelve TODO el historial
// (activas + canceladas) para la pantalla de perfil

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit(0); }

// Validación JWT
$headers = getallheaders();
$auth    = $headers['Authorization'] ?? '';

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

require_once '../shared-infra/db.php';

$id_usuario = (int) $payload['id_usuario'];

// Todo el historial: activas + canceladas, ordenado por fecha descendente
$query = "
    SELECT r.id_reserva, r.fecha, r.hora_inicio, r.hora_fin,
           r.asistentes, r.estatus, r.notas,
           e.nombre, e.tipo, e.piso
    FROM reservas r
    JOIN espacios e ON r.id_espacio = e.id_espacio
    WHERE r.id_usuario = $id_usuario
    ORDER BY r.fecha DESC, r.hora_inicio DESC
";

$result   = mysqli_query($conn, $query);
$reservas = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reservas[] = $row;
    }
}

http_response_code(200);
echo json_encode(["status" => "success", "data" => $reservas]);
?>