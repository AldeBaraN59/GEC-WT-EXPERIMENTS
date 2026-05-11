<?php
require_once 'includes/db.php';

try {
    // 1. Drop existing tables safely to reset DB
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS activity, contacts, enrollments, course_materials, course_sections, courses, users;");
    
    // 2. Load and run the new schema
    $schema = file_get_contents('db/schema.sql');
    $pdo->exec($schema);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Schema updated successfully.<br>";

    // 3. Seed Mentors
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, bio) VALUES (?, ?, ?, 'mentor', ?)");
    $stmt->execute(['Sarah Chen', 'sarah@example.com', $password, 'Senior Designer at Airbnb']);
    $mentor1 = $pdo->lastInsertId();
    
    $stmt->execute(['John Doe', 'john@example.com', $password, 'Software Engineer at Google']);
    $mentor2 = $pdo->lastInsertId();
    
    $stmt->execute(['Michael Smith', 'michael@example.com', $password, 'Product Lead at Meta']);
    $mentor3 = $pdo->lastInsertId();

    $stmt->execute(['Dr. Emily Watson', 'emily@example.com', $password, 'Data Scientist at Netflix']);
    $mentor4 = $pdo->lastInsertId();
    
    // 4. Seed Student
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')");
    $stmt->execute(['shivank', 'shiv@example.com', $password]);
    echo "Mentors and Students seeded.<br>";

    // 5. Seed Courses
    $courses = [
        [
            'mentor_id' => $mentor1,
            'title' => 'UI/UX Design Fundamentals',
            'description' => 'Master user-centered design from research to high-fidelity prototypes.',
            'category' => 'Design',
            'level' => 'Intermediate',
            'price' => 89.00,
            'thumbnail' => '🎨',
            'duration' => '24h',
            'rating' => 4.9,
            'students_count' => 12400
        ],
        [
            'mentor_id' => $mentor2,
            'title' => 'Full-Stack Web Development',
            'description' => 'HTML, CSS, JavaScript, React, Node.js — the complete package.',
            'category' => 'Development',
            'level' => 'Beginner',
            'price' => 129.00,
            'thumbnail' => '💻',
            'duration' => '48h',
            'rating' => 4.8,
            'students_count' => 18200
        ],
        [
            'mentor_id' => $mentor3,
            'title' => 'Product Strategy & Growth',
            'description' => 'Build products users love using frameworks from top PMs.',
            'category' => 'Business',
            'level' => 'Advanced',
            'price' => 79.00,
            'thumbnail' => '📈',
            'duration' => '18h',
            'rating' => 4.7,
            'students_count' => 7800
        ],
        [
            'mentor_id' => $mentor4,
            'title' => 'Data Science Fundamentals',
            'description' => 'Python, pandas, and machine learning basics.',
            'category' => 'Data Science',
            'level' => 'Beginner',
            'price' => 99.00,
            'thumbnail' => '📊',
            'duration' => '36h',
            'rating' => 4.9,
            'students_count' => 5400
        ]
    ];

    $stmtCourse = $pdo->prepare("INSERT INTO courses (mentor_id, title, description, category, level, price, thumbnail, duration, rating, students_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtSection = $pdo->prepare("INSERT INTO course_sections (course_id, title, order_index) VALUES (?, ?, ?)");
    $stmtMaterial = $pdo->prepare("INSERT INTO course_materials (section_id, type, title, file_path) VALUES (?, ?, ?, ?)");

    foreach ($courses as $c) {
        $stmtCourse->execute([$c['mentor_id'], $c['title'], $c['description'], $c['category'], $c['level'], $c['price'], $c['thumbnail'], $c['duration'], $c['rating'], $c['students_count']]);
        $courseId = $pdo->lastInsertId();
        
        // Add some dummy sections and materials to each course
        $stmtSection->execute([$courseId, 'Introduction & Setup', 0]);
        $sec1Id = $pdo->lastInsertId();
        $stmtMaterial->execute([$sec1Id, 'video', 'Welcome to the Course', 'uploads/dummy_video.mp4']);
        $stmtMaterial->execute([$sec1Id, 'pdf', 'Course Syllabus', 'uploads/syllabus.pdf']);
        
        $stmtSection->execute([$courseId, 'Core Concepts', 1]);
        $sec2Id = $pdo->lastInsertId();
        $stmtMaterial->execute([$sec2Id, 'video', 'Main Lecture', 'uploads/lecture.mp4']);
    }

    echo "Courses, Sections, and Materials seeded successfully!<br>";
    echo "<strong>Please login with email 'shiv@example.com' or 'sarah@example.com' (password: password123)</strong>";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
