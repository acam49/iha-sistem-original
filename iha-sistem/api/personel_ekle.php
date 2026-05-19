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
$soyad = isset($_POST['soyad']) ? trim($_POST['soyad']) : '';
$telefon = isset($_POST['telefon']) ? trim($_POST['telefon']) : '';
$eposta = isset($_POST['eposta']) ? trim($_POST['eposta']) : '';
$sifre = isset($_POST['sifre']) ? trim($_POST['sifre']) : '';
$yetki = isset($_POST['yetki']) ? trim($_POST['yetki']) : 'Personel';

if ($id === '' || $ad === '' || $soyad === '' || $eposta === '' || $sifre === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Lütfen gerekli tüm alanları (ID, Ad, Soyad, E-posta ve Şifre) doldurun.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // ID çakışma kontrolü
    $stmt = $conn->prepare("SELECT COUNT(*) FROM PERSONEL WHERE PERSONEL_ID = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Bu ID numarasına sahip bir personel zaten kayıtlı.']);
        exit;
    }

    // E-posta çakışma kontrolü
    $stmt = $conn->prepare("SELECT COUNT(*) FROM PERSONEL WHERE Eposta = ?");
    $stmt->execute([$eposta]);
    if ($stmt->fetchColumn() > 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Bu e-posta adresine sahip bir personel zaten kayıtlı.']);
        exit;
    }

    // Şifre hashleme
    $sifreHash = password_hash($sifre, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO PERSONEL (PERSONEL_ID, Ad, Soyad, Eposta, Sifre_Hash, Rol, Telefon) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $ad, $soyad, $eposta, $sifreHash, $yetki, $telefon]);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Personel başarıyla kaydedildi.']);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
