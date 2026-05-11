<?php
require_once 'includes/init.php';

$email = sanitize($_GET['email'] ?? '');
$username = sanitize($_GET['username'] ?? '');

header('Content-Type: application/json');

if ($email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    echo json_encode(['available' => !$stmt->fetch()]);
    exit();
}

if ($username) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    echo json_encode(['available' => !$stmt->fetch()]);
    exit();
}

echo json_encode(['error' => 'Invalid parameters']);
?>
