<?php
// db.php — credentials loaded from config.env (not tracked by git)
$env  = parse_ini_file(__DIR__ . '/config.env');
$conn = mysqli_connect(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME']
);
if (!$conn) {
    error_log(mysqli_connect_error());
    die("Database error. Please try again later.");
}
?>