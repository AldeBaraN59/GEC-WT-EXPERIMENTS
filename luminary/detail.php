<?php
$activePage = 'courses';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("
    SELECT 
        c.*, 
        u.username as instructor_name, 
        u.bio as instructor_role,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as real_student_count,
        (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as avg_rating
    FROM courses c 
    LEFT JOIN users u ON c.mentor_id = u.id 
    WHERE c.id = ?
");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('courses.php');
}

// Format the dynamic values
$course['students_count'] = $course['real_student_count'];
$course['rating'] = number_format($course['avg_rating'] ?: 0.0, 1);

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
  <div class="course-detail-hero" style="padding: 2.5rem 3rem;">
    <div class="course-detail-inner">
      <div class="course-detail-text">
        <div class="breadcrumb" style="margin-bottom: 0.75rem;"><a href="courses.php">Courses</a> / <?php echo $course['category']; ?></div>
        <span class="tag" style="background:rgba(200,146,42,0.1); color:var(--gold); border:1px solid rgba(200,146,42,0.2); padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;"><?php echo $course['category']; ?> · <?php echo $course['level']; ?></span>
        <h1 style="font-size:3.2rem; margin: 0.75rem 0 1.25rem; font-family: 'Playfair Display', serif;"><?php echo $course['title']; ?></h1>
        <div class="course-badges" style="margin-bottom: 2rem; gap: 0.5rem;">
          <div class="badge">⭐ <?php echo $course['rating']; ?></div>
          <div class="badge">👥 <?php echo number_format($course['students_count']); ?> students</div>
          <div class="badge">⏱ <?php echo $course['duration']; ?></div>
          <div class="badge">📅 Updated May 2026</div>
        </div>
        <div class="instructor-row" style="margin-bottom: 3rem;">
          <div class="avatar" style="background:var(--gold); color:#000; font-weight:700;"><?php echo substr($course['instructor_name'], 0, 1); ?></div>
          <div class="instructor-info">
            <small>Instructor</small>
            <strong><?php echo $course['instructor_name']; ?> · <?php echo $course['instructor_role']; ?></strong>
          </div>
        </div>

        <!-- MERGED ABOUT & CURRICULUM -->
        <div class="about-section">
          <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 1.5rem;">About this Course</h2>
          <div style="color:var(--text-muted); line-height:1.8; font-size:1.05rem; margin-bottom: 4rem;">
              <?= nl2br(htmlspecialchars($course['description'])) ?>
          </div>
          
          <div class="course-curriculum">
            <h2 style="margin-bottom:2rem; font-size:1.8rem; font-family: 'Playfair Display', serif;">Course Curriculum</h2>
            <?php foreach ($sections as $secIdx => $sec): 
              $matStmt = $pdo->prepare("SELECT * FROM course_materials WHERE section_id = ? ORDER BY id ASC");
              $matStmt->execute([$sec['id']]);
              $materials = $matStmt->fetchAll();
              $lessonCount = count($materials);
            ?>
              <div class="module" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:12px; margin-bottom:1rem; overflow:hidden;">
                <div class="module-header" style="padding:1.25rem 1.5rem; background:rgba(255,255,255,0.02); font-weight:600; display:flex; justify-content:space-between; align-items:center;">
                    <span>Module <?php echo $secIdx + 1; ?>: <?php echo htmlspecialchars($sec['title']); ?></span>
                    <span class="module-meta" style="font-size:0.8rem; color:var(--text-muted); font-weight:400;"><?= $lessonCount ?> Lessons</span>
                </div>
                <div class="module-lessons">
                  <?php foreach ($materials as $mat): ?>
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
        </div>
      </div>

      <aside style="display: flex; flex-direction: column; gap: 2rem;">
        <!-- ENROLL CARD (Moved from outer scope to inside the 2nd column) -->
        <div class="enroll-card" style="background:var(--bg-elevated); border:1px solid var(--border); box-shadow:0 30px 60px rgba(0,0,0,0.5); position: sticky; top: 100px; padding: 2rem; border-radius: 20px;">
          <div class="enroll-price" style="font-family: 'Playfair Display', serif; font-size: 2.8rem; margin-bottom: 0.5rem;"><?php echo $course['price'] > 0 ? formatPrice($course['price']) : 'Free'; ?></div>
          <div class="enroll-price-sub" style="color:var(--text-muted); font-size:0.85rem; margin-bottom: 2rem;">One-time payment · Lifetime access</div>
          
          <?php if ($isEnrolled): ?>
            <a class="btn btn-gold" href="course_view.php?id=<?php echo $course['id']; ?>" style="width:100%; justify-content:center;">Continue Learning →</a>
          <?php elseif (isLoggedIn()): ?>
            <?php if ($_SESSION['role'] === 'mentor'): ?>
              <div style="background:rgba(255,255,255,0.05); padding:1.5rem; border-radius:12px; border:1px solid var(--border); text-align:center;">
                <p style="color:var(--text-muted); margin-bottom:0; font-size:0.85rem;">Instructors cannot enroll in courses.</p>
              </div>
            <?php else: ?>
              <form id="enrollForm" action="enroll.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                <button type="submit" id="enrollBtn" class="btn btn-gold" style="width:100%; justify-content:center; font-weight:700;">Enroll Now</button>
              </form>
            <?php endif; ?>
          <?php else: ?>
            <a class="btn btn-gold" href="/signup.php" style="width:100%; justify-content:center; font-weight:700;">Enroll Now</a>
          <?php endif; ?>
          
          <button class="btn btn-outline" style="margin-top:0.75rem; width:100%; justify-content:center;">Try Free Preview</button>
          
          <ul class="enroll-perks" style="margin-top:2rem; list-style:none; padding:0; display:flex; flex-direction:column; gap:0.75rem;">
            <li>Full lifetime access</li>
            <li>Access on mobile and TV</li>
            <li>Certificate of completion</li>
            <li>Industry-recognized curriculum</li>
          </ul>

          <div style="margin-top: 2rem; text-align: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1.5rem;">
            <a href="#" id="openReportModal" style="color:var(--rust); font-size:0.75rem; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; opacity:0.7; transition:all 0.2s;">
              <span>⚠️ Report this course</span>
            </a>
          </div>
        </div>

        <!-- SKILLS SECTION -->
        <div class="skills-section" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:20px; padding:2rem;">
          <h3 style="margin-bottom:1.5rem; font-family: 'Playfair Display', serif; font-size:1.25rem;">Skills You'll Gain</h3>
          <div class="skills-list" style="display:flex; flex-wrap:wrap; gap:0.75rem;">
            <?php 
              $skillsString = $course['skills'] ?? '';
              $skills = explode(',', $skillsString);
              foreach ($skills as $skill):
                $skill = trim($skill);
                if (!$skill) continue;
            ?>
              <span class="skill-tag" style="background:var(--bg-elevated); padding:0.6rem 1.25rem; border-radius:30px; font-size:0.8rem; border:1px solid var(--border); color:var(--text-main); font-weight:500;">
                <?= htmlspecialchars($skill) ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>
    </div>
  </div>

<?php require_once 'includes/footer.php'; ?>
