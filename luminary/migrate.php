<?php
require_once 'includes/db.php';

try {
    // Helper to check if column exists
    function columnExists($pdo, $table, $column) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $stmt->fetch() !== false;
    }

    // Add remember_token to users
    if (!columnExists($pdo, 'users', 'remember_token')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL");
        echo "Added remember_token to users table.\n";
    }
    
    // Add price_paid to enrollments
    if (!columnExists($pdo, 'enrollments', 'price_paid')) {
        $pdo->exec("ALTER TABLE enrollments ADD COLUMN price_paid DECIMAL(10,2) DEFAULT 0.00");
        echo "Added price_paid to enrollments table.\n";
    }
    
    // Update existing enrollments with current course prices
    $pdo->exec("UPDATE enrollments e JOIN courses c ON e.course_id = c.id SET e.price_paid = c.price WHERE e.price_paid = 0");
    
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
