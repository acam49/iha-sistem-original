<?php

require_once __DIR__ . '/crud_helpers.php';
require_once __DIR__ . '/../lib/FlightLogRepository.php';

$userId = api_require_user_id();
$repo = new FlightLogRepository();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$uavId = isset($_GET['uav_id']) ? (int) $_GET['uav_id'] : 0;

if ($method === 'GET') {
    if ($id > 0) {
        $row = $repo->findByIdForUser($id, $userId);
        if ($row === null) {
            api_json_response(['ok' => false, 'error' => 'Uçuş kaydı bulunamadı.'], 404);
        }
        api_json_response(['ok' => true, 'data' => $row]);
    }
    if ($uavId <= 0) {
        api_json_response(['ok' => false, 'error' => 'Liste için uav_id gerekli.'], 400);
    }
    api_json_response(['ok' => true, 'data' => $repo->listByUavForUser($uavId, $userId)]);
}

if ($method === 'POST') {
    $body = api_read_json_body();
    if ($body === null) {
        api_json_response(['ok' => false, 'error' => 'Geçersiz JSON.'], 400);
    }
    $postUav = isset($body['uav_id']) ? (int) $body['uav_id'] : (isset($_GET['uav_id']) ? $uavId : 0);
    $started = isset($body['started_at']) ? trim((string) $body['started_at']) : '';
    
    // Default Bitis Saati to 1 hour after started if not provided
    $ended = array_key_exists('ended_at', $body) && $body['ended_at'] !== '' ? trim((string) $body['ended_at']) : date('Y-m-d H:i:s', strtotime($started . ' + 1 hour'));
    $notes = array_key_exists('notes', $body) ? ($body['notes'] === null ? null : trim((string) $body['notes'])) : null;
    
    if ($postUav <= 0 || $started === '') {
        api_json_response(['ok' => false, 'error' => 'uav_id ve started_at zorunludur (YYYY-MM-DD HH:MM:SS).'], 422);
    }
    
    try {
        $newId = $repo->create($postUav, $userId, $started, $ended, $notes);
        $row = $repo->findByIdForUser($newId, $userId);
        api_json_response(['ok' => true, 'data' => $row], 201);
    } catch (PDOException $e) {
        api_json_response(['ok' => false, 'error' => 'Kayıt hatası: ' . $e->getMessage()], 400);
    }
}

if ($method === 'PUT' || $method === 'PATCH') {
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id parametresi gerekli.'], 400);
    }
    $body = api_read_json_body();
    if ($body === null) {
        api_json_response(['ok' => false, 'error' => 'Geçersiz JSON.'], 400);
    }
    $patch = [];
    if (array_key_exists('started_at', $body)) {
        $patch['started_at'] = trim((string) $body['started_at']);
    }
    if (array_key_exists('ended_at', $body)) {
        $patch['ended_at'] = $body['ended_at'] === null || $body['ended_at'] === '' ? null : trim((string) $body['ended_at']);
    }
    if (array_key_exists('notes', $body)) {
        $patch['notes'] = $body['notes'] === null ? null : trim((string) $body['notes']);
    }
    if ($patch === []) {
        api_json_response(['ok' => false, 'error' => 'Güncellenecek alan yok.'], 400);
    }
    if (isset($patch['started_at']) && $patch['started_at'] === '') {
        api_json_response(['ok' => false, 'error' => 'started_at boş olamaz.'], 422);
    }
    if (!$repo->update($id, $userId, $patch)) {
        api_json_response(['ok' => false, 'error' => 'Güncelleme yapılamadı veya kayıt yok.'], 404);
    }
    api_json_response(['ok' => true, 'data' => $repo->findByIdForUser($id, $userId)]);
}

if ($method === 'DELETE') {
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id parametresi gerekli.'], 400);
    }
    if (!$repo->delete($id, $userId)) {
        api_json_response(['ok' => false, 'error' => 'Silinemedi veya kayıt yok.'], 404);
    }
    api_json_response(['ok' => true, 'deleted' => $id]);
}

api_json_response(['ok' => false, 'error' => 'Desteklenmeyen metot.'], 405);
