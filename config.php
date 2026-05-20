<?php
// filepath: config.php

// Veritabanı ve Güvenlik Yapılandırması (Railway / Local Uyumlu)
$dbHost = getenv('MYSQLHOST') ?: ($_ENV['MYSQLHOST'] ?? ($_SERVER['MYSQLHOST'] ?? 'localhost'));
$dbPort = getenv('MYSQLPORT') ?: ($_ENV['MYSQLPORT'] ?? ($_SERVER['MYSQLPORT'] ?? '3306'));

if ($dbPort && $dbPort !== '3306') {
    $dbHost .= ';port=' . $dbPort;
}

define('DB_HOST', $dbHost);
define('DB_NAME', getenv('MYSQLDATABASE') ?: ($_ENV['MYSQLDATABASE'] ?? ($_SERVER['MYSQLDATABASE'] ?? 'iha_sistemi')));
define('DB_USER', getenv('MYSQLUSER') ?: ($_ENV['MYSQLUSER'] ?? ($_SERVER['MYSQLUSER'] ?? 'root')));
define('DB_PASS', getenv('MYSQLPASSWORD') ?: ($_ENV['MYSQLPASSWORD'] ?? ($_SERVER['MYSQLPASSWORD'] ?? '')));

// SHA-256 için güvenlik anahtarı
define('AUTH_SALT', 'IHA_SISTEM_2026_SECURE_KEY_X9kL2mP');

// Session timeout (dakika)
define('SESSION_TIMEOUT', 30);

// Kayıt / doğrulama
define('USERNAME_MIN_LEN', 3);
define('USERNAME_MAX_LEN', 32);
define('PASSWORD_MIN_LEN', 8);
define('DEFAULT_USER_ROLE', 'user');

// Hata mesajları
define('ERR_INVALID_USER', 'Kullanıcı adı veya şifre hatalı!');
define('ERR_EMPTY_FIELDS', 'Lütfen tüm alanları doldurun!');
define('ERR_SESSION_EXPIRED', 'Oturum süresi doldu, tekrar giriş yapın!');
define('ERR_USERNAME_TAKEN', 'Bu kullanıcı adı zaten kayıtlı.');
define('ERR_PASSWORD_MISMATCH', 'Şifreler eşleşmiyor.');
define('ERR_USERNAME_FORMAT', 'Kullanıcı adı 3–32 karakter olmalı; yalnızca harf, rakam ve alt çizgi kullanın.');
define('ERR_PASSWORD_SHORT', 'Şifre en az 8 karakter olmalıdır.');
define('ERR_REGISTER_FAILED', 'Kayıt sırasında bir hata oluştu. Tekrar deneyin.');

// Otomatik veritabanı kurulumu ve tohumlama (Self-healing Auto-setup)
try {
    $conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tabloların var olup olmadığını kontrol edelim
    $tableCheck = $conn->query("SHOW TABLES LIKE 'PERSONEL'");
    if ($tableCheck->rowCount() == 0) {
        // schema.sql dosyasını çalıştır
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $conn->exec($sql);
            
            // Varsayılan yönetici (admin) kullanıcısı ekle
            $salt = AUTH_SALT;
            $pass = 'admin123';
            $hash1 = hash('sha256', $salt . $pass);
            $hashedPassword = hash('sha256', $hash1 . $salt);
            
            // Örnek bir yönetici kullanıcısı ekle
            $stmt = $conn->prepare("INSERT INTO PERSONEL (Ad, Soyad, Eposta, Sifre_Hash, Rol, Telefon) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Admin', 'User', 'admin@sirius.com', $hashedPassword, 'Yazılımcı', '5551234567']);
            
            // Örnek bir İHA ekle
            $stmt = $conn->prepare("INSERT INTO IHA (Ad, Model, Seri_No, Durum) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Bayraktar TB2', 'Tactical UAV', 'SERI-TB2-001', 'Müsait']);
            
            // Örnek bir Envanter parçası ekle
            $stmt = $conn->prepare("INSERT INTO ENVANTER (Parca_Adi, Tip, Stok_Adedi, Kritik_Seviye) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Karbon Pervane', 'Donanım', 12, 4]);
        }
    }
} catch (PDOException $e) {
    // Veritabanı henüz hazır değilse veya mevcut değilse hata vermeden sessizce devam etsin
}
?>