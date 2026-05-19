<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

// Post verilerini al
$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
$model = isset($_POST['model']) ? trim($_POST['model']) : '';
$seri = isset($_POST['seri']) ? trim($_POST['seri']) : '';
$durum = isset($_POST['durum']) ? trim($_POST['durum']) : 'Müsait';

if ($ad === '' || $model === '' || $seri === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen tüm gerekli alanları doldurun.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // ID girilmişse kontrol et
    if ($id !== '') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM IHA WHERE IhaID = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Bu İHA ID zaten kullanımda.']);
            exit;
        }
    }

    // Seri No kontrolü
    $stmt = $conn->prepare("SELECT COUNT(*) FROM IHA WHERE Seri_No = ?");
    $stmt->execute([$seri]);
    if ($stmt->fetchColumn() > 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Bu Seri No zaten kayıtlı.']);
        exit;
    }

    if ($id !== '') {
        $stmt = $conn->prepare("INSERT INTO IHA (IhaID, Ad, Model, Seri_No, Durum) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $ad, $model, $seri, $durum]);
    } else {
        $stmt = $conn->prepare("INSERT INTO IHA (Ad, Model, Seri_No, Durum) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ad, $model, $seri, $durum]);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'İHA başarıyla kaydedildi.']);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
