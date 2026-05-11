<?php
$activePage = 'courses';
$pageTitle = 'Courses';

// If this is an AJAX request for filtering
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    require_once 'includes/init.php';
    $category = isset($_GET['category']) ? sanitize($_GET['category']) : 'All';
    if ($category && $category !== 'All') {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE category = ?");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("SELECT * FROM courses");
    }
    $courses = $stmt->fetchAll();
    ?>
    <div class="courses-grid" id="coursesGrid">
        <?php if (empty($courses)): ?>
            <p style="grid-column: 1/-1; text-align: center; padding: 4rem; color: var(--text-muted);">No courses found in this category.</p>
        <?php else: ?>
            <?php foreach ($courses as $course): ?>
                <a class="course-card" href="detail.php?id=<?php echo $course['id']; ?>" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; transition:transform 0.3s ease, box-shadow 0.3s ease; text-decoration:none; color:inherit; display:flex; flex-direction:column;">
                    <div class="course-thumb <?= strtolower($course['category']); ?>" style="height:180px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.02); overflow:hidden;">
                        <?php if (strpos($course['thumbnail'], '/') !== false || strpos($course['thumbnail'], '.') !== false): ?>
                            <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span style="font-size:2.5rem;"><?= htmlspecialchars($course['thumbnail']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="course-body" style="padding:1.5rem; flex:1; display:flex; flex-direction:column;">
                        <div class="course-level" style="font-size:0.7rem; font-weight:700; text-transform:uppercase; color:var(--gold); margin-bottom:0.5rem; letter-spacing:0.05em;"><?php echo $course['level']; ?> · <?php echo $course['category']; ?></div>
                        <h3 style="font-size:1.15rem; margin-bottom:0.75rem; line-height:1.3; font-weight:700;"><?php echo $course['title']; ?></h3>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.5rem; line-height:1.5; flex:1;"><?php echo substr($course['description'], 0, 100) . '...'; ?></p>
                        <div class="course-meta" style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; color:var(--text-muted); border-top:1px solid var(--border); padding-top:1rem;">
                            <span>⭐ <?php echo $course['rating']; ?></span>
                            <span>⏱ <?php echo $course['duration']; ?></span>
                            <span style="color:var(--text-main); font-weight:700;"><?php echo formatPrice($course['price']); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    exit();
}

require_once 'includes/header.php';

// Category filter (Initial load)
$category = isset($_GET['category']) ? sanitize($_GET['category']) : 'All';

if ($category && $category !== 'All') {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE category = ?");
    $stmt->execute([$category]);
} else {
    $stmt = $pdo->query("SELECT * FROM courses");
}
$courses = $stmt->fetchAll();

// Get unique categories for filters
$catStmt = $pdo->query("SELECT DISTINCT category FROM courses");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
?>

  <!-- PAGE HERO -->
  <div class="page-hero">
    <div class="page-hero-bg"></div>
    <div class="page-hero-inner">
      <span class="tag">Course Catalog</span>
      <h1>320+ courses. Real skills. Real results.</h1>
      <p>Every course is built by practitioners with years of hands-on experience. Learn at your own pace with lifetime access.</p>
    </div>
  </div>

  <!-- FILTERS -->
  <div class="filters-bar">
    <div class="filters-bar-inner" id="categoryFilters">
      <a href="courses.php?category=All" data-category="All" class="filter-btn <?php echo ($category == 'All') ? 'active' : ''; ?>">All Categories</a>
      <?php foreach ($categories as $cat): ?>
        <a href="courses.php?category=<?php echo urlencode($cat); ?>" data-category="<?php echo htmlspecialchars($cat); ?>" class="filter-btn <?php echo ($category == $cat) ? 'active' : ''; ?>">
          <?php echo $cat; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- COURSE GRID -->
  <div class="courses-full">
    <div class="courses-full-inner" id="gridContainer">
      <div class="courses-grid" id="coursesGrid">
        <?php if (empty($courses)): ?>
          <p style="grid-column: 1/-1; text-align: center; padding: 4rem; color: var(--text-muted);">No courses found in this category.</p>
        <?php else: ?>
          <?php foreach ($courses as $course): ?>
            <a class="course-card" href="detail.php?id=<?php echo $course['id']; ?>" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; transition:transform 0.3s ease, box-shadow 0.3s ease; text-decoration:none; color:inherit; display:flex; flex-direction:column;">
              <div class="course-thumb <?= strtolower($course['category']); ?>" style="height:180px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.02); overflow:hidden;">
                <?php if (strpos($course['thumbnail'], '/') !== false || strpos($course['thumbnail'], '.') !== false): ?>
                  <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                  <span style="font-size:2.5rem;"><?= htmlspecialchars($course['thumbnail']) ?></span>
                <?php endif; ?>
              </div>
              <div class="course-body" style="padding:1.5rem; flex:1; display:flex; flex-direction:column;">
                <div class="course-level" style="font-size:0.7rem; font-weight:700; text-transform:uppercase; color:var(--gold); margin-bottom:0.5rem; letter-spacing:0.05em;"><?php echo $course['level']; ?> · <?php echo $course['category']; ?></div>
                <h3 style="font-size:1.15rem; margin-bottom:0.75rem; line-height:1.3; font-weight:700;"><?php echo $course['title']; ?></h3>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.5rem; line-height:1.5; flex:1;"><?php echo substr($course['description'], 0, 100) . '...'; ?></p>
                <div class="course-meta" style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; color:var(--text-muted); border-top:1px solid var(--border); padding-top:1rem;">
                  <span>⭐ <?php echo $course['rating']; ?></span>
                  <span>⏱ <?php echo $course['duration']; ?></span>
                  <span style="color:var(--text-main); font-weight:700;"><?php echo formatPrice($course['price']); ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const filters = document.querySelectorAll('.filter-btn');
    const gridContainer = document.getElementById('gridContainer');

    filters.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const category = btn.getAttribute('data-category');
        
        // Update URL
        const url = new URL(window.location);
        url.searchParams.set('category', category);
        window.history.pushState({}, '', url);

        // UI Feedback
        filters.forEach(f => f.classList.remove('active'));
        btn.classList.add('active');
        gridContainer.style.opacity = '0.5';
        gridContainer.style.pointerEvents = 'none';

        // Fetch AJAX
        fetch(`courses.php?category=${encodeURIComponent(category)}&ajax=1`)
          .then(res => res.text())
          .then(html => {
            gridContainer.innerHTML = html;
            gridContainer.style.opacity = '1';
            gridContainer.style.pointerEvents = 'all';
          })
          .catch(err => {
            console.error('Filtering error:', err);
            gridContainer.style.opacity = '1';
          });
      });
    });
  });
  </script>

<?php require_once 'includes/footer.php'; ?>
