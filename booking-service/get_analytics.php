<?php
// /booking-service/get_analytics.php
// Devuelve métricas REALES de ocupación. No se inyectan datos sintéticos:
// el dashboard refleja exclusivamente lo que hay en la base de datos.

require_once '../shared-infra/auth.php';
aplicar_cors('GET, OPTIONS');

requerir_jwt('ADMINISTRADOR');   // solo administradores

require_once '../shared-infra/db.php';

// ── MÉTRICA 1: Espacios más utilizados (top 6) ──────────────────────────────
$res1 = mysqli_query($conn, "
    SELECT e.nombre, e.tipo,
           COUNT(r.id_reserva) AS total_reservas,
           SUM(CASE WHEN r.estatus = 'Activa'    THEN 1 ELSE 0 END) AS activas,
           SUM(CASE WHEN r.estatus = 'Cancelada' THEN 1 ELSE 0 END) AS canceladas
      FROM espacios e
      LEFT JOIN reservas r ON e.id_espacio = r.id_espacio
     WHERE e.activo = 1
     GROUP BY e.id_espacio, e.nombre, e.tipo
     ORDER BY total_reservas DESC
     LIMIT 6
");
$espacios_uso = [];
while ($row = mysqli_fetch_assoc($res1)) {
    $espacios_uso[] = $row;
}

// ── MÉTRICA 2: Horarios pico (reservas por hora) ─────────────────────────────
$res2 = mysqli_query($conn, "
    SELECT HOUR(hora_inicio) AS hora, COUNT(*) AS total
      FROM reservas
     WHERE estatus = 'Activa'
     GROUP BY HOUR(hora_inicio)
     ORDER BY hora ASC
");
$horarios_raw = [];
while ($row = mysqli_fetch_assoc($res2)) {
    $horarios_raw[(int) $row['hora']] = (int) $row['total'];
}
// Rellenar el rango de oficina (07-20) con 0 donde no haya datos reales.
$horarios_pico = [];
for ($h = 7; $h <= 20; $h++) {
    $horarios_pico[] = ["hora" => sprintf("%02d:00", $h), "total" => $horarios_raw[$h] ?? 0];
}

// ── MÉTRICA 3: Tasa de cancelaciones ─────────────────────────────────────────
$res3  = mysqli_query($conn, "
    SELECT COUNT(*) AS total,
           SUM(CASE WHEN estatus = 'Activa'    THEN 1 ELSE 0 END) AS activas,
           SUM(CASE WHEN estatus = 'Cancelada' THEN 1 ELSE 0 END) AS canceladas
      FROM reservas
");
$stats = mysqli_fetch_assoc($res3);
$total = (int) $stats['total'];
$tasa_cancelacion = $total > 0
    ? round(((int) $stats['canceladas'] / $total) * 100, 1)
    : 0;

// ── MÉTRICA 4: Reservas por día de la semana ─────────────────────────────────
$res4 = mysqli_query($conn, "
    SELECT DAYOFWEEK(fecha) AS dia_num, COUNT(*) AS total
      FROM reservas
     WHERE estatus = 'Activa'
     GROUP BY DAYOFWEEK(fecha)
     ORDER BY dia_num ASC
");
$dias_raw = [];
while ($row = mysqli_fetch_assoc($res4)) {
    $dias_raw[(int) $row['dia_num']] = (int) $row['total'];
}
// DAYOFWEEK: 1=Dom, 2=Lun ... 7=Sáb
$dias_semana = [
    ["dia" => "Lun", "total" => $dias_raw[2] ?? 0],
    ["dia" => "Mar", "total" => $dias_raw[3] ?? 0],
    ["dia" => "Mié", "total" => $dias_raw[4] ?? 0],
    ["dia" => "Jue", "total" => $dias_raw[5] ?? 0],
    ["dia" => "Vie", "total" => $dias_raw[6] ?? 0],
    ["dia" => "Sáb", "total" => $dias_raw[7] ?? 0],
];

// ── MÉTRICA 5: Resumen general ────────────────────────────────────────────────
$total_espacios = (int) mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM espacios WHERE activo = 1")
)['total'];
$total_usuarios = (int) mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM usuarios WHERE activo = 1")
)['total'];

http_response_code(200);
echo json_encode([
    "status"  => "success",
    "resumen" => [
        "total_reservas"   => $total,
        "activas"          => (int) ($stats['activas'] ?? 0),
        "canceladas"       => (int) ($stats['canceladas'] ?? 0),
        "tasa_cancelacion" => $tasa_cancelacion,
        "total_espacios"   => $total_espacios,
        "total_usuarios"   => $total_usuarios,
    ],
    "espacios_uso"  => $espacios_uso,
    "horarios_pico" => $horarios_pico,
    "dias_semana"   => $dias_semana,
]);
