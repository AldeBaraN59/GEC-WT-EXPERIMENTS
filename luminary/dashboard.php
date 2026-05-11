<?php
require_once 'includes/init.php';
requireLogin();

$activePage = 'dashboard';
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

$user = getCurrentUser($pdo);

// Handle view toggle
$view = $_GET['view'] ?? 'overview';

// Fetch enrolled courses
$stmt = $pdo->prepare("
    SELECT c.*, e.progress, e.status 
    FROM courses c 
    JOIN enrollments e ON c.id = e.course_id 
    WHERE e.user_id = ?
");
$stmt->execute([$user['id']]);
$enrolledCourses = $stmt->fetchAll();

// Fetch recent activity
$stmt = $pdo->prepare("SELECT * FROM activity WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user['id']]);
$activities = $stmt->fetchAll();

// Fetch certificates
$stmt = $pdo->prepare("
    SELECT cert.*, c.title as course_title, c.thumbnail 
    FROM certificates cert 
    JOIN courses c ON cert.course_id = c.id 
    WHERE cert.user_id = ?
");
$stmt->execute([$user['id']]);
$certificates = $stmt->fetchAll();

// Stats
$courseCount = count($enrolledCourses);
$completedCount = count($certificates);
$hoursLearned = calculateHoursLearned($pdo, $user['id']);
?>

  <div class="dashboard-layout">
    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar">
      <div class="sidebar-greeting">Welcome back,</div>
      <div class="sidebar-name"><?php echo sanitize($user['username']); ?></div>
      <ul class="sidebar-nav">
        <li>
          <a href="dashboard.php?view=overview" class="<?= $view === 'overview' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Overview
          </a>
        </li>
        <li>
          <a href="dashboard.php?view=certificates" class="<?= $view === 'certificates' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
            Certificates
          </a>
        </li>
        <li>
          <a href="courses.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Browse Catalog
          </a>
        </li>
        <li>
          <a href="settings.php">
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
      <div class="dash-header">
        <h2 style="display:flex; align-items:center; gap:0.75rem;">
          Good morning, <?php echo sanitize($user['username']); ?> 
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
        </h2>
        <p>You have <?php echo $courseCount; ?> courses in progress. Keep going!</p>
      </div>

      <!-- STATS -->
      <div class="dash-stats">
        <div class="dash-stat">
          <div class="stat-label">Courses Enrolled</div>
          <div class="stat-value"><?php echo $courseCount; ?></div>
          <div class="stat-sub">+<?php echo $courseCount; ?> this month</div>
        </div>
        <div class="dash-stat">
          <div class="stat-label">Hours Learned</div>
          <div class="stat-value"><?= $hoursLearned ?></div>
          <div class="stat-sub">↑ 0.5h this week</div>
        </div>
        <div class="dash-stat">
          <div class="stat-label">Certificates</div>
          <div class="stat-value"><?php echo $completedCount; ?></div>
          <div class="stat-sub"><?php echo ($courseCount - $completedCount); ?> in progress</div>
        </div>
        <div class="dash-stat">
          <div class="stat-label">Day Streak</div>
          <div class="stat-value" style="display:flex; align-items:center; gap:0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--rust)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.256 1.181-3.122L8.5 14.5z"/></svg>
            1
          </div>
          <div class="stat-sub">Starting strong!</div>
        </div>
      </div>

      <?php if ($view === 'certificates'): ?>
        <!-- CERTIFICATES VIEW -->
        <div class="dash-section-title">Your Credentials</div>
        <div class="my-courses-grid">
          <?php if (empty($certificates)): ?>
            <div style="text-align:center; padding:3rem; background:var(--bg-surface); border-radius:12px; grid-column:1/-1;">
              <p style="color:var(--text-muted); margin-bottom:1rem;">You haven't earned any certificates yet.</p>
              <a href="dashboard.php" class="btn btn-outline">Back to Learning</a>
            </div>
          <?php else: ?>
            <?php foreach ($certificates as $cert): ?>
              <div class="my-course-card">
                <div class="my-course-thumb">
                  <?php if (strpos($cert['thumbnail'], '/') !== false || strpos($cert['thumbnail'], '.') !== false): ?>
                    <img src="<?= htmlspecialchars($cert['thumbnail']) ?>" alt="Thumbnail">
                  <?php else: ?>
                    <span class="emoji-thumb"><?= htmlspecialchars($cert['thumbnail']) ?></span>
                  <?php endif; ?>
                </div>
                <div class="my-course-info">
                  <h4 style="margin-bottom:0.25rem;"><?= htmlspecialchars($cert['course_title']) ?></h4>
                  <span class="course-meta-inline" style="display:block; margin-bottom:1rem;">Issued on <?= date('M d, Y', strtotime($cert['issued_at'])) ?></span>
                  <div style="display:flex; gap:0.5rem;">
                    <a href="#" class="btn btn-outline" style="padding:0.4rem 1rem; font-size:0.75rem;">Download PDF</a>
                    <a href="#" class="btn btn-outline" style="padding:0.4rem 1rem; font-size:0.75rem;">Verify ID</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <!-- OVERVIEW VIEW -->
        <div class="dash-section-title">Continue Learning</div>
        <div class="my-courses-grid">
          <?php if (empty($enrolledCourses)): ?>
            <p>You haven't enrolled in any courses yet. <a href="courses.php" style="color:var(--gold);">Browse catalog</a></p>
          <?php else: ?>
            <?php foreach ($enrolledCourses as $course): 
                $lastLessonId = getLastViewedLesson($pdo, $_SESSION['user_id'], $course['id']);
                $resumeUrl = "course_view.php?id=" . $course['id'];
                if ($lastLessonId) $resumeUrl .= "&mat=" . $lastLessonId;
            ?>
              <a href="<?= $resumeUrl ?>" class="my-course-card-link">
                <div class="my-course-card">
                  <div class="my-course-thumb">
                    <?php if (strpos($course['thumbnail'], '/') !== false || strpos($course['thumbnail'], '.') !== false): ?>
                      <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail">
                    <?php else: ?>
                      <span class="emoji-thumb"><?= htmlspecialchars($course['thumbnail']) ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="my-course-info">
                    <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                    <span class="course-meta-inline"><?php echo htmlspecialchars($course['category']); ?> · <?php echo ($course['status'] == 'completed') ? 'Completed ✓' : 'In Progress'; ?></span>
                    <div class="my-course-progress">
                      <div class="prog-label"><span>Progress</span><span><?php echo $course['progress']; ?>%</span></div>
                      <div class="prog-track"><div class="prog-fill" style="width:<?php echo $course['progress']; ?>%"></div></div>
                    </div>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- ACTIVITY -->
      <div class="dash-section-title">Recent Activity</div>
      <div class="activity-list" id="activityList">
        <?php if (empty($activities)): ?>
          <div class="activity-item">No recent activity.</div>
        <?php else: ?>
          <?php foreach ($activities as $act): ?>
            <div class="activity-item">
              <div class="activity-dot gold"></div> 
              <?php echo htmlspecialchars($act['description']); ?> 
              <span class="time"><?php echo date('M d, H:i', strtotime($act['created_at'])); ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
  function refreshStats() {
    fetch('get_stats.php', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update Stats
            const statValues = document.querySelectorAll('.stat-value');
            if (statValues.length >= 3) {
                statValues[0].innerText = data.stats.courses;
                statValues[1].innerText = data.stats.hours;
                statValues[2].innerText = data.stats.certificates;
            }

            // Update Activity
            const list = document.getElementById('activityList');
            if (data.stats.activities.length > 0) {
                let html = '';
                data.stats.activities.forEach(act => {
                    const date = new Date(act.created_at);
                    const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' + 
                                  date.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
                    
                    html += `
                        <div class="activity-item">
                            <div class="activity-dot gold"></div> 
                            ${act.description}
                            <span class="time">${dateStr}</span>
                        </div>
                    `;
                });
                list.innerHTML = html;
            }
        }
    })
    .catch(err => console.error('Stats refresh failed:', err));
  }

  // Poll every 30 seconds
  setInterval(refreshStats, 30000);
  </script>

<?php require_once 'includes/footer.php'; ?>
