<?php
// Giriş sayfası
require_once __DIR__ . '/config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: kullanici paneli/anasayfa/dashboard.html');
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
        header('Location: kullanici paneli/anasayfa/dashboard.html');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/home_style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <nav>
        <div class="logo-kapsayici" onclick="window.location.href='index.html'">
            <img src="assets/images/logo.png" alt="Tulpar Logo" class="logo-img">
        </div>
        
        <div class="nav-links">
            <a href="index.html" id="link-ana"><span class="tr">Ana Sayfa</span></a>
            <a href="index.html#sayfa-hakkimizda" id="link-hakkimizda"><span class="tr">Hakkımızda</span></a>
            <a href="login.php" class="login-btn"><span class="tr">SİSTEME GİRİŞ</span></a>
            <a href="register.php" class="register-nav"><span class="tr">KAYIT</span></a>
        </div>
    </nav>

    <div class="auth-container" style="margin-top: 80px;">
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

    </div>
    <script src="js/validation.js"></script>
</body>
</html>
