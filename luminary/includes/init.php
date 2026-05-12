<?php
/**
 * init.php
 * 
 * Central initialization file for abcd. 
 * Loads all core libraries and establishes database connection
 * without outputting any HTML, allowing for redirects.
 */

// If we are in the includes directory, we might need a different path logic
// but since all entry points include from root, we can rely on relative paths 
// or let the include_path handle it.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

checkRememberMe($pdo);
$currentUser = isLoggedIn() ? getCurrentUser($pdo) : null;
$csrfToken = generateCsrfToken();
?>
