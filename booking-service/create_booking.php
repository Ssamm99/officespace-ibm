<?php
// /booking-service/create_booking.php

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

// 3. Conexión a la base de datos
require_once '../shared-infra/db.php';

// 4. Recibir payload JSON
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->id_espacio) &&
    !empty($data->id_usuario) &&
    !empty($data->fecha) &&
    !empty($data->hora_inicio) &&
    !empty($data->hora_fin) &&
    !empty($data->asistentes)
) {
    $id_espacio  = (int) $data->id_espacio;
    $id_usuario  = (int) $data->id_usuario;
    $fecha       = mysqli_real_escape_string($conn, $data->fecha);
    $hora_inicio = mysqli_real_escape_string($conn, $data->hora_inicio);
    $hora_fin    = mysqli_real_escape_string($conn, $data->hora_fin);
    $asistentes  = (int) $data->asistentes;
    // Notas es opcional
    $notas       = mysqli_real_escape_string($conn, $data->notas ?? '');

    // VALIDACIÓN 1: Consistencia temporal
    if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "La hora de fin debe ser posterior a la hora de inicio."]);
        exit();
    }

    // VALIDACIÓN 2: No reservar en fechas pasadas
date_default_timezone_set('America/Mexico_City'); // ← MUEVE ESTA LÍNEA AQUÍ ARRIBA
$fecha_hoy = date('Y-m-d');

if ($fecha < $fecha_hoy) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No puedes crear reservas en fechas pasadas."]);
    exit();
}

// VALIDACIÓN 3: Si es hoy, la hora de inicio no debe haber pasado
if ($fecha === $fecha_hoy) {
    $hora_actual_segundos = strtotime(date('H:i'));
    $hora_inicio_segundos = strtotime($hora_inicio);
    if ($hora_inicio_segundos <= $hora_actual_segundos) {
        $hora_actual_str = date('H:i');
        http_response_code(400);
        echo json_encode([
            "status"  => "error",
            "message" => "La hora de inicio ya pasó. Son las {$hora_actual_str}, elige un horario futuro."
        ]);
        exit();
    }
}

    // VALIDACIÓN 4: Horario de oficina (07:00 - 21:00)
    $apertura = strtotime('07:00');
    $cierre   = strtotime('21:00');
    if (strtotime($hora_inicio) < $apertura || strtotime($hora_fin) > $cierre) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Las reservas solo están permitidas en horario de oficina (07:00 - 21:00)."]);
        exit();
    }

    // VALIDACIÓN 5: Capacidad del espacio
    $query_capacidad = "SELECT capacidad FROM espacios WHERE id_espacio = $id_espacio AND activo = 1";
    $res_capacidad   = mysqli_query($conn, $query_capacidad);
    $espacio         = mysqli_fetch_assoc($res_capacidad);

    if (!$espacio) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "El espacio no existe o no está disponible."]);
        exit();
    }

    if ($asistentes > $espacio['capacidad']) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "El número de asistentes excede la capacidad del espacio ({$espacio['capacidad']})."]);
        exit();
    }

    // VALIDACIÓN 6: No-solapamiento
    $query_colision = "
        SELECT COUNT(*) as colisiones
        FROM reservas
        WHERE id_espacio = $id_espacio
          AND fecha = '$fecha'
          AND estatus = 'Activa'
          AND hora_inicio < '$hora_fin'
          AND hora_fin    > '$hora_inicio'
    ";
    $res_colision = mysqli_query($conn, $query_colision);
    $fila         = mysqli_fetch_assoc($res_colision);

    if ($fila['colisiones'] > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "El espacio ya está ocupado en ese horario."]);
        exit();
    }

    // Todo válido — insertar con notas
    $query_insert = "
        INSERT INTO reservas (id_espacio, id_usuario, fecha, hora_inicio, hora_fin, asistentes, notas)
        VALUES ($id_espacio, $id_usuario, '$fecha', '$hora_inicio', '$hora_fin', $asistentes, '$notas')
    ";

    if (mysqli_query($conn, $query_insert)) {
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Reserva confirmada exitosamente."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error interno al guardar la reserva."]);
    }

} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para procesar la reserva."]);
}
?>