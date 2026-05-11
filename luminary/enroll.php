<?php
require_once 'includes/init.php';
requireLogin();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if (!$course_id) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid course ID']);
            exit();
        }
        redirect('courses.php');
    }

    try {
        // Check if already enrolled
        $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$user_id, $course_id]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $course_id]);
            
            // Add activity
            $stmt = $pdo->prepare("INSERT INTO activity (user_id, type, description) VALUES (?, 'Enrollment', 'Enrolled in a new course')");
            $stmt->execute([$user_id]);
        }
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => 'course_view.php?id=' . $course_id]);
            exit();
        }
        
        redirect('dashboard.php');
    } catch (PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit();
        }
        die("Error: " . $e->getMessage());
    }
}
?>
