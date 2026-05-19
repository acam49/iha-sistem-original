<?php
// filepath: logout.php
// Güvenli Çıkış İşlemi

require_once __DIR__ . '/api/auth.php';

$auth = new SHAAuth();
$auth->logout();

header('Location: index.php?loggedout=1');
exit;
?>