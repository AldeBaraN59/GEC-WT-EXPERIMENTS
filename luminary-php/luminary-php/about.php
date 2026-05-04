<?php require_once 'session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About — Luminary</title>
  <link rel="stylesheet" href="style.css">
  <style><?= get_theme_css($current_theme) ?></style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="about-hero">
  <div class="about-hero-inner">
    <div>
      <span class="tag" style="display:block;margin-bottom:0.75rem;">Our story</span>
      <h1>Learning should change your <em>life</em>, not your schedule.</h1>
      <p>Founded in 2021, Luminary was born from a simple frustration: existing platforms were either too shallow or too rigid.</p>
    </div>
    <div class="about-numbers">
      <div class="about-number"><strong>48K+</strong><span>Students worldwide</span></div>
      <div class="about-number"><strong>320+</strong><span>Expert courses</span></div>
      <div class="about-number"><strong>180+</strong><span>Instructors</span></div>
      <div class="about-number"><strong>94%</strong><span>Career outcomes</span></div>
    </div>
  </div>
</div>

<div class="mission-section">
  <div class="mission-inner">
    <div>
      <span class="tag">Our Mission</span>
      <h2>Democratizing world-class education</h2>
      <p>We believe geography, background, or circumstance shouldn't limit what someone can learn and become.</p>
      <p>Our instructors aren't academics — they're practitioners with years of real-world experience.</p>
      <a class="btn btn-primary" href="courses.php" style="margin-top:1.5rem;">Explore courses</a>
    </div>
    <div class="values-list">
      <div class="value-item"><div class="value-num">01</div><div><h4>Radically practical</h4><p>Every lesson builds toward something real.</p></div></div>
      <div class="value-item"><div class="value-num">02</div><div><h4>Quality over quantity</h4><p>300 great courses beats 3,000 mediocre ones.</p></div></div>
      <div class="value-item"><div class="value-num">03</div><div><h4>Community-driven</h4><p>Forums and live sessions keep you accountable.</p></div></div>
      <div class="value-item"><div class="value-num">04</div><div><h4>Accessible by design</h4><p>Flexible pricing and mobile-first delivery.</p></div></div>
    </div>
  </div>
</div>

<div class="team-section">
  <div class="team-inner">
    <h2>Meet the team</h2>
    <p style="color:var(--muted);margin-bottom:3rem;">Former teachers, engineers, designers, and learners.</p>
    <div class="team-grid">
      <div class="team-card"><div class="team-avatar" style="background:var(--ink);color:var(--gold);">E</div><h4>Elena Vasquez</h4><p>Co-Founder &amp; CEO</p></div>
      <div class="team-card"><div class="team-avatar" style="background:var(--slate);">D</div><h4>David Park</h4><p>CTO &amp; Co-Founder</p></div>
      <div class="team-card"><div class="team-avatar" style="background:var(--sage);">N</div><h4>Nadia Osei</h4><p>Head of Curriculum</p></div>
      <div class="team-card"><div class="team-avatar" style="background:var(--rust);">T</div><h4>Tom Reinholt</h4><p>Head of Design</p></div>
    </div>
  </div>
</div>

<footer><div class="footer-inner"><div class="footer-bottom" style="border-top:1px solid #1a1a1a;padding-top:1.5rem;"><p style="color:#444;font-size:0.8rem;">© 2026 Luminary Learning, Inc.</p></div></div></footer>
<?php if (is_logged_in()): ?>
  <script>
    window.LUMINARY_THEME_COOKIE = "<?= htmlspecialchars(get_theme_cookie_name(), ENT_QUOTES, 'UTF-8') ?>";
  </script>
  <script src="js/theme-engine.js"></script>
<?php endif; ?>
</body>
</html>
