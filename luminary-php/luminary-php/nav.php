<?php
// nav.php — Shared Navigation

function current_page(): string {
    return basename($_SERVER['SCRIPT_NAME']);
}
?>
<!-- BANNER -->
<div class="banner">
  🎓 New semester starting March 1st — Get 30% off.
  <a href="pricing.php">See plans →</a>
</div>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="index.php">Lumin<span>ary</span></a>
  <div class="nav-links">
    <a href="index.php"     class="<?= current_page() === 'index.php' ? 'active' : '' ?>">Home</a>
    <a href="courses.php"   class="<?= current_page() === 'courses.php' ? 'active' : '' ?>">Courses</a>
    <a href="about.php"     class="<?= current_page() === 'about.php' ? 'active' : '' ?>">About</a>
    <a href="pricing.php"   class="<?= current_page() === 'pricing.php' ? 'active' : '' ?>">Pricing</a>
    <a href="dashboard.php" class="<?= current_page() === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="contact.php"   class="<?= current_page() === 'contact.php' ? 'active' : '' ?>">Contact</a>

    <?php if (is_logged_in()): ?>
      <span style="color:#aaa;font-size:0.82rem;padding:0.5rem 0.75rem;letter-spacing:0.04em;">
        👤 <?= get_user_name() ?>
      </span>
      <a href="logout.php" style="color:#c0442c !important;">Logout</a>
    <?php else: ?>
      <a href="login.php" class="<?= current_page() === 'login.php' ? 'active' : '' ?>">Login</a>
      <a href="pricing.php" class="nav-cta">Enroll Now</a>
    <?php endif; ?>
  </div>
</nav>