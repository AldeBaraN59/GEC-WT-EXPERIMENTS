<?php
require_once __DIR__ . '/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle . " — " . SITE_NAME : SITE_NAME . " — Learn by Doing"; ?></title>
  
  <?php
  $themes = [
      'classic' => ["--ink"=>"#0d0d0d","--paper"=>"#f5f0e8","--cream"=>"#faf7f2","--gold"=>"#c8922a","--gold-light"=>"#e8b84b","--muted"=>"#8a8278","--border"=>"#d8cfc0"],
      'midnight'=> ["--ink"=>"#e8e4dc","--paper"=>"#1a1a1a","--cream"=>"#141414","--gold"=>"#e8b84b","--gold-light"=>"#f5d07a","--muted"=>"#6a6460","--border"=>"#2a2a2a"],
      'sepia'   => ["--ink"=>"#2c1a0e","--paper"=>"#f2e8d5","--cream"=>"#faf4e8","--gold"=>"#a0621a","--gold-light"=>"#c8922a","--muted"=>"#7a6a58","--border"=>"#c8b898"],
      'frost'   => ["--ink"=>"#1a2a3a","--paper"=>"#eef4fb","--cream"=>"#f5f9ff","--gold"=>"#2a7ab8","--gold-light"=>"#4a9ad8","--muted"=>"#6a8298","--border"=>"#c8d8e8"]
  ];

  $themeCookieName = function_exists('get_theme_cookie_name')
      ? get_theme_cookie_name()
      : 'luminary_theme';

  $activeTheme = $_COOKIE[$themeCookieName] ?? 'classic';

  if (!isset($themes[$activeTheme])) $activeTheme = 'classic';

  echo "<style>:root {";
  foreach($themes[$activeTheme] as $k => $v) { echo "$k: $v;"; }
  echo "}</style>";
  ?>

  <link rel="stylesheet" href="style.css?v=<?= time() ?>">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <?php if (isset($currentUser)): ?>
    <script>
      window.LUMINARY_THEME_COOKIE = "<?= htmlspecialchars(get_theme_cookie_name(), ENT_QUOTES, 'UTF-8') ?>";
    </script>
  <?php else: ?>
    <script>
      window.LUMINARY_THEME_COOKIE = "luminary_theme_guest";
    </script>
  <?php endif; ?>

  <script src="js/theme-engine.js"></script>
</head>
<body>

  <!-- FLASH MESSAGES -->
  <?php $flash = getFlash(); if ($flash): ?>
    <div class="flash-message <?php echo $flash['type']; ?>" id="flashMessage" style="position:fixed; top:20px; right:20px; z-index:10000; padding:1rem 1.5rem; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.3); display:flex; align-items:center; gap:1rem; border:1px solid rgba(255,255,255,0.1); backdrop-filter:blur(10px); background: <?php echo $flash['type'] === 'success' ? 'rgba(46, 213, 115, 0.9)' : 'rgba(255, 71, 87, 0.9)'; ?>; color:#fff; animation: slideIn 0.3s ease-out;">
      <span><?php echo htmlspecialchars($flash['message']); ?></span>
      <button onclick="this.parentElement.remove()" style="background:none; border:none; color:#fff; font-size:1.2rem; cursor:pointer; padding:0; display:flex;">&times;</button>
    </div>
    <style>
      @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
    <script>setTimeout(() => document.getElementById('flashMessage')?.remove(), 5000);</script>
  <?php endif; ?>

  <!-- BANNER -->
  <div class="banner">🎓 New semester starting March 1st — Get 30% off all courses. <a href="pricing.php">See plans →</a></div>

  <!-- NAV -->
  <nav>
    <a class="nav-logo" href="index.php"><?php echo substr(SITE_NAME, 0, 2); ?><span><?php echo substr(SITE_NAME, 2); ?></span></a>
    <div class="nav-links">
      <a href="index.php" class="<?php echo ($activePage == 'home') ? 'active' : ''; ?>">Home</a>
      <a href="courses.php" class="<?php echo ($activePage == 'courses') ? 'active' : ''; ?>">Courses</a>
      <a href="about.php">About</a>
      <a href="contact.php" class="<?php echo ($activePage == 'contact') ? 'active' : ''; ?>">Contact</a>
      
      <?php if (isLoggedIn()): ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'mentor'): ?>
          <a href="mentor_dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">Mentor Dashboard</a>
        <?php else: ?>
          <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="signup.php" class="nav-cta">Enroll Now</a>
      <?php endif; ?>
    </div>
  </nav>
