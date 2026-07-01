<?php
// /booking-service/get_reservas_hoy.php

require_once '../shared-infra/auth.php';
aplicar_cors('GET, OPTIONS');

requerir_jwt('ADMINISTRADOR');   // solo administradores

require_once '../shared-infra/db.php';

$hoy = date('Y-m-d');

// Reservas activas de hoy con datos de espacio y usuario
$stmt = mysqli_prepare(
    $conn,
    "SELECT r.id_reserva, r.hora_inicio, r.hora_fin, r.asistentes,
            e.nombre AS nombre_espacio, e.tipo,
            u.email AS email_usuario
       FROM reservas r
       JOIN espacios e ON r.id_espacio = e.id_espacio
       JOIN usuarios u ON r.id_usuario = u.id_usuario
      WHERE r.fecha = ?
        AND r.estatus = 'Activa'
      ORDER BY r.hora_inicio ASC"
);
mysqli_stmt_bind_param($stmt, 's', $hoy);
mysqli_stmt_execute($stmt);
$res_reservas = mysqli_stmt_get_result($stmt);

$reservas = [];
while ($row = mysqli_fetch_assoc($res_reservas)) {
    $reservas[] = $row;
}

// Total de espacios activos
$res_total      = mysqli_query($conn, "SELECT COUNT(*) AS total FROM espacios WHERE activo = 1");
$total_espacios = (int) mysqli_fetch_assoc($res_total)['total'];

http_response_code(200);
echo json_encode([
    "status"         => "success",
    "data"           => $reservas,
    "total_espacios" => $total_espacios
]);
