<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // BAKIM_KAYDI ile MASRAF tablosunu join ederek çek
    $stmt = $conn->query("
        SELECT 
            b.BakimID,
            i.Ad AS IhaAd,
            b.Ariza_Notu,
            b.Cozum_Notu,
            b.Tarih,
            CONCAT(p.Ad, ' ', p.Soyad) AS Sorumlu,
            COALESCE(m.Miktar, 0) AS Masraf
        FROM BAKIM_KAYDI b
        LEFT JOIN IHA i ON b.IhaID = i.IhaID
        LEFT JOIN PERSONEL p ON b.SorumluID = p.PERSONEL_ID
        LEFT JOIN MASRAF m ON m.Ilgili_Kayit_ID = b.BakimID
        ORDER BY b.BakimID DESC
    ");
    $kayitlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'kayitlar' => $kayitlar]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
