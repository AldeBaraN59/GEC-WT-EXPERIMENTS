<?php require_once 'session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UI/UX Design Fundamentals — Luminary</title>
  <link rel="stylesheet" href="style.css">
  <style><?= get_theme_css($current_theme) ?></style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="course-detail-hero">
  <div class="course-detail-inner">
    <div class="course-detail-text">
      <div class="breadcrumb"><a href="courses.php">Courses</a> / Design / UI&amp;UX</div>
      <span class="tag">Design · Intermediate</span>
      <h1>UI/UX Design Fundamentals</h1>
      <p>Go from zero to job-ready with a comprehensive, project-based journey through user research, wireframing, prototyping, and design systems using Figma.</p>
      <div class="course-badges">
        <div class="badge">⭐ 4.9 (2,418 reviews)</div>
        <div class="badge">👥 12,400 students</div>
        <div class="badge">⏱ 24 hours</div>
      </div>
      <div class="instructor-row">
        <div class="avatar">S</div>
        <div class="instructor-info"><small>Instructor</small><strong>Sarah Chen · Senior Designer at Airbnb</strong></div>
      </div>
    </div>
    <div class="enroll-card">
      <div class="enroll-price">$89</div>
      <div class="enroll-price-sub">One-time payment · Lifetime access</div>
      <?php if (is_logged_in()): ?>
        <!-- SESSION: Personalised enroll button -->
        <button class="btn btn-gold" onclick="alert('Enrolled! Welcome, <?= get_user_name() ?>!')">Enroll Now</button>
      <?php else: ?>
        <a class="btn btn-gold" href="login.php">Login to Enroll</a>
      <?php endif; ?>
      <ul class="enroll-perks">
        <li>24 hours of HD video</li>
        <li>18 real-world projects</li>
        <li>Figma source files included</li>
        <li>Certificate of completion</li>
        <li>30-day money-back guarantee</li>
      </ul>
    </div>
  </div>
</div>

<div class="course-curriculum">
  <div class="course-curriculum-inner">
    <div class="curriculum-section">
      <h2>Course Curriculum</h2>
      <div class="module"><div class="module-header">Module 1: Design Thinking &amp; Research <span class="module-meta">5 lessons · 3h 20m</span></div><div class="module-lessons"><div class="lesson"><div class="lesson-icon free">▶</div> What is UX Design? <span>12:40</span></div><div class="lesson"><div class="lesson-icon free">▶</div> User Research Methods <span>28:15</span></div><div class="lesson"><div class="lesson-icon">📄</div> Creating User Personas <span>22:10</span></div></div></div>
      <div class="module"><div class="module-header">Module 2: Figma Mastery <span class="module-meta">6 lessons · 4h 10m</span></div><div class="module-lessons"><div class="lesson"><div class="lesson-icon free">▶</div> Figma Interface Overview <span>Free preview</span></div><div class="lesson"><div class="lesson-icon">📄</div> Components &amp; Auto Layout <span>42:00</span></div></div></div>
    </div>
    <div>
      <div class="skills-section" style="margin-bottom:2rem;">
        <h3>Skills You'll Gain</h3>
        <div class="skills-list">
          <span class="skill-tag">Figma</span><span class="skill-tag">UX Research</span><span class="skill-tag">Prototyping</span><span class="skill-tag">Design Systems</span><span class="skill-tag">Wireframing</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="js/progress-tracker.js"></script>
<?php if (is_logged_in()): ?>
  <script>
    window.LUMINARY_THEME_COOKIE = "<?= htmlspecialchars(get_theme_cookie_name(), ENT_QUOTES, 'UTF-8') ?>";
  </script>
  <script src="js/theme-engine.js"></script>
<?php endif; ?>
</body>
</html>
