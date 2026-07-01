<?php
// /booking-service/create_booking.php

require_once '../shared-infra/auth.php';
aplicar_cors('POST, OPTIONS');

$payload = requerir_jwt();   // exige JWT válido (firma + exp)

require_once '../shared-infra/db.php';

// Recibir payload JSON
$data = json_decode(file_get_contents("php://input"));

if (
    empty($data->id_espacio)  ||
    empty($data->fecha)       ||
    empty($data->hora_inicio) ||
    empty($data->hora_fin)    ||
    empty($data->asistentes)
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para procesar la reserva."]);
    exit();
}

$id_espacio  = (int) $data->id_espacio;
// El dueño de la reserva SIEMPRE es el usuario autenticado del token (no se confía en el body).
$id_usuario  = (int) $payload['id_usuario'];
$fecha       = (string) $data->fecha;
$hora_inicio = (string) $data->hora_inicio;
$hora_fin    = (string) $data->hora_fin;
$asistentes  = (int) $data->asistentes;
$notas       = (string) ($data->notas ?? '');

// ── VALIDACIONES DE NEGOCIO (antes de tocar la BD) ──────────────────────────
date_default_timezone_set('America/Mexico_City');

// 1. Consistencia temporal
if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "La hora de fin debe ser posterior a la hora de inicio."]);
    exit();
}

// 2. No reservar en fechas pasadas
$fecha_hoy = date('Y-m-d');
if ($fecha < $fecha_hoy) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No puedes crear reservas en fechas pasadas."]);
    exit();
}

// 3. Si es hoy, la hora de inicio no debe haber pasado
if ($fecha === $fecha_hoy && strtotime($hora_inicio) <= strtotime(date('H:i'))) {
    $hora_actual_str = date('H:i');
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "La hora de inicio ya pasó. Son las {$hora_actual_str}, elige un horario futuro."
    ]);
    exit();
}

// 4. Horario de oficina (07:00 - 21:00)
if (strtotime($hora_inicio) < strtotime('07:00') || strtotime($hora_fin) > strtotime('21:00')) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Las reservas solo están permitidas en horario de oficina (07:00 - 21:00)."]);
    exit();
}

// ── SECCIÓN TRANSACCIONAL (evita condición de carrera / doble reserva) ───────
// Bloqueamos la fila del espacio con SELECT ... FOR UPDATE: cualquier reserva
// concurrente sobre el MISMO espacio queda serializada hasta el COMMIT.
mysqli_begin_transaction($conn);

try {
    // 5. Capacidad del espacio (y bloqueo de la fila)
    $stmt = mysqli_prepare($conn, "SELECT capacidad FROM espacios WHERE id_espacio = ? AND activo = 1 FOR UPDATE");
    mysqli_stmt_bind_param($stmt, 'i', $id_espacio);
    mysqli_stmt_execute($stmt);
    $espacio = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$espacio) {
        mysqli_rollback($conn);
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "El espacio no existe o no está disponible."]);
        exit();
    }

    if ($asistentes > (int) $espacio['capacidad']) {
        mysqli_rollback($conn);
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "El número de asistentes excede la capacidad del espacio ({$espacio['capacidad']})."]);
        exit();
    }

    // 6. No-solapamiento (dentro de la transacción, ya con la fila bloqueada)
    $col = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS colisiones
           FROM reservas
          WHERE id_espacio = ?
            AND fecha = ?
            AND estatus = 'Activa'
            AND hora_inicio < ?
            AND hora_fin    > ?"
    );
    mysqli_stmt_bind_param($col, 'isss', $id_espacio, $fecha, $hora_fin, $hora_inicio);
    mysqli_stmt_execute($col);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($col));

    if ((int) $fila['colisiones'] > 0) {
        mysqli_rollback($conn);
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "El espacio ya está ocupado en ese horario."]);
        exit();
    }

    // 7. Insertar
    $ins = mysqli_prepare(
        $conn,
        "INSERT INTO reservas (id_espacio, id_usuario, fecha, hora_inicio, hora_fin, asistentes, notas)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($ins, 'iisssis', $id_espacio, $id_usuario, $fecha, $hora_inicio, $hora_fin, $asistentes, $notas);
    mysqli_stmt_execute($ins);

    mysqli_commit($conn);

    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "Reserva confirmada exitosamente."]);

} catch (\mysqli_sql_exception $e) {
    mysqli_rollback($conn);
    error_log('create_booking error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno al guardar la reserva."]);
}
