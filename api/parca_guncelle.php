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
$stok = isset($_POST['stok']) ? trim($_POST['stok']) : '';
$kritik = isset($_POST['kritik']) ? trim($_POST['kritik']) : '';

if ($id === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen güncellenecek Parça ID girin.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // Parça var mı kontrol et
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ENVANTER WHERE ParcaID = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Girdiğiniz ID değerine sahip bir parça bulunamadı.']);
        exit;
    }

    $updates = [];
    $params = [];

    if ($stok !== '') {
        $updates[] = "Stok_Adedi = ?";
        $params[] = $stok;
    }
    if ($kritik !== '') {
        $updates[] = "Kritik_Seviye = ?";
        $params[] = $kritik;
    }

    if (empty($updates)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Lütfen güncellenecek en az bir alan girin (Stok veya Kritik Seviye).']);
        exit;
    }

    $params[] = $id;
    $sql = "UPDATE ENVANTER SET " . implode(", ", $updates) . " WHERE ParcaID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Stok bilgileri başarıyla güncellendi.']);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
