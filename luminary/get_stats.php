<?php
require_once 'includes/init.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // 1. Hours Learned
    $hoursLearned = calculateHoursLearned($pdo, $userId);

    // 2. Enrollment count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
    $stmt->execute([$userId]);
    $courseCount = $stmt->fetchColumn();

    // 3. Certificates count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE user_id = ?");
    $stmt->execute([$userId]);
    $completedCount = $stmt->fetchColumn();

    // 4. Recent Activity
    $stmt = $pdo->prepare("SELECT * FROM activity WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $activities = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'stats' => [
            'hours' => $hoursLearned,
            'courses' => $courseCount,
            'certificates' => $completedCount,
            'activities' => $activities
        ]
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
