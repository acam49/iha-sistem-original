<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

$gorevId = isset($_POST['gorevId']) ? trim($_POST['gorevId']) : '';

if ($gorevId === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Görev ID boş olamaz.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // Görev var mı kontrol et
    $stmt = $conn->prepare("SELECT COUNT(*) FROM GOREV WHERE GorevID = ?");
    $stmt->execute([$gorevId]);
    if ($stmt->fetchColumn() == 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Görev bulunamadı.']);
        exit;
    }

    // Görevi sil
    $stmt = $conn->prepare("DELETE FROM GOREV WHERE GorevID = ?");
    $stmt->execute([$gorevId]);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Görev sistemden başarıyla silindi.']);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
