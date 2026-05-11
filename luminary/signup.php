<?php
require_once 'includes/init.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $role = (isset($_POST['role']) && $_POST['role'] === 'mentor') ? 'mentor' : 'student';
    $bio = sanitize($_POST['bio'] ?? '');

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $result = signup($pdo, $username, $email, $password, $role, $bio);
        if ($result === true) {
            login($pdo, $email, $password);
            if ($role === 'mentor') {
                redirect('mentor_dashboard.php');
            } else {
                redirect('dashboard.php');
            }
        } else {
            $error = $result;
        }
    }
}

$pageTitle = "Sign Up";
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
    max-width: 520px;
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
  .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
  }
  .form-input-premium {
      width:100%; 
      padding:0.9rem; 
      border-radius:8px; 
      border:1px solid var(--border); 
      background:rgba(255,255,255,0.03); 
      color:var(--text-main); 
      margin-bottom:1.25rem;
      transition: border 0.2s;
  }
  .form-input-premium:focus { border-color: var(--gold); outline:none; }
  
  #mentorFields { display: none; animation: slideDown 0.3s ease; }
  @keyframes slideDown { from { opacity:0; transform: translateY(-10px); } to { opacity:1; transform: translateY(0); } }
</style>

<div class="auth-container">
    <div class="auth-card">
      <h1>Create Account</h1>
      <p id="authSubtitle">Join our community of lifelong learners.</p>

      <?php if ($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
      <?php endif; ?>

      <form action="signup.php" method="POST">
        
        <!-- Toggle Switch -->
        <div class="role-toggle" style="display: flex; background: var(--bg-base); border-radius: 10px; padding: 0.4rem; margin-bottom: 2rem; border: 1px solid var(--border);">
            <div class="role-btn active" id="btnStudent" onclick="setRole('student')" style="flex: 1; text-align: center; padding: 0.6rem; cursor: pointer; border-radius: 7px; font-size: 0.9rem; font-weight: 600; color: var(--text-muted); transition: all 0.25s;">Student</div>
            <div class="role-btn" id="btnMentor" onclick="setRole('mentor')" style="flex: 1; text-align: center; padding: 0.6rem; cursor: pointer; border-radius: 7px; font-size: 0.9rem; font-weight: 600; color: var(--text-muted); transition: all 0.25s;">Mentor</div>
        </div>
        <input type="hidden" name="role" id="roleInput" value="student">

        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="janesmith" class="form-input-premium" required>
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="jane@example.com" class="form-input-premium" required>
        </div>
        
        <!-- Mentor Specific -->
        <div id="mentorFields">
            <div class="form-group">
              <label>Expertise & Bio *</label>
              <textarea name="bio" id="bioInput" placeholder="e.g. Senior Software Engineer..." class="form-input-premium" style="min-height: 100px;"></textarea>
            </div>
        </div>

        <div class="form-row" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" placeholder="••••••••" class="form-input-premium" required>
            </div>
            <div class="form-group">
              <label>Confirm</label>
              <input type="password" name="confirm_password" placeholder="••••••••" class="form-input-premium" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-gold form-submit" style="width: 100%; padding: 1rem; font-weight: 700; margin-top: 1rem;">Create Account →</button>
      </form>

      <div class="auth-footer">
        Already have an account? <a href="login.php">Log In</a>
      </div>
    </div>
</div>

<script>
    function setRole(role) {
        document.getElementById('roleInput').value = role;
        document.getElementById('btnStudent').classList.remove('active');
        document.getElementById('btnMentor').classList.remove('active');
        
        const activeStyles = { background: 'var(--gold)', color: '#000', boxShadow: '0 4px 12px rgba(200,146,42,0.3)' };
        const inactiveStyles = { background: 'none', color: 'var(--text-muted)', boxShadow: 'none' };

        if (role === 'mentor') {
            document.getElementById('btnMentor').classList.add('active');
            Object.assign(document.getElementById('btnMentor').style, activeStyles);
            Object.assign(document.getElementById('btnStudent').style, inactiveStyles);
            document.getElementById('mentorFields').style.display = 'block';
            document.getElementById('bioInput').setAttribute('required', 'required');
            document.getElementById('authSubtitle').textContent = "Share your expertise and inspire others.";
        } else {
            document.getElementById('btnStudent').classList.add('active');
            Object.assign(document.getElementById('btnStudent').style, activeStyles);
            Object.assign(document.getElementById('btnMentor').style, inactiveStyles);
            document.getElementById('mentorFields').style.display = 'none';
            document.getElementById('bioInput').removeAttribute('required');
            document.getElementById('authSubtitle').textContent = "Join our community of lifelong learners.";
        }
    }
</script>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.querySelector('input[name="email"]');
    const userInput = document.querySelector('input[name="username"]');

    function createFeedback(el) {
        let feedback = el.parentNode.querySelector('.ajax-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'ajax-feedback';
            feedback.style.fontSize = '0.75rem';
            feedback.style.marginTop = '0.4rem';
            el.parentNode.appendChild(feedback);
        }
        return feedback;
    }

    emailInput.addEventListener('blur', () => {
        if (!emailInput.value) return;
        const feedback = createFeedback(emailInput);
        feedback.innerText = 'Checking...';
        
        fetch(`check_user.php?email=${encodeURIComponent(emailInput.value)}`)
            .then(res => res.json())
            .then(data => {
                if (data.available) {
                    feedback.innerText = '✓ Email is available';
                    feedback.style.color = 'var(--sage)';
                } else {
                    feedback.innerText = '✕ Email already registered';
                    feedback.style.color = 'var(--rust)';
                }
            });
    });

    userInput.addEventListener('blur', () => {
        if (!userInput.value) return;
        const feedback = createFeedback(userInput);
        feedback.innerText = 'Checking...';
        
        fetch(`check_user.php?username=${encodeURIComponent(userInput.value)}`)
            .then(res => res.json())
            .then(data => {
                if (data.available) {
                    feedback.innerText = '✓ Username is available';
                    feedback.style.color = 'var(--sage)';
                } else {
                    feedback.innerText = '✕ Username already taken';
                    feedback.style.color = 'var(--rust)';
                }
            });
    });
  });
  </script>

<?php require_once 'includes/footer.php'; ?>
