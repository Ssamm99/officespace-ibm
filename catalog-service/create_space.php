<?php
// /catalog-service/create_space.php

require_once '../shared-infra/auth.php';
aplicar_cors('POST, OPTIONS');

// Requiere JWT válido con rol ADMINISTRADOR (firma + exp verificadas)
requerir_jwt('ADMINISTRADOR');

require_once '../shared-infra/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->nombre) || empty($data->tipo) || empty($data->capacidad) || empty($data->piso)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Nombre, tipo, capacidad y piso son obligatorios."]);
    exit();
}

$nombre    = (string) $data->nombre;
$tipo      = in_array($data->tipo, ['SALA', 'DESK'], true) ? $data->tipo : 'SALA';
$capacidad = (int) $data->capacidad;
$piso      = (string) $data->piso;
$recursos  = (string) ($data->recursos ?? '');
$activo    = isset($data->activo) ? (int) $data->activo : 1;

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO espacios (nombre, tipo, capacidad, piso, recursos, activo) VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'ssissi', $nombre, $tipo, $capacidad, $piso, $recursos, $activo);

if (mysqli_stmt_execute($stmt)) {
    http_response_code(201);
    echo json_encode([
        "status"     => "success",
        "message"    => "Espacio creado exitosamente.",
        "id_espacio" => mysqli_insert_id($conn)
    ]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno al crear el espacio."]);
}
