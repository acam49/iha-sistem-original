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

    // İHA'ları Getir
    $stmt1 = $conn->query("SELECT IhaID, Ad, Model, Durum FROM IHA ORDER BY IhaID DESC");
    $ihalar = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Envanter/Parçaları Getir
    $stmt2 = $conn->query("SELECT ParcaID, Parca_Adi, Tip, Stok_Adedi, Kritik_Seviye FROM ENVANTER ORDER BY ParcaID DESC");
    $envanter = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'ihalar' => $ihalar,
        'envanter' => $envanter
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
