<?php
// /auth-service/login.php

// 1. CONFIGURACIÓN DE CORS (CRÍTICO PARA MICROSERVICIOS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Si es una petición OPTIONS (Pre-flight de los navegadores), salimos temprano
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// 2. INCLUIR CONEXIÓN A BD
require_once '../shared-infra/db.php';

// 3. RECIBIR DATOS DEL FRONTEND (JSON)
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $email = mysqli_real_escape_string($conn, $data->email);
    $password = mysqli_real_escape_string($conn, $data->password);

    // 4. CONSULTA A LA BASE DE DATOS
    $query = "SELECT id_usuario, email, rol FROM usuarios WHERE email = '$email' AND password = '$password' AND activo = 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // 5. GENERACIÓN DE TOKEN JWT SIMPLE (Requisito de IBM)
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
    'id_usuario' => (int) $row['id_usuario'],
    'email'      => $row['email'],
    'rol'        => $row['rol'],
    'exp'        => time() + (60 * 60 * 24)
]);

        // Codificación Base64Url
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        // Firma (Signature)
        $secret = 'ibm_hackathon_2026_super_secret';
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt_token = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        // 6. RESPUESTA EXITOSA EN JSON
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Login exitoso",
            "token" => $jwt_token,
            "user" => [
                "id" => $row['id_usuario'],
                "email" => $row['email'],
                "rol" => $row['rol']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Credenciales incorrectas"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan datos (email o password)"]);
}
?>