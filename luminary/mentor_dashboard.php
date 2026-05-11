<?php
require_once 'includes/init.php';
requireLogin();

if ($currentUser['role'] !== 'mentor') {
    redirect('dashboard.php');
}

$pageTitle = "Mentor Dashboard";
require_once 'includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM courses WHERE mentor_id = ? ORDER BY created_at DESC");
$stmt->execute([$currentUser['id']]);
$courses = $stmt->fetchAll();
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

    <div class="dash-section-title">Your Published Courses</div>
    
    <?php if (count($courses) > 0): ?>
    <div class="my-courses-grid">
      <?php foreach ($courses as $course): ?>
        <div class="my-course-card">
          <div class="my-course-thumb">
            <?php if (strpos($course['thumbnail'], '/') !== false || strpos($course['thumbnail'], '.') !== false): ?>
              <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail">
            <?php else: ?>
              <span class="emoji-thumb"><?= htmlspecialchars($course['thumbnail']) ?></span>
            <?php endif; ?>
          </div>
          <div class="my-course-info">
            <h4><?= htmlspecialchars($course['title']) ?></h4>
            <span class="course-meta-inline"><?= htmlspecialchars($course['level']) ?> • <?= htmlspecialchars($course['students_count']) ?> Students Enrolled</span>
            <div style="margin-top:1.25rem; display:flex; gap:0.75rem;">
              <a href="course_view.php?id=<?= $course['id'] ?>" class="btn btn-outline" style="padding:0.5rem 1.25rem; font-size:0.8rem;">Preview Content</a>
              <a href="#" class="btn btn-outline" style="padding:0.5rem 1.25rem; font-size:0.8rem; opacity:0.5; cursor:not-allowed;">Edit Details</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div style="text-align:center; padding:4rem 2rem; background:var(--bg-surface); border-radius:12px; border:1px dashed var(--border);">
        <p style="color:var(--text-muted); margin-bottom:1.5rem;">You haven't created any courses yet. Share your knowledge with the world!</p>
        <a href="create_course.php" class="btn btn-gold">Create Your First Course</a>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php require_once 'includes/footer.php'; ?>
