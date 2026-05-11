<?php
require_once 'includes/init.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $courseId = (int)($data['course_id'] ?? 0);
    $rating = (int)($data['rating'] ?? 0);
    $comment = sanitize($data['comment'] ?? '');
    $userId = $_SESSION['user_id'];

    if (!$courseId || $rating < 1 || $rating > 5) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, course_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)");
        $stmt->execute([$userId, $courseId, $rating, $comment]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit();
}
?>
