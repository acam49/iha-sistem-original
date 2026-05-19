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

if ($gorevId === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Görev ID boş olamaz.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");

    // Görev bilgilerini çek
    $stmt = $conn->prepare("SELECT * FROM GOREV WHERE GorevID = ?");
    $stmt->execute([$gorevId]);
    $gorev = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gorev) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Görev bulunamadı.']);
        exit;
    }

    if ($gorev['Durum'] === 'Tamamlandı') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Bu görev zaten tamamlanmış.']);
        exit;
    }

    $ihaId = $gorev['IhaID'];
    $pilotId = $gorev['PilotID'];

    // Eğer eski bir kayıt ise ve IhaID / PilotID null ise veritabanından varsayılan atayalım
    if (!$ihaId || !$pilotId) {
        // İlk bulduğumuz İHA'yı alalım
        $stmtIha = $conn->query("SELECT IhaID FROM IHA LIMIT 1");
        $ihaId = $stmtIha->fetchColumn();

        // İlk bulduğumuz Personel'i alalım
        $stmtPilot = $conn->query("SELECT PERSONEL_ID FROM PERSONEL LIMIT 1");
        $pilotId = $stmtPilot->fetchColumn();

        if (!$ihaId || !$pilotId) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Bu görev eski bir kayıttır ve İHA/Pilot ataması bulunmamaktadır. Görevi sonlandırmak için lütfen sistemde en az 1 İHA ve 1 Personel tanımlı olduğundan emin olun.']);
            exit;
        }
        
        // Görevi güncelleyelim ki tekrar null hatası vermesin
        $stmtUpdateGorev = $conn->prepare("UPDATE GOREV SET IhaID = ?, PilotID = ? WHERE GorevID = ?");
        $stmtUpdateGorev->execute([$ihaId, $pilotId, $gorevId]);
    }

    // Transaction başlatalım
    $conn->beginTransaction();

    // Görevi Tamamlandı yap
    $stmt = $conn->prepare("UPDATE GOREV SET Durum = 'Tamamlandı' WHERE GorevID = ?");
    $stmt->execute([$gorevId]);

    // İHA'yı tekrar Müsait durumuna çekelim
    $stmt = $conn->prepare("UPDATE IHA SET Durum = 'Müsait' WHERE IhaID = ?");
    $stmt->execute([$ihaId]);

    // Uçuş logu oluştur
    $tarih = $gorev['Planlanan_Tarih'];
    $baslangic = $tarih . ' 10:00:00';
    
    // 1 ile 4 saat arası rastgele uçuş süresi ekleyelim
    $sureSaat = rand(1, 4);
    $sureDakika = rand(0, 59);
    $bitis = date('Y-m-d H:i:s', strtotime("+$sureSaat hours +$sureDakika minutes", strtotime($baslangic)));

    $havaDurumlari = ['Açık', 'Güneşli', 'Bulutlu', 'Hafif Rüzgarlı'];
    $hava = $havaDurumlari[array_rand($havaDurumlari)];

    $stmtLog = $conn->prepare("INSERT INTO UCUS_LOGU (IhaID, GorevID, PilotID, Baslangic_Saati, Bitis_Saati, Hava_Durumu) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtLog->execute([$ihaId, $gorevId, $pilotId, $baslangic, $bitis, $hava]);

    $conn->commit();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Görev başarıyla tamamlandı ve uçuş logu kaydedildi.']);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
