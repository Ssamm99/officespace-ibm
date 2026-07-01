<?php
// /shared-infra/auth.php
// Middleware compartido: CORS con allowlist + emisión y VERIFICACIÓN de JWT.
// Centraliza la lógica que antes estaba duplicada en cada endpoint (principio DRY).

require_once __DIR__ . '/config.php';

/**
 * Aplica cabeceras CORS restringidas a una allowlist y resuelve el pre-flight.
 * @param string $metodos Métodos HTTP permitidos, ej. "POST, OPTIONS".
 */
function aplicar_cors(string $metodos = 'GET, POST, OPTIONS'): void
{
    $permitidos = array_map('trim', explode(',', CORS_ALLOWED_ORIGINS));
    $origin     = $_SERVER['HTTP_ORIGIN'] ?? '';

    if ($origin !== '' && in_array($origin, $permitidos, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
    }

    header('Content-Type: application/json; charset=UTF-8');
    header("Access-Control-Allow-Methods: $metodos");
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit(0);
    }
}

/** Codificación Base64Url (sin padding, segura para URL). */
function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/** Decodificación Base64Url. */
function base64url_decode(string $data): string
{
    return base64_decode(strtr($data, '-_', '+/')
        . str_repeat('=', (4 - strlen($data) % 4) % 4));
}

/**
 * Genera un JWT firmado (HS256) con el secreto de configuración.
 */
function generar_jwt(array $claims): string
{
    if (JWT_SECRET === '') {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Configuración de seguridad incompleta (JWT_SECRET)."]);
        exit();
    }

    $header  = base64url_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64url_encode(json_encode($claims));
    $firma   = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));

    return "$header.$payload.$firma";
}

/**
 * Verifica un JWT: estructura, FIRMA HMAC (comparación en tiempo constante) y expiración.
 * Si $rolRequerido se indica, además exige ese rol.
 * Devuelve el payload validado o termina la petición con el código HTTP adecuado.
 *
 * @param string|null $rolRequerido Ej. 'ADMINISTRADOR'.
 * @return array Payload verificado.
 */
function requerir_jwt(?string $rolRequerido = null): array
{
    if (JWT_SECRET === '') {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Configuración de seguridad incompleta (JWT_SECRET)."]);
        exit();
    }

    $headers = function_exists('getallheaders') ? getallheaders() : [];
    // Normalizar claves para tolerar variaciones de mayúsculas del servidor
    $auth = '';
    foreach ($headers as $k => $v) {
        if (strcasecmp($k, 'Authorization') === 0) { $auth = $v; break; }
    }
    if ($auth === '') {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    }

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

    [$h, $p, $firmaRecibida] = $partes;

    // VERIFICACIÓN DE FIRMA (lo que faltaba): recalcular y comparar en tiempo constante.
    $firmaEsperada = base64url_encode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
    if (!hash_equals($firmaEsperada, $firmaRecibida)) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Firma del token inválida."]);
        exit();
    }

    $payload = json_decode(base64url_decode($p), true);
    if (!is_array($payload) || !isset($payload['id_usuario'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Token malformado."]);
        exit();
    }

    // Verificación de expiración.
    if (isset($payload['exp']) && time() >= (int)$payload['exp']) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Token expirado. Inicia sesión de nuevo."]);
        exit();
    }

    // Verificación de rol (RBAC) si se exige.
    if ($rolRequerido !== null && ($payload['rol'] ?? '') !== $rolRequerido) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Acceso restringido. Permisos insuficientes."]);
        exit();
    }

    return $payload;
}
