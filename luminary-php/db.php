<?php
// db.php — credentials loaded from config.env

$config_path = dirname(__DIR__) . '/config.env';

$env = parse_ini_file($config_path);

if ($env === false) {
    die('Database config file not found or invalid: ' .
        htmlspecialchars($config_path, ENT_QUOTES, 'UTF-8'));
}

$required_keys = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];

foreach ($required_keys as $key) {
    if (!array_key_exists($key, $env)) {
        die('Missing database config key: ' .
            htmlspecialchars($key, ENT_QUOTES, 'UTF-8'));
    }
}

try {
    $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO(
        $dsn,
        $env['DB_USER'],
        $env['DB_PASS'],
        $options
    );

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>