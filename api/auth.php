<?php
// Kimlik doğrulama: giriş, kayıt, oturum, çıkış
require_once __DIR__ . '/../config.php';

// SHA-256 tabanlı bir kimlik doğrulama sınıfı
class SHAAuth {
    private $conn;

    public function __construct() {
        $this->dbConnect();
    }

    private function dbConnect() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $this->conn = new PDO($dsn, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Veritabanı bağlantı hatası: ' . $e->getMessage());
        }
    }

    public function hashPassword(string $password) {
        $salt = AUTH_SALT;
        $hash = hash('sha256', "$salt$password");
        $hash = hash('sha256', "$hash$salt");
        return $hash;
    }

    public function login(string $eposta, string $password) {
        $eposta = strip_tags(trim($eposta));
        $password = strip_tags(trim($password));

        if ($eposta === '' || $password === '') {
            return ['success' => false, 'message' => ERR_EMPTY_FIELDS];
        }

        $hashedPassword = $this->hashPassword($password);
        $stmt = $this->conn->prepare('SELECT PERSONEL_ID as id, CONCAT(Ad, \' \', Soyad) as username, Rol as role FROM PERSONEL WHERE Eposta = ? AND Sifre_Hash = ? LIMIT 1');
        $stmt->execute([$eposta, $hashedPassword]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => ERR_INVALID_USER];
        }

        $this->createSession($user);
        return ['success' => true, 'message' => 'Giriş başarılı.'];
    }

    public function register(string $ad, string $soyad, string $eposta, string $password, string $passwordConfirm) {
        $ad = strip_tags(trim($ad));
        $soyad = strip_tags(trim($soyad));
        $eposta = strip_tags(trim($eposta));
        $password = strip_tags(trim($password));
        $passwordConfirm = strip_tags(trim($passwordConfirm));

        if ($ad === '' || $soyad === '' || $eposta === '' || $password === '' || $passwordConfirm === '') {
            return ['success' => false, 'message' => ERR_EMPTY_FIELDS];
        }

        if (strlen($password) < PASSWORD_MIN_LEN) {
            return ['success' => false, 'message' => ERR_PASSWORD_SHORT];
        }

        if ($password !== $passwordConfirm) {
            return ['success' => false, 'message' => ERR_PASSWORD_MISMATCH];
        }

        $hashedPassword = $this->hashPassword($password);
        $role = 'Yazılımcı'; // Varsayılan rol

        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO PERSONEL (Ad, Soyad, Eposta, Sifre_Hash, Rol) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$ad, $soyad, $eposta, $hashedPassword, $role]);
        } catch (PDOException $e) {
            $code = (string) $e->getCode();
            if ($code === '23000' || strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['success' => false, 'message' => 'Bu e-posta adresi zaten kayıtlı.'];
            }
            return ['success' => false, 'message' => ERR_REGISTER_FAILED . ' Detay: ' . $e->getMessage()];
        }

        $newId = (int) $this->conn->lastInsertId();
        $user = [
            'id' => $newId,
            'username' => $ad . ' ' . $soyad,
            'role' => $role,
        ];

        $this->createSession($user);
        return ['success' => true, 'message' => 'Kayıt başarılı. Yönlendiriliyorsunuz.'];
    }

    private function createSession(array $user) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['last_activity']) && (time() - (int) $_SESSION['last_activity']) > (SESSION_TIMEOUT * 60)) {
            session_unset();
            session_destroy();
            if (session_status() === PHP_SESSION_NONE) session_start();
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }

    public function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) return false;
        if (isset($_SESSION['last_activity']) && (time() - (int) $_SESSION['last_activity']) > (SESSION_TIMEOUT * 60)) {
            $this->logout();
            return false;
        }
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            $this->logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
        return true;
    }

    public function verifyPassword(string $userId, string $password) {
        $hashedPassword = $this->hashPassword($password);
        $stmt = $this->conn->prepare('SELECT PERSONEL_ID FROM PERSONEL WHERE PERSONEL_ID = ? AND Sifre_Hash = ? LIMIT 1');
        $stmt->execute([$userId, $hashedPassword]);
        return (bool) $stmt->fetch();
    }
}
