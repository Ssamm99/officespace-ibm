<?php
// /booking-service/get_all_user_bookings.php
// A diferencia de get_user_bookings.php, este devuelve TODO el historial
// (activas + canceladas) para la pantalla de perfil.

require_once '../shared-infra/auth.php';
aplicar_cors('GET, OPTIONS');

$payload = requerir_jwt();

require_once '../shared-infra/db.php';

$id_usuario = (int) $payload['id_usuario'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT r.id_reserva, r.fecha, r.hora_inicio, r.hora_fin,
            r.asistentes, r.estatus, r.notas,
            e.nombre, e.tipo, e.piso
       FROM reservas r
       JOIN espacios e ON r.id_espacio = e.id_espacio
      WHERE r.id_usuario = ?
      ORDER BY r.fecha DESC, r.hora_inicio DESC"
);
mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$reservas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reservas[] = $row;
}

http_response_code(200);
echo json_encode(["status" => "success", "data" => $reservas]);
