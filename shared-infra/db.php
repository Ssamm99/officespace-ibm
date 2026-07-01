<?php
// /shared-infra/db.php
// Conexión a la base de datos. En Docker el host es 'mysql'; en local, 127.0.0.1.
// Las credenciales se leen SIEMPRE de variables de entorno (ver config.php / .env).

require_once __DIR__ . '/config.php';

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS');           // sin valor por defecto: debe venir del entorno
$db   = getenv('DB_NAME') ?: 'officespace_db';

if ($pass === false) {
    $pass = ''; // permite arranques locales sin password, pero no hardcodea un secreto real
}

// Hacer que mysqli lance excepciones en lugar de devolver false silenciosamente.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $pass, $db, (int)$port);
    mysqli_set_charset($conn, 'utf8mb4');
} catch (\mysqli_sql_exception $e) {
    http_response_code(500);
    // No exponer detalles internos al cliente; registrar en el log del servidor.
    error_log('DB connection error: ' . $e->getMessage());
    echo json_encode([
        "status"  => "error",
        "message" => "Error de conexión a la base de datos."
    ]);
    exit();
}
