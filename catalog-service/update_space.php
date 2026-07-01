<?php
// /catalog-service/update_space.php

require_once '../shared-infra/auth.php';
aplicar_cors('POST, OPTIONS');

requerir_jwt('ADMINISTRADOR');

require_once '../shared-infra/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id_espacio) || empty($data->nombre) || empty($data->capacidad) || empty($data->piso)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan campos obligatorios."]);
    exit();
}

$id_espacio = (int) $data->id_espacio;
$nombre     = (string) $data->nombre;
$tipo       = in_array($data->tipo ?? '', ['SALA', 'DESK'], true) ? $data->tipo : 'SALA';
$capacidad  = (int) $data->capacidad;
$piso       = (string) $data->piso;
$recursos   = (string) ($data->recursos ?? '');
$activo     = isset($data->activo) ? (int) $data->activo : 1;

// Verificar que el espacio existe (preparado)
$chk = mysqli_prepare($conn, "SELECT id_espacio FROM espacios WHERE id_espacio = ?");
mysqli_stmt_bind_param($chk, 'i', $id_espacio);
mysqli_stmt_execute($chk);
if (mysqli_stmt_get_result($chk)->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Espacio no encontrado."]);
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE espacios
        SET nombre = ?, tipo = ?, capacidad = ?, piso = ?, recursos = ?, activo = ?
      WHERE id_espacio = ?"
);
mysqli_stmt_bind_param($stmt, 'ssissii', $nombre, $tipo, $capacidad, $piso, $recursos, $activo, $id_espacio);

if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Espacio actualizado exitosamente."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno al actualizar el espacio."]);
}
