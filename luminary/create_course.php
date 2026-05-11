<?php
require_once 'includes/init.php';
requireLogin();

if ($currentUser['role'] !== 'mentor') {
    redirect('dashboard.php');
}

require_once 'includes/upload.php';

$error = '';
$success = '';
$isEdit = isset($_GET['id']);
$courseId = $isEdit ? (int)$_GET['id'] : 0;
$courseData = null;
$existingSections = [];

if ($isEdit) {
    // Fetch Course
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND mentor_id = ?");
    $stmt->execute([$courseId, $currentUser['id']]);
    $courseData = $stmt->fetch();

    if (!$courseData) {
        redirect('mentor_dashboard.php');
    }

    // Fetch Sections and Materials
    $stmt = $pdo->prepare("SELECT * FROM course_sections WHERE course_id = ? ORDER BY order_index ASC");
    $stmt->execute([$courseId]);
    $existingSections = $stmt->fetchAll();
    
    foreach ($existingSections as &$sec) {
        $stmt = $pdo->prepare("SELECT * FROM course_materials WHERE section_id = ? ORDER BY id ASC");
        $stmt->execute([$sec['id']]);
        $sec['materials'] = $stmt->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? ''); 
    $level = trim($_POST['level'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $duration = trim($_POST['duration'] ?? '');
    
    $thumbnailUrl = $isEdit ? $courseData['thumbnail'] : '🎨'; 

    try {
        $pdo->beginTransaction();

        if (empty($title) || empty($desc)) {
            throw new Exception("Title and description are required.");
        }

        // Handle Custom Thumbnail Upload
        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['thumbnail_file']);
            if (isset($uploadResult['error'])) {
                throw new Exception("Thumbnail Upload Error: " . $uploadResult['error']);
            }
            $thumbnailUrl = $uploadResult['path'];
        }

        if ($isEdit) {
            // Update Course
            $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, category = ?, level = ?, price = ?, thumbnail = ?, duration = ? WHERE id = ?");
            $stmt->execute([$title, $desc, $category, $level, $price, $thumbnailUrl, $duration, $courseId]);
        } else {
            // Insert Course
            $stmt = $pdo->prepare("INSERT INTO courses (mentor_id, title, description, category, level, price, thumbnail, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$currentUser['id'], $title, $desc, $category, $level, $price, $thumbnailUrl, $duration]);
            $courseId = $pdo->lastInsertId();
        }

        // --- Process Sections and Materials ---
        $postedSections = $_POST['sections'] ?? [];
        $activeSectionIds = [];
        $activeMaterialIds = [];

        $orderIndex = 0;
        foreach ($postedSections as $secKey => $sec) {
            $secId = isset($sec['id']) ? (int)$sec['id'] : 0;
            $secTitle = trim($sec['title'] ?? "Untitled Section");

            if ($secId > 0) {
                // Update Existing Section
                $stmt = $pdo->prepare("UPDATE course_sections SET title = ?, order_index = ? WHERE id = ? AND course_id = ?");
                $stmt->execute([$secTitle, $orderIndex, $secId, $courseId]);
                $activeSectionIds[] = $secId;
            } else {
                // Insert New Section
                $stmt = $pdo->prepare("INSERT INTO course_sections (course_id, title, order_index) VALUES (?, ?, ?)");
                $stmt->execute([$courseId, $secTitle, $orderIndex]);
                $secId = $pdo->lastInsertId();
                $activeSectionIds[] = $secId;
            }
            $orderIndex++;

            // Materials
            $materials = $sec['materials'] ?? [];
            foreach ($materials as $matKey => $mat) {
                $matId = isset($mat['id']) ? (int)$mat['id'] : 0;
                $matTitle = trim($mat['title'] ?? "Untitled Material");
                $matType = $mat['type'] ?? 'file';

                if ($matType === 'quiz') {
                    $quizJson = trim($mat['quiz_json'] ?? '[]');
                    if ($matId > 0) {
                        $stmt = $pdo->prepare("UPDATE course_materials SET title = ?, content = ? WHERE id = ? AND section_id = ?");
                        $stmt->execute([$matTitle, $quizJson, $matId, $secId]);
                        $activeMaterialIds[] = $matId;
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO course_materials (section_id, type, title, content) VALUES (?, 'quiz', ?, ?)");
                        $stmt->execute([$secId, $matTitle, $quizJson]);
                        $activeMaterialIds[] = $pdo->lastInsertId();
                    }
                } else {
                    // File handling
                    $fileKey = $mat['file_key'] ?? '';
                    $currentPath = isset($mat['existing_path']) ? $mat['existing_path'] : '';
                    
                    if ($fileKey && isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
                        $uploadResult = uploadFile($_FILES[$fileKey]);
                        if (isset($uploadResult['error'])) throw new Exception("Upload Error: " . $uploadResult['error']);
                        $currentPath = $uploadResult['path'];
                    }

                    $matDur = (int)($mat['duration'] ?? 0);
                    if ($matId > 0) {
                        $stmt = $pdo->prepare("UPDATE course_materials SET title = ?, file_path = ?, duration = ? WHERE id = ? AND section_id = ?");
                        $stmt->execute([$matTitle, $currentPath, $matDur, $matId, $secId]);
                        $activeMaterialIds[] = $matId;
                    } else {
                        // Guess type from path if new
                        $dbType = 'pdf';
                        if (strpos($currentPath, '.mp4') !== false) $dbType = 'video';
                        if (strpos($currentPath, '.jpg') !== false || strpos($currentPath, '.png') !== false) $dbType = 'image';

                        $stmt = $pdo->prepare("INSERT INTO course_materials (section_id, type, title, file_path, duration) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$secId, $dbType, $matTitle, $currentPath, $matDur]);
                        $activeMaterialIds[] = $pdo->lastInsertId();
                    }
                }
            }
        }

        // Cleanup: Delete sections/materials not in the POST
        if ($isEdit) {
            // Delete Materials
            if (!empty($activeMaterialIds)) {
                $placeholders = implode(',', array_fill(0, count($activeMaterialIds), '?'));
                $stmt = $pdo->prepare("DELETE FROM course_materials WHERE section_id IN (SELECT id FROM course_sections WHERE course_id = ?) AND id NOT IN ($placeholders)");
                $stmt->execute(array_merge([$courseId], $activeMaterialIds));
            }
            // Delete Sections
            if (!empty($activeSectionIds)) {
                $placeholders = implode(',', array_fill(0, count($activeSectionIds), '?'));
                $stmt = $pdo->prepare("DELETE FROM course_sections WHERE course_id = ? AND id NOT IN ($placeholders)");
                $stmt->execute(array_merge([$courseId], $activeSectionIds));
            }
        }

        $pdo->commit();
        setFlash("Course '$title' " . ($isEdit ? 'updated' : 'published') . " successfully!");
        redirect('mentor_dashboard.php');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

$pageTitle = $isEdit ? "Edit Course: " . $courseData['title'] : "Create New Course";
require_once 'includes/header.php';
?>

<style>
/* Builder UI Styles */
.builder-module {
    background: linear-gradient(145deg, var(--bg-surface), rgba(255,255,255,0.02));
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    position: relative;
    transition: transform 0.3s ease;
}
.builder-module:hover {
    border-color: rgba(200, 146, 42, 0.3);
}
.builder-module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}
.builder-module-header h4 { 
    margin: 0; 
    color: var(--gold); 
    font-size: 1.3rem; 
    font-weight: 800;
    letter-spacing: -0.02em;
}
.material-block {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
    transition: all 0.2s ease;
}
.material-block:hover {
    background: rgba(255,255,255,0.04);
    border-color: var(--gold);
}
.material-header {
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    font-weight: 700;
    color: var(--text-main);
    font-size: 0.95rem;
}
.remove-btn {
    color: #ff4d4d;
    background: rgba(255, 77, 77, 0.08);
    border: 1px solid rgba(255, 77, 77, 0.15);
    cursor: pointer;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    transition: all 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.remove-btn:hover { 
    background: #ff4d4d;
    color: white;
    border-color: #ff4d4d;
    box-shadow: 0 4px 12px rgba(255, 77, 77, 0.3);
}
.input-styled {
    width: 100%; 
    padding: 1rem; 
    border-radius: 10px; 
    border: 1px solid var(--border); 
    background: rgba(0,0,0,0.2); 
    color: var(--text-base); 
    font-size: 1rem;
    transition: all 0.2s;
    box-sizing: border-box;
}
.input-styled:focus {
    border-color: var(--gold);
    background: rgba(0,0,0,0.3);
    box-shadow: 0 0 0 4px rgba(200, 146, 42, 0.1);
    outline: none;
}
.btn-gold-outline {
    background: transparent;
    border: 1px solid var(--gold);
    color: var(--gold);
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
}
.btn-gold-outline:hover {
    background: var(--gold);
    color: black;
    box-shadow: 0 8px 20px rgba(200, 146, 42, 0.2);
}

/* Quiz UI Enhancements */
.quiz-question-block {
    background: rgba(0,0,0,0.3);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 1rem;
    border: 1px solid var(--border);
}
.quiz-option-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.75rem;
    background: rgba(255,255,255,0.01);
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid transparent;
}
.quiz-option-row:focus-within {
    border-color: rgba(200, 146, 42, 0.2);
}
.quiz-option-row input[type="radio"] {
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid var(--border);
    border-radius: 50%;
    cursor: pointer;
    position: relative;
}
.quiz-option-row input[type="radio"]:checked {
    border-color: var(--gold);
    background: var(--gold);
}
.quiz-option-row input[type="radio"]:checked::after {
    content: '✓';
    color: black;
    font-size: 12px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: 900;
}

.quiz-q-text, .quiz-opt-text {
    background: #000 !important;
    color: #fff !important;
    border: 1px solid var(--border);
    padding: 0.75rem;
    border-radius: 8px;
    font-size: 0.9rem;
    outline: none;
    transition: all 0.2s;
}
.quiz-q-text:focus, .quiz-opt-text:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(200, 146, 42, 0.15);
}

.add-question-btn {
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 2px dashed var(--border);
    color: var(--text-muted);
    padding: 1rem;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    margin-top: 1rem;
    transition: all 0.2s;
}
.add-question-btn:hover {
    border-color: var(--gold);
    color: var(--gold);
    background: rgba(200, 146, 42, 0.05);
}

/* Tags System */
.tags-input-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: rgba(0,0,0,0.2);
}
.tag-badge {
    background: linear-gradient(135deg, var(--gold), #fcd34d);
    color: #000;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 10px rgba(200, 146, 42, 0.2);
}
.tag-badge::after {
    content: '×';
    font-size: 1.2rem;
    line-height: 1;
}
</style>

<div class="dashboard-layout">
  <aside class="dashboard-sidebar">
    <div class="sidebar-greeting">Welcome back,</div>
    <div class="sidebar-name"><?= htmlspecialchars($currentUser['username']) ?></div>
    <ul class="sidebar-nav">
      <li><a href="mentor_dashboard.php">My Courses</a></li>
      <li><a href="create_course.php" class="<?= !$isEdit ? 'active' : '' ?>">Create Course</a></li>
      <li><a href="logout.php" style="color:var(--rust);">Logout</a></li>
    </ul>
  </aside>

  <main class="dashboard-main">
    <div class="dash-header">
      <h2><?= $isEdit ? 'Edit Course' : 'Create New Course' ?></h2>
      <p><?= $isEdit ? 'Update your course content and curriculum.' : 'Use the dynamic curriculum builder to architect your entire course.' ?></p>
    </div>

    <?php if ($error): ?><div style="background:var(--rust); color:white; padding:1rem; border-radius:6px; margin-bottom:1.5rem;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="courseForm" style="background:var(--bg-elevated); padding:2rem; border-radius:10px; border:1px solid var(--border); max-width:800px;">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      
      <div class="form-row">
        <div class="form-group">
          <label>Course Title *</label>
          <input type="text" name="title" required value="<?= $isEdit ? htmlspecialchars($courseData['title']) : '' ?>" placeholder="e.g. Advanced PHP Mastery">
        </div>
        <div class="form-group">
          <label>Thumbnail (<?= $isEdit ? 'Optional Change' : 'Custom Image' ?>)</label>
          <input type="file" name="thumbnail_file" accept="image/*" style="padding:0.5rem; background:var(--bg-base);">
        </div>
      </div>

      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" required style="min-height: 100px;"><?= $isEdit ? htmlspecialchars($courseData['description']) : '' ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Tags / Category *</label>
          <input type="hidden" name="category" id="categoryInput" required value="<?= $isEdit ? htmlspecialchars($courseData['category']) : '' ?>">
          <div class="tags-input-wrapper" id="tagsContainer">
            <input type="text" id="tagInput" placeholder="Type a tag and press Enter...">
          </div>
        </div>
        <div class="form-group">
          <label>Level</label>
          <select name="level">
            <option <?= ($isEdit && $courseData['level'] == 'Beginner') ? 'selected' : '' ?>>Beginner</option>
            <option <?= ($isEdit && $courseData['level'] == 'Intermediate') ? 'selected' : '' ?>>Intermediate</option>
            <option <?= ($isEdit && $courseData['level'] == 'Advanced') ? 'selected' : '' ?>>Advanced</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Price ($)</label>
          <input type="number" step="0.01" name="price" value="<?= $isEdit ? $courseData['price'] : '0.00' ?>">
        </div>
        <div class="form-group">
          <label>Duration</label>
          <input type="text" name="duration" id="durationInput" readonly value="<?= $isEdit ? htmlspecialchars($courseData['duration']) : '' ?>" style="background: var(--bg-base); cursor: not-allowed;">
        </div>
      </div>

      <hr style="border:0; border-top:1px solid var(--border); margin:2rem 0;">
      
      <div class="dash-section-title">Curriculum Builder</div>
      <div id="curriculumBuilder"></div>

      <button type="button" id="addModuleBtn" class="add-question-btn" style="margin-bottom: 2.5rem; padding: 1.5rem; font-size: 1rem;">+ Add New Module</button>
      <button type="submit" class="btn btn-gold" style="width: 100%; padding: 1.25rem; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; box-shadow: 0 10px 20px rgba(200, 146, 42, 0.3);">
          <?= $isEdit ? 'Save Changes' : 'Publish Complete Course' ?>
      </button>
    </form>
  </main>
</div>

<script>
// --- TAGS SYSTEM ---
const tagInput = document.getElementById('tagInput');
const tagsContainer = document.getElementById('tagsContainer');
const categoryInput = document.getElementById('categoryInput');
let tags = categoryInput.value ? categoryInput.value.split(',').map(t => t.trim()) : [];

function renderTags() {
    document.querySelectorAll('.tag-badge').forEach(e => e.remove());
    tags.forEach(t => {
        if (!t) return;
        const span = document.createElement('span');
        span.className = 'tag-badge';
        span.textContent = t;
        span.onclick = () => { tags = tags.filter(x => x !== t); renderTags(); };
        tagsContainer.insertBefore(span, tagInput);
    });
    categoryInput.value = tags.join(', ');
}
renderTags();
tagInput.addEventListener('keydown', function(e) {
    if(e.key === 'Enter') { e.preventDefault(); const tag = this.value.trim(); if(tag && !tags.includes(tag)) { tags.push(tag); renderTags(); } this.value = ''; }
});

// --- CURRICULUM BUILDER ---
let moduleCount = 0;
function addModule(data = null) {
    const builder = document.getElementById('curriculumBuilder');
    const modId = moduleCount++;
    const secId = data ? data.id : 0;
    const secTitle = data ? data.title : "";
    
    const modHTML = `
      <div class="builder-module" id="mod_${modId}">
        <input type="hidden" name="sections[${modId}][id]" value="${secId}">
        <div class="builder-module-header">
            <h4><span style="opacity:0.5; margin-right:0.5rem;">0${modId + 1}</span> Module</h4>
            <button type="button" class="remove-btn" onclick="document.getElementById('mod_${modId}').remove(); recalculateTotalDuration();">Delete Module</button>
        </div>
        <div style="margin-bottom:1.5rem;">
            <label style="font-size:0.8rem; text-transform:uppercase; color:var(--text-muted); font-weight:700; display:block; margin-bottom:0.5rem;">Module Title</label>
            <input type="text" name="sections[${modId}][title]" value="${secTitle}" class="input-styled" placeholder="e.g. Getting Started" required style="font-weight:700; font-size:1.1rem;">
        </div>
        <div id="materials_${modId}"></div>
        <div style="display:flex; gap:1rem; margin-top:2rem; padding-top:1.5rem; border-top:1px solid rgba(255,255,255,0.05);">
            <button type="button" class="btn-gold-outline" style="flex:1;" onclick="addMaterial(${modId}, 'file')">+ Add Media Lesson</button>
            <button type="button" class="btn-gold-outline" style="flex:1;" onclick="addMaterial(${modId}, 'quiz')">+ Add Quiz Assessment</button>
        </div>
      </div>
    `;
    builder.insertAdjacentHTML('beforeend', modHTML);
    if (data && data.materials) {
        data.materials.forEach(mat => addMaterial(modId, mat.type, mat));
    }
}

function addMaterial(modId, type, data = null) {
    const container = document.getElementById(`materials_${modId}`);
    const matId = Date.now() + Math.floor(Math.random() * 1000); 
    const dbId = data ? data.id : 0;
    const title = data ? data.title : "";
    const path = data ? data.file_path : "";
    const dur = data ? data.duration : 0;
    const quizJson = data ? data.content : "[]";

    let html = `
        <div class="material-block" id="mat_${matId}">
            <input type="hidden" name="sections[${modId}][materials][${matId}][id]" value="${dbId}">
            <input type="hidden" name="sections[${modId}][materials][${matId}][type]" value="${type}">
            <div class="material-header">
                <span>${type === 'quiz' ? '📝 Quiz Assessment' : '🎬 Media Lesson'}</span>
                <button type="button" class="remove-btn" style="padding:0.3rem 0.6rem; font-size:0.65rem;" onclick="document.getElementById('mat_${matId}').remove(); recalculateTotalDuration();">Remove</button>
            </div>
            <input type="text" name="sections[${modId}][materials][${matId}][title]" value="${title}" class="input-styled" placeholder="Enter Lesson Title..." required>
    `;

    if (type === 'file') {
        html += `
            <div style="background:rgba(0,0,0,0.2); padding:1rem; border-radius:8px; border:1px solid var(--border);">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.75rem; display:flex; justify-content:space-between;">
                    <span>${path ? 'Current File: ' + path.split('/').pop() : 'Select Video/PDF File'}</span>
                    ${dur > 0 ? `<span style="color:var(--gold);">⏱ ${Math.floor(dur/60)}m ${dur%60}s</span>` : ''}
                </div>
                <input type="file" name="material_file_${modId}_${matId}" class="material-file input-styled" ${!path ? 'required' : ''} style="padding:0.5rem; font-size:0.8rem; border-style:dashed;">
                <input type="hidden" name="sections[${modId}][materials][${matId}][existing_path]" value="${path}">
                <input type="hidden" name="sections[${modId}][materials][${matId}][file_key]" value="material_file_${modId}_${matId}">
                <input type="hidden" name="sections[${modId}][materials][${matId}][duration]" class="mat-duration-hidden" value="${dur}">
            </div>
        `;
    } else {
        html += `
            <textarea name="sections[${modId}][materials][${matId}][quiz_json]" id="quiz_json_${matId}" style="display:none;">${quizJson}</textarea>
            <div id="quiz_questions_${matId}"></div>
            <button type="button" class="add-question-btn" onclick="addQuestion('${matId}')">+ Add Question to Quiz</button>
        `;
    }

    html += `</div>`;
    container.insertAdjacentHTML('beforeend', html);
    
    if (type === 'quiz' && data && data.content) {
        const questions = JSON.parse(data.content);
        questions.forEach(q => renderExistingQuestion(matId, q));
    }
}

// Pre-fill existing curriculum
<?php if ($isEdit): ?>
    document.addEventListener('DOMContentLoaded', () => {
        const existingData = <?= json_encode($existingSections) ?>;
        existingData.forEach(sec => addModule(sec));
        recalculateTotalDuration();
    });
<?php endif; ?>

document.getElementById('addModuleBtn').addEventListener('click', () => addModule());

// --- DURATION & QUIZ LOGIC (Simplified from original) ---
function recalculateTotalDuration() {
    let total = 0;
    document.querySelectorAll('.mat-duration-hidden').forEach(input => total += parseInt(input.value || 0));
    const h = Math.floor(total / 3600), m = Math.floor((total % 3600) / 60);
    document.getElementById('durationInput').value = `${h}h ${m}m`;
}

// ... (Rest of Quiz Logic remains similar to original but needs to handle existing data)
function renderExistingQuestion(matId, qData) {
    const qIdx = (window.qCounters = window.qCounters || {})[matId] = (window.qCounters[matId] || 0) + 1;
    const container = document.getElementById(`quiz_questions_${matId}`);
    const html = `<div class="quiz-question-block" id="qq_${matId}_${qIdx}">
        <input type="text" class="input-styled quiz-q-text" value="${qData.question}" placeholder="Question" required>
        <div class="quiz-options-area" id="opts_${matId}_${qIdx}"></div>
        <button type="button" class="add-option-btn" onclick="addOption('${matId}', '${qIdx}')">+ Option</button>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    qData.options.forEach(opt => {
        const isCorrect = (opt === qData.answer);
        addOption(matId, qIdx, opt, isCorrect);
    });
}

function addOption(matId, qIdx, text = "", isCorrect = false) {
    const key = `${matId}_${qIdx}`;
    const oIdx = (window.oCounters = window.oCounters || {})[key] = (window.oCounters[key] || 0) + 1;
    const container = document.getElementById(`opts_${matId}_${qIdx}`);
    const html = `<div class="quiz-option-row">
        <input type="radio" name="correct_${matId}_${qIdx}" ${isCorrect ? 'checked' : ''}>
        <input type="text" class="quiz-opt-text" value="${text}" placeholder="Option" required style="flex:1;">
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

document.getElementById('courseForm').addEventListener('submit', function() {
    document.querySelectorAll('.quiz-block').forEach(block => {
        const matId = block.id.replace('mat_', '');
        const questions = [];
        block.querySelectorAll('.quiz-question-block').forEach(qBlock => {
            const qText = qBlock.querySelector('.quiz-q-text').value;
            const options = []; let answer = '';
            qBlock.querySelectorAll('.quiz-option-row').forEach(optRow => {
                const optText = optRow.querySelector('.quiz-opt-text').value;
                options.push(optText);
                if (optRow.querySelector('input[type="radio"]').checked) answer = optText;
            });
            if (qText) questions.push({ question: qText, options: options, answer: answer });
        });
        const jsonField = document.getElementById(`quiz_json_${matId}`);
        if(jsonField) jsonField.value = JSON.stringify(questions);
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>
