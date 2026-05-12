<?php
require_once '../includes/init.php';
requireAdmin();

$activePage = 'admin';
$pageTitle = 'Course Reports';

// Handle Action (Dismiss or Resolve)
if (isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'])) die("CSRF Failed");
    $reportId = (int)$_POST['report_id'];
    $status = $_POST['action'] === 'resolve' ? 'resolved' : 'reviewed';
    
    $stmt = $pdo->prepare("UPDATE course_reports SET status = ? WHERE id = ?");
    $stmt->execute([$status, $reportId]);
    setFlash("Report updated.");
    redirect('reports.php');
}

// Fetch Reports
$reports = $pdo->query("
    SELECT r.*, u.username as student_name, c.title as course_title, c.id as course_id
    FROM course_reports r
    JOIN users u ON r.user_id = u.id
    JOIN courses c ON r.course_id = c.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
")->fetchAll();

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
    .admin-table th { background: rgba(255,255,255,0.02); text-align: left; padding: 1rem 1.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
    .admin-table td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
    
    .action-btn { background: none; border: 1px solid var(--border); color: #fff; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; transition: all 0.2s; }
    .action-btn:hover { border-color: var(--gold); color: var(--gold); }
    .resolve-btn:hover { background: var(--sage); border-color: var(--sage); color: #000; }
</style>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 800; margin-bottom: 3rem;">AB<span>CD</span> <span style="font-size: 0.6rem; background: var(--gold); color: #000; padding: 0.2rem 0.5rem; border-radius: 4px; vertical-align: middle; font-family: sans-serif;">ADMIN</span></div>
        <ul class="admin-nav">
            <li><a href="/admin/index.php">Dashboard</a></li>
            <li><a href="/admin/users.php">User Management</a></li>
            <li><a href="/admin/courses.php">Content Moderation</a></li>
            <li><a href="/admin/reports.php" class="active">Course Reports</a></li>
            <li><hr style="border:none; border-top:1px solid var(--border); margin:1rem 0;"></li>
            <li><a href="/index.php">Back to Site</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div style="margin-bottom: 3rem;">
            <h1 style="font-size: 2.8rem; letter-spacing: -0.03em;">Course Reports</h1>
            <p style="color: var(--text-muted);">Review and act on student-flagged concerns.</p>
        </div>

        <?php if (empty($reports)): ?>
            <div style="text-align: center; padding: 5rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 24px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem;">All Clear!</h3>
                <p style="color: var(--text-muted);">No pending course reports at the moment.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reported Course</th>
                        <th>Reporter</th>
                        <th>Reason / Concern</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $rep): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700;"><?= htmlspecialchars($rep['course_title']) ?></div>
                            <div style="font-size: 0.75rem;"><a href="/detail.php?id=<?= $rep['course_id'] ?>" target="_blank" style="color:var(--gold);">View Course →</a></div>
                        </td>
                        <td><?= htmlspecialchars($rep['student_name']) ?></td>
                        <td style="max-width: 300px; color: var(--text-muted); line-height: 1.4;"><?= nl2br(htmlspecialchars($rep['reason'])) ?></td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($rep['created_at'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                                    <button type="submit" name="action" value="resolve" class="action-btn resolve-btn">Dismiss</button>
                                </form>
                                <a href="/admin/courses.php?search=<?= urlencode($rep['course_title']) ?>" class="action-btn" style="color:var(--rust); border-color:rgba(239, 68, 68, 0.2);">Take Action</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
