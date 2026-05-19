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

    // Öncelikle böyle bir görev var mı kontrol edelim
    $stmtGorev = $conn->prepare("
        SELECT 
            g.GorevID,
            g.IhaID,
            g.PilotID,
            CONCAT(p.Ad, ' ', p.Soyad) AS PilotAdi,
            p.PERSONEL_ID,
            i.Ad AS IhaAdi
        FROM GOREV g
        LEFT JOIN PERSONEL p ON g.PilotID = p.PERSONEL_ID
        LEFT JOIN IHA i ON g.IhaID = i.IhaID
        WHERE g.GorevID = ?
        LIMIT 1
    ");
    $stmtGorev->execute([$gorevId]);
    $gorev = $stmtGorev->fetch(PDO::FETCH_ASSOC);

    if (!$gorev) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['found' => false, 'message' => 'Bu ID ile eşleşen görev bulunamadı.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Görev ID ile UCUS_LOGU tablosundaki uçuşları çekelim
    $stmtLogs = $conn->prepare("
        SELECT 
            ul.LogID,
            ul.PilotID,
            CONCAT(p.Ad, ' ', p.Soyad) AS PilotAdi,
            i.Ad AS IhaAdi,
            ul.Baslangic_Saati,
            ul.Bitis_Saati,
            ul.Hava_Durumu,
            TIMESTAMPDIFF(MINUTE, ul.Baslangic_Saati, ul.Bitis_Saati) AS SureDakika
        FROM UCUS_LOGU ul
        LEFT JOIN PERSONEL p ON ul.PilotID = p.PERSONEL_ID
        LEFT JOIN IHA i ON ul.IhaID = i.IhaID
        WHERE ul.GorevID = ?
        ORDER BY ul.LogID ASC
    ");
    $stmtLogs->execute([$gorevId]);
    $dbLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

    $logs = [];

    if (count($dbLogs) > 0) {
        foreach ($dbLogs as $dbLog) {
            $sureDk = (int)$dbLog['SureDakika'];
            $sureStr = floor($sureDk / 60) . ' Saat ' . ($sureDk % 60) . ' Dk';

            $logs[] = [
                'logId' => $dbLog['LogID'],
                'pilotId' => $dbLog['PilotID'] ?? '-',
                'pilotAdi' => $dbLog['PilotAdi'] ?? 'Belirtilmedi',
                'ihaAdi' => $dbLog['IhaAdi'] ?? 'Belirtilmedi',
                'sure' => $sureStr,
                'havaDurumu' => $dbLog['Hava_Durumu'] ?? '-'
            ];
        }
    } else {
        // Uçuş logu yoksa planlanan görevi tek bir log gibi ekleyelim
        $logs[] = [
            'logId' => null,
            'pilotId' => $gorev['PERSONEL_ID'] ?? '-',
            'pilotAdi' => $gorev['PilotAdi'] ?? 'Belirtilmedi',
            'ihaAdi' => $gorev['IhaAdi'] ?? 'Belirtilmedi',
            'sure' => 'Uçuş Yapılmadı',
            'havaDurumu' => '-'
        ];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'found' => true,
        'gorevId' => $gorev['GorevID'],
        'logs' => $logs
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
