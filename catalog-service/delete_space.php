<?php
// /catalog-service/delete_space.php

require_once '../shared-infra/auth.php';
aplicar_cors('POST, OPTIONS');

requerir_jwt('ADMINISTRADOR');

require_once '../shared-infra/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id_espacio)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta el id_espacio."]);
    exit();
}

$id_espacio = (int) $data->id_espacio;

// Verificar que el espacio existe
$chk = mysqli_prepare($conn, "SELECT id_espacio FROM espacios WHERE id_espacio = ?");
mysqli_stmt_bind_param($chk, 'i', $id_espacio);
mysqli_stmt_execute($chk);
if (mysqli_stmt_get_result($chk)->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Espacio no encontrado."]);
    exit();
}

// Verificar que no tiene reservas activas futuras
$hoy = date('Y-m-d');
$rsv = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total FROM reservas
      WHERE id_espacio = ? AND estatus = 'Activa' AND fecha >= ?"
);
mysqli_stmt_bind_param($rsv, 'is', $id_espacio, $hoy);
mysqli_stmt_execute($rsv);
$row_check = mysqli_fetch_assoc(mysqli_stmt_get_result($rsv));

if ((int) $row_check['total'] > 0) {
    http_response_code(409);
    echo json_encode([
        "status"  => "error",
        "message" => "No puedes eliminar este espacio, tiene reservas activas futuras. Cancélalas primero o desactívalo."
    ]);
    exit();
}

// Eliminación física
$del = mysqli_prepare($conn, "DELETE FROM espacios WHERE id_espacio = ?");
mysqli_stmt_bind_param($del, 'i', $id_espacio);

if (mysqli_stmt_execute($del)) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Espacio eliminado exitosamente."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno al eliminar el espacio."]);
}
