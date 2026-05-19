<?php
// Yeni kullanıcı kaydı
require_once __DIR__ . '/config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/api/auth.php';

    $auth = new SHAAuth();
    $result = $auth->register(
        $_POST['ad'] ?? '',
        $_POST['soyad'] ?? '',
        $_POST['eposta'] ?? '',
        $_POST['password'] ?? '',
        $_POST['password_confirm'] ?? ''
    );

    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İHA Sistem - Kayıt</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-logo">
            <h1>Kayıt</h1>
            <p>Yeni hesap oluşturun</p>
        </div>

        <?php if ($error): ?>
            <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="ad">Ad</label>
                <input type="text" id="ad" name="ad" required
                       minlength="2" maxlength="50" autocomplete="given-name">
            </div>
            <div class="form-group">
                <label for="soyad">Soyad</label>
                <input type="text" id="soyad" name="soyad" required
                       minlength="2" maxlength="50" autocomplete="family-name">
            </div>

            <div class="form-group">
                <label for="eposta">E-posta Adresi</label>
                <input type="email" id="eposta" name="eposta" required
                       autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required
                       minlength="<?php echo (int) PASSWORD_MIN_LEN; ?>"
                       autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="password_confirm">Şifre (tekrar)</label>
                <input type="password" id="password_confirm" name="password_confirm" required
                       minlength="<?php echo (int) PASSWORD_MIN_LEN; ?>"
                       autocomplete="new-password">
            </div>

            <button type="submit" class="btn-primary">Kayıt ol</button>
        </form>

        <div class="auth-footer">
            <a href="index.php">Zaten hesabım var — giriş</a>
            <span class="sep">·</span>
            <a href="index.html">Ana sayfa</a>
        </div>
    </div>
    <script src="js/validation.js"></script>
</body>
</html>
