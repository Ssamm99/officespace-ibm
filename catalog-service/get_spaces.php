<?php
// /catalog-service/get_spaces.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit(0); }

require_once '../shared-infra/db.php';

// Parámetros de filtro
$fecha           = isset($_GET['fecha'])            ? mysqli_real_escape_string($conn, $_GET['fecha'])            : null;
$hora_inicio     = isset($_GET['hora_inicio'])      ? mysqli_real_escape_string($conn, $_GET['hora_inicio'])      : null;
$hora_fin        = isset($_GET['hora_fin'])         ? mysqli_real_escape_string($conn, $_GET['hora_fin'])         : null;
$tipo            = isset($_GET['tipo'])             ? mysqli_real_escape_string($conn, $_GET['tipo'])             : null;
$capacidad       = isset($_GET['capacidad'])        ? (int) $_GET['capacidad']                                    : null;
$mostrar_inactivos = isset($_GET['mostrar_inactivos']) && $_GET['mostrar_inactivos'] == '1';

// Base de la consulta
// Si es admin pidiendo todos, no filtramos por activo
$query = "SELECT e.* FROM espacios e WHERE 1=1";

if (!$mostrar_inactivos) {
    $query .= " AND e.activo = 1";
}

// Filtro por tipo
if ($tipo && in_array($tipo, ['SALA', 'DESK'])) {
    $query .= " AND e.tipo = '$tipo'";
}

// Filtro por capacidad mínima
if ($capacidad && $capacidad > 0) {
    $query .= " AND e.capacidad >= $capacidad";
}

// Filtro de disponibilidad por horario
if ($fecha && $hora_inicio && $hora_fin) {
    $query .= "
        AND e.id_espacio NOT IN (
            SELECT r.id_espacio
            FROM reservas r
            WHERE r.fecha = '$fecha'
              AND r.estatus = 'Activa'
              AND r.hora_inicio < '$hora_fin'
              AND r.hora_fin   > '$hora_inicio'
        )
    ";
}

$query .= " ORDER BY e.tipo ASC, e.capacidad ASC";

$result  = mysqli_query($conn, $query);
$espacios = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $espacios[] = $row;
    }
}

http_response_code(200);
echo json_encode([
    "status" => "success",
    "data"   => $espacios,
    "filtros" => [
        "fecha"       => $fecha,
        "hora_inicio" => $hora_inicio,
        "hora_fin"    => $hora_fin,
        "tipo"        => $tipo,
        "capacidad"   => $capacidad
    ]
]);
?>