<?php
require_once __DIR__ . '/auth.php';

$auth = new SHAAuth();
if (!$auth->checkSession()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

$gorevId = trim($_GET['gorev_id'] ?? '');

if ($gorevId === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Görev ID gerekli']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // Görev ID ile UCUS_LOGU'ndan uçuş verilerini çek
    $stmt = $conn->prepare("
        SELECT 
            ul.LogID,
            ul.GorevID,
            CONCAT(p.Ad, ' ', p.Soyad) AS PilotAdi,
            p.PERSONEL_ID,
            i.Ad AS IhaAdi,
            ul.Baslangic_Saati,
            ul.Bitis_Saati,
            ul.Hava_Durumu,
            TIMESTAMPDIFF(MINUTE, ul.Baslangic_Saati, ul.Bitis_Saati) AS SureDakika
        FROM UCUS_LOGU ul
        LEFT JOIN PERSONEL p ON ul.PilotID = p.PersonelID
        LEFT JOIN IHA i ON ul.IhaID = i.IhaID
        WHERE ul.GorevID = ?
        LIMIT 1
    ");
    $stmt->execute([$gorevId]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$log) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['found' => false, 'message' => 'Bu ID ile eşleşen uçuş logu bulunamadı.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Süreyi saat/dakika formatına çevir
    $sureDk = (int)$log['SureDakika'];
    $sureStr = floor($sureDk / 60) . ' Saat ' . ($sureDk % 60) . ' Dk';

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'found' => true,
        'pilotId' => $log['PERSONEL_ID'],
        'pilotAdi' => $log['PilotAdi'],
        'ihaAdi' => $log['IhaAdi'],
        'sure' => $sureStr,
        'havaDurumu' => $log['Hava_Durumu'] ?? '-',
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
