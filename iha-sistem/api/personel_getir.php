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

    // PERSONEL tablosundan tüm personelleri çek
    $stmt = $conn->query("
        SELECT PERSONEL_ID, Ad, Soyad, Telefon, Eposta, Rol
        FROM PERSONEL
        ORDER BY Ad ASC
    ");
    $personeller = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'personeller' => $personeller], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
