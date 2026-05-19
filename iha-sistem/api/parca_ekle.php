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
$tip = isset($_POST['tip']) ? trim($_POST['tip']) : '';
$stok = isset($_POST['stok']) ? trim($_POST['stok']) : '';
$kritik = isset($_POST['kritik']) ? trim($_POST['kritik']) : '';

if ($ad === '' || $tip === '' || $stok === '' || $kritik === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen tüm gerekli alanları doldurun.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    if ($id !== '') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ENVANTER WHERE ParcaID = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Bu Parça ID zaten kullanımda.']);
            exit;
        }
    }

    if ($id !== '') {
        $stmt = $conn->prepare("INSERT INTO ENVANTER (ParcaID, Parca_Adi, Tip, Stok_Adedi, Kritik_Seviye) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $ad, $tip, $stok, $kritik]);
    } else {
        $stmt = $conn->prepare("INSERT INTO ENVANTER (Parca_Adi, Tip, Stok_Adedi, Kritik_Seviye) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ad, $tip, $stok, $kritik]);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Parça başarıyla envantere eklendi.']);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
