<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

$ihaId = isset($_POST['ihaId']) ? trim($_POST['ihaId']) : '';
$sorumluId = isset($_POST['sorumluId']) ? trim($_POST['sorumluId']) : '';
$tarih = isset($_POST['tarih']) ? trim($_POST['tarih']) : '';
$ariza = isset($_POST['ariza']) ? trim($_POST['ariza']) : '';
$cozum = isset($_POST['cozum']) ? trim($_POST['cozum']) : '';
$masraf = isset($_POST['masraf']) ? trim($_POST['masraf']) : 0;
$durum = isset($_POST['durum']) ? trim($_POST['durum']) : ''; // 'tamirde' veya 'bitti'

if ($ihaId === '' || $sorumluId === '' || $tarih === '' || $ariza === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen gerekli alanları (İHA ID, Sorumlu ID, Tarih ve Arıza Notu) doldurun.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // İHA kontrolü
    $stmt = $conn->prepare("SELECT COUNT(*) FROM IHA WHERE IhaID = ?");
    $stmt->execute([$ihaId]);
    if ($stmt->fetchColumn() == 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Girdiğiniz ID değerine sahip bir İHA bulunamadı.']);
        exit;
    }

    // Personel kontrolü
    $stmt = $conn->prepare("SELECT COUNT(*) FROM PERSONEL WHERE PERSONEL_ID = ?");
    $stmt->execute([$sorumluId]);
    if ($stmt->fetchColumn() == 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Girdiğiniz ID değerine sahip bir personel bulunamadı.']);
        exit;
    }

    // İşlemleri transaction ile yapalım
    $conn->beginTransaction();

    // 1. BAKIM_KAYDI tablosuna ekle
    // Eğer durum 'tamirde' ise cozum_notu boş veya null kalsın. 'bitti' ise cozum notunu yazalım
    $realCozum = ($durum === 'bitti') ? ($cozum !== '' ? $cozum : 'Tamir tamamlandı.') : '';

    $stmt = $conn->prepare("INSERT INTO BAKIM_KAYDI (IhaID, SorumluID, Tarih, Ariza_Notu, Cozum_Notu) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ihaId, $sorumluId, $tarih, $ariza, $realCozum]);
    $bakimId = $conn->lastInsertId();

    // 2. MASRAF tablosuna ekle (masraf > 0 ise)
    if (floatval($masraf) > 0) {
        $stmt = $conn->prepare("INSERT INTO MASRAF (Ilgili_Kayit_ID, Miktar, Kategori, Tarih) VALUES (?, ?, ?, ?)");
        $stmt->execute([$bakimId, $masraf, 'Bakım', $tarih]);
    }

    // 3. İHA'nın durumunu güncelle
    // Durum 'tamirde' ise İHA'yı 'Bakımda', 'bitti' ise 'Müsait' yapalım
    $ihaDurum = ($durum === 'bitti') ? 'Müsait' : 'Bakımda';
    $stmt = $conn->prepare("UPDATE IHA SET Durum = ? WHERE IhaID = ?");
    $stmt->execute([$ihaDurum, $ihaId]);

    $conn->commit();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Bakım kaydı başarıyla oluşturuldu.']);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
