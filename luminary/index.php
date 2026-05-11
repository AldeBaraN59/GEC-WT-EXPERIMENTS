<?php
$activePage = 'home';
require_once 'includes/header.php';

// Fetch featured courses (limit to 3)
$stmt = $pdo->query("SELECT * FROM courses LIMIT 3");
$featuredCourses = $stmt->fetchAll();
?>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>
    <div class="hero-inner">
      <div class="hero-text">
        <div class="hero-label">The modern learning platform</div>
        <h1>Learn skills that <em>actually</em> matter.</h1>
        <p class="hero-sub">Expert-led courses in design, development, business, and more. Built for the curious mind, taught by real practitioners.</p>
        <div class="hero-actions">
          <?php if (isLoggedIn()): ?>
            <a class="btn btn-gold" href="<?= $_SESSION['role'] === 'mentor' ? 'mentor_dashboard.php' : 'dashboard.php' ?>">Go to Dashboard →</a>
            <a class="btn btn-outline-light" href="courses.php">Explore Catalog</a>
          <?php else: ?>
            <a class="btn btn-gold" href="signup.php">Enroll Now</a>
            <a class="btn btn-outline-light" href="courses.php">Browse Courses</a>
          <?php endif; ?>
        </div>
        <div class="hero-stats">
          <div class="hero-stat"><strong>48K+</strong><span>Active students</span></div>
          <div class="hero-stat"><strong>320+</strong><span>Expert courses</span></div>
          <div class="hero-stat"><strong>98%</strong><span>Satisfaction rate</span></div>
        </div>
      </div>
      <div class="hero-card-stack">
        <div class="hero-card" style="background:var(--bg-elevated); border:1px solid var(--border); box-shadow: 0 30px 60px rgba(0,0,0,0.5);">
          <div class="card-tag" style="background:var(--gold); color:#000;">UI Design</div>
          <h3>Advanced Figma Mastery</h3>
          <p>From wireframes to production-ready prototypes.</p>
          <div class="progress-bar">
            <div class="label"><span>Your progress</span><span>67%</span></div>
            <div class="track"><div class="fill" style="width:67%; background:var(--gold);"></div></div>
          </div>
        </div>
        <div class="hero-card" style="background:var(--bg-elevated); border:1px solid var(--border); box-shadow: 0 30px 60px rgba(0,0,0,0.4); transform: translate(30px, -20px) rotate(2deg);">
          <div class="card-tag green" style="background:var(--sage);">Live · 3 students</div>
          <h3>React &amp; TypeScript Bootcamp</h3>
          <p>Build production apps with modern tooling.</p>
        </div>
        <div class="hero-card" style="background:var(--bg-elevated); border:1px solid var(--border); box-shadow: 0 30px 60px rgba(0,0,0,0.3); transform: translate(60px, -40px) rotate(4deg);">
          <div class="card-tag blue" style="background:var(--gold); opacity:0.8;">New · 4.9 ★</div>
          <h3>Data Science Fundamentals</h3>
          <p>Python, pandas, and machine learning basics.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES STRIP -->
  <div class="features-strip">
    <div class="features-strip-inner">
      <div class="feature-item">
        <div class="feature-icon" style="color:var(--gold);">▶</div>
        <h4>Self-Paced Learning</h4>
        <p>Study on your own schedule with lifetime access to all content.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon" style="color:var(--gold);">◈</div>
        <h4>Project-Based</h4>
        <p>Build real projects you can add to your portfolio right away.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon" style="color:var(--gold);">✦</div>
        <h4>Expert Instructors</h4>
        <p>Learn from practitioners actively working in the industry.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon" style="color:var(--gold);">◎</div>
        <h4>Verified Certificates</h4>
        <p>Earn credentials recognized by top companies worldwide.</p>
      </div>
    </div>
  </div>

  <!-- COURSE PREVIEW -->
  <div class="courses-preview">
    <div class="courses-preview-inner">
      <div class="section-header">
        <div>
          <div class="eyebrow" style="color:var(--gold); font-weight:700; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem;">Most Popular</div>
          <h2 style="font-size:2.5rem; letter-spacing:-0.03em;">Courses to get<br>you started</h2>
        </div>
        <a class="btn btn-outline" href="courses.php">View all courses →</a>
      </div>
      <div class="courses-grid">
        <?php foreach ($featuredCourses as $course): ?>
          <a class="course-card" href="detail.php?id=<?php echo $course['id']; ?>" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; transition:transform 0.3s ease, box-shadow 0.3s ease;">
            <div class="course-thumb <?= strtolower($course['category']); ?>" style="height:200px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.02);">
              <?php if (strpos($course['thumbnail'], '/') !== false || strpos($course['thumbnail'], '.') !== false): ?>
                <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail" style="width:100%; height:100%; object-fit:cover;">
              <?php else: ?>
                <span style="font-size:3rem;"><?= htmlspecialchars($course['thumbnail']) ?></span>
              <?php endif; ?>
            </div>
            <div class="course-body" style="padding:1.5rem;">
              <div class="course-level" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:var(--gold); margin-bottom:0.5rem;"><?php echo $course['level']; ?> · <?php echo $course['category']; ?></div>
              <h3 style="font-size:1.25rem; margin-bottom:0.75rem; line-height:1.3;"><?php echo $course['title']; ?></h3>
              <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:1.5rem;"><?php echo substr($course['description'], 0, 80) . '...'; ?></p>
              <div class="course-meta" style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; color:var(--text-muted); border-top:1px solid var(--border); padding-top:1rem;">
                <span>⭐ <?php echo $course['rating']; ?></span>
                <span>⏱ <?php echo $course['duration']; ?></span>
                <span><?php echo formatPrice($course['price']); ?></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- TESTIMONIALS -->
  <div class="testimonials-section">
    <div class="testimonials-inner">
      <div style="text-align:center;margin-bottom:0.5rem;"><span class="tag" style="background:rgba(200,146,42,0.1); color:var(--gold);">What students say</span></div>
      <h2 style="text-align:center; font-size:2.5rem; margin-bottom:4rem;">Stories of transformation</h2>
      <div class="testimonials-grid">
        <div class="testimonial-card" style="background:var(--bg-elevated); border:1px solid var(--border); padding:2.5rem; border-radius:16px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
          <span class="quote-mark" style="font-size:4rem; color:var(--gold); opacity:0.3; font-family:serif; line-height:1;">"</span>
          <p style="font-size:1.1rem; line-height:1.6; margin-bottom:2rem;"><?php echo SITE_NAME; ?> completely changed my career trajectory. I went from an accountant to a UX designer in 8 months thanks to their structured curriculum.</p>
          <div class="testimonial-author" style="display:flex; align-items:center; gap:1rem;">
            <div class="author-avatar av1" style="width:48px; height:48px; background:var(--gold); color:#000; display:flex; align-items:center; justify-content:center; border-radius:50%; font-weight:700;">M</div>
            <div class="author-info"><strong>Maria Santos</strong><span style="display:block; font-size:0.8rem; color:var(--text-muted);">UX Designer at Spotify</span></div>
          </div>
        </div>
        <div class="testimonial-card" style="background:var(--bg-elevated); border:1px solid var(--border); padding:2.5rem; border-radius:16px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
          <span class="quote-mark" style="font-size:4rem; color:var(--gold); opacity:0.3; font-family:serif; line-height:1;">"</span>
          <p style="font-size:1.1rem; line-height:1.6; margin-bottom:2rem;">The project-based approach is what sets <?php echo SITE_NAME; ?> apart. I had a real portfolio piece to show by the end of week two. Incredible quality instruction.</p>
          <div class="testimonial-author" style="display:flex; align-items:center; gap:1rem;">
            <div class="author-avatar av2" style="width:48px; height:48px; background:var(--gold); color:#000; display:flex; align-items:center; justify-content:center; border-radius:50%; font-weight:700;">J</div>
            <div class="author-info"><strong>James Okwu</strong><span style="display:block; font-size:0.8rem; color:var(--text-muted);">Frontend Dev at Stripe</span></div>
          </div>
        </div>
        <div class="testimonial-card" style="background:var(--bg-elevated); border:1px solid var(--border); padding:2.5rem; border-radius:16px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
          <span class="quote-mark" style="font-size:4rem; color:var(--gold); opacity:0.3; font-family:serif; line-height:1;">"</span>
          <p style="font-size:1.1rem; line-height:1.6; margin-bottom:2rem;">I've tried every platform out there — <?php echo SITE_NAME; ?> is the only one where I actually finish courses. The pacing and instructor quality are unmatched.</p>
          <div class="testimonial-author" style="display:flex; align-items:center; gap:1rem;">
            <div class="author-avatar av3" style="width:48px; height:48px; background:var(--gold); color:#000; display:flex; align-items:center; justify-content:center; border-radius:50%; font-weight:700;">A</div>
            <div class="author-info"><strong>Aiko Tanaka</strong><span style="display:block; font-size:0.8rem; color:var(--text-muted);">Product Manager at Linear</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php require_once 'includes/footer.php'; ?>
