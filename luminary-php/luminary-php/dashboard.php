<?php
require_once 'session.php';
require_once 'db.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$conn = get_db_connection(); // <- now $conn is clearly defined

$stmt = mysqli_prepare($conn, 'SELECT created_at FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result    = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
$joined    = $user_data ? date('M Y', strtotime($user_data['created_at'])) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Luminary</title>
  <link rel="stylesheet" href="style.css">
  <style><?= get_theme_css($current_theme) ?></style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="dashboard-layout">
  <!-- SIDEBAR -->
  <aside class="dashboard-sidebar">
    <div class="sidebar-greeting">Welcome back,</div>
    <!-- SESSION: Display logged-in user's name from session -->
    <div class="sidebar-name"><?= get_user_name() ?></div>
    <ul class="sidebar-nav">
      <li><a href="dashboard.php" class="active">🏠 Overview</a></li>
      <li><a href="courses.php">📚 My Courses</a></li>
      <li><a href="#">🏆 Certificates</a></li>
      <li><a href="courses.php">🔍 Browse Catalog</a></li>
      <li><a href="pricing.php">⭐ Upgrade to Pro</a></li>
      <li><a href="contact.php">⚙️ Settings</a></li>
      <li><a href="logout.php" style="color:#c0442c;">🚪 Logout</a></li>
    </ul>
  </aside>

  <!-- MAIN -->
  <main class="dashboard-main">
    <div class="dash-header">
      <!-- SESSION: Personalised greeting using session data -->
      <h2>Good morning, <?= get_user_name() ?> ☀️</h2>
      <p>
        Email: <strong><?= get_user_email() ?></strong> ·
        Member since: <strong><?= $joined ?></strong> ·
        Theme: <strong><?= ucfirst($current_theme) ?></strong>
      </p>
    </div>

    <!-- STATS -->
    <div class="dash-stats">
      <div class="dash-stat"><div class="stat-label">Courses Enrolled</div><div class="stat-value">4</div><div class="stat-sub">+1 this month</div></div>
      <div class="dash-stat"><div class="stat-label">Hours Learned</div><div class="stat-value">38</div><div class="stat-sub">↑ 12h this week</div></div>
      <div class="dash-stat"><div class="stat-label">Certificates</div><div class="stat-value">2</div><div class="stat-sub">2 in progress</div></div>
      <div class="dash-stat"><div class="stat-label">Day Streak</div><div class="stat-value">🔥 7</div><div class="stat-sub">Personal best!</div></div>
    </div>

    <!-- THEME SWITCHER using cookies -->
    <div class="dash-section-title" style="margin-bottom:0.5rem;">Theme Preference</div>
    <p style="font-size:0.82rem;color:var(--muted);margin-bottom:1rem;">
      Your current theme is saved as a cookie and persists across sessions.
    </p>
    <form method="GET" action="dashboard.php" style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:2rem;">
      <?php
      $theme_options = [
        'classic'  => '☀️ Classic',
        'midnight' => '🌙 Midnight',
        'sepia'    => '📜 Sepia',
        'frost'    => '❄️ Frost',
      ];
      foreach ($theme_options as $key => $label):
      ?>
        <button type="submit" name="theme" value="<?= $key ?>"
          style="padding:0.5rem 1.25rem;border-radius:100px;border:1.5px solid <?= $key === $current_theme ? 'var(--ink)' : 'var(--border)' ?>;
                 background:<?= $key === $current_theme ? 'var(--ink)' : 'transparent' ?>;
                 color:<?= $key === $current_theme ? 'var(--paper)' : 'var(--muted)' ?>;
                 font-family:'DM Sans',sans-serif;font-size:0.82rem;cursor:pointer;transition:all 0.2s;">
          <?= $label ?>
        </button>
      <?php endforeach; ?>
    </form>

    <!-- COURSES IN PROGRESS -->
    <div class="dash-section-title">Continue Learning</div>
    <div class="my-courses-grid">
      <div class="my-course-card">
        <div class="my-course-thumb design">🎨</div>
        <div class="my-course-info">
          <h4>UI/UX Design Fundamentals</h4>
          <span>Module 3 · Figma Mastery</span>
          <div class="my-course-progress">
            <div class="prog-label"><span>Progress</span><span>67%</span></div>
            <div class="prog-track"><div class="prog-fill" style="width:67%"></div></div>
          </div>
        </div>
      </div>
      <div class="my-course-card">
        <div class="my-course-thumb code">💻</div>
        <div class="my-course-info">
          <h4>Full-Stack Web Development</h4>
          <span>Module 7 · React Hooks</span>
          <div class="my-course-progress">
            <div class="prog-label"><span>Progress</span><span>42%</span></div>
            <div class="prog-track"><div class="prog-fill" style="width:42%"></div></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ACTIVITY -->
    <div class="dash-section-title">Recent Activity</div>
    <div class="activity-list">
      <div class="activity-item"><div class="activity-dot gold"></div> Completed lesson: "Auto Layout in Figma" <span class="time">2h ago</span></div>
      <div class="activity-item"><div class="activity-dot green"></div> Earned certificate: Copywriting for Digital Media <span class="time">Yesterday</span></div>
      <div class="activity-item"><div class="activity-dot blue"></div> Started lesson: "React State Management" <span class="time">2 days ago</span></div>
    </div>
  </main>
</div>


<script src="js/progress-tracker.js"></script>
<?php if (is_logged_in()): ?>
  <script>
    window.LUMINARY_THEME_COOKIE = "<?= htmlspecialchars(get_theme_cookie_name(), ENT_QUOTES, 'UTF-8') ?>";
  </script>
  <script src="js/course-search.js"></script>
  <script src="js/theme-engine.js"></script>
<?php endif; ?>
</body>
</html>
