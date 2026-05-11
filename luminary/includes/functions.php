<?php

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specific URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login for a page
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Get current user details from session/database
 */
function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Flash Messages
 */
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * CSRF Protection
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Price Formatter
 */
function formatPrice($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Get category icon/emoji
 */
function getCategoryIcon($category) {
    $icons = [
        'Design' => '🎨',
        'Development' => '💻',
        'Business' => '📈',
        'Photography' => '📸',
        'Data Science' => '📊',
        'Writing' => '✍️'
    ];
    return $icons[$category] ?? '🎓';
}
/**
 * Calculate dynamic course progress for a user
 */
function calculateCourseProgress($pdo, $userId, $courseId) {
    // Total materials in course
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM course_materials m
        JOIN course_sections s ON m.section_id = s.id
        WHERE s.course_id = ?
    ");
    $stmt->execute([$courseId]);
    $total = $stmt->fetchColumn();
    
    if ($total == 0) return 0;
    
    // Completed materials
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_progress WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    $completed = $stmt->fetchColumn();
    
    $percent = round(($completed / $total) * 100);
    
    // Update enrollment table
    $stmt = $pdo->prepare("UPDATE enrollments SET progress = ?, status = ? WHERE user_id = ? AND course_id = ?");
    $status = ($percent >= 100) ? 'completed' : 'active';
    $stmt->execute([$percent, $status, $userId, $courseId]);
    
    // Auto-generate certificate if completed
    if ($percent >= 100) {
        generateCertificate($pdo, $userId, $courseId);
    }
    
    return $percent;
}

/**
 * Calculate total hours learned based on completed materials
 */
function calculateHoursLearned($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT SUM(m.duration) 
        FROM user_progress p
        JOIN course_materials m ON p.material_id = m.id
        WHERE p.user_id = ?
    ");
    $stmt->execute([$userId]);
    $seconds = $stmt->fetchColumn();
    
    return round(($seconds ?? 0) / 3600, 1);
}

/**
 * Get Last Viewed Lesson for a user
 */
function getLastViewedLesson($pdo, $userId, $courseId) {
    $stmt = $pdo->prepare("SELECT material_id FROM user_progress WHERE user_id = ? AND course_id = ? ORDER BY completed_at DESC LIMIT 1");
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetchColumn();
}

/**
 * Generate a certificate hash
 */
function generateCertificate($pdo, $userId, $courseId) {
    $hash = hash('sha256', "cert_{$userId}_{$courseId}_" . time());
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO certificates (user_id, course_id, cert_hash) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $courseId, $hash]);
    
    return $hash;
}

/**
 * Format duration (seconds to Hh Mm)
 */
function formatDuration($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    if ($h > 0) return "{$h}h {$m}m";
    return "{$m}m";
}
?>
