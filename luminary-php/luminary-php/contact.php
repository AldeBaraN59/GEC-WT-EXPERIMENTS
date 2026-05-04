<?php require_once 'session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact — Luminary</title>
  <link rel="stylesheet" href="style.css">
  <style><?= get_theme_css($current_theme) ?></style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="contact-layout">
  <div class="contact-left">
    <span class="tag" style="display:block;margin-bottom:0.75rem;">Get in touch</span>
    <h1>We'd love to hear from you.</h1>
    <p>Whether you have questions about courses, need help with your account, or want to discuss a Teams plan.</p>
    <div class="contact-info">
      <div class="contact-info-item"><div class="contact-icon">✉</div><div><strong>Email us</strong><span>hello@luminary.io</span></div></div>
      <div class="contact-info-item"><div class="contact-icon">💬</div><div><strong>Live chat</strong><span>Mon–Fri, 9am–6pm EST</span></div></div>
      <div class="contact-info-item"><div class="contact-icon">🏢</div><div><strong>Office</strong><span>340 Pine Street, San Francisco, CA</span></div></div>
    </div>
  </div>
  <div class="contact-right">
    <h2>Send us a message</h2>
    <form method="POST">
      <div class="form-row">
        <!-- SESSION: Pre-fill name if logged in -->
        <div class="form-group"><label>First Name</label><input type="text" name="fname" placeholder="Jane" value="<?= is_logged_in() ? explode(' ', get_user_name())[0] : '' ?>"></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="lname" placeholder="Smith"></div>
      </div>
      <!-- SESSION: Pre-fill email if logged in -->
      <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="jane@example.com" value="<?= get_user_email() ?>"></div>
      <div class="form-group"><label>Subject</label><select name="subject"><option>General inquiry</option><option>Course support</option><option>Billing</option><option>Teams &amp; enterprise</option></select></div>
      <div class="form-group"><label>Message</label><textarea name="message" placeholder="Tell us how we can help…"></textarea></div>
      <button type="submit" class="btn btn-primary form-submit">Send Message →</button>
    </form>
  </div>
</div>
<?php if (is_logged_in()): ?>
  <script>
    window.LUMINARY_THEME_COOKIE = "<?= htmlspecialchars(get_theme_cookie_name(), ENT_QUOTES, 'UTF-8') ?>";
  </script>
  <script src="js/theme-engine.js"></script>
<?php endif; ?>
</body>
</html>
