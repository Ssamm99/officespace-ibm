<?php
// /auth-service/login.php

require_once '../shared-infra/auth.php';   // CORS + helpers JWT
aplicar_cors('POST, OPTIONS');

require_once '../shared-infra/db.php';

// Recibir datos del frontend (JSON)
$data = json_decode(file_get_contents("php://input"));

if (empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan datos (email o password)"]);
    exit();
}

$email    = (string) $data->email;
$password = (string) $data->password;

// CONSULTA CON SENTENCIA PREPARADA (previene SQL injection)
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_usuario, email, password, rol FROM usuarios WHERE email = ? AND activo = 1"
);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row    = mysqli_fetch_assoc($result);

// Verificación de credenciales con hash (bcrypt/argon2)
$autenticado = false;
if ($row) {
    $hash = $row['password'];
    if (password_verify($password, $hash)) {
        $autenticado = true;
        // Re-hash si el algoritmo/costo cambió
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $nuevo = password_hash($password, PASSWORD_DEFAULT);
            $up = mysqli_prepare($conn, "UPDATE usuarios SET password = ? WHERE id_usuario = ?");
            mysqli_stmt_bind_param($up, 'si', $nuevo, $row['id_usuario']);
            mysqli_stmt_execute($up);
        }
    } elseif (!str_starts_with((string)$hash, '$2') && hash_equals((string)$hash, $password)) {
        // Compatibilidad con cuentas legadas en texto plano: migrar a hash al vuelo.
        $autenticado = true;
        $nuevo = password_hash($password, PASSWORD_DEFAULT);
        $up = mysqli_prepare($conn, "UPDATE usuarios SET password = ? WHERE id_usuario = ?");
        mysqli_stmt_bind_param($up, 'si', $nuevo, $row['id_usuario']);
        mysqli_stmt_execute($up);
    }
}

if (!$autenticado) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Credenciales incorrectas"]);
    exit();
}

// GENERACIÓN DE TOKEN JWT FIRMADO (HS256)
$jwt_token = generar_jwt([
    'id_usuario' => (int) $row['id_usuario'],
    'email'      => $row['email'],
    'rol'        => $row['rol'],
    'iat'        => time(),
    'exp'        => time() + JWT_TTL,
]);

http_response_code(200);
echo json_encode([
    "status"  => "success",
    "message" => "Login exitoso",
    "token"   => $jwt_token,
    "user"    => [
        "id"    => (int) $row['id_usuario'],
        "email" => $row['email'],
        "rol"   => $row['rol'],
    ],
]);
