<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // Görev/Uçuş masraflarını (Bakım hariç olanları) çek
    $stmt = $conn->query("
        SELECT 
            m.MasrafID,
            m.Ilgili_Kayit_ID AS GorevID,
            ul.PilotID AS PersonelID,
            CONCAT(p.Ad, ' ', p.Soyad) AS PilotAdi,
            m.Kategori,
            m.Miktar AS Tutar
        FROM MASRAF m
        LEFT JOIN GOREV g ON m.Ilgili_Kayit_ID = g.GorevID
        LEFT JOIN UCUS_LOGU ul ON ul.GorevID = g.GorevID
        LEFT JOIN PERSONEL p ON ul.PilotID = p.PERSONEL_ID
        WHERE m.Kategori != 'Bakım'
        ORDER BY m.MasrafID DESC
    ");
    $masraflar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'masraflar' => $masraflar], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
