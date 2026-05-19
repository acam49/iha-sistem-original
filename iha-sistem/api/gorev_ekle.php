<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

$baslik = isset($_POST['baslik']) ? trim($_POST['baslik']) : '';
$detay = isset($_POST['detay']) ? trim($_POST['detay']) : '';
$lokasyon = isset($_POST['lokasyon']) ? trim($_POST['lokasyon']) : '';
$ihaId = isset($_POST['ihaId']) ? trim($_POST['ihaId']) : '';
$pilotId = isset($_POST['pilotId']) ? trim($_POST['pilotId']) : '';
$tarih = isset($_POST['tarih']) ? trim($_POST['tarih']) : '';

if ($baslik === '' || $detay === '' || $lokasyon === '' || $ihaId === '' || $pilotId === '' || $tarih === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen tüm alanları eksiksiz doldurun.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // İHA var mı ve müsait mi kontrol et
    $stmt = $conn->prepare("SELECT Durum FROM IHA WHERE IhaID = ?");
    $stmt->execute([$ihaId]);
    $ihaDurum = $stmt->fetchColumn();
    if (!$ihaDurum) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Seçilen İHA sistemde kayıtlı değil.']);
        exit;
    }

    // Personel var mı kontrol et
    $stmt = $conn->prepare("SELECT COUNT(*) FROM PERSONEL WHERE PERSONEL_ID = ?");
    $stmt->execute([$pilotId]);
    if ($stmt->fetchColumn() == 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Girdiğiniz ID değerine sahip bir personel bulunamadı.']);
        exit;
    }

    // Transaction başlatalım
    $conn->beginTransaction();

    // Görevi ekle
    $stmt = $conn->prepare("INSERT INTO GOREV (Baslik, Detay, Planlanan_Tarih, Lokasyon, IhaID, PilotID, Durum) VALUES (?, ?, ?, ?, ?, ?, 'Devam Ediyor')");
    $stmt->execute([$baslik, $detay, $tarih, $lokasyon, $ihaId, $pilotId]);
    
    // İHA durumunu 'Uçuşta' yapalım
    $stmt = $conn->prepare("UPDATE IHA SET Durum = 'Uçuşta' WHERE IhaID = ?");
    $stmt->execute([$ihaId]);

    $conn->commit();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Görev başarıyla planlandı ve İHA göreve atandı.']);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
