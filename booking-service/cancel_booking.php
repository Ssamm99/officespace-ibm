<?php
// /booking-service/cancel_booking.php

require_once '../shared-infra/auth.php';
aplicar_cors('POST, OPTIONS');

$payload          = requerir_jwt();
$id_usuario_token = (int) $payload['id_usuario'];
$rol_token        = $payload['rol'] ?? '';

require_once '../shared-infra/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id_reserva)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta el id_reserva."]);
    exit();
}

$id_reserva = (int) $data->id_reserva;

// Verificar que la reserva existe
$chk = mysqli_prepare(
    $conn,
    "SELECT id_reserva, id_usuario, fecha, hora_inicio, estatus
       FROM reservas WHERE id_reserva = ?"
);
mysqli_stmt_bind_param($chk, 'i', $id_reserva);
mysqli_stmt_execute($chk);
$reserva = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));

if (!$reserva) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Reserva no encontrada."]);
    exit();
}

// Solo el dueño o un ADMINISTRADOR puede cancelar
if ((int) $reserva['id_usuario'] !== $id_usuario_token && $rol_token !== 'ADMINISTRADOR') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "No tienes permiso para cancelar esta reserva."]);
    exit();
}

if ($reserva['estatus'] === 'Cancelada') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Esta reserva ya fue cancelada."]);
    exit();
}

// No se pueden cancelar reservas pasadas o ya iniciadas
$fecha_hora_reserva = strtotime($reserva['fecha'] . ' ' . $reserva['hora_inicio']);
if ($fecha_hora_reserva <= time()) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No puedes cancelar una reserva que ya comenzó o ya pasó."]);
    exit();
}

// Cancelar
$upd = mysqli_prepare($conn, "UPDATE reservas SET estatus = 'Cancelada' WHERE id_reserva = ?");
mysqli_stmt_bind_param($upd, 'i', $id_reserva);

if (mysqli_stmt_execute($upd)) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Reserva cancelada exitosamente."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno al cancelar la reserva."]);
}
