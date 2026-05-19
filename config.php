<?php
// filepath: config.php
// Veritabanı ve Güvenlik Yapılandırması (Railway / Local Uyumlu)
$dbHost = getenv('MYSQLHOST') ?: 'localhost';
if (getenv('MYSQLPORT')) {
    $dbHost .= ';port=' . getenv('MYSQLPORT');
}
define('DB_HOST', $dbHost);
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'iha_sistemi');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

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
?>