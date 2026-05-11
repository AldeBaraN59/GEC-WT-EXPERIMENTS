<?php
require_once 'includes/init.php';
requireLogin();

$pageTitle = "Course Viewer";
require_once 'includes/header.php';

$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$materialId = isset($_GET['mat']) ? (int)$_GET['mat'] : 0;

// Fetch course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    die("<div class='page-hero'><div class='page-hero-inner'><h1>Course not found.</h1></div></div>");
}

// Check authorization (must be enrolled student or the mentor)
$isMentor = ($currentUser['role'] === 'mentor' && $course['mentor_id'] == $currentUser['id']);
$isEnrolled = false;

if (!$isMentor) {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$currentUser['id'], $courseId]);
    if ($stmt->fetch()) {
        $isEnrolled = true;
    }
}

if (!$isMentor && !$isEnrolled) {
    // If not enrolled, redirect to detail page
    redirect('detail.php?id=' . $courseId);
}

// Fetch sections and materials
$stmt = $pdo->prepare("SELECT * FROM course_sections WHERE course_id = ? ORDER BY order_index ASC, id ASC");
$stmt->execute([$courseId]);
$sections = $stmt->fetchAll();

$allMaterials = [];
foreach ($sections as $sec) {
    $stmt = $pdo->prepare("SELECT * FROM course_materials WHERE section_id = ? ORDER BY id ASC");
    $stmt->execute([$sec['id']]);
    $allMaterials[$sec['id']] = $stmt->fetchAll();
}

// Determine active material
$activeMaterial = null;
if ($materialId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM course_materials WHERE id = ?");
    $stmt->execute([$materialId]);
    $activeMaterial = $stmt->fetch();
} else {
    // Pick the first material from the first section
    if (count($sections) > 0 && count($allMaterials[$sections[0]['id']]) > 0) {
        $activeMaterial = $allMaterials[$sections[0]['id']][0];
    }
}

// Fetch completed materials for this user
$completedMaterials = [];
if (isLoggedIn()) {
    $compStmt = $pdo->prepare("SELECT material_id FROM user_progress WHERE user_id = ? AND course_id = ?");
    $compStmt->execute([$currentUser['id'], $courseId]);
    $completedMaterials = $compStmt->fetchAll(PDO::FETCH_COLUMN);
}

?>

<style>
.viewer-layout {
    display: flex;
    height: calc(100vh - 80px); /* Assuming nav is ~80px */
    background: var(--bg-surface);
}
.viewer-sidebar {
    width: 300px;
    background: var(--bg-elevated);
    border-right: 1px solid var(--border);
    overflow-y: auto;
    padding: 1.5rem;
}
.viewer-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}
.viewer-content-area {
    flex: 1;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}
.viewer-header {
    padding: 1.5rem 2rem;
    background: var(--bg-base);
    border-bottom: 1px solid var(--border);
}
.section-title {
    font-weight: bold;
    color: var(--text-base);
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.material-link {
    display: block;
    padding: 0.75rem;
    color: var(--text-muted);
    text-decoration: none;
    border-radius: 6px;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
    transition: all 0.2s;
}
.material-link:hover {
    background: rgba(255,255,255,0.05);
    color: var(--text-base);
}
.material-link.active {
    background: rgba(200, 146, 42, 0.1);
    color: var(--gold);
    border-left: 3px solid var(--gold);
}

/* Quiz UI Styles */
.quiz-container {
    background: var(--bg-base);
    padding: 2.5rem;
    border-radius: 12px;
    width: 100%;
    max-width: 700px;
    color: var(--text-base);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}
.quiz-q-header {
    font-size: 0.8rem;
    color: var(--gold);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
}
.quiz-q-text {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 2rem;
    line-height: 1.4;
}
.quiz-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.quiz-opt-label {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    padding: 1rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}
.quiz-opt-label:hover {
    background: rgba(255,255,255,0.06);
    border-color: var(--gold);
}
.quiz-opt-label input[type="radio"] {
    accent-color: var(--gold);
    width: 18px;
    height: 18px;
}
</style>

<div class="viewer-layout">
  <!-- Sidebar Timeline -->
  <aside class="viewer-sidebar">
    <?php $backLink = ($currentUser['role'] === 'mentor') ? 'mentor_dashboard.php' : 'dashboard.php'; ?>
    <a href="<?= $backLink ?>" style="color:var(--text-muted); font-size:0.85rem; text-decoration:none;">← Back to Dashboard</a>
    <h3 style="margin-top:1rem; font-size:1.1rem; line-height:1.3;"><?= htmlspecialchars($course['title']) ?></h3>
    
    <?php if (empty($sections)): ?>
      <p style="color:var(--text-muted); margin-top:2rem; font-size:0.9rem;">No content uploaded yet.</p>
    <?php endif; ?>

    <?php foreach ($sections as $index => $sec): ?>
      <div class="section-title">Module <?= $index + 1 ?>: <?= htmlspecialchars($sec['title']) ?></div>
      <?php foreach ($allMaterials[$sec['id']] as $mat): ?>
        <?php $isActive = ($activeMaterial && $activeMaterial['id'] == $mat['id']); ?>
        <a href="course_view.php?id=<?= $courseId ?>&mat=<?= $mat['id'] ?>" class="material-link <?= $isActive ? 'active' : '' ?>">
          <span class="status-icon"><?= in_array($mat['id'], $completedMaterials) ? '✅' : '' ?></span>
          <?php
            if ($mat['type'] == 'video') echo '▶ ';
            else if ($mat['type'] == 'pdf') echo '📄 ';
            else if ($mat['type'] == 'quiz') echo '📝 ';
            else echo '🖼️ ';
          ?>
          <?= htmlspecialchars($mat['title']) ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </aside>

  <!-- Main Content Area -->
  <main class="viewer-main">
    <?php if ($activeMaterial): ?>
      <div class="viewer-header">
        <h2 style="margin:0; font-size:1.5rem;"><?= htmlspecialchars($activeMaterial['title']) ?></h2>
      </div>
      <div class="viewer-content-area">
        <?php if ($activeMaterial['type'] === 'video'): ?>
          <video controls style="width:100%; max-height:100%; max-width:1000px; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.5);">
            <source src="<?= htmlspecialchars($activeMaterial['file_path']) ?>" type="video/mp4">
            Your browser does not support the video tag.
          </video>
        <?php elseif ($activeMaterial['type'] === 'pdf'): ?>
          <object data="<?= htmlspecialchars($activeMaterial['file_path']) ?>" type="application/pdf" style="width:100%; height:100%; border-radius:8px;"></object>
        <?php elseif ($activeMaterial['type'] === 'image'): ?>
          <img src="<?= htmlspecialchars($activeMaterial['file_path']) ?>" alt="Course Material" style="max-width:100%; max-height:100%; border-radius:8px;">
        <?php elseif ($activeMaterial['type'] === 'quiz'): ?>
          <?php $quizData = json_decode($activeMaterial['content'], true) ?? []; ?>
          <div class="quiz-container" id="quizContainer">
            <h3 style="margin-top:0; color:var(--gold); display:flex; align-items:center; gap:0.5rem;">
                <span>📝</span> Quiz Assessment
            </h3>
            
            <?php if (empty($quizData)): ?>
              <p style="color:var(--text-muted);">This quiz has no questions yet.</p>
            <?php else: ?>
              <div id="quizForm">
                <?php foreach ($quizData as $qIdx => $q): ?>
                  <div class="quiz-q-item" id="q_<?= $qIdx ?>" style="display: <?= $qIdx === 0 ? 'block' : 'none' ?>;">
                    <div class="quiz-q-header">Question <?= $qIdx + 1 ?> of <?= count($quizData) ?></div>
                    <div class="quiz-q-text"><?= htmlspecialchars($q['question']) ?></div>
                    
                    <div class="quiz-options">
                      <?php foreach ($q['options'] as $oIdx => $opt): ?>
                        <label class="quiz-opt-label">
                          <input type="radio" name="q_<?= $qIdx ?>" value="<?= htmlspecialchars($opt) ?>" data-correct="<?= ($opt === $q['answer']) ? '1' : '0' ?>">
                          <span><?= htmlspecialchars($opt) ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>

                    <div style="margin-top:2rem; display:flex; justify-content:space-between;">
                      <?php if ($qIdx > 0): ?>
                        <button type="button" class="btn btn-outline" onclick="showQ(<?= $qIdx - 1 ?>)">Previous</button>
                      <?php else: ?>
                        <div></div>
                      <?php endif; ?>

                      <?php if ($qIdx < count($quizData) - 1): ?>
                        <button type="button" class="btn btn-gold" onclick="showQ(<?= $qIdx + 1 ?>)">Next Question</button>
                      <?php else: ?>
                        <button type="button" class="btn btn-gold" onclick="finishQuiz()">Finish Quiz</button>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div id="quizResult" style="display:none; text-align:center; padding:2rem 0;">
                <div id="scoreCircle" style="width:120px; height:120px; border-radius:50%; border:5px solid var(--gold); display:flex; flex-direction:column; align-items:center; justify-content:center; margin:0 auto 1.5rem; font-size:1.5rem; font-weight:bold;">
                    <span id="finalScore">0</span>
                    <small style="font-size:0.8rem; color:var(--text-muted);">Score</small>
                </div>
                <h3 id="resultTitle">Quiz Completed!</h3>
                <p id="resultMsg" style="color:var(--text-muted); margin-bottom:2rem;"></p>
                <button type="button" class="btn btn-outline" onclick="location.reload()">Retake Quiz</button>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Completion Footer -->
      <div class="viewer-footer" style="margin-top:4rem; padding:2rem; border-top:1px solid var(--border); display:flex; justify-content:flex-end;">
        <?php if (!in_array($activeMaterial['id'], $completedMaterials)): ?>
          <button id="markCompleteBtn" class="btn btn-gold" onclick="markAsComplete(<?= $activeMaterial['id'] ?>)">
            Mark as Complete
          </button>
        <?php else: ?>
          <button class="btn btn-outline" disabled style="opacity:0.6; cursor:default; border-color:var(--sage); color:var(--sage);">
            ✓ Completed
          </button>
        <?php endif; ?>
      </div>

      <script>
      function markAsComplete(matId) {
        const btn = document.getElementById('markCompleteBtn');
        if (!btn) return;
        const originalText = btn.innerText;
        btn.innerText = 'Processing...';
        btn.disabled = true;

        fetch('mark_complete.php', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            course_id: <?= $courseId ?>,
            material_id: matId
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            // Update button state
            btn.className = 'btn btn-outline';
            btn.style.borderColor = 'var(--sage)';
            btn.style.color = 'var(--sage)';
            btn.style.opacity = '0.6';
            btn.style.cursor = 'default';
            btn.innerText = '✓ Completed';
            
            // Update sidebar checkmark
            const activeLink = document.querySelector('.material-link.active');
            if (activeLink) {
              const statusIcon = activeLink.querySelector('.status-icon');
              if (statusIcon) statusIcon.innerText = '✅';
            }
            
            // Show alert for certificate if completed
            if (data.completed) {
              const confetti = document.createElement('div');
              confetti.style.position = 'fixed';
              confetti.style.top = '50%';
              confetti.style.left = '50%';
              confetti.style.transform = 'translate(-50%, -50%)';
              confetti.style.fontSize = '4rem';
              confetti.style.zIndex = '9999';
              confetti.innerText = '🎉';
              document.body.appendChild(confetti);
              setTimeout(() => confetti.remove(), 3000);
              
              setTimeout(() => {
                alert('Congratulations! You have completed the course. Your certificate has been generated.');
              }, 500);
            }
          } else {
            alert('Error: ' + data.error);
            btn.innerText = originalText;
            btn.disabled = false;
          }
        });
      }
      </script>
    <?php else: ?>
      <div class="viewer-content-area" style="background:var(--bg-surface);">
        <p style="color:var(--text-muted);">Select a lesson from the sidebar to begin.</p>
      </div>
    <?php endif; ?>
  </main>
</div>

<script src="js/jquery-4.js"></script>
<script>
function showQ(idx) {
    $('.quiz-q-item').hide();
    $('#q_' + idx).fadeIn();
}

function finishQuiz() {
    let total = $('.quiz-q-item').length;
    let correct = 0;
    
    $('.quiz-q-item').each(function() {
        let selected = $(this).find('input[type="radio"]:checked');
        if (selected.length > 0 && selected.data('correct') == '1') {
            correct++;
        }
    });
    
    $('#quizForm').hide();
    $('#finalScore').text(correct + '/' + total);
    
    let percent = (correct / total) * 100;
    let msg = "";
    if (percent === 100) msg = "Perfect! You mastered this lesson.";
    else if (percent >= 70) msg = "Great job! You passed.";
    else msg = "Good effort, but you might want to review the material again.";
    
    $('#resultMsg').text(msg);
    $('#quizResult').fadeIn();
}
</script>
</body>
</html>
