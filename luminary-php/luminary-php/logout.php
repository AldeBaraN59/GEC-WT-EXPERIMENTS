<?php
require_once 'session.php';

// Clear session variables
$_SESSION = [];

// Delete only the PHP session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy session
session_destroy();

// Redirect home
header('Location: index.php');
exit;
?>