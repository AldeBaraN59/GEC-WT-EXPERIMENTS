<?php
// Configuration settings for Luminary

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'luminary_db');
define('DB_USER', getenv('DB_USER') ?: 'root'); 
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''); 

define('SITE_NAME', 'abcd');
define('SITE_URL', 'http://localhost');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
?>
