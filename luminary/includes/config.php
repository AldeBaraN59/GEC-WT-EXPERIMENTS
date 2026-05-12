<?php
function loadEnv($path)
{
    if (!file_exists($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

loadEnv(__DIR__ . '/.env');

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'luminary_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'abcd');
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost');

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
?>



?- register(john, 9999).
User already exists
true.

?- login(john, 1234).
Login successful
true.

?- login(john, 1111).
Invalid username or password
true.