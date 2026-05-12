<?php
require_once 'includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed.']);
        exit;
    }

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to report a course.']);
        exit;
    }

    $courseId = (int)($_POST['course_id'] ?? 0);
    $reason = sanitize($_POST['reason'] ?? '');
    $userId = $_SESSION['user_id'];

    if (!$courseId || !$reason) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO course_reports (user_id, course_id, reason) VALUES (?, ?, ?)");
    if ($stmt->execute([$userId, $courseId, $reason])) {
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully. Our team will review it.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit report.']);
    }
    exit;
}
