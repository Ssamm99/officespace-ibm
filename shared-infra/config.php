<?php
// /shared-infra/config.php
// Configuración central. Los secretos se leen de variables de entorno.
// Docker Compose las inyecta desde el archivo .env; también pueden definirse
// o exportarlas en el shell. NUNCA se hardcodean secretos reales aquí.

// ── Carga opcional de un archivo .env (clave=valor por línea) ────────────────
$__env_path = __DIR__ . '/../.env';
if (is_readable($__env_path)) {
    foreach (file($__env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__line) {
        $__line = trim($__line);
        if ($__line === '' || $__line[0] === '#') continue;
        [$__k, $__v] = array_pad(explode('=', $__line, 2), 2, '');
        $__k = trim($__k);
        $__v = trim($__v, " \t\"'");
        if ($__k !== '' && getenv($__k) === false) {
            putenv("$__k=$__v");
            $_ENV[$__k] = $__v;
        }
    }
}

// ── Secreto de firma del JWT ─────────────────────────────────────────────────
// Obligatorio en producción. Si falta, el sistema falla de forma segura.
define('JWT_SECRET', getenv('JWT_SECRET') ?: '');

// ── Orígenes permitidos para CORS (allowlist separada por comas) ─────────────
define('CORS_ALLOWED_ORIGINS', getenv('CORS_ORIGINS')
    ?: 'http://localhost:8080,http://127.0.0.1:8080');

// Vigencia del token en segundos (por defecto 24h)
define('JWT_TTL', (int)(getenv('JWT_TTL') ?: 86400));
