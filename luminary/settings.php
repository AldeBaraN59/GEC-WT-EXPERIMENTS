<?php
require_once 'includes/init.php';

requireLogin();

if (!isset($currentUser)) {
    redirect('login.php');
}

$pageTitle = "Settings";

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? '');
    $theme       = trim($_POST['theme'] ?? 'classic');

    if ($newUsername === '') {
        $errors[] = "Username cannot be empty.";
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $newUsername)) {
        $errors[] = "Username must be 3–30 characters, letters, numbers, and underscores only.";
    }

    $allowedThemes = ['classic', 'midnight', 'sepia', 'frost'];
    if (!in_array($theme, $allowedThemes, true)) {
        $errors[] = "Invalid theme selected.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$newUsername, $currentUser['id']]);

        $currentUser['username'] = $newUsername;
        $_SESSION['user']['username'] = $newUsername;

        $cookieName  = get_theme_cookie_name();
        $cookieValue = $theme;
        $oneYear     = time() + 60 * 60 * 24 * 365;

        setcookie($cookieName, $cookieValue, [
            'expires'  => $oneYear,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Settings updated successfully.'
        ];

        header("Location: settings.php");
        exit;
    }
}

$currentThemeCookie = $_COOKIE[get_theme_cookie_name()] ?? 'classic';

require_once 'includes/header.php';
?>

<script>
  window.LUMINARY_THEME_COOKIE = "<?= htmlspecialchars(get_theme_cookie_name(), ENT_QUOTES, 'UTF-8') ?>";
</script>
<script src="js/theme-engine.js"></script>

<?php
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<style>
  .settings-card {
    background: var(--paper);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem 1.75rem;
    color: var(--ink);
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
  }

  .settings-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.35rem;
    color: var(--ink);
  }

  .settings-input,
  .settings-select {
    width: 100%;
    padding: 0.7rem 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--cream);
    color: var(--ink);
    font-size: 0.95rem;
    outline: none;
  }

  .settings-input::placeholder {
    color: var(--muted);
  }

  .settings-input:focus,
  .settings-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(200, 146, 42, 0.15);
  }

  .settings-help {
    margin-top: 0.4rem;
    font-size: 0.85rem;
    color: var(--muted);
  }

  .settings-title {
    margin-bottom: 0.5rem;
    color: var(--ink);
  }

  .settings-subtitle {
    color: var(--muted);
    margin-bottom: 1.5rem;
  }

  .settings-error {
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    border: 1px solid #b3261e;
    background: #fef4f4;
    color: #b3261e;
  }

  .settings-success {
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    border: 1px solid #0f7b3e;
    background: #f3fbf6;
    color: #0f7b3e;
  }
</style>

<div class="container" style="max-width:720px; margin:2.5rem auto 4rem; padding:0 1rem; color:var(--ink);">
  <h1 class="settings-subtitle">Settings</h1>
  <p class="settings-subtitle">Update your profile and theme preference.</p>

  <?php if (!empty($errors)): ?>
    <div class="settings-error">
      <ul style="margin:0; padding-left:1.25rem;">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php elseif ($success): ?>
    <div class="settings-success">
      Settings updated successfully.
    </div>
  <?php endif; ?>

  <form method="post" action="settings.php" class="settings-card">
    <div style="margin-bottom:1.25rem;">
      <label for="username" class="settings-label">Username</label>
      <input
        type="text"
        id="username"
        name="username"
        value="<?= e($currentUser['username']) ?>"
        class="settings-input"
        required
      >
    </div>

    <div style="margin-bottom:1.25rem;">
      <label for="theme" class="settings-label">Theme</label>
      <select
        id="theme"
        name="theme"
        class="settings-select"
      >
        <option value="classic"  <?= $currentThemeCookie === 'classic'  ? 'selected' : '' ?>>☀️ Classic</option>
        <option value="midnight" <?= $currentThemeCookie === 'midnight' ? 'selected' : '' ?>>🌙 Midnight</option>
        <option value="sepia"    <?= $currentThemeCookie === 'sepia'    ? 'selected' : '' ?>>📜 Sepia</option>
        <option value="frost"    <?= $currentThemeCookie === 'frost'    ? 'selected' : '' ?>>❄️ Frost</option>
      </select>
      <p class="settings-help">
        This theme will be saved as a cookie, just for your account on this browser.
      </p>
    </div>

    <button type="submit" class="btn btn-gold">Save Settings</button>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>