<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("
    SELECT c.*, u.username as instructor_name, u.bio as instructor_role 
    FROM courses c 
    LEFT JOIN users u ON c.mentor_id = u.id 
    WHERE c.id = ?
");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('courses.php');
}

$pageTitle = $course['title'];
// Fetch curriculum
$stmt = $pdo->prepare("SELECT * FROM course_sections WHERE course_id = ? ORDER BY order_index ASC");
$stmt->execute([$id]);
$sections = $stmt->fetchAll();

$isEnrolled = false;
if (isLoggedIn()) {
    $enrollStmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $enrollStmt->execute([$_SESSION['user_id'], $id]);
    if ($enrollStmt->fetch()) $isEnrolled = true;
}
?>

  <!-- COURSE HERO -->
  <div class="course-detail-hero">
    <div class="course-detail-inner">
      <div class="course-detail-text">
        <div class="breadcrumb"><a href="courses.php">Courses</a> / <?php echo $course['category']; ?></div>
        <span class="tag" style="background:rgba(200,146,42,0.2); color:var(--gold); border:1px solid rgba(200,146,42,0.3);"><?php echo $course['category']; ?> · <?php echo $course['level']; ?></span>
        <h1 style="font-size:3.5rem; margin: 1.5rem 0;"><?php echo $course['title']; ?></h1>
        <p style="font-size:1.1rem; line-height:1.6; max-width:600px;"><?php echo $course['description']; ?></p>
        <div class="course-badges">
          <div class="badge">⭐ <?php echo $course['rating']; ?></div>
          <div class="badge">👥 <?php echo number_format($course['students_count']); ?> students</div>
          <div class="badge">⏱ <?php echo $course['duration']; ?></div>
          <div class="badge">📅 Updated May 2026</div>
        </div>
        <div class="instructor-row">
          <div class="avatar" style="background:var(--gold); color:#000; font-weight:700;"><?php echo substr($course['instructor_name'], 0, 1); ?></div>
          <div class="instructor-info">
            <small>Instructor</small>
            <strong><?php echo $course['instructor_name']; ?> · <?php echo $course['instructor_role']; ?></strong>
          </div>
        </div>
      </div>

      <div class="enroll-card" style="background:var(--bg-elevated); border:1px solid var(--border); box-shadow:0 30px 60px rgba(0,0,0,0.5);">
        <div class="enroll-price"><?php echo $course['price'] > 0 ? formatPrice($course['price']) : 'Free'; ?></div>
        <div class="enroll-price-sub">One-time payment · Lifetime access</div>
        
        <?php if ($isEnrolled): ?>
          <a class="btn btn-gold" href="course_view.php?id=<?php echo $course['id']; ?>">Continue Learning →</a>
        <?php elseif (isLoggedIn()): ?>
          <form id="enrollForm" action="enroll.php" method="POST">
            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
            <button type="submit" id="enrollBtn" class="btn btn-gold">Enroll Now</button>
          </form>

          <script>
          document.getElementById('enrollForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('enrollBtn');
            const originalText = btn.innerText;
            btn.innerText = 'Processing...';
            btn.disabled = true;

            const formData = new FormData(this);
            fetch('enroll.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.innerText = 'Redirecting...';
                    window.location.href = data.redirect;
                } else {
                    alert('Enrollment failed: ' + data.error);
                    btn.innerText = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error('Enrollment error:', err);
                btn.innerText = originalText;
                btn.disabled = false;
            });
          });
          </script>
        <?php else: ?>
          <a class="btn btn-gold" href="signup.php">Enroll Now</a>
        <?php endif; ?>
        
        <button class="btn btn-outline" style="margin-top:0.75rem;">Try Free Preview</button>
        <ul class="enroll-perks" style="margin-top:2rem;">
          <li>Full lifetime access</li>
          <li>Access on mobile and TV</li>
          <li>Certificate of completion</li>
          <li>Industry-recognized curriculum</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- CURRICULUM -->
  <div class="course-curriculum" style="padding: 5rem 0;">
    <div class="course-curriculum-inner" style="display: grid; grid-template-columns: 2fr 1fr; gap: 4rem;">
      <div class="curriculum-section">
        <h2 style="margin-bottom:2rem; font-size:2rem;">Course Curriculum</h2>

        <?php foreach ($sections as $secIdx => $sec): ?>
          <div class="module" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:12px; margin-bottom:1rem; overflow:hidden;">
            <div class="module-header" style="padding:1.25rem 1.5rem; background:rgba(255,255,255,0.02); font-weight:600; display:flex; justify-content:space-between; align-items:center;">
                <span>Module <?php echo $secIdx + 1; ?>: <?php echo htmlspecialchars($sec['title']); ?></span>
                <span class="module-meta" style="font-size:0.8rem; color:var(--text-muted); font-weight:400;">Dynamic Content</span>
            </div>
            <div class="module-lessons">
              <?php
                $matStmt = $pdo->prepare("SELECT * FROM course_materials WHERE section_id = ? ORDER BY id ASC");
                $matStmt->execute([$sec['id']]);
                $materials = $matStmt->fetchAll();
                foreach ($materials as $mat):
              ?>
                <div class="lesson" style="padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; align-items:center; gap:1rem;">
                  <div class="lesson-icon" style="color:var(--gold); opacity:0.6;">
                    <?php if ($mat['type'] === 'video') echo '▶'; else if ($mat['type'] === 'quiz') echo '📝'; else echo '📄'; ?>
                  </div>
                  <span style="font-size:0.95rem;"><?php echo htmlspecialchars($mat['title']); ?></span>
                  <?php if ($mat['duration'] > 0): ?>
                    <span style="margin-left:auto; font-size:0.8rem; color:var(--text-muted);"><?php echo formatDuration($mat['duration']); ?></span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <aside>
        <div class="skills-section" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:12px; padding:2rem;">
          <h3 style="margin-bottom:1.5rem;">Skills You'll Gain</h3>
          <div class="skills-list" style="display:flex; flex-wrap:wrap; gap:0.5rem;">
            <span class="skill-tag" style="background:var(--bg-base); padding:0.5rem 1rem; border-radius:20px; font-size:0.85rem; border:1px solid var(--border);"><?php echo $course['category']; ?></span>
            <span class="skill-tag" style="background:var(--bg-base); padding:0.5rem 1rem; border-radius:20px; font-size:0.85rem; border:1px solid var(--border);">Problem Solving</span>
            <span class="skill-tag" style="background:var(--bg-base); padding:0.5rem 1rem; border-radius:20px; font-size:0.85rem; border:1px solid var(--border);">Architecture</span>
          </div>
        </div>
      </aside>
    </div>
  </div>

<?php require_once 'includes/footer.php'; ?>
