<?php require_once 'session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pricing — Luminary</title>
  <link rel="stylesheet" href="style.css">
  <style><?= get_theme_css($current_theme) ?></style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="page-hero" style="text-align:center;">
  <div class="page-hero-bg"></div>
  <div class="page-hero-inner" style="max-width:700px;margin:0 auto;">
    <span class="tag" style="display:block;margin-bottom:0.75rem;">Simple Pricing</span>
    <h1>Invest in yourself.<br>Not a subscription trap.</h1>
    <p style="margin:0 auto;">Pay once per course, or go unlimited with Pro.</p>
  </div>
</div>

<div class="pricing-section">
  <div class="pricing-inner">
    <div class="pricing-grid">

      <div class="pricing-card">
        <div class="pricing-header">
          <span class="pricing-tag-label">Pay per course</span>
          <div class="plan-name">Starter</div>
          <div class="price-display"><span class="price-currency">$</span><span class="price-amount">49–129</span></div>
          <div class="price-period">per course, one-time</div>
        </div>
        <div class="pricing-body">
          <ul class="feature-list">
            <li><span class="check">✓</span> Full course access</li>
            <li><span class="check">✓</span> Lifetime access</li>
            <li><span class="check">✓</span> Certificate of completion</li>
            <li><span class="cross">—</span> Community forums</li>
            <li><span class="cross">—</span> Live Q&amp;A sessions</li>
          </ul>
          <a class="btn btn-outline" href="courses.php">Browse Courses</a>
        </div>
      </div>

      <div class="pricing-card featured">
        <div class="pricing-header dark">
          <span class="pricing-tag-label">Most popular ✦</span>
          <div class="plan-name">Pro</div>
          <div class="price-display"><span class="price-currency" style="color:#888;">$</span><span class="price-amount" style="color:var(--paper);">29</span></div>
          <div class="price-period" style="color:#555;">per month, billed annually</div>
        </div>
        <div class="pricing-body">
          <ul class="feature-list">
            <li><span class="check">✓</span> <strong>All 320+ courses</strong></li>
            <li><span class="check">✓</span> New courses every week</li>
            <li><span class="check">✓</span> All certificates</li>
            <li><span class="check">✓</span> Community forum access</li>
            <li><span class="check">✓</span> Monthly live Q&amp;A</li>
          </ul>
          <?php if (is_logged_in()): ?>
            <button class="btn btn-gold" onclick="alert('Pro activated for <?= get_user_name() ?>!')">Activate Pro</button>
          <?php else: ?>
            <a class="btn btn-gold" href="login.php?mode=register">Start Free Trial</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="pricing-card">
        <div class="pricing-header">
          <span class="pricing-tag-label">For organizations</span>
          <div class="plan-name">Teams</div>
          <div class="price-display"><span class="price-currency">$</span><span class="price-amount">19</span></div>
          <div class="price-period">per seat / month</div>
        </div>
        <div class="pricing-body">
          <ul class="feature-list">
            <li><span class="check">✓</span> Everything in Pro</li>
            <li><span class="check">✓</span> Team dashboard</li>
            <li><span class="check">✓</span> Custom learning paths</li>
            <li><span class="check">✓</span> SSO integration</li>
            <li><span class="check">✓</span> Dedicated account manager</li>
          </ul>
          <a class="btn btn-primary" href="contact.php">Contact Sales</a>
        </div>
      </div>

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
