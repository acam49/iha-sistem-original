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
$kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : '';
$masraf = isset($_POST['masraf']) ? trim($_POST['masraf']) : '';

if ($gorevId === '' || $kategori === '' || $masraf === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen tüm gerekli alanları (Görev ID, Kategori ve Tutar) doldurun.']);
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
        echo json_encode(['error' => 'Girdiğiniz ID değerine sahip bir görev bulunamadı.']);
        exit;
    }

    // Uçuş logu var mı kontrol edip tarihini alalım
    $stmt = $conn->prepare("SELECT DATE(Baslangic_Saati) as UcusTarihi FROM UCUS_LOGU WHERE GorevID = ? LIMIT 1");
    $stmt->execute([$gorevId]);
    $ucusTarihi = $stmt->fetchColumn();

    $tarih = $ucusTarihi ? $ucusTarihi : date('Y-m-d');

    // MASRAF tablosuna ekle
    $stmt = $conn->prepare("INSERT INTO MASRAF (Ilgili_Kayit_ID, Miktar, Kategori, Tarih) VALUES (?, ?, ?, ?)");
    $stmt->execute([$gorevId, $masraf, $kategori, $tarih]);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Masraf başarıyla sisteme işlendi.']);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
