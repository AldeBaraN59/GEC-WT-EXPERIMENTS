<?php
// login.php — Login & Register Page
require_once 'session.php';
require_once 'db.php';

/** @var mysqli $conn */

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';
$mode    = isset($_GET['mode']) && $_GET['mode'] === 'register' ? 'register' : 'login';

// ── HANDLE FORM SUBMISSION ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── REGISTER ──────────────────────────────────────────────
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm  = $_POST['confirm'];

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check if email already exists
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = 'An account with this email already exists.';
            } else {
                // Hash password and insert user
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt2  = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt2, 'sss', $name, $email, $hashed);

                if (mysqli_stmt_execute($stmt2)) {
                    $success = 'Account created! You can now log in.';
                    $mode    = 'login';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }

    // ── LOGIN ─────────────────────────────────────────────────
    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required.';
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, name, email, password FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user   = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user['password'])) {
                // ── SET SESSION ───────────────────────────────
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $mode === 'register' ? 'Register' : 'Login' ?> — Luminary</title>
  <link rel="stylesheet" href="style.css">
  <style>
    <?= get_theme_css($current_theme) ?>

    .auth-layout {
      display: grid; grid-template-columns: 1fr 1fr;
      min-height: calc(100vh - 64px);
    }
    .auth-left {
      background: var(--ink); padding: 5rem 4rem;
      display: flex; flex-direction: column; justify-content: center;
    }
    .auth-left h1 { font-family:'Playfair Display',serif; font-size:2.8rem; color:var(--paper); letter-spacing:-0.04em; line-height:1.1; margin-bottom:1rem; }
    .auth-left p  { color:#888; line-height:1.8; }
    .auth-left .brand { font-family:'Playfair Display',serif; font-size:1.4rem; color:var(--paper); margin-bottom:3rem; }
    .auth-left .brand span { color:var(--gold); }

    .auth-right { padding: 5rem 4rem; background: var(--cream); display:flex; flex-direction:column; justify-content:center; }
    .auth-right h2 { font-family:'Playfair Display',serif; font-size:1.8rem; letter-spacing:-0.03em; margin-bottom:0.5rem; }
    .auth-tabs { display:flex; gap:0.5rem; margin-bottom:2rem; margin-top:0.5rem; }
    .auth-tab {
      padding:0.4rem 1.2rem; border-radius:100px; font-size:0.82rem; font-weight:600;
      text-decoration:none; border:1.5px solid var(--border); color:var(--muted); transition:all 0.2s;
    }
    .auth-tab.active { background:var(--ink); color:var(--paper); border-color:var(--ink); }

    .form-group { margin-bottom:1.25rem; }
    .form-group label { display:block; font-size:0.8rem; font-weight:600; letter-spacing:0.06em; text-transform:uppercase; color:var(--muted); margin-bottom:0.5rem; }
    .form-group input {
      width:100%; padding:0.85rem 1rem; border:1.5px solid var(--border); border-radius:6px;
      background:var(--paper); font-family:'DM Sans',sans-serif; font-size:0.9rem; color:var(--ink);
      outline:none; transition:border-color 0.2s;
    }
    .form-group input:focus { border-color:var(--ink); }

    .alert-error   { background:#fef2f2; border:1px solid #fca5a5; color:#b91c1c; padding:0.8rem 1rem; border-radius:6px; font-size:0.87rem; margin-bottom:1.25rem; }
    .alert-success { background:#f0fdf4; border:1px solid #86efac; color:#166534; padding:0.8rem 1rem; border-radius:6px; font-size:0.87rem; margin-bottom:1.25rem; }

    .btn-submit { width:100%; padding:0.9rem; background:var(--ink); color:var(--paper); border:none; border-radius:6px; font-family:'DM Sans',sans-serif; font-size:0.95rem; font-weight:600; cursor:pointer; transition:background 0.2s; }
    .btn-submit:hover { background:#333; }
  </style>
</head>
<body>

  <!-- NAV -->
  <nav>
    <a class="nav-logo" href="index.php">Lumin<span>ary</span></a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="courses.php">Courses</a>
      <a href="about.php">About</a>
      <a href="pricing.php">Pricing</a>
      <a href="login.php" class="active">Login</a>
    </div>
  </nav>

  <div class="auth-layout">
    <!-- LEFT PANEL -->
    <div class="auth-left">
      <div class="brand">Lumin<span>ary</span></div>
      <h1>Your learning journey starts here.</h1>
      <p>Join 48,000+ students mastering real-world skills with expert-led courses in design, development, business and more.</p>
    </div>

    <!-- RIGHT PANEL -->
    <div class="auth-right">
      <h2><?= $mode === 'register' ? 'Create Account' : 'Welcome back' ?></h2>
      <div class="auth-tabs">
        <a href="login.php?mode=login"     class="auth-tab <?= $mode === 'login'    ? 'active' : '' ?>">Login</a>
        <a href="login.php?mode=register"  class="auth-tab <?= $mode === 'register' ? 'active' : '' ?>">Register</a>
      </div>

      <?php if ($error):   ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

      <?php if ($mode === 'register'): ?>
      <!-- REGISTER FORM -->
      <form method="POST" action="login.php?mode=register">
        <input type="hidden" name="action" value="register">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="Jane Smith" required>
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="jane@example.com" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min. 6 characters" required>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm" placeholder="Repeat password" required>
        </div>
        <button type="submit" class="btn-submit">Create Account</button>
      </form>

      <?php else: ?>
      <!-- LOGIN FORM -->
      <form method="POST" action="login.php?mode=login">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="jane@example.com" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Your password" required>
        </div>
        <button type="submit" class="btn-submit">Log In</button>
      </form>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
