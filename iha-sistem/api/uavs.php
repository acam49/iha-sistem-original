<?php

require_once __DIR__ . '/crud_helpers.php';
require_once __DIR__ . '/../lib/UavRepository.php';

$userId = api_require_user_id();

function uav_valid_status(string $status): bool
{
    return in_array(mb_convert_case(trim($status), MB_CASE_TITLE, "UTF-8"), ['Müsait', 'Uçuşta', 'Bakımda', 'Arızalı'], true);
}

$repo = new UavRepository();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($method === 'GET') {
    if ($id > 0) {
        $row = $repo->findById($id);
        if ($row === null) {
            api_json_response(['ok' => false, 'error' => 'İHA bulunamadı.'], 404);
        }
        api_json_response(['ok' => true, 'data' => $row]);
    }
    api_json_response(['ok' => true, 'data' => $repo->listAll()]);
}

if ($method === 'POST') {
    $body = api_read_json_body();
    if ($body === null) {
        api_json_response(['ok' => false, 'error' => 'Geçersiz JSON.'], 400);
    }
    $name = isset($body['name']) ? trim((string) $body['name']) : '';
    $model = isset($body['model']) ? trim((string) $body['model']) : 'Sabit Kanat';
    $serial = isset($body['serial_number']) ? trim((string) $body['serial_number']) : '';
    $status = isset($body['status']) ? trim((string) $body['status']) : 'Müsait';
    
    if ($name === '' || $serial === '') {
        api_json_response(['ok' => false, 'error' => 'name ve serial_number zorunludur.'], 422);
    }
    if (!uav_valid_status($status)) {
        api_json_response(['ok' => false, 'error' => 'Geçersiz status (Müsait, Uçuşta, Bakımda, Arızalı).'], 422);
    }
    
    try {
        $newId = $repo->create($name, $model, $serial, $status);
        $row = $repo->findById($newId);
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
    if (array_key_exists('name', $body)) {
        $patch['Ad'] = trim((string) $body['name']);
    }
    if (array_key_exists('model', $body)) {
        $patch['Model'] = trim((string) $body['model']);
    }
    if (array_key_exists('serial_number', $body)) {
        $patch['Seri_No'] = trim((string) $body['serial_number']);
    }
    if (array_key_exists('status', $body)) {
        $patch['Durum'] = trim((string) $body['status']);
        if (!uav_valid_status($patch['Durum'])) {
            api_json_response(['ok' => false, 'error' => 'Geçersiz status.'], 422);
        }
    }
    if ($patch === []) {
        api_json_response(['ok' => false, 'error' => 'Güncellenecek alan yok.'], 400);
    }
    if (!$repo->update($id, $patch)) {
        api_json_response(['ok' => false, 'error' => 'Güncelleme yapılamadı veya kayıt yok.'], 404);
    }
    api_json_response(['ok' => true, 'data' => $repo->findById($id)]);
}

if ($method === 'DELETE') {
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id parametresi gerekli.'], 400);
    }
    if (!$repo->delete($id)) {
        api_json_response(['ok' => false, 'error' => 'Silinemedi veya kayıt yok.'], 404);
    }
    api_json_response(['ok' => true, 'deleted' => $id]);
}

api_json_response(['ok' => false, 'error' => 'Desteklenmeyen metot.'], 405);
