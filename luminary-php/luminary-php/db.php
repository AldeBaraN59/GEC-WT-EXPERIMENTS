<?php
// db.php — credentials loaded from config.env (not tracked by git)

$config_path = __DIR__ . '/config.env';
$env = parse_ini_file(dirname(__DIR__) . '/config.env');

if ($env === false) {
    die('Database config file not found or invalid: ' . htmlspecialchars($config_path, ENT_QUOTES, 'UTF-8'));
}

$required_keys = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];

foreach ($required_keys as $key) {
    if (!array_key_exists($key, $env)) {
        die('Missing database config key: ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8'));
    }
}

$conn = mysqli_connect(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME']
);

if (!$conn) {
    error_log(mysqli_connect_error());
    die('Database connection failed. Check config.env credentials.');
}
?>