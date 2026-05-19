<?php
// filepath: api/db_test.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

echo json_encode([
    'MYSQLHOST_getenv' => getenv('MYSQLHOST') ? 'Set' : 'Empty',
    'MYSQLHOST_ENV' => isset($_ENV['MYSQLHOST']) ? 'Set' : 'Empty',
    'MYSQLHOST_SERVER' => isset($_SERVER['MYSQLHOST']) ? 'Set' : 'Empty',
    'DB_HOST_constant' => defined('DB_HOST') ? DB_HOST : 'Not defined',
    'DB_NAME_constant' => defined('DB_NAME') ? DB_NAME : 'Not defined',
    'DB_USER_constant' => defined('DB_USER') ? DB_USER : 'Not defined',
    'DB_PASS_length' => defined('DB_PASS') ? strlen(DB_PASS) : 0
], JSON_PRETTY_PRINT);
?>
