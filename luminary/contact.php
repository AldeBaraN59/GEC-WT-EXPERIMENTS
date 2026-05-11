<?php
$activePage = 'contact';
$pageTitle = 'Contact';
require_once 'includes/header.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    $stmt = $pdo->prepare("INSERT INTO contacts (first_name, last_name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$first_name, $last_name, $email, $subject, $message])) {
        $success = "Message sent! We'll be in touch within 24 hours.";
    }
}
?>

  <div class="contact-layout">
    <!-- LEFT PANEL -->
    <div class="contact-left">
      <span class="tag" style="display:block;margin-bottom:0.75rem;">Get in touch</span>
      <h1>We'd love to hear from you.</h1>
      <p>Whether you have questions about courses, need help with your account, or want to discuss a Teams plan — our team responds within one business day.</p>
      <div class="contact-info">
        <div class="contact-info-item">
          <div class="contact-icon">✉</div>
          <div>
            <strong>Email us</strong>
            <span>hello@abcd.io</span>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-icon">💬</div>
          <div>
            <strong>Live chat</strong>
            <span>Available Mon–Fri, 9am–6pm EST</span>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-icon">🏢</div>
          <div>
            <strong>Office</strong>
            <span>340 Pine Street, San Francisco, CA</span>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL (FORM) -->
    <div class="contact-right">
      <h2>Send us a message</h2>
      
      <?php if ($success): ?>
        <div style="background:rgba(34, 197, 94, 0.1); color:var(--sage); padding:1rem; border-radius:6px; margin-bottom:1.5rem; border:1px solid rgba(34, 197, 94, 0.2);">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>

      <form action="contact.php" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" placeholder="Jane" required>
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" placeholder="Smith" required>
          </div>
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="jane@example.com" required>
        </div>
        <div class="form-group">
          <label>Subject</label>
          <select name="subject">
            <option>General inquiry</option>
            <option>Course support</option>
            <option>Billing &amp; payments</option>
            <option>Teams &amp; enterprise</option>
            <option>Become an instructor</option>
          </select>
        </div>
        <div class="form-group">
          <label>Message</label>
          <textarea name="message" placeholder="Tell us how we can help…" required></textarea>
        </div>
        <button type="submit" class="btn btn-gold form-submit">Send Message →</button>
      </form>
    </div>
  </div>

<?php require_once 'includes/footer.php'; ?>
