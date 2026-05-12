<?php
require_once 'includes/db.php';

echo "<h1>abcd Backend Verification</h1>";

try {
    // Check connection
    echo "<p style='color:green;'>✅ Database connection successful.</p>";

    // Check Tables
    $tables = ['users', 'courses', 'enrollments', 'contacts', 'activity'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            echo "<p style='color:green;'>✅ Table '$table' exists.</p>";
        } else {
            echo "<p style='color:red;'>❌ Table '$table' is missing.</p>";
        }
    }

    // Check Data
    $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
    $count = $stmt->fetchColumn();
    echo "<p>Total Courses: $count</p>";

    if ($count == 0) {
        echo "<p style='color:orange;'>⚠️ No courses found. Please run <a href='seed_data.php'>seed_data.php</a> to populate the database.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
