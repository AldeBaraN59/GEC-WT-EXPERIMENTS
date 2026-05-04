<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'shivankabcd0604!1');
define('DB_NAME', 'luminary_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

function get_db_connection(): mysqli {
    global $conn;
    return $conn;
}