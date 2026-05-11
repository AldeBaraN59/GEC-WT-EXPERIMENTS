<?php
require_once 'db.php';
require_once 'functions.php';

/**
 * Handle Login
 */
function login($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Store role for mentor checks
        return true;
    }
    return false;
}

/**
 * Handle Signup
 */
function signup($pdo, $username, $email, $password, $role = 'student', $bio = '') {
    // Check if email or username already exists
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
    return "Error creating account. Please try again.";
}

/**
 * Handle Logout
 */
function logout() {
    session_unset();
    session_destroy();
}
?>
