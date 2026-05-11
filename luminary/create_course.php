<?php
require_once 'includes/init.php';
requireLogin();

if ($currentUser['role'] !== 'mentor') {
    redirect('dashboard.php');
}

require_once 'includes/upload.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? ''); // Comma separated tags
    $level = trim($_POST['level'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $duration = trim($_POST['duration'] ?? '');
    
    // Default thumbnail logic
    $thumbnailUrl = '🎨'; 

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

        // Insert Course
        $stmt = $pdo->prepare("INSERT INTO courses (mentor_id, title, description, category, level, price, thumbnail, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$currentUser['id'], $title, $desc, $category, $level, $price, $thumbnailUrl, $duration]);
        $courseId = $pdo->lastInsertId();

        // Process Sections and Materials
        if (isset($_POST['sections']) && is_array($_POST['sections'])) {
            $orderIndex = 0;
            foreach ($_POST['sections'] as $sec) {
                $secTitle = trim($sec['title'] ?? "Untitled Section");
                
                // Insert Section
                $stmtSec = $pdo->prepare("INSERT INTO course_sections (course_id, title, order_index) VALUES (?, ?, ?)");
                $stmtSec->execute([$courseId, $secTitle, $orderIndex]);
                $sectionId = $pdo->lastInsertId();
                $orderIndex++;

                if (isset($sec['materials']) && is_array($sec['materials'])) {
                    foreach ($sec['materials'] as $mat) {
                        $matTitle = trim($mat['title'] ?? "Untitled Material");
                        $matTypeRaw = $mat['type'] ?? 'file';

                        if ($matTypeRaw === 'quiz') {
                            // Handle Quiz JSON
                            $quizJson = trim($mat['quiz_json'] ?? '[]');
                            // Validate JSON
                            json_decode($quizJson);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                throw new Exception("Invalid JSON provided for quiz: $matTitle");
                            }

                            $stmtMat = $pdo->prepare("INSERT INTO course_materials (section_id, type, title, content) VALUES (?, 'quiz', ?, ?)");
                            $stmtMat->execute([$sectionId, $matTitle, $quizJson]);

                        } else {
                            // Handle File Upload
                            $fileKey = $mat['file_key'] ?? '';
                            if ($fileKey && isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
                                $uploadResult = uploadFile($_FILES[$fileKey]);
                                if (isset($uploadResult['error'])) {
                                    throw new Exception("Material Upload Error ($matTitle): " . $uploadResult['error']);
                                }

                                // Determine exact type based on mime
                                $mime = $_FILES[$fileKey]['type'];
                                $dbType = 'pdf';
                                if (strpos($mime, 'video') !== false) $dbType = 'video';
                                if (strpos($mime, 'image') !== false) $dbType = 'image';

                                $matDuration = (int)($mat['duration'] ?? 0);

                                $stmtMat = $pdo->prepare("INSERT INTO course_materials (section_id, type, title, file_path, duration) VALUES (?, ?, ?, ?, ?)");
                                $stmtMat->execute([$sectionId, $dbType, $matTitle, $uploadResult['path'], $matDuration]);
                            }
                        }
                    }
                }
            }
        }

        $pdo->commit();
        $success = "Course created successfully!";
        // Redirect to mentor dashboard on success to avoid double submission
        redirect('mentor_dashboard.php');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

$pageTitle = "Create New Course";
require_once 'includes/header.php';
?>

<style>
/* Builder UI Styles */
.builder-module {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}
.builder-module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--border);
    padding-bottom: 0.75rem;
}
.builder-module-header h4 { margin: 0; color: var(--gold); font-size: 1.2rem;}
.material-block {
    background: var(--bg-base);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    position: relative;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}
.material-header {
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    font-weight: 600;
    color: var(--text-base);
    margin-bottom: 0.25rem;
}
.quiz-block textarea {
    min-height: 100px;
    font-family: monospace;
}
.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
}
.remove-btn {
    color: var(--rust);
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    cursor: pointer;
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    transition: all 0.2s;
}
.remove-btn:hover { 
    background: rgba(239, 68, 68, 0.2);
}
.input-styled {
    width: 100%; 
    padding: 0.85rem; 
    border-radius: 6px; 
    border: 1px solid var(--border); 
    background: rgba(255,255,255,0.03); 
    color: var(--text-base); 
    font-size: 0.95rem;
    transition: border 0.2s;
    box-sizing: border-box;
}
.input-styled:focus {
    border-color: var(--gold);
    outline: none;
}
.input-file-styled {
    width: 100%; 
    padding: 0.85rem; 
    border-radius: 6px; 
    border: 1px dashed var(--border); 
    background: rgba(255,255,255,0.02); 
    color: var(--text-base); 
    cursor: pointer;
}

/* Quiz Builder */
.quiz-question-block {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
}
.quiz-question-block .q-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}
.quiz-question-block .q-header span {
    font-weight: 600;
    color: var(--gold);
    font-size: 0.85rem;
}
.quiz-option-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}
.quiz-option-row input[type="text"] {
    flex: 1;
    padding: 0.6rem;
    border-radius: 4px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.03);
    color: var(--text-base);
    font-size: 0.9rem;
}
.quiz-option-row input[type="text"]:focus {
    border-color: var(--gold);
    outline: none;
}
.quiz-option-row input[type="radio"] {
    accent-color: var(--gold);
    width: 16px;
    height: 16px;
    cursor: pointer;
}
.quiz-option-row label {
    font-size: 0.8rem;
    color: var(--text-muted);
    cursor: pointer;
}
.add-option-btn {
    background: none;
    border: 1px dashed var(--border);
    color: var(--text-muted);
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.2s;
}
.add-option-btn:hover {
    border-color: var(--gold);
    color: var(--gold);
}
.add-question-btn {
    width: 100%;
    background: none;
    border: 1px dashed var(--border);
    color: var(--text-muted);
    padding: 0.6rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
    margin-top: 0.5rem;
    transition: all 0.2s;
}
.add-question-btn:hover {
    border-color: var(--gold);
    color: var(--gold);
}

/* Tag Input Styles */
.tags-input-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.5rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--bg-surface);
}
.tag-badge {
    background: var(--gold);
    color: #000;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}
.tag-badge:hover { opacity: 0.8; }
.tags-input-wrapper input {
    border: none;
    background: transparent;
    outline: none;
    color: var(--text-base);
    flex: 1;
    min-width: 150px;
    padding: 0;
}
</style>

<div class="dashboard-layout">
  <aside class="dashboard-sidebar">
    <div class="sidebar-greeting">Welcome back,</div>
    <div class="sidebar-name"><?= htmlspecialchars($currentUser['username']) ?></div>
    <ul class="sidebar-nav">
      <li><a href="mentor_dashboard.php">My Courses</a></li>
      <li><a href="create_course.php" class="active">Create Course</a></li>
      <li><a href="logout.php" style="color:var(--rust);">Logout</a></li>
    </ul>
  </aside>

  <main class="dashboard-main">
    <div class="dash-header">
      <h2>Create New Course (Advanced)</h2>
      <p>Use the dynamic curriculum builder to architect your entire course.</p>
    </div>

    <?php if ($error): ?>
      <div style="background:var(--rust); color:white; padding:1rem; border-radius:6px; margin-bottom:1.5rem;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div style="background:var(--sage); color:white; padding:1rem; border-radius:6px; margin-bottom:1.5rem;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="courseForm" style="background:var(--bg-elevated); padding:2rem; border-radius:10px; border:1px solid var(--border); max-width:800px;">
      
      <!-- Basic Details -->
      <div class="form-row">
        <div class="form-group">
          <label>Course Title *</label>
          <input type="text" name="title" required placeholder="e.g. Advanced PHP Mastery">
        </div>
        <div class="form-group">
          <label>Custom Thumbnail Image</label>
          <input type="file" name="thumbnail_file" accept="image/*" style="padding:0.5rem; background:var(--bg-base);">
        </div>
      </div>

      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" required placeholder="Detailed course description..." style="min-height: 100px;"></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Tags / Category *</label>
          <input type="hidden" name="category" id="categoryInput" required>
          <div class="tags-input-wrapper" id="tagsContainer">
            <input type="text" id="tagInput" placeholder="Type a tag and press Enter...">
          </div>
        </div>
        <div class="form-group">
          <label>Level</label>
          <select name="level">
            <option>Beginner</option>
            <option>Intermediate</option>
            <option>Advanced</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Price ($)</label>
          <input type="number" step="0.01" name="price" value="0.00">
        </div>
        <div class="form-group">
          <label>Calculated Duration</label>
          <input type="text" name="duration" id="durationInput" readonly placeholder="Calculated automatically from videos" style="background: var(--bg-base); cursor: not-allowed;">
        </div>
      </div>

      <hr style="border:0; border-top:1px solid var(--border); margin:2rem 0;">
      
      <!-- Dynamic Curriculum Builder -->
      <div class="dash-section-title">Curriculum Builder</div>
      <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">Add modules, upload videos, and attach JSON quizzes dynamically.</p>
      
      <div id="curriculumBuilder"></div>

      <button type="button" id="addModuleBtn" class="btn btn-outline" style="width: 100%; border-style: dashed; margin-bottom: 2rem;">+ Add New Module</button>

      <button type="submit" class="btn btn-gold" style="width: 100%;">Publish Complete Course</button>
    </form>
  </main>
</div>

<script>
// --- TAGS SYSTEM ---
const tagInput = document.getElementById('tagInput');
const tagsContainer = document.getElementById('tagsContainer');
const categoryInput = document.getElementById('categoryInput');
let tags = [];

tagInput.addEventListener('keydown', function(e) {
    if(e.key === 'Enter') {
        e.preventDefault();
        const tag = this.value.trim();
        if(tag && !tags.includes(tag)) {
            tags.push(tag);
            renderTags();
        }
        this.value = '';
    }
});

function renderTags() {
    document.querySelectorAll('.tag-badge').forEach(e => e.remove());
    tags.forEach(t => {
        const span = document.createElement('span');
        span.className = 'tag-badge';
        span.textContent = t;
        span.style.cursor = 'pointer';
        span.onclick = () => { tags = tags.filter(x => x !== t); renderTags(); };
        tagsContainer.insertBefore(span, tagInput);
    });
    categoryInput.value = tags.join(', ');
}

// --- DYNAMIC DURATION CALCULATION ---
let totalSeconds = 0;
function addDuration(seconds) {
    totalSeconds += seconds;
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    document.getElementById('durationInput').value = `${h}h ${m}m`;
}

document.addEventListener('change', function(e) {
    if(e.target && e.target.classList.contains('material-file')) {
        const file = e.target.files[0];
        const matBlock = e.target.closest('.material-block');
        const durationField = matBlock ? matBlock.querySelector('.mat-duration-hidden') : null;
        
        if(file && file.type.startsWith('video/')) {
            const video = document.createElement('video');
            video.preload = 'metadata';
            video.onloadedmetadata = function() {
                window.URL.revokeObjectURL(video.src);
                const seconds = Math.round(video.duration);
                if (durationField) durationField.value = seconds;
                recalculateTotalDuration();
            };
            video.src = URL.createObjectURL(file);
        }
    }
});

function recalculateTotalDuration() {
    let total = 0;
    document.querySelectorAll('.mat-duration-hidden').forEach(input => {
        total += parseInt(input.value || 0);
    });
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    document.getElementById('durationInput').value = `${h}h ${m}m`;
}

// --- CURRICULUM BUILDER ---
let moduleCount = 0;
document.getElementById('addModuleBtn').addEventListener('click', () => {
    const builder = document.getElementById('curriculumBuilder');
    const modId = moduleCount++;
    const modHTML = `
      <div class="builder-module" id="mod_${modId}">
        <div class="builder-module-header">
            <h4>Module ${modId + 1}</h4>
            <button type="button" class="remove-btn" onclick="document.getElementById('mod_${modId}').remove()">Remove</button>
        </div>
        <input type="text" name="sections[${modId}][title]" class="input-styled" placeholder="Enter Module Title (e.g. Introduction)" required style="margin-bottom:1rem; font-weight:600; font-size:1.1rem;">
        
        <div id="materials_${modId}"></div>
        
        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="button" class="btn btn-outline btn-small" onclick="addMaterial(${modId}, 'file')">+ Add Media (Video/PDF/Image)</button>
            <button type="button" class="btn btn-outline btn-small" onclick="addMaterial(${modId}, 'quiz')">+ Add Quiz</button>
        </div>
      </div>
    `;
    builder.insertAdjacentHTML('beforeend', modHTML);
});

function addMaterial(modId, type) {
    const container = document.getElementById(`materials_${modId}`);
    // Use timestamp for unique mat id to prevent collisions if items are removed
    const matId = Date.now() + Math.floor(Math.random() * 1000); 
    
    let html = '';
    if (type === 'file') {
        html = `
            <div class="material-block" id="mat_${matId}">
                <div class="material-header">
                    <span>🎬 Media Lesson</span>
                    <button type="button" class="remove-btn btn-small" onclick="document.getElementById('mat_${matId}').remove()">Remove</button>
                </div>
                <input type="text" name="sections[${modId}][materials][${matId}][title]" class="input-styled" placeholder="Lesson Title" required>
                <input type="file" name="material_file_${modId}_${matId}" class="material-file input-file-styled" required>
                <input type="hidden" name="sections[${modId}][materials][${matId}][file_key]" value="material_file_${modId}_${matId}">
                <input type="hidden" name="sections[${modId}][materials][${matId}][type]" value="file">
                <input type="hidden" name="sections[${modId}][materials][${matId}][duration]" class="mat-duration-hidden" value="0">
            </div>
        `;
    } else if (type === 'quiz') {
        html = `
            <div class="material-block quiz-block" id="mat_${matId}">
                <div class="material-header">
                    <span>📝 Quiz Assessment</span>
                    <button type="button" class="remove-btn btn-small" onclick="document.getElementById('mat_${matId}').remove()">Remove</button>
                </div>
                <input type="text" name="sections[${modId}][materials][${matId}][title]" class="input-styled" placeholder="Quiz Title (e.g. Module 1 Review)" required>
                <textarea name="sections[${modId}][materials][${matId}][quiz_json]" id="quiz_json_${matId}" style="display:none;"></textarea>
                <input type="hidden" name="sections[${modId}][materials][${matId}][type]" value="quiz">
                <div id="quiz_questions_${matId}"></div>
                <button type="button" class="add-question-btn" onclick="addQuestion('${matId}')">+ Add Question</button>
            </div>
        `;
    }
    container.insertAdjacentHTML('beforeend', html);
}

// --- VISUAL QUIZ BUILDER ---
let questionCounters = {};

function addQuestion(matId) {
    if (!questionCounters[matId]) questionCounters[matId] = 0;
    const qIdx = questionCounters[matId]++;
    const container = document.getElementById(`quiz_questions_${matId}`);
    const html = `
        <div class="quiz-question-block" id="qq_${matId}_${qIdx}">
            <div class="q-header">
                <span>Question ${qIdx + 1}</span>
                <button type="button" class="remove-btn" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="document.getElementById('qq_${matId}_${qIdx}').remove()">✕</button>
            </div>
            <input type="text" class="input-styled quiz-q-text" data-mat="${matId}" placeholder="Enter your question..." required style="margin-bottom:0.75rem;">
            <div class="quiz-options-area" id="opts_${matId}_${qIdx}"></div>
            <button type="button" class="add-option-btn" onclick="addOption('${matId}', '${qIdx}')">+ Add Option</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    // Auto-add 2 starter options
    addOption(matId, qIdx);
    addOption(matId, qIdx);
}

let optionCounters = {};
function addOption(matId, qIdx) {
    const key = `${matId}_${qIdx}`;
    if (!optionCounters[key]) optionCounters[key] = 0;
    const oIdx = optionCounters[key]++;
    const container = document.getElementById(`opts_${matId}_${qIdx}`);
    const radioName = `correct_${matId}_${qIdx}`;
    const html = `
        <div class="quiz-option-row" id="opt_${key}_${oIdx}">
            <input type="radio" name="${radioName}" value="${oIdx}" class="quiz-correct-radio" data-mat="${matId}">
            <label style="white-space:nowrap;">Correct</label>
            <input type="text" class="quiz-opt-text" data-mat="${matId}" placeholder="Option ${oIdx + 1}" required>
            <button type="button" class="remove-btn" style="padding:0.2rem 0.4rem; font-size:0.7rem;" onclick="document.getElementById('opt_${key}_${oIdx}').remove()">✕</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// Before form submit, serialize quiz GUI into JSON
document.getElementById('courseForm').addEventListener('submit', function() {
    document.querySelectorAll('.quiz-block').forEach(block => {
        const matId = block.id.replace('mat_', '');
        const jsonField = document.getElementById(`quiz_json_${matId}`);
        if (!jsonField) return;
        const questions = [];
        block.querySelectorAll('.quiz-question-block').forEach(qBlock => {
            const qText = qBlock.querySelector('.quiz-q-text').value;
            const options = [];
            let answer = '';
            qBlock.querySelectorAll('.quiz-option-row').forEach(optRow => {
                const optText = optRow.querySelector('.quiz-opt-text').value;
                const radio = optRow.querySelector('.quiz-correct-radio');
                options.push(optText);
                if (radio.checked) answer = optText;
            });
            if (qText) questions.push({ question: qText, options: options, answer: answer });
        });
        jsonField.value = JSON.stringify(questions);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
