<?php
require_once 'db.php';
require_once 'functions.php';

/**
 * Handle Login
 */
/**
 * Handle Login with Remember Me
 */
function login($pdo, $email, $password, $remember = false) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
            setcookie('luminary_remember', $token, time() + (86400 * 30), "/"); // 30 days
        }
        
        return true;
    }
    return false;
}

/**
 * Check Remember Me Cookie
 */
function checkRememberMe($pdo) {
    if (isLoggedIn()) return true;

    if (isset($_COOKIE['luminary_remember'])) {
        $token = $_COOKIE['luminary_remember'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}

/**
 * Handle Signup
 */
function signup($pdo, $username, $email, $password, $role = 'student', $bio = '') {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        return "Email or Username already exists.";
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, bio) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$username, $email, $hashedPassword, $role, $bio])) {
        return true;
    }
    return "Error creating account.";
}

/**
 * Handle Logout
 */
function logout($pdo = null) {
    if ($pdo && isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
    setcookie('luminary_remember', '', time() - 3600, "/");
    session_unset();
    session_destroy();
}
?>
