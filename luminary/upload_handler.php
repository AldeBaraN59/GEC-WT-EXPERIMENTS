<?php
require_once 'includes/init.php';
require_once 'includes/upload.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'mentor') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $result = uploadFile($_FILES['file']);
    
    header('Content-Type: application/json');
    if (isset($result['error'])) {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    } else {
        echo json_encode([
            'success' => true, 
            'path' => $result['path'],
            'mime' => $_FILES['file']['type']
        ]);
    }
    exit();
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'No file uploaded']);
?>
