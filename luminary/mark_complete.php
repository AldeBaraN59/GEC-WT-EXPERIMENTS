<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

session_start();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$courseId = (int)($data['course_id'] ?? 0);
$materialId = (int)($data['material_id'] ?? 0);

if (!$courseId || !$materialId) {
    echo json_encode(['success' => false, 'error' => 'Missing IDs']);
    exit();
}

try {
    // 1. Record progress
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_progress (user_id, course_id, material_id) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $courseId, $materialId]);
    
    // 2. Recalculate course progress
    $newProgress = calculateCourseProgress($pdo, $userId, $courseId);
    
    // 3. Get total hours learned
    $totalHours = calculateHoursLearned($pdo, $userId);
    
    echo json_encode([
        'success' => true, 
        'progress' => $newProgress,
        'hours' => $totalHours,
        'completed' => ($newProgress >= 100)
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
