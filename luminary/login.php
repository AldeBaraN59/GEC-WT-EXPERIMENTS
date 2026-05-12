<?php
require_once 'includes/init.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (login($pdo, $email, $password, $remember)) {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'mentor') {
            redirect('mentor_dashboard.php');
        } else {
            redirect('dashboard.php');
        }
    } else {
        $error = "Invalid email or password.";
    }
}

$pageTitle = "Log In";
$activePage = 'login';
require_once 'includes/header.php';
?>

<style>
  .auth-container {
    min-height: calc(100vh - 150px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6rem 2rem;
    background: var(--bg-base);
  }
  .auth-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 3.5rem;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 40px 80px rgba(0,0,0,0.4);
  }
  .auth-card h1 {
    font-size: 2.25rem;
    margin-bottom: 0.75rem;
    text-align: center;
    letter-spacing: -0.02em;
  }
  .auth-card p {
    color: var(--text-muted);
    text-align: center;
    margin-bottom: 2.5rem;
    font-size: 1rem;
    line-height: 1.5;
  }
  .error-msg {
    background: rgba(239, 68, 68, 0.1);
    color: var(--rust);
    padding: 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    text-align: center;
    border: 1px solid rgba(239, 68, 68, 0.2);
  }
  .auth-footer {
    margin-top: 2rem;
    text-align: center;
    font-size: 0.9rem;
    color: var(--text-muted);
    border-top: 1px solid var(--border);
    padding-top: 1.5rem;
  }
  .auth-footer a {
    color: var(--gold);
    text-decoration: none;
    font-weight: 600;
  }
  .auth-footer a:hover {
    text-decoration: underline;
  }
  .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
  }
</style>

<div class="auth-container">
  <div class="auth-card">
    <h1>Welcome Back</h1>
    <p id="authSubtitle">Log in to continue your learning journey.</p>

    <?php if ($error): ?>
      <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Role Toggle -->
    <div class="role-toggle" style="display: flex; background: var(--bg-base); border-radius: 10px; padding: 0.4rem; margin-bottom: 2rem; border: 1px solid var(--border);">
        <div class="role-btn active" id="btnStudent" onclick="setRole('student')" style="flex: 1; text-align: center; padding: 0.6rem; cursor: pointer; border-radius: 7px; font-size: 0.9rem; font-weight: 600; color: var(--text-muted); transition: all 0.25s;">Student</div>
        <div class="role-btn" id="btnMentor" onclick="setRole('mentor')" style="flex: 1; text-align: center; padding: 0.6rem; cursor: pointer; border-radius: 7px; font-size: 0.9rem; font-weight: 600; color: var(--text-muted); transition: all 0.25s;">Mentor</div>
    </div>

    <form action="login.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="jane@example.com" required style="width:100%; padding:0.9rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:var(--text-main); margin-bottom:1.25rem;">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required style="width:100%; padding:0.9rem; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:var(--text-main); margin-bottom:1.5rem;">
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; font-size:0.85rem;">
          <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; color:var(--text-muted);">
              <input type="checkbox" name="remember" style="accent-color:var(--gold);"> Remember me
          </label>
          <a href="#" style="color:var(--gold); text-decoration:none;">Forgot password?</a>
      </div>

      <button type="submit" class="btn btn-gold form-submit" style="width: 100%; padding: 1rem; font-weight: 700;">Log In →</button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="signup.php">Sign Up</a>
    </div>
  </div>
</div>

<script>
  function setRole(role) {
      document.getElementById('btnStudent').classList.remove('active');
      document.getElementById('btnMentor').classList.remove('active');
      
      const activeStyles = { background: 'var(--gold)', color: '#000', boxShadow: '0 4px 12px rgba(200,146,42,0.3)' };
      const inactiveStyles = { background: 'none', color: 'var(--text-muted)', boxShadow: 'none' };

      if (role === 'mentor') {
          document.getElementById('btnMentor').classList.add('active');
          Object.assign(document.getElementById('btnMentor').style, activeStyles);
          Object.assign(document.getElementById('btnStudent').style, inactiveStyles);
          document.getElementById('authSubtitle').textContent = "Access your mentor dashboard and curriculum.";
      } else {
          document.getElementById('btnStudent').classList.add('active');
          Object.assign(document.getElementById('btnStudent').style, activeStyles);
          Object.assign(document.getElementById('btnMentor').style, inactiveStyles);
          document.getElementById('authSubtitle').textContent = "Log in to continue your learning journey.";
      }
  }
  setRole('student');
</script>

<?php require_once 'includes/footer.php'; ?>
