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

    // 1. Aktif İHA Sayısı
    $stmt1 = $conn->query("SELECT COUNT(*) FROM IHA WHERE Durum = 'Müsait'");
    $aktifIha = $stmt1->fetchColumn();

    // 2. Kritik Stok
    $stmt2 = $conn->query("SELECT COUNT(*) FROM ENVANTER WHERE Stok_Adedi <= Kritik_Seviye");
    $kritikStok = $stmt2->fetchColumn();

    $stmt2b = $conn->query("SELECT Parca_Adi FROM ENVANTER WHERE Stok_Adedi <= Kritik_Seviye LIMIT 1");
    $kritikParca = $stmt2b->fetchColumn();
    $kritikDetay = $kritikParca ? $kritikParca . ' Azaldı' : 'Tüm stoklar yeterli';

    // 3. Bekleyen Görev
    $stmt3 = $conn->query("SELECT COUNT(*) FROM GOREV WHERE Planlanan_Tarih >= CURDATE()");
    $bekleyenGorev = $stmt3->fetchColumn();

    $stmt3b = $conn->query("SELECT Baslik, Planlanan_Tarih FROM GOREV WHERE Planlanan_Tarih >= CURDATE() ORDER BY Planlanan_Tarih ASC LIMIT 1");
    $gorevRow = $stmt3b->fetch(PDO::FETCH_ASSOC);
    $gorevDetay = $gorevRow ? $gorevRow['Planlanan_Tarih'] . ' (' . $gorevRow['Baslik'] . ')' : 'Planlı görev yok';

    // 4. Son Bakım Masrafı
    $stmt4 = $conn->query("SELECT m.Miktar, b.Ariza_Notu 
                           FROM MASRAF m 
                           LEFT JOIN BAKIM_KAYDI b ON m.Ilgili_Kayit_ID = b.BakimID 
                           ORDER BY m.MasrafID DESC LIMIT 1");
    $masrafRow = $stmt4->fetch(PDO::FETCH_ASSOC);
    
    $sonMasraf = $masrafRow ? $masrafRow['Miktar'] : 0;
    $sonMasrafDetay = ($masrafRow && !empty($masrafRow['Ariza_Notu'])) ? $masrafRow['Ariza_Notu'] : 'Masraf/Bakım kaydı yok';

    header('Content-Type: application/json');
    echo json_encode([
        'username' => $_SESSION['username'] ?? 'Kullanıcı',
        'role' => $_SESSION['role'] ?? 'Personel',
        'aktifIha' => $aktifIha,
        'kritikStok' => $kritikStok,
        'kritikDetay' => $kritikDetay,
        'bekleyenGorev' => $bekleyenGorev,
        'gorevDetay' => $gorevDetay,
        'sonMasraf' => number_format((float)$sonMasraf, 0, ',', '.') . ' TL',
        'sonMasrafDetay' => $sonMasrafDetay
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
