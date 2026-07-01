<?php
// /booking-service/get_user_bookings.php

require_once '../shared-infra/auth.php';
aplicar_cors('GET, OPTIONS');

$payload = requerir_jwt();   // cualquier usuario autenticado

require_once '../shared-infra/db.php';

// Usar el id_usuario del TOKEN verificado (no del query string — más seguro)
$id_usuario = (int) $payload['id_usuario'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT r.id_reserva, r.fecha, r.hora_inicio, r.hora_fin,
            r.asistentes, r.estatus, r.notas,
            e.nombre, e.tipo, e.piso
       FROM reservas r
       JOIN espacios e ON r.id_espacio = e.id_espacio
      WHERE r.id_usuario = ?
        AND r.estatus = 'Activa'
        AND r.fecha >= CURDATE()
      ORDER BY r.fecha ASC, r.hora_inicio ASC"
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
