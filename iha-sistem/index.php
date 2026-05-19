<?php
// Giriş sayfası
require_once __DIR__ . '/config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $success = 'Kayıt tamam. Kullanıcı adı ve şifrenizle giriş yapabilirsiniz.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/api/auth.php';

    $auth = new SHAAuth();
    $result = $auth->login($_POST['eposta'] ?? '', $_POST['password'] ?? '');

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
    <title>İHA Sistem - Giriş</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-logo">
            <h1>İHA SİSTEM</h1>
            <p>Güvenli giriş · SHA-256</p>
        </div>

        <?php if ($success): ?>
            <div class="msg-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="" autocomplete="on">
            <div class="form-group">
                <label for="eposta">E-posta Adresi</label>
                <input type="email" id="eposta" name="eposta" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-primary">Sisteme giriş</button>
        </form>

        <div class="auth-footer">
            <a href="register.php">Hesap oluştur</a>
            <span class="sep">·</span>
            <a href="index.html">Ana sayfa</a>
        </div>
    </div>
    <script src="js/validation.js"></script>
</body>
</html>
