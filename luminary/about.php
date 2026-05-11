<?php
$pageTitle = "About Us";
require_once 'includes/header.php';
?>

  <!-- HERO -->
  <div class="about-hero" style="padding: 8rem 0; background: var(--bg-base);">
    <div class="about-hero-inner" style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; padding: 0 2rem;">
      <div>
        <span class="tag" style="background:rgba(200,146,42,0.1); color:var(--gold); padding:0.5rem 1rem; border-radius:4px; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em;">Our story</span>
        <h1 style="font-size:3.5rem; line-height:1.1; margin:1.5rem 0; letter-spacing:-0.03em;">Learning should change your <em>life</em>, not your schedule.</h1>
        <p style="font-size:1.1rem; line-height:1.6; color:var(--text-muted);">Founded in 2021, <?php echo SITE_NAME; ?> was born from a simple frustration: existing platforms were either too shallow or too rigid. We built something different — rigorous, flexible, and relentlessly practical.</p>
      </div>
      <div class="about-numbers" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div class="about-number" style="background:var(--bg-surface); padding:2rem; border-radius:12px; border:1px solid var(--border);"><strong style="display:block; font-size:2rem; color:var(--gold);">48K+</strong><span style="font-size:0.85rem; color:var(--text-muted);">Students worldwide</span></div>
        <div class="about-number" style="background:var(--bg-surface); padding:2rem; border-radius:12px; border:1px solid var(--border);"><strong style="display:block; font-size:2rem; color:var(--gold);">320+</strong><span style="font-size:0.85rem; color:var(--text-muted);">Expert courses</span></div>
        <div class="about-number" style="background:var(--bg-surface); padding:2rem; border-radius:12px; border:1px solid var(--border);"><strong style="display:block; font-size:2rem; color:var(--gold);">180+</strong><span style="font-size:0.85rem; color:var(--text-muted);">Instructors</span></div>
        <div class="about-number" style="background:var(--bg-surface); padding:2rem; border-radius:12px; border:1px solid var(--border);"><strong style="display:block; font-size:2rem; color:var(--gold);">94%</strong><span style="font-size:0.85rem; color:var(--text-muted);">Career outcomes</span></div>
      </div>
    </div>
  </div>

  <!-- MISSION -->
  <div class="mission-section" style="padding: 8rem 0; background: var(--bg-surface);">
    <div class="mission-inner" style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.5fr; gap: 6rem; padding: 0 2rem;">
      <div>
        <span class="tag" style="background:var(--gold); color:#000; padding:0.4rem 0.8rem; border-radius:4px; font-size:0.75rem; font-weight:700;">Our Mission</span>
        <h2 style="font-size:2.5rem; margin:1.5rem 0; letter-spacing:-0.02em;">Democratizing world-class education</h2>
        <p style="margin-bottom:1.5rem; line-height:1.6;">We believe that geography, background, or circumstance shouldn't limit what someone can learn and become. Every course on <?php echo SITE_NAME; ?> is designed with one goal: giving you the exact skills to move forward.</p>
        <p style="margin-bottom:2rem; line-height:1.6; color:var(--text-muted);">Our instructors aren't academics — they're practitioners. Designers who work at FAANG companies, developers who've shipped to millions of users, founders who've built and sold companies.</p>
        <a class="btn btn-gold" href="courses.php">Explore courses</a>
      </div>
      <div class="values-list" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem;">
        <div class="value-item">
          <div class="value-num" style="font-size:2.5rem; font-weight:800; color:var(--gold); opacity:0.2; margin-bottom:1rem;">01</div>
          <div>
            <h4 style="font-size:1.2rem; margin-bottom:0.75rem;">Radically practical</h4>
            <p style="font-size:0.9rem; color:var(--text-muted);">Every lesson builds toward something real. Projects, portfolios, and skills employers actually value.</p>
          </div>
        </div>
        <div class="value-item">
          <div class="value-num" style="font-size:2.5rem; font-weight:800; color:var(--gold); opacity:0.2; margin-bottom:1rem;">02</div>
          <div>
            <h4 style="font-size:1.2rem; margin-bottom:0.75rem;">Quality over quantity</h4>
            <p style="font-size:0.9rem; color:var(--text-muted);">We'd rather have 300 great courses than 3,000 mediocre ones. Every instructor is vetted rigorously.</p>
          </div>
        </div>
        <div class="value-item">
          <div class="value-num" style="font-size:2.5rem; font-weight:800; color:var(--gold); opacity:0.2; margin-bottom:1rem;">03</div>
          <div>
            <h4 style="font-size:1.2rem; margin-bottom:0.75rem;">Community-driven</h4>
            <p style="font-size:0.9rem; color:var(--text-muted);">Learning in isolation is hard. Our community forums keep you connected and accountable.</p>
          </div>
        </div>
        <div class="value-item">
          <div class="value-num" style="font-size:2.5rem; font-weight:800; color:var(--gold); opacity:0.2; margin-bottom:1rem;">04</div>
          <div>
            <h4 style="font-size:1.2rem; margin-bottom:0.75rem;">Accessible by design</h4>
            <p style="font-size:0.9rem; color:var(--text-muted);">Flexible pricing and mobile-first delivery mean <?php echo SITE_NAME; ?> works for everyone.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php require_once 'includes/footer.php'; ?>
