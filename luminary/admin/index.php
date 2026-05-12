<?php
require_once '../includes/init.php';
requireAdmin();

$activePage = 'admin';
$pageTitle = 'Admin Dashboard';

// Fetch Global Stats
$totalRevenue = $pdo->query("SELECT SUM(price_paid) FROM enrollments")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$pendingReports = $pdo->query("SELECT COUNT(*) FROM course_reports WHERE status = 'pending'")->fetchColumn();

// Recent Activity
$recentEnrollments = $pdo->query("
    SELECT e.*, u.username, c.title as course_title 
    FROM enrollments e 
    JOIN users u ON e.user_id = u.id 
    JOIN courses c ON e.course_id = c.id 
    ORDER BY e.enrolled_at DESC LIMIT 5
")->fetchAll();

// Category Breakdown
$categories = $pdo->query("SELECT category, COUNT(*) as count FROM courses GROUP BY category")->fetchAll();

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
    
    .stat-card { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; transition: transform 0.3s ease; }
    .stat-card:hover { transform: translateY(-5px); border-color: var(--gold); }
    .stat-value { font-size: 2.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem; font-family: 'Playfair Display', serif; }
    .stat-label { color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.7rem; font-weight: 700; }
    
    .admin-table { width: 100%; border-collapse: collapse; background: var(--bg-surface); border-radius: 12px; overflow: hidden; border: 1px solid var(--border); }
    .admin-table th { background: rgba(255,255,255,0.02); text-align: left; padding: 1.25rem 1.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
    .admin-table td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
</style>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 800; margin-bottom: 3rem;">AB<span>CD</span> <span style="font-size: 0.6rem; background: var(--gold); color: #000; padding: 0.2rem 0.5rem; border-radius: 4px; vertical-align: middle; font-family: sans-serif;">ADMIN</span></div>
        <ul class="admin-nav">
            <li><a href="/admin/index.php" class="active">Dashboard</a></li>
            <li><a href="/admin/users.php">User Management</a></li>
            <li><a href="/admin/courses.php">Content Moderation</a></li>
            <li><a href="/admin/reports.php">Course Reports <?= $pendingReports > 0 ? "<span style='background:var(--rust); color:#fff; padding:0.1rem 0.4rem; border-radius:50px; font-size:0.6rem; margin-left:0.5rem;'>$pendingReports</span>" : "" ?></a></li>
            <li><hr style="border:none; border-top:1px solid var(--border); margin:1rem 0;"></li>
            <li><a href="/index.php">Back to Site</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div style="margin-bottom: 3rem;">
            <h1 style="font-size: 2.8rem; letter-spacing: -0.03em;">Platform Overview</h1>
            <p style="color: var(--text-muted);">Real-time metrics and system health monitoring.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.25rem; margin-bottom: 3rem;">
            <div class="stat-card">
                <div class="stat-value"><?= formatPrice($totalRevenue) ?></div>
                <div class="stat-label">Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($totalUsers) ?></div>
                <div class="stat-label">Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($totalCourses) ?></div>
                <div class="stat-label">Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($totalEnrollments) ?></div>
                <div class="stat-label">Enrollments</div>
            </div>
            <div class="stat-card" style="<?= $pendingReports > 0 ? 'border-color:var(--rust);' : '' ?>">
                <div class="stat-value" style="<?= $pendingReports > 0 ? 'color:var(--rust);' : '' ?>"><?= number_format($pendingReports) ?></div>
                <div class="stat-label">Pending Reports</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem;">
            <div>
                <h3 style="margin-bottom: 1.5rem; font-size: 1.4rem;">Recent Platform Enrollments</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentEnrollments as $enr): ?>
                        <tr>
                            <td><?= htmlspecialchars($enr['username']) ?></td>
                            <td><?= htmlspecialchars($enr['course_title']) ?></td>
                            <td style="color: var(--text-muted); font-size: 0.8rem;"><?= date('M d, Y', strtotime($enr['enrolled_at'])) ?></td>
                            <td style="font-weight: 700; color: var(--gold);"><?= formatPrice($enr['price_paid']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div>
                <h3 style="margin-bottom: 1.5rem; font-size: 1.4rem;">Course Breakdown</h3>
                <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; padding: 2rem;">
                    <?php foreach ($categories as $cat): ?>
                        <div style="margin-bottom: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.6rem; font-size: 0.85rem;">
                                <span style="color:var(--text-muted);"><?= htmlspecialchars($cat['category']) ?></span>
                                <span style="font-weight: 700; color:var(--gold);"><?= $cat['count'] ?></span>
                            </div>
                            <div style="height: 4px; background: rgba(255,255,255,0.05); border-radius: 2px;">
                                <div style="height: 100%; background: var(--gold); border-radius: 2px; width: <?= ($cat['count'] / ($totalCourses ?: 1)) * 100 ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
