<?php
require_once 'includes/init.php';
requireLogin();

if ($currentUser['role'] !== 'mentor') {
    redirect('dashboard.php');
}

$pageTitle = "Mentor Dashboard";
require_once 'includes/header.php';

// Fetch Mentor's Courses
$stmt = $pdo->prepare("SELECT * FROM courses WHERE mentor_id = ? ORDER BY created_at DESC");
$stmt->execute([$currentUser['id']]);
$courses = $stmt->fetchAll();

// Calculate Revenue and Total Students
$revenueStmt = $pdo->prepare("
    SELECT SUM(e.price_paid) as total_revenue, COUNT(e.id) as total_enrollments
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.mentor_id = ?
");
$revenueStmt->execute([$currentUser['id']]);
$stats = $revenueStmt->fetch();

// Fetch Recent Enrollments
$enrollStmt = $pdo->prepare("
    SELECT e.*, u.username, u.email, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.mentor_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");
$enrollStmt->execute([$currentUser['id']]);
$recentEnrollments = $enrollStmt->fetchAll();
?>

<div class="dashboard-layout">
  <!-- SIDEBAR -->
  <aside class="dashboard-sidebar">
    <div class="sidebar-greeting">Welcome back,</div>
    <div class="sidebar-name"><?php echo sanitize($currentUser['username']); ?></div>
    <ul class="sidebar-nav">
      <li>
        <a href="mentor_dashboard.php" class="active">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6"/><path d="M8 11h8"/></svg>
          My Courses
        </a>
      </li>
      <li>
        <a href="create_course.php">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Create Course
        </a>
      </li>
      <li>
        <a href="contact.php">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.1a2 2 0 0 1-1-1.72v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
          Settings
        </a>
      </li>
      <li>
        <a href="logout.php" style="color:var(--rust);">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </li>
    </ul>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="dashboard-main">
    <div class="dash-header" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:3rem;">
      <div>
        <h2 style="font-size: 2rem; letter-spacing: -0.03em; margin-bottom: 0.5rem;">Mentor Dashboard</h2>
        <p style="color: var(--text-muted);">Manage your published courses and student analytics.</p>
      </div>
      <a href="create_course.php" class="btn btn-gold" style="box-shadow: 0 4px 15px rgba(200,146,42,0.3);">+ Create New Course</a>
    </div>

    <!-- STATS GRID -->
    <div class="dash-stats-grid" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1.5rem; margin-bottom:3rem;">
        <div class="dash-stat-card" style="background:var(--bg-elevated); border:1px solid var(--border); padding:1.5rem; border-radius:12px;">
            <div class="stat-label" style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Total Revenue</div>
            <div class="stat-value" style="font-size:1.75rem; font-weight:700; color:var(--gold);"><?= formatPrice($stats['total_revenue'] ?? 0) ?></div>
        </div>
        <div class="dash-stat-card" style="background:var(--bg-elevated); border:1px solid var(--border); padding:1.5rem; border-radius:12px;">
            <div class="stat-label" style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Total Students</div>
            <div class="stat-value" style="font-size:1.75rem; font-weight:700; color:var(--text-main);"><?= number_format($stats['total_enrollments'] ?? 0) ?></div>
        </div>
        <div class="dash-stat-card" style="background:var(--bg-elevated); border:1px solid var(--border); padding:1.5rem; border-radius:12px;">
            <div class="stat-label" style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Active Courses</div>
            <div class="stat-value" style="font-size:1.75rem; font-weight:700; color:var(--text-main);"><?= count($courses) ?></div>
        </div>
    </div>

    <div class="dash-section-title" style="margin-bottom:1.5rem; font-weight:700; font-size:1.25rem;">Your Published Courses</div>
    
    <?php if (count($courses) > 0): ?>
    <div class="my-courses-grid" style="display:grid; grid-template-columns:1fr; gap:1.25rem; margin-bottom:4rem;">
      <?php foreach ($courses as $course): ?>
        <div class="my-course-card" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:12px; display:flex; padding:1rem; align-items:center; gap:1.5rem;">
          <div class="my-course-thumb" style="width:120px; height:80px; border-radius:8px; overflow:hidden; background:rgba(255,255,255,0.02); display:flex; align-items:center; justify-content:center;">
            <?php if (strpos($course['thumbnail'], '/') !== false || strpos($course['thumbnail'], '.') !== false): ?>
              <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <span style="font-size:1.5rem;"><?= htmlspecialchars($course['thumbnail']) ?></span>
            <?php endif; ?>
          </div>
          <div class="my-course-info" style="flex:1;">
            <h4 style="margin-bottom:0.4rem; font-size:1.1rem;"><?= htmlspecialchars($course['title']) ?></h4>
            <div style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($course['level']) ?> • <?= htmlspecialchars($course['category']) ?></div>
          </div>
          <div class="my-course-actions" style="display:flex; gap:0.5rem;">
            <a href="course_view.php?id=<?= $course['id'] ?>" class="btn btn-outline" style="font-size:0.75rem; padding:0.5rem 1rem;">View</a>
            <a href="create_course.php?id=<?= $course['id'] ?>" class="btn btn-outline" style="font-size:0.75rem; padding:0.5rem 1rem;">Edit</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div style="text-align:center; padding:4rem 2rem; background:var(--bg-surface); border-radius:12px; border:1px dashed var(--border); margin-bottom:4rem;">
        <p style="color:var(--text-muted); margin-bottom:1.5rem;">You haven't created any courses yet.</p>
        <a href="create_course.php" class="btn btn-gold">Create Your First Course</a>
      </div>
    <?php endif; ?>

    <!-- RECENT ENROLLMENTS -->
    <div class="dash-section-title" style="margin-bottom:1.5rem; font-weight:700; font-size:1.25rem;">Recent Student Enrollments</div>
    <div class="recent-enrollments-table" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:12px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:0.9rem; text-align:left;">
            <thead>
                <tr style="background:rgba(255,255,255,0.02); border-bottom:1px solid var(--border);">
                    <th style="padding:1rem 1.5rem; font-weight:600;">Student</th>
                    <th style="padding:1rem 1.5rem; font-weight:600;">Course</th>
                    <th style="padding:1rem 1.5rem; font-weight:600;">Date</th>
                    <th style="padding:1rem 1.5rem; font-weight:600;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentEnrollments)): ?>
                    <tr>
                        <td colspan="4" style="padding:2rem; text-align:center; color:var(--text-muted);">No enrollments yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentEnrollments as $enr): ?>
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:1rem 1.5rem;">
                                <div style="font-weight:600;"><?= htmlspecialchars($enr['username']) ?></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($enr['email']) ?></div>
                            </td>
                            <td style="padding:1rem 1.5rem;"><?= htmlspecialchars($enr['course_title']) ?></td>
                            <td style="padding:1rem 1.5rem; color:var(--text-muted);"><?= date('M d, Y', strtotime($enr['enrolled_at'])) ?></td>
                            <td style="padding:1rem 1.5rem; font-weight:600; color:var(--gold);"><?= formatPrice($enr['price_paid']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
  </main>
</div>

<?php require_once 'includes/footer.php'; ?>
