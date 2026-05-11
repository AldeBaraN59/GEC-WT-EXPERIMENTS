<?php
require_once 'session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Luminary — Learn by Doing</title>
  <link rel="stylesheet" href="style.css">
  <!-- COOKIE: Apply theme from cookie before page renders (no flash) -->
  <style><?= get_theme_css($current_theme) ?></style>
</head>
<body>

<?php include 'nav.php'; ?>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-inner">
    <div class="hero-text">
      <div class="hero-label">The modern learning platform</div>
      <h1>Learn skills that <em>actually</em> matter.</h1>
      <!-- SESSION: Personalised greeting if logged in -->
      <?php if (is_logged_in()): ?>
        <p class="hero-sub">Welcome back, <strong style="color:var(--gold)"><?= get_user_name() ?></strong>! Ready to continue your learning journey?</p>
      <?php else: ?>
        <p class="hero-sub">Expert-led courses in design, development, business, and more. Built for the curious mind, taught by real practitioners.</p>
      <?php endif; ?>
      <div class="hero-actions">
        <a class="btn btn-gold" href="courses.php">Browse Courses</a>
        <?php if (is_logged_in()): ?>
          <a class="btn btn-outline-light" href="dashboard.php">My Dashboard</a>
        <?php else: ?>
          <a class="btn btn-outline-light" href="login.php?mode=register">Join Free</a>
        <?php endif; ?>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><strong>48K+</strong><span>Active students</span></div>
        <div class="hero-stat"><strong>320+</strong><span>Expert courses</span></div>
        <div class="hero-stat"><strong>98%</strong><span>Satisfaction rate</span></div>
      </div>
    </div>
    <div class="hero-card-stack" aria-hidden="true">
      <div class="hero-card">
        <div class="card-tag">UI Design</div>
        <h3>Advanced Figma Mastery</h3>
        <p>From wireframes to production-ready prototypes.</p>
        <div class="progress-bar">
          <div class="label"><span>Your progress</span><span>67%</span></div>
          <div class="track"><div class="fill" style="width:67%"></div></div>
        </div>
      </div>
      <div class="hero-card">
        <div class="card-tag green">Live · 3 students</div>
        <h3>React &amp; TypeScript Bootcamp</h3>
        <p>Build production apps with modern tooling.</p>
      </div>
      <div class="hero-card">
        <div class="card-tag blue">New · 4.9 ★</div>
        <h3>Data Science Fundamentals</h3>
        <p>Python, pandas, and machine learning basics.</p>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<div class="features-strip">
  <div class="features-strip-inner">
    <div class="feature-item"><div class="feature-icon">▶</div><h4>Self-Paced Learning</h4><p>Study on your schedule with lifetime access.</p></div>
    <div class="feature-item"><div class="feature-icon">◈</div><h4>Project-Based</h4><p>Build real projects for your portfolio.</p></div>
    <div class="feature-item"><div class="feature-icon">✦</div><h4>Expert Instructors</h4><p>Learn from active industry practitioners.</p></div>
    <div class="feature-item"><div class="feature-icon">◎</div><h4>Verified Certificates</h4><p>Credentials recognized by top companies.</p></div>
  </div>
</div>

<!-- COURSE PREVIEW -->
<div class="courses-preview">
  <div class="courses-preview-inner">
    <div class="section-header">
      <div><div class="eyebrow">Most Popular</div><h2>Courses to get<br>you started</h2></div>
      <a class="btn btn-outline" href="courses.php">View all courses →</a>
    </div>
    <div class="courses-grid">
      <a class="course-card" href="detail.php"><div class="course-thumb design">🎨</div><div class="course-body"><div class="course-level">Intermediate</div><h3>UI/UX Design Fundamentals</h3><p>Master user-centered design from research to prototypes.</p><div class="course-meta"><span>⭐ 4.9</span><span>⏱ 24h</span><span>👥 12.4K</span></div></div></a>
      <a class="course-card" href="detail.php"><div class="course-thumb code">💻</div><div class="course-body"><div class="course-level">Beginner</div><h3>Full-Stack Web Development</h3><p>HTML, CSS, JavaScript, React, Node.js — the complete package.</p><div class="course-meta"><span>⭐ 4.8</span><span>⏱ 48h</span><span>👥 18.2K</span></div></div></a>
      <a class="course-card" href="detail.php"><div class="course-thumb business">📈</div><div class="course-body"><div class="course-level">Advanced</div><h3>Product Strategy &amp; Growth</h3><p>Build products users love using top PM frameworks.</p><div class="course-meta"><span>⭐ 4.7</span><span>⏱ 18h</span><span>👥 7.8K</span></div></div></a>
    </div>
  </div>
</div>

<!-- TESTIMONIALS -->
<div class="testimonials-section">
  <div class="testimonials-inner">
    <div style="text-align:center;margin-bottom:0.5rem;"><span class="tag">What students say</span></div>
    <h2>Stories of transformation</h2>
    <div class="testimonials-grid">
      <div class="testimonial-card"><span class="quote-mark">"</span><p>Luminary completely changed my career trajectory. I went from accountant to UX designer in 8 months.</p><div class="testimonial-author"><div class="author-avatar av1">M</div><div class="author-info"><strong>Maria Santos</strong><span>UX Designer at Spotify</span></div></div></div>
      <div class="testimonial-card"><span class="quote-mark">"</span><p>The project-based approach is what sets Luminary apart. I had a real portfolio piece by week two.</p><div class="testimonial-author"><div class="author-avatar av2">J</div><div class="author-info"><strong>James Okwu</strong><span>Frontend Dev at Stripe</span></div></div></div>
      <div class="testimonial-card"><span class="quote-mark">"</span><p>Luminary is the only platform where I actually finish courses. The pacing and quality are unmatched.</p><div class="testimonial-author"><div class="author-avatar av3">A</div><div class="author-info"><strong>Aiko Tanaka</strong><span>Product Manager at Linear</span></div></div></div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand"><a class="logo" href="index.php">Lumin<span>ary</span></a><p>The modern learning platform for ambitious people.</p></div>
      <div class="footer-col"><h4>Platform</h4><ul><li><a href="courses.php">All Courses</a></li><li><a href="pricing.php">Pricing</a></li><li><a href="dashboard.php">Dashboard</a></li></ul></div>
      <div class="footer-col"><h4>Company</h4><ul><li><a href="about.php">About Us</a></li><li><a href="contact.php">Contact</a></li></ul></div>
      <div class="footer-col"><h4>Account</h4><ul>
        <?php if (is_logged_in()): ?>
          <li><a href="dashboard.php">My Dashboard</a></li>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php">Login</a></li>
          <li><a href="login.php?mode=register">Register</a></li>
        <?php endif; ?>
      </ul></div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 Luminary Learning, Inc. All rights reserved.</p>
      <!-- COOKIE: Show active theme -->
      <p style="color:#333;font-size:0.8rem;">Theme: <?= ucfirst($current_theme) ?></p>
    </div>
  </div>
</footer>


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
