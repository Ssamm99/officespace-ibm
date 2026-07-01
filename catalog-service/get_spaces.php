<?php
// /catalog-service/get_spaces.php

require_once '../shared-infra/auth.php';
aplicar_cors('GET, OPTIONS');

require_once '../shared-infra/db.php';

// Parámetros de filtro
$fecha       = isset($_GET['fecha'])       ? (string) $_GET['fecha']       : null;
$hora_inicio = isset($_GET['hora_inicio']) ? (string) $_GET['hora_inicio'] : null;
$hora_fin    = isset($_GET['hora_fin'])    ? (string) $_GET['hora_fin']    : null;
$tipo        = isset($_GET['tipo'])        ? (string) $_GET['tipo']        : null;
$capacidad   = isset($_GET['capacidad'])   ? (int) $_GET['capacidad']      : null;

// Mostrar espacios inactivos es una operación administrativa.
$mostrar_inactivos = false;
if (isset($_GET['mostrar_inactivos']) && $_GET['mostrar_inactivos'] == '1') {
    requerir_jwt('ADMINISTRADOR');   // solo admins ven inactivos
    $mostrar_inactivos = true;
}

// Construcción de consulta con marcadores de posición y binding dinámico.
$query  = "SELECT e.* FROM espacios e WHERE 1=1";
$types  = '';
$params = [];

if (!$mostrar_inactivos) {
    $query .= " AND e.activo = 1";
}

if ($tipo && in_array($tipo, ['SALA', 'DESK'], true)) {
    $query   .= " AND e.tipo = ?";
    $types   .= 's';
    $params[] = $tipo;
}

if ($capacidad && $capacidad > 0) {
    $query   .= " AND e.capacidad >= ?";
    $types   .= 'i';
    $params[] = $capacidad;
}

if ($fecha && $hora_inicio && $hora_fin) {
    $query .= "
        AND e.id_espacio NOT IN (
            SELECT r.id_espacio FROM reservas r
            WHERE r.fecha = ?
              AND r.estatus = 'Activa'
              AND r.hora_inicio < ?
              AND r.hora_fin    > ?
        )";
    $types  .= 'sss';
    $params[] = $fecha;
    $params[] = $hora_fin;
    $params[] = $hora_inicio;
}

$query .= " ORDER BY e.tipo ASC, e.capacidad ASC";

$stmt = mysqli_prepare($conn, $query);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$espacios = [];
while ($row = mysqli_fetch_assoc($result)) {
    $espacios[] = $row;
}

http_response_code(200);
echo json_encode([
    "status"  => "success",
    "data"    => $espacios,
    "filtros" => [
        "fecha"       => $fecha,
        "hora_inicio" => $hora_inicio,
        "hora_fin"    => $hora_fin,
        "tipo"        => $tipo,
        "capacidad"   => $capacidad
    ]
]);
