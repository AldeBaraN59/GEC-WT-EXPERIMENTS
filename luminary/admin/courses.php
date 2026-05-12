<?php
require_once '../includes/init.php';
requireAdmin();

$activePage = 'admin';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$pendingReports = $pdo->query("SELECT COUNT(*) FROM course_reports WHERE status = 'pending'")->fetchColumn();

// Handle Delete
if (isset($_POST['action']) && $_POST['action'] === 'delete_course') {
    if (!verifyCsrfToken($_POST['csrf_token'])) die("CSRF Failed");
    $targetId = (int)$_POST['course_id'];
    
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$targetId]);
    setFlash("Course deleted successfully.");
    redirect('courses.php');
}

// Fetch Courses with stats
$query = "
    SELECT c.*, u.username as mentor_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as students_count,
    (SELECT SUM(price_paid) FROM enrollments WHERE course_id = c.id) as total_revenue
    FROM courses c
    JOIN users u ON c.mentor_id = u.id
    WHERE 1=1";
$params = [];
if ($search) {
    $query .= " AND (c.title LIKE ? OR c.category LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$query .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<style>
    .admin-layout { display: grid; grid-template-columns: 280px 1fr; min-height: calc(100vh - 64px); }
    .admin-sidebar { background: var(--bg-surface); border-right: 1px solid var(--border); padding: 2rem; position: sticky; top: 64px; height: calc(100vh - 64px); }
    .admin-main { padding: 3rem; background: var(--bg-base); }
    .admin-nav { list-style: none; padding: 0; margin-top: 2rem; }
    .admin-nav li { margin-bottom: 0.5rem; }
    .admin-nav a { display: block; padding: 0.75rem 1rem; border-radius: 8px; color: var(--text-muted); text-decoration: none; transition: all 0.2s; font-weight: 500; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .admin-nav a:hover, .admin-nav a.active { background: rgba(212, 175, 55, 0.05); color: var(--gold); }
    
    .admin-table { width: 100%; border-collapse: collapse; background: var(--bg-surface); border-radius: 12px; overflow: hidden; border: 1px solid var(--border); }
    .admin-table th { background: rgba(255,255,255,0.02); text-align: left; padding: 1.25rem 1.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
    .admin-table td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
    
    .action-btn { background: none; border: 1px solid var(--border); color: #fff; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; }
    .action-btn:hover { border-color: var(--gold); color: var(--gold); }
    .delete-btn { color: var(--rust); border-color: rgba(239, 68, 68, 0.2); }
    .delete-btn:hover { background: var(--rust); border-color: var(--rust); color: #fff; }
</style>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 800; margin-bottom: 3rem;">AB<span>CD</span> <span style="font-size: 0.6rem; background: var(--gold); color: #000; padding: 0.2rem 0.5rem; border-radius: 4px; vertical-align: middle; font-family: sans-serif;">ADMIN</span></div>
        <ul class="admin-nav">
            <li><a href="/admin/index.php">Dashboard</a></li>
            <li><a href="/admin/users.php">User Management</a></li>
            <li><a href="/admin/courses.php" class="active">Content Moderation</a></li>
            <li><a href="/admin/reports.php">Course Reports <?= $pendingReports > 0 ? "<span style='background:var(--rust); color:#fff; padding:0.1rem 0.4rem; border-radius:50px; font-size:0.6rem; margin-left:0.5rem;'>$pendingReports</span>" : "" ?></a></li>
            <li><hr style="border:none; border-top:1px solid var(--border); margin:1rem 0;"></li>
            <li><a href="/index.php">Back to Site</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
            <div>
                <h1 style="font-size: 2.8rem; letter-spacing: -0.03em;">Content Moderation</h1>
                <p style="color: var(--text-muted);">Monitor and manage all platform courses.</p>
            </div>
            <form action="courses.php" method="GET" style="position: relative;">
                <input type="text" name="search" placeholder="Search courses..." value="<?= htmlspecialchars($search) ?>" 
                       style="padding: 0.8rem 1.25rem; padding-left: 2.8rem; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-surface); color: #fff; width: 320px; font-family: inherit; font-size: 0.9rem;">
                <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); opacity: 0.4;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </form>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Course Detail</th>
                    <th>Mentor</th>
                    <th>Category</th>
                    <th>Students</th>
                    <th>Revenue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td>
                        <div style="font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($course['title']) ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;"><?= $course['level'] ?></div>
                    </td>
                    <td><?= htmlspecialchars($course['mentor_name']) ?></td>
                    <td><span style="color:var(--text-muted);"><?= htmlspecialchars($course['category']) ?></span></td>
                    <td style="font-weight: 600;"><?= number_format($course['students_count']) ?></td>
                    <td style="font-weight: 700; color: var(--gold);"><?= formatPrice($course['total_revenue'] ?? 0) ?></td>
                    <td>
                        <div style="display: flex; gap: 0.75rem;">
                            <a href="/detail.php?id=<?= $course['id'] ?>" class="action-btn" target="_blank">View</a>
                            <form method="POST" onsubmit="return confirm('Permanently delete this course? This will remove all enrollments and progress.')">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <input type="hidden" name="action" value="delete_course">
                                <button type="submit" class="action-btn delete-btn">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
