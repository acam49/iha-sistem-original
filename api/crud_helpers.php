<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

/**
 * JSON gövde (POST/PUT); bozuk JSON'da null döner.
 */
function api_read_json_body(): ?array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function api_json_response(array $payload, int $httpCode = 200): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($httpCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Oturum yoksa 401 ve çıkış.
 */
function api_require_user_id(): int
{
    $auth = new SHAAuth();
    if (!$auth->checkSession()) {
        api_json_response(['ok' => false, 'error' => ERR_SESSION_EXPIRED], 401);
    }
    return (int) $_SESSION['user_id'];
}
