<?php
require_once 'includes/init.php';
requireLogin();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check (Only if not AJAX, or you can add it to headers for AJAX)
    // For now, simple POST CSRF check
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        if (!$isAjax) die("CSRF token validation failed.");
    }

    $course_id = (int)($_POST['course_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'] ?? 'student';

    if ($user_role === 'mentor') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Instructors cannot enroll in courses.']);
            exit();
        }
        setFlash("Instructors cannot enroll in courses.", "error");
        redirect("detail.php?id=$course_id");
    }

    if (!$course_id) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid course ID']);
            exit();
        }
        redirect('courses.php');
    }

    try {
        // Get course details for pricing
        $courseStmt = $pdo->prepare("SELECT title, price FROM courses WHERE id = ?");
        $courseStmt->execute([$course_id]);
        $course = $courseStmt->fetch();
        
        if (!$course) throw new Exception("Course not found.");

        // Check if already enrolled
        $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$user_id, $course_id]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, price_paid) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $course_id, $course['price']]);
            
            // Add activity
            $stmt = $pdo->prepare("INSERT INTO activity (user_id, type, description) VALUES (?, 'Enrollment', 'Enrolled in " . $course['title'] . "')");
            $stmt->execute([$user_id]);

            setFlash("You have successfully enrolled in " . $course['title'] . "!");
        }
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => 'course_view.php?id=' . $course_id]);
            exit();
        }
        
        redirect('course_view.php?id=' . $course_id);
    } catch (Exception $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit();
        }
        die("Error: " . $e->getMessage());
    }
}
?>
