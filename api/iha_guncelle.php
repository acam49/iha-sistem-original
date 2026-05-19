<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
$durum = isset($_POST['durum']) ? trim($_POST['durum']) : '';

if ($id === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen güncellenecek İHA ID girin.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // İHA var mı kontrol et
    $stmt = $conn->prepare("SELECT COUNT(*) FROM IHA WHERE IhaID = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Girdiğiniz ID değerine sahip bir İHA bulunamadı.']);
        exit;
    }

    // Dinamik sorgu oluşturma
    $updates = [];
    $params = [];

    if ($ad !== '') {
        $updates[] = "Ad = ?";
        $params[] = $ad;
    }
    if ($durum !== '') {
        $updates[] = "Durum = ?";
        $params[] = $durum;
    }

    if (empty($updates)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Lütfen güncellenecek en az bir alan doldurun (Yeni Ad veya Durum).']);
        exit;
    }

    $params[] = $id;
    $sql = "UPDATE IHA SET " . implode(", ", $updates) . " WHERE IhaID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'İHA başarıyla güncellendi.']);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
