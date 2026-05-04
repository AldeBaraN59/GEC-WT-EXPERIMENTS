<?php require_once 'session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Courses — Luminary</title>
  <link rel="stylesheet" href="style.css">
  <style><?= get_theme_css($current_theme) ?></style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="page-hero">
  <div class="page-hero-bg"></div>
  <div class="page-hero-inner">
    <span class="tag">Course Catalog</span>
    <h1>320+ courses. Real skills. Real results.</h1>
    <p>Every course built by practitioners with years of hands-on experience.</p>
  </div>
</div>

<div class="filters-bar">
  <div class="filters-bar-inner">
    <button class="filter-btn active" onclick="filterBtn(this)">All</button>
    <button class="filter-btn" onclick="filterBtn(this)">Design</button>
    <button class="filter-btn" onclick="filterBtn(this)">Development</button>
    <button class="filter-btn" onclick="filterBtn(this)">Business</button>
    <button class="filter-btn" onclick="filterBtn(this)">Data Science</button>
    <button class="filter-btn" onclick="filterBtn(this)">Writing</button>
  </div>
</div>

<div class="courses-full">
  <div class="courses-full-inner">
    <div class="courses-grid">
      <a class="course-card" href="detail.php"><div class="course-thumb design">🎨</div><div class="course-body"><div class="course-level">Intermediate · Design</div><h3>UI/UX Design Fundamentals</h3><p>From research to high-fidelity prototypes in Figma.</p><div class="course-meta"><span>⭐ 4.9</span><span>⏱ 24h</span><span>$89</span></div></div></a>
      <a class="course-card" href="detail.php"><div class="course-thumb code">💻</div><div class="course-body"><div class="course-level">Beginner · Development</div><h3>Full-Stack Web Development</h3><p>HTML, CSS, JavaScript, React, and Node.js.</p><div class="course-meta"><span>⭐ 4.8</span><span>⏱ 48h</span><span>$129</span></div></div></a>
      <a class="course-card" href="detail.php"><div class="course-thumb business">📈</div><div class="course-body"><div class="course-level">Advanced · Business</div><h3>Product Strategy &amp; Growth</h3><p>Build products users love with top PM frameworks.</p><div class="course-meta"><span>⭐ 4.7</span><span>⏱ 18h</span><span>$79</span></div></div></a>
      <a class="course-card" href="detail.php"><div class="course-thumb photo">📸</div><div class="course-body"><div class="course-level">Beginner · Photography</div><h3>Portrait Photography Mastery</h3><p>Light, composition, and post-processing.</p><div class="course-meta"><span>⭐ 4.9</span><span>⏱ 16h</span><span>$69</span></div></div></a>
      <a class="course-card" href="detail.php"><div class="course-thumb data">📊</div><div class="course-body"><div class="course-level">Intermediate · Data Science</div><h3>Data Science with Python</h3><p>Pandas, visualization, and machine learning.</p><div class="course-meta"><span>⭐ 4.8</span><span>⏱ 36h</span><span>$99</span></div></div></a>
      <a class="course-card" href="detail.php"><div class="course-thumb writing">✍️</div><div class="course-body"><div class="course-level">All levels · Writing</div><h3>Copywriting for Digital Media</h3><p>Craft compelling copy that converts.</p><div class="course-meta"><span>⭐ 4.6</span><span>⏱ 12h</span><span>$59</span></div></div></a>
    </div>
  </div>
</div>

<footer><div class="footer-inner"><div class="footer-bottom" style="border-top:1px solid #1a1a1a;padding-top:1.5rem;"><p style="color:#444;font-size:0.8rem;">© 2026 Luminary Learning, Inc.</p><a class="btn btn-gold" href="pricing.php" style="padding:0.6rem 1.5rem;font-size:0.85rem;">Start learning today</a></div></div></footer>

<script src="js/course-search.js"></script>
<script src="js/progress-tracker.js"></script>
<?php if (is_logged_in()): ?>
  <script>
    window.LUMINARY_THEME_COOKIE = "<?= htmlspecialchars(get_theme_cookie_name(), ENT_QUOTES, 'UTF-8') ?>";
  </script>
  <script src="js/theme-engine.js"></script>
<?php endif; ?>
<script>function filterBtn(el){document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));el.classList.add('active');}</script>
</body>
</html>
