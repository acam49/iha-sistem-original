<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

$mevcutId = isset($_POST['mevcutId']) ? trim($_POST['mevcutId']) : '';
$yeniId = isset($_POST['yeniId']) ? trim($_POST['yeniId']) : '';
$ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
$soyad = isset($_POST['soyad']) ? trim($_POST['soyad']) : '';
$telefon = isset($_POST['telefon']) ? trim($_POST['telefon']) : '';
$eposta = isset($_POST['eposta']) ? trim($_POST['eposta']) : '';
$yetki = isset($_POST['yetki']) ? trim($_POST['yetki']) : '';

if ($mevcutId === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen güncellenecek mevcut Personel ID girin.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // Personel var mı kontrol et
    $stmt = $conn->prepare("SELECT COUNT(*) FROM PERSONEL WHERE PERSONEL_ID = ?");
    $stmt->execute([$mevcutId]);
    if ($stmt->fetchColumn() == 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Girdiğiniz ID değerine sahip bir personel bulunamadı.']);
        exit;
    }

    // Yeni ID çakışma kontrolü
    if ($yeniId !== '' && $yeniId !== $mevcutId) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM PERSONEL WHERE PERSONEL_ID = ?");
        $stmt->execute([$yeniId]);
        if ($stmt->fetchColumn() > 0) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Girdiğiniz yeni ID numarası zaten başka bir personele ait.']);
            exit;
        }
    }

    // E-posta çakışma kontrolü
    if ($eposta !== '') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM PERSONEL WHERE Eposta = ? AND PERSONEL_ID != ?");
        $stmt->execute([$eposta, $mevcutId]);
        if ($stmt->fetchColumn() > 0) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Girdiğiniz yeni e-posta adresi zaten başka bir personel tarafından kullanılıyor.']);
            exit;
        }
    }

    $updates = [];
    $params = [];

    if ($yeniId !== '') {
        $updates[] = "PERSONEL_ID = ?";
        $params[] = $yeniId;
    }
    if ($ad !== '') {
        $updates[] = "Ad = ?";
        $params[] = $ad;
    }
    if ($soyad !== '') {
        $updates[] = "Soyad = ?";
        $params[] = $soyad;
    }
    if ($telefon !== '') {
        $updates[] = "Telefon = ?";
        $params[] = $telefon;
    }
    if ($eposta !== '') {
        $updates[] = "Eposta = ?";
        $params[] = $eposta;
    }
    if ($yetki !== '') {
        $updates[] = "Rol = ?";
        $params[] = $yetki;
    }

    if (empty($updates)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Lütfen güncellenecek en az bir alan girin.']);
        exit;
    }

    $params[] = $mevcutId;
    $sql = "UPDATE PERSONEL SET " . implode(", ", $updates) . " WHERE PERSONEL_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Personel bilgileri başarıyla güncellendi.']);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
